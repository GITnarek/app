<?php

namespace App\Providers;

use App\Services\Shopify\RedisSessionStorage;
use App\Services\Shopify\ShopifyService;
use Illuminate\Support\ServiceProvider;
use Shopify\Context;
use Illuminate\Support\Facades\App;

/**
 * ShopifyServiceProvider class
 *
 * @package App\Providers
 */
class ShopifyServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (!config('mt.build')) {
            Context::initialize(
                config('shopify.client_id'),
                config('shopify.client_secret'),
                config('shopify.api_scopes'),
                config('app.url'),
                App::make(RedisSessionStorage::class),
                config('shopify.api_version'),
                true,
                false
            );

            $this->app->singleton(ShopifyService::class, function ($app) {
                return new ShopifyService();
            });
        }
    }
}
