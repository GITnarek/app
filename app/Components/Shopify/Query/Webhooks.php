<?php

namespace App\Components\Shopify\Query;

class Webhooks
{
    /**
     * Get list of registered webhooks.
     * @param int $first
     * @return array
     */
    public static function build(int $first = 10): array
    {
        $query = '
            query GetWebhooks($first: Int) {
                webhookSubscriptions(first: $first) {
                    nodes {
                        id
                        topic
                        format
                        callbackUrl
                        metafieldNamespaces
                        privateMetafieldNamespaces
                        includeFields
                        createdAt
                    }
                }
            }
        ';

        $variables = [
            'first' => $first,
        ];

        return ['query' => $query, 'variables' => $variables];
    }
}
