<?php

namespace App\Components\Shopify\Query;

class Shop
{
    /**
     * Get shop info.
     *
     * @return array
     */
    public static function build(): array
    {
        $query = '
            query GetShop {
                shop {
                    id
                    name
                }
            }
        ';

        return ['query' => $query, 'variables' => []];
    }
}
