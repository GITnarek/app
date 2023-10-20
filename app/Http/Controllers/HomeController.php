<?php

namespace App\Http\Controllers;

use App\Components\Shopify\Query\Shop;
use App\Services\ConfigService;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use League\Flysystem\Exception;
use Shopify\Auth\OAuth;
use Shopify\Exception\CookieSetException;
use Shopify\Exception\HttpRequestException;
use Shopify\Exception\MissingArgumentException;
use Shopify\Exception\PrivateAppException;
use Shopify\Exception\SessionStorageException;
use Shopify\Exception\UninitializedContextException;
use Shopify\Utils;
use Shopify\Clients\Graphql;

class HomeController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response|RedirectResponse
     * @throws CookieSetException
     * @throws HttpRequestException
     * @throws MissingArgumentException
     * @throws PrivateAppException
     * @throws SessionStorageException
     * @throws UninitializedContextException
     * @throws Exception
     */
    public function index(Request $request): Response|RedirectResponse
    {
        $data = $request->all();
        $context = ConfigService::getShopifyContext();

        if (!Utils::validateHmac($data, $context['apiSecretKey'])) {
            abort(403);
        }

        $shop = $request->query('shop');
        $storeConfig = ConfigService::getStoreConfig($shop);
        if (!$storeConfig
            || !empty($storeConfig['uninstalled'])
            || !$this->checkAccess($shop, $storeConfig['token'])
        ) {
            $url = OAuth::begin(
                $shop,
                route('oauth.callback', [], false),
                false
            );

            return Redirect::away($url);
        }

        $active = (bool)$storeConfig['status'];
        if ($host = $request->query('host')) {
            session(['app_host' => $host]);
        }
        $bridgeConfig = [
            'apiKey' => config('shopify.client_id'),
            'host' => $host ?? session('app_host'),
        ];

        return response()
            ->view('home.index', compact('active', 'shop', 'bridgeConfig'))
            ->header('Content-Security-Policy', "frame-ancestors https://$shop https://admin.shopify.com");
    }

    /**
     * @param string $shop
     * @param string $token
     * @return bool
     * @throws HttpRequestException
     * @throws MissingArgumentException
     */
    private function checkAccess(string $shop, string $token)
    {
        $client = new Graphql($shop, $token);
        $response = $client->query(Shop::build());

        return $response->getStatusCode() !== 401;
    }
}
