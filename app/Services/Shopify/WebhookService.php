<?php

namespace App\Services\Shopify;

use App\Components\Shopify\Query\Webhooks as WebhooksQuery;
use App\Components\Shopify\Mutation\Webhook\Create as CreateWebhook;
use App\Components\Shopify\Mutation\Webhook\Delete as DeleteWebhook;
use App\Services\LogService;
use Throwable;
use App\Models\Store;
use Shopify\Webhooks\Topics;

/**
 * WebhookService class
 *
 * @package App\Services\Shopify
 */
class WebhookService
{
    private const WEBHOOK_URI = '/webhook';

    public ShopifyService $shopifyService;

    private array $webhooks;

    /**
     * @param ShopifyService $shopifyService
     */
    public function __construct(
        ShopifyService $shopifyService
    ) {
        $this->shopifyService = $shopifyService;
        $this->webhooks = [
            Topics::ORDERS_CREATE => config('mt.services.s2oms.webhook_receiver'),
            Topics::ORDERS_UPDATED => config('mt.services.s2oms.webhook_receiver'),
            Topics::ORDERS_CANCELLED => config('mt.services.s2oms.webhook_receiver'),
            Topics::ORDERS_FULFILLED => config('mt.services.s2oms.webhook_receiver'),
            Topics::ORDERS_PARTIALLY_FULFILLED => config('mt.services.s2oms.webhook_receiver'),
            Topics::REFUNDS_CREATE => config('mt.services.s2oms.webhook_receiver'),
            Topics::APP_UNINSTALLED => config('mt.services.s2oms.webhook_receiver'),
        ];
    }

    /**
     * @param Store|string $store
     * @return $this
     */
    public function __invoke(Store|string $store): static
    {
        $this->switchStore($store);

        return $this;
    }

    /**
     * @param Store|string $store
     * @return $this
     */
    public function switchStore(Store|string $store): static
    {
        $this->shopifyService->switchStore($store->shop);

        return $this;
    }

    /**
     * @return array
     */
    public function getWebhooks(): array
    {
        try {
            $response = $this->shopifyService->getClient()->query(WebhooksQuery::build(50))->getDecodedBody();

            return $response['data']['webhookSubscriptions']['nodes'] ?? [];
        } catch (Throwable $throwable) {
            LogService::error($throwable);
        }

        return [];
    }

    /**
     * @param string $topic
     * @param string $url
     * @param array $includeFields
     * @param array $metafieldNamespaces
     * @param array $privateMetafieldNamespaces
     * @return array
     */
    public function createWebhook(
        string $topic,
        string $url,
        array $includeFields = [],
        array $metafieldNamespaces = [],
        array $privateMetafieldNamespaces = []
    ): array {
        try {
            $query = CreateWebhook::build($topic, $url, $includeFields, $metafieldNamespaces, $privateMetafieldNamespaces);
            $response = $this->shopifyService->getClient()->query($query)->getDecodedBody();
            if (!empty($response['data']['webhookSubscription']['userErrors'])) {
                throw new \Exception('Webhook Registration Failure : ' . $topic . ' '
                    . ($response['data']['webhookSubscription']['userErrors'][0]['message'] ?? 'Unknown error'));
            }

            return $response['data']['webhookSubscription'] ?? [];
        } catch (Throwable $throwable) {
            LogService::error($throwable);
        }

        return [];
    }

    /**
     * @param string $id
     * @return string|null
     */
    public function deleteWebhook(string $id): string|null
    {
        try {
            $response = $this->shopifyService->getClient()->query(DeleteWebhook::build($id))->getDecodedBody();

            return $response['data']['deletedWebhookSubscriptionId'] ?? null;
        } catch (Throwable $throwable) {
            LogService::error($throwable);
        }

        return null;
    }

    /**
     * @param bool $active
     * @return void
     */
    public function verifyWebhooks(bool $active = true): void
    {
        $webhooks = $this->getWebhooks();
        foreach ($this->webhooks as $topic => $url) {
            if (!$active && $topic !== Topics::APP_UNINSTALLED) {
                continue;
            }

            $exists = false;
            foreach ($webhooks as $key => $webhook) {
                if ($webhook['topic'] === $topic) {
                    $exists = true;
                    unset($webhooks[$key]);
                    break;
                }
            }
            if (!$exists) {
                $this->createWebhook($topic, $url . self::WEBHOOK_URI);
            }
        }

        foreach ($webhooks as $webhook) {
            $this->deleteWebhook($webhook['id']);
        }
    }
}
