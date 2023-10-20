<?php

namespace App\Components\Shopify;

use App\Components\Shopify\DTO\Cost;
use App\Exceptions\ShopifyException;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Shopify\Clients\Graphql;
use Shopify\Clients\HttpResponse;
use Shopify\Exception\MissingArgumentException;

/**
 * Class GraphqlClient
 *
 * @package App\Components\Shopify
 */
class GraphqlClient extends Graphql
{
    public const TIMEOUT_PREFIX = 'shopify_graphql_timeout_';

    public const MAX_DELAY = 1000;

    public const DEFAULT_TRIES = 3;

    public const THROTTLED = 'THROTTLED';

    public const ACCESS_DENIED = 'ACCESS_DENIED';

    public const SHOP_INACTIVE = 'SHOP_INACTIVE';

    public const INTERNAL_SERVER_ERROR = 'INTERNAL_SERVER_ERROR';

    /**
     * @var string
     */
    protected string $store;

    /**
     * @param string $domain
     * @param string $token
     *
     * @throws MissingArgumentException
     */
    public function __construct(
        string $domain,
        string $token
    ) {
        parent::__construct($domain, $token);

        $this->store = $domain;
    }

    /**
     * Sends a GraphQL query to this client's domain.
     *
     * @param string|array $data         Query to be posted to endpoint
     * @param array        $query        Parameters on a query to be added to the URL
     * @param array        $extraHeaders Any extra headers to send along with the request
     * @param int|null     $tries        How many times to attempt the request
     *
     * @return HttpResponse
     * @throws \Shopify\Exception\HttpRequestException
     * @throws \Shopify\Exception\MissingArgumentException
     * @throws ShopifyException|\JsonException
     * @throws \Exception
     */
    public function query(
        $data,
        array $query = [],
        array $extraHeaders = [],
        ?int $tries = self::DEFAULT_TRIES,
    ): HttpResponse {
        while (Redis::get($this->timeoutKey()) > 0) {
            usleep(1000 * 100);
        }
        $response = parent::query($data, $query, $extraHeaders, $tries);
        $body = $response->getDecodedBody();
        $statusCode = $response->getStatusCode();

        [$errorCode, $errorMessage] = $this->getError($body);
        if ($errorCode && $tries) {
            Log::error($errorMessage . "\n" . 'Query: ' . print_r($data, 1) . ' Try: ' . $tries);
            $tries--;

            if ($errorCode === self::THROTTLED) {
                $cost = new Cost($body['extensions']['cost'] ?? []);
                $msDelay = $this->calculateDelayCost($cost);
                if ($msDelay === null) {
                    throw new Exception($message ?? ('Error while calculating delay cost: ' . print_r($data, 1)));
                }
                Redis::set($this->timeoutKey(), '1', 'PX', $msDelay ?: self::MAX_DELAY);
            } else if (
                ($statusCode >= 400 && $statusCode < 500 && $statusCode !== 423)
                || $errorCode === self::ACCESS_DENIED
                || $errorCode === self::SHOP_INACTIVE
            ) {
                Log::error('[Shopify Non-Retryable Error]: ' . print_r($errorMessage, 1));

                Log::channel('amqp')->error('Shopify Non-Retryable Error', [
                    'service' => config('app.name'),
                    'query' => json_encode($data, JSON_PRETTY_PRINT),
                    'response' => ['code' => $statusCode, 'message' => $errorMessage],
                ]);

                return $response;
            } else if (
                $errorCode === self::INTERNAL_SERVER_ERROR
                || ($statusCode >= 500 && $statusCode < 600)
                || $statusCode === 423
            ) {
                Log::error('[Shopify Retryable Error]: ' . print_r($errorMessage, 1));
                throw new ShopifyException('[Shopify Retryable Error]: ' . print_r($errorMessage, 1));
            }

            return $this->query($data, $query, $extraHeaders, $tries);
        }

        return $response;
    }

    /**
     * @param array $body
     *
     * @return array
     */
    private function getError(array $body): array
    {
        $errorCode = $errorMessage = null;
        if (!empty($body['errors'])) {
            $errorCode = $errors[0]['extensions']['code'] ?? self::INTERNAL_SERVER_ERROR;
            $errorMessage = is_array($body['errors']) ? ($body['errors'][0]['message'] ?? null) : $body['errors'];
        } else if (!empty($body['data'])) {
            foreach ($body['data'] as $query) {
                if (!empty($query['userErrors'])) {
                    $errorCode = self::INTERNAL_SERVER_ERROR;
                    $errorMessage = $query['userErrors'][0]['message'] ?? '';
                }
            }
        }

        return [$errorCode, $errorMessage];
    }

    /**
     * @param Cost $cost
     *
     * @return int|null
     */
    private function calculateDelayCost(Cost $cost): ?int
    {
        $requested = $cost->actualQueryCost ?? $cost->requestedQueryCost;
        $restoreAmount = max(0, $requested - $cost->throttleStatus->currentlyAvailable);

        return $cost->throttleStatus->restoreRate ?
            (ceil($restoreAmount / $cost->throttleStatus->restoreRate) * 1000) :
            null;
    }

    /**
     * @return string
     */
    private function timeoutKey(): string
    {
        return self::TIMEOUT_PREFIX . $this->store;
    }
}
