<?php

namespace App\Components\Shopify\Query;

class FulfillmentService
{
    /**
     * Returns a FulfillmentService resource by ID.
     *
     * @param int $id
     *
     * @return array
     */
    public static function build(int $id): array
    {
        $query = '
            query ($id: ID!) {
                fulfillmentService(id: $id) {
                    id
                    location {
                        id
                    }
                }
            }
        ';

        $variables = [
            'id' => "gid://shopify/FulfillmentService/$id",
        ];

        return ['query' => $query, 'variables' => $variables];
    }
}
