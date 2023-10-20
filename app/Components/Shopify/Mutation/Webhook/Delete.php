<?php

namespace App\Components\Shopify\Mutation\Webhook;

class Delete
{
    /**
     * Delete webhook.
     * @param string $id
     * @return array
     */
    public static function build(string $id): array
    {
        $query = '
            mutation DeleteWebhook($id: ID!) {
                webhookSubscriptionDelete(id: $id) {
                    deletedWebhookSubscriptionId
                    userErrors {
                        field
                        message
                    }
                }
            }
        ';

        $variables = [
            'id' => $id,
        ];

        return ['query' => $query, 'variables' => $variables];
    }
}
