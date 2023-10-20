<?php

namespace App\Components\Shopify\Mutation\Webhook;

class Create
{
    /**
     * Create webhook.
     * @param string $topic
     * @param string $url
     * @param array $includeFields
     * @param array $metafieldNamespaces
     * @param array $privateMetafieldNamespaces
     * @return array
     */
    public static function build(
        string $topic,
        string $url,
        array $includeFields = [],
        array $metafieldNamespaces = [],
        array $privateMetafieldNamespaces = []
    ): array {
        $query = '
            mutation CreateWebhook($topic: WebhookSubscriptionTopic!, $webhookSubscription: WebhookSubscriptionInput!) {
                webhookSubscriptionCreate(topic: $topic, webhookSubscription: $webhookSubscription) {
                    webhookSubscription {
                        id
                        topic
                        callbackUrl
                        createdAt
                    }
                    userErrors {
                        field
                        message
                    }
                }
            }
        ';

        $variables = [
            'topic' => $topic,
            'webhookSubscription' => [
                'callbackUrl' => $url,
                'format' => 'JSON',
                'includeFields' => $includeFields,
                'metafieldNamespaces' => $metafieldNamespaces,
                'privateMetafieldNamespaces' => $privateMetafieldNamespaces,
            ]
        ];

        return ['query' => $query, 'variables' => $variables];
    }
}
