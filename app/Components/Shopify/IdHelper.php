<?php

namespace App\Components\Shopify;

class IdHelper
{
    /**
     * Returns numeric ID from Shopify-formatted ID.
     *
     * @param string $shopifyId
     *
     * @return int
     */
    public static function extract(string $shopifyId): int
    {
        return (int)preg_replace('/^gid:\/\/shopify\/.*\/(\d+)/', '$1', $shopifyId);
    }

    /**
     * Returns Shopify-formatted ID for given resource type and numeric ID.
     *
     * @param string $resourceType
     * @param int    $id
     *
     * @return string
     */
    public static function create(string $resourceType, int $id): string
    {
        return "gid://shopify/$resourceType/$id";
    }
}
