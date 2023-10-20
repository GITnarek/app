<?php

namespace App\Services;

use App\Models\Store;
use GuzzleHttp\Utils;
use Illuminate\Support\Facades\Redis;
use League\Flysystem\Exception;

class ConfigService
{
    public const SHOPIFY_DOMAIN = '.myshopify.com';

    public const SHOPIFY_CONTEXT = 'shopify_context';

    public const SHOPIFY_STORE_PREFIX = 'shopify_store_';

    public const OMS_STORE_PREFIX = 'oms_store_';

    public const OMS_CONTEXT = 'oms_context';

    /**
     * @return void
     */
    public static function expose(): void
    {
        self::exposeShopify();
        self::exposeOms();

        foreach (Store::all() as $store) {
            self::exposeStore($store);
        }
    }

    protected static function exposeOms(): void
    {
        Redis::set(self::OMS_CONTEXT, Utils::jsonEncode([
            'apiUser' => config('mt.oms.api_user'),
            'apiKey' => config('mt.oms.api_key'),
            'apiUrl' => config('mt.oms.api_url'),
        ]));
    }

    protected static function exposeShopify(): void
    {
        Redis::set(self::SHOPIFY_CONTEXT, Utils::jsonEncode([
            'apiKey' => config('shopify.client_id'),
            'apiSecretKey' => config('shopify.client_secret'),
            'scopes' => config('shopify.api_scopes'),
            'hostName' => config('app.url'),
            'apiVersion' => config('shopify.api_version'),
        ]));
    }

    /**
     * @param \App\Models\Store $store
     *
     * @return void
     */
    public static function exposeStore(Store $store): void
    {
        Redis::set(self::SHOPIFY_STORE_PREFIX . $store->shop, Utils::jsonEncode([
            'store_id' => $store->store_id,
            'domain' => $store->shop,
            'token' => $store->access_token,
            'version' => $store->version,
            'status' => $store->status,
            'carrier_service_id' => $store->carrier_service_id,
            'fulfillment_service_id' => $store->fulfillment_service_id,
            'location_id' => $store->location_id,
            'is_tax_prepaid' => $store->is_tax_prepaid,
            'use_client_sku' => $store->use_client_sku,
            'submit_with_tax' => $store->submit_with_tax,
            'submit_all_items' => $store->submit_all_items,
            'channel' => $store->channel,
            'uninstalled' => $store->uninstalled,
        ]));

        Redis::set(self::OMS_STORE_PREFIX . $store->store_id, $store->shop);
    }

    /**
     * @param Store $store
     * @return void
     */
    public static function deleteStore(Store $store): void
    {
        Redis::del(self::SHOPIFY_STORE_PREFIX . $store->shop);
        if ($store->store_id) {
            Redis::del(self::OMS_STORE_PREFIX . $store->store_id);
        }
    }

    /**
     * @param string $storeDomain
     *
     * @return array
     */
    public static function getStoreConfig(string $storeDomain): array
    {
        $config = Redis::get(self::SHOPIFY_STORE_PREFIX . $storeDomain);
        if (!$config) {
            return [];
        }

        return Utils::jsonDecode($config, true);
    }

    /**
     * @return array
     * @throws Exception
     */
    public static function getShopifyContext(): array
    {
        $context = Redis::get(self::SHOPIFY_CONTEXT);
        if (!$context) {
            throw new Exception('Context config not set');
        }

        return Utils::jsonDecode($context, true);
    }
}
