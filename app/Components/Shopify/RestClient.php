<?php

namespace App\Components\Shopify;

use App\Exceptions\ShopifyException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Shopify\Clients\Rest;
use Shopify\Clients\RestResponse;

/**
 * RestClient class
 *
 * @package App\Components\Shopify
 */
class RestClient extends Rest
{
    public const TIMEOUT_KEY = 'shopify_timeout';

    public const CALLS_IN_SECONDS = 2;

    /**
     * @param string   $path
     * @param string   $method
     * @param null     $body
     * @param array    $headers
     * @param array    $query
     * @param int|null $tries
     * @param string   $dataType
     *
     * @return RestResponse
     * @throws \App\Exceptions\ShopifyException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \Shopify\Exception\UninitializedContextException
     */
    protected function request(
        string $path,
        string $method,
        $body = null,
        array $headers = [],
        array $query = [],
        ?int $tries = null,
        string $dataType = self::DATA_TYPE_JSON
    ): RestResponse {
        while (($slotName = $this->getFreeSlot()) === null) {
            usleep(1000 * 100);
        }
        Redis::set($slotName, 1, 'EX', 1);

        $request = parent::request($path, $method, $body, $headers, $query, $tries, $dataType);
        $body = $request->getDecodedBody();
        $statusCode = $request->getStatusCode();

        if (!empty($body['errors'])) {
            $errorMessage = is_array($body['errors']) ? ($body['errors'][0]['message'] ?? null) : $body['errors'];
            Log::error($errorMessage . "\n" . 'Path: ' . $path . ' Body: ' . print_r($body, 1));
            if ($statusCode >= 400 && $statusCode < 500 && $statusCode !== 423) {
                Log::error('[Shopify Non-Retryable Error]: ' . print_r($errorMessage, 1));

                return $request;
            } else {
                if (($statusCode >= 500 && $statusCode < 600) || $statusCode === 423) {
                    Log::error('[Shopify Retryable Error]: ' . print_r($errorMessage, 1));
                    throw new ShopifyException('[Shopify Retryable Error]: ' . print_r($errorMessage, 1));
                }
            }
        }

        return $request;
    }

    /**
     *
     * @return string|null
     */
    private function getFreeSlot(): ?string
    {
        for ($i = 0; $i < self::CALLS_IN_SECONDS; $i++) {
            $key = self::TIMEOUT_KEY . "_$i";
            ${$key} = Redis::get($key);
            if (${$key} === -2 || ${$key} === null) {
                return $key;
            }
        }

        return null;
    }
}
