<?php

namespace App\Services\Shopify;

use App\Components\Shopify\GraphqlClient;
use App\Components\Shopify\RestClient;
use App\Services\ConfigService;
use InvalidArgumentException;
use Shopify\Exception\MissingArgumentException;

/**
 * ShopifyService class
 *
 * @package App\Services\Shopify
 */
class ShopifyService
{
    /**
     * @var array
     */
    private array $clients = [];

    /**
     * @var array
     */
    private array $stores = [];

    /**
     * @var string|null
     */
    private ?string $activeStoreDomain = null;

    /**
     * @param  string|null  $storeDomain
     * @param  bool  $graphql
     *
     * @return GraphqlClient|RestClient
     * @throws MissingArgumentException
     */
    public function getClient(string $storeDomain = null, bool $graphql = true): GraphqlClient|RestClient
    {
        if ($storeDomain === null) {
            if ($this->activeStoreDomain === null || empty($this->stores[$this->activeStoreDomain])) {
                throw new InvalidArgumentException(__('Active shopify store is not set'));
            }
            $store = $this->stores[$this->activeStoreDomain];
        } else {
            $store = $this->getStore($storeDomain);
        }

        $clientKey = $store['domain'] . '_' . ((int) $graphql);
        if (empty($this->clients[$clientKey])) {
            $this->clients[$clientKey] = $graphql
                ? new GraphqlClient($store['domain'], $store['token'])
                : new RestClient($store['domain'], $store['token']);
        }

        return $this->clients[$clientKey];
    }

    /**
     * @param  string  $storeDomain
     *
     * @return $this
     */
    public function switchStore(string $storeDomain): static
    {
        $this->activeStoreDomain = $this->getStore($storeDomain)['domain'];

        return $this;
    }

    /**
     * @return array|null
     */
    public function getActiveStore(): ?array
    {
        return $this->activeStoreDomain
            ? $this->getStore($this->activeStoreDomain)
            : null;
    }

    /**
     * @param  string  $storeDomain
     *
     * @return array
     */
    protected function getStore(string $storeDomain): array
    {
        if (!str_ends_with($storeDomain, ConfigService::SHOPIFY_DOMAIN)) {
            $storeDomain = $storeDomain . ConfigService::SHOPIFY_DOMAIN;
        }

        if (!array_key_exists($storeDomain, $this->stores)) {
            $storeConfig = ConfigService::getStoreConfig($storeDomain);
            if (!$storeConfig) {
                throw new InvalidArgumentException(__('Unknown shopify store'));
            }

            $this->stores[$storeConfig['domain']] = $storeConfig;
        }

        return $this->stores[$storeDomain];
    }
}
