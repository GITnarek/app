<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequest;
use App\Models\Store;
use App\Services\ConfigService;
use App\Services\Shopify\WebhookService;
use App\Services\StoreService;
use Illuminate\Http\JsonResponse;

/**
 * OmsController class
 *
 * @package App\Http\Controllers
 */
class OmsController extends Controller
{
    /**
     * @throws \Shopify\Exception\HttpRequestException
     * @throws \Shopify\Exception\MissingArgumentException
     * @throws \JsonException
     */
    public function store(StoreRequest $request, StoreService $storeService, WebhookService $webhookService): JsonResponse
    {
        $domain = $request->post('domain');

        /** @var Store $store */
        $store = Store::query()->firstOrNew(['shop' => $domain]);
        if (!$store->id) {
            $store->shop = $domain;
            $store->version = config('app.version');
        }

        $store->store_id = $request->post('storeId');
        $store->status = (bool)$request->post('status');
        $store->is_tax_prepaid = (bool)$request->post('isTaxPrepaid');
        $store->use_client_sku = (bool)$request->post('useClientSku');
        $store->submit_with_tax = (bool)$request->post('submitWithTax');
        $store->submit_all_items = (bool)$request->post('submitAllItems');
        $store->channel = $request->post('channel');

        $store->save();

        if ($store->access_token && $store->status && !$store->uninstalled) {
            $storeService->activateStore($store);
        } else {
            ConfigService::exposeStore($store);
            $webhookService->switchStore($store)->verifyWebhooks(false);
        }

        return response()->json(['success' => true]);
    }
}
