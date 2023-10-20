<?php

namespace App\Http\Controllers;

use App\Http\Requests\UninstallRequest;
use App\Services\ConfigService;
use App\Services\Shopify\WebhookService;
use App\Services\StoreService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redirect;
use Shopify\Auth\OAuth;
use Exception;
use App\Models\Store;

/**
 * HeartbeatController class
 *
 * @package App\Http\Controllers
 */
class OAuthController extends Controller
{
    /**
     * @throws \Shopify\Exception\CookieSetException
     * @throws \Shopify\Exception\UninitializedContextException
     * @throws \Shopify\Exception\PrivateAppException
     * @throws \Shopify\Exception\SessionStorageException
     * @throws \Exception
     */
    public function install(): \Illuminate\Http\RedirectResponse
    {
        if (!request()->has('shop')) {
            throw new Exception(__('Shop parameter is required'));
        }

        $url = OAuth::begin(
            request()->query('shop'),
            route('oauth.callback', [], false),
            false
        );

        return Redirect::away($url);
    }

    /**
     * @throws \Shopify\Exception\OAuthSessionNotFoundException
     * @throws \Shopify\Exception\UninitializedContextException
     * @throws \Shopify\Exception\PrivateAppException
     * @throws \Shopify\Exception\MissingArgumentException
     * @throws \Shopify\Exception\SessionStorageException
     * @throws \JsonException
     * @throws \Shopify\Exception\HttpRequestException
     * @throws \Shopify\Exception\OAuthCookieNotFoundException
     * @throws \Shopify\Exception\InvalidOAuthException
     */
    public function callback(StoreService $storeService, WebhookService $webhookService): \Illuminate\Http\RedirectResponse
    {
        $queryParams = request()->query();
        $session = OAuth::callback(
            request()->cookie(),
            $queryParams
        );

        /**
         * @var Store|null $store
         */
        $store = Store::query()->firstOrNew(['shop' => $session->getShop()]);
        if (!$store->id) {
            $store->shop = $session->getShop();
            $store->version = config('app.version');
        }
        $store->uninstalled = false;
        $store->status = $store->status ?? false;
        $store->access_token = $session->getAccessToken();
        $store->save();

        if ($store->store_id && $store->status) {
            $storeService->activateStore($store);
        } else {
            ConfigService::exposeStore($store);
            $webhookService->switchStore($store)->verifyWebhooks(false);
        }

        return Redirect::route('home', $queryParams);
    }

    public function uninstall(UninstallRequest $request)
    {
        $domain = $request->post('domain');
        $store = Store::where(['shop' => $domain])->first();
        if ($store && $store->id && $store->updated_at < Carbon::now()->subMinutes(15)) {
            $store->uninstalled = true;
            $store->save();
            ConfigService::exposeStore($store);
        }

        return response()->json(['success' => true]);
    }
}
