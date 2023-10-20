<?php

namespace App\Services;

use App\Components\Shopify\IdHelper;
use App\Components\Shopify\Mutation\FulfillmentServiceCreate as FulfillmentServiceCreateMutation;
use App\Components\Shopify\Query\FulfillmentService as FulfillmentServiceQuery;
use App\Models\Store;
use App\Services\Shopify\Carrier\CarrierService;
use App\Services\Shopify\ShopifyService;
use App\Services\Shopify\WebhookService;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Shopify\Exception\ShopifyException;

class StoreService
{
    public function __construct(
        private readonly WebhookService $webhookService,
        private readonly CarrierService $carrierService,
        private readonly ShopifyService $shopifyService
    ) {
    }

    /**
     * @return Collection|Store[]
     */
    public function getStores(?int $status = null, array $shops = []): Collection|array
    {
        $query = Store::query();

        if (!is_null($status)) {
            $query->where('status', $status);
        }

        if (!empty($shops)) {
            $query->whereIn('shop', $shops);
        }

        return $query->get();
    }

    /**
     * @param \App\Models\Store $store
     *
     * @return void
     * @throws \JsonException
     * @throws \Shopify\Exception\HttpRequestException
     * @throws \Shopify\Exception\MissingArgumentException
     * @throws \Shopify\Exception\ShopifyException
     */
    public function activateStore(Store $store): void
    {
        ConfigService::exposeStore($store);
        $this->prepareCarrierService($store);
        $this->prepareWebhookService($store);
        $this->prepareFulfillmentService($store);
        $this->updateInventoryLevel($store);

        $store->save();
        ConfigService::exposeStore($store);
    }

    protected function prepareCarrierService(Store $store): void
    {
        $this->carrierService->prepareCarrierService($store);
    }

    protected function prepareWebhookService(Store $store): void
    {
        $this->webhookService->switchStore($store)->verifyWebhooks($store->status);
    }

    /**
     * @param \App\Models\Store $store
     *
     * @return void
     * @throws \JsonException
     * @throws \Shopify\Exception\HttpRequestException
     * @throws \Shopify\Exception\MissingArgumentException
     * @throws \Shopify\Exception\ShopifyException
     */
    protected function prepareFulfillmentService(Store $store): void
    {
        if ($store->fulfillment_service_id) {
            $responseBody = $this->shopifyService
                ->getClient($store->shop)
                ->query(FulfillmentServiceQuery::build($store->fulfillment_service_id))
                ->getDecodedBody();

            if (!$responseBody['data']['fulfillmentService']) {
                $store->fulfillment_service_id = null;
            }
        }

        if (!$store->fulfillment_service_id) {
            $this->createFulfillmentService($store);
        }
    }

    /**
     * @throws \Shopify\Exception\HttpRequestException
     * @throws \Shopify\Exception\MissingArgumentException
     * @throws \JsonException
     * @throws \Shopify\Exception\ShopifyException
     */
    protected function createFulfillmentService(Store $store): void
    {
        $responseBody = $this->shopifyService
            ->getClient($store->shop)
            ->query(FulfillmentServiceCreateMutation::build([
                'name' => config('mt.services.fulfillment_service_name'),
                'callbackUrl' => config('mt.services.s2oms.webhook_receiver'),
            ]))
            ->getDecodedBody();

        if (!empty($responseBody['data']['fulfillmentServiceCreate']['userErrors'])) {
            Log::error(
                'Failed to create fulfillment service',
                $responseBody['data']['fulfillmentServiceCreate']['userErrors']
            );

            $messages = array_column($responseBody['data']['fulfillmentServiceCreate']['userErrors'], 'message');

            throw new ShopifyException('Failed to create fulfillment service: ' . implode(',', $messages));
        }

        $fulfillmentService = $responseBody['data']['fulfillmentServiceCreate']['fulfillmentService'];

        $store->fulfillment_service_id = IdHelper::extract($fulfillmentService['id']);
        $store->location_id = IdHelper::extract($fulfillmentService['location']['id']);
    }

    /**
     * @param Store $store
     *
     * @return void
     */
    protected function updateInventoryLevel(Store $store)
    {
        $url = config('mt.services.oms2s.webhook_receiver') . '/api/inventory-level';
        $headers = ['Authorization' => config('mt.oms2s.verification_key')];

        try {
            Http::withHeaders($headers)
                ->post($url, [
                    'body' => json_encode(['storeId' => $store->store_id]),
                ]);
        } catch (Exception $e) {
            Log::error('updateInventoryLevel: ' . $e->getMessage());
        }

    }

    /**
     * @param string $domain
     * @return string
     */
    public static function getAppUrl(string $domain): string
    {
        return 'https://admin.shopify.com/store/'
            . str_replace(ConfigService::SHOPIFY_DOMAIN, '', $domain)
            . '/apps/' . config('shopify.client_id');
    }
}
