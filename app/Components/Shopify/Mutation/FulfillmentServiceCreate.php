<?php

namespace App\Components\Shopify\Mutation;

class FulfillmentServiceCreate
{
    /**
     * Creates a fulfillment service.
     *
     * @param array $variables
     *
     * @return array
     */
    public static function build(array $variables): array
    {
        $query = '
            mutation (
                $callbackUrl: URL!,
                $fulfillmentOrdersOptIn: Boolean!,
                $name: String!,
                $inventoryManagement: Boolean,
                $permitsSkuSharing: Boolean,
                $trackingSupport: Boolean,
            ) {
              fulfillmentServiceCreate(
                  callbackUrl: $callbackUrl,
                  fulfillmentOrdersOptIn: $fulfillmentOrdersOptIn,
                  name: $name,
                  inventoryManagement: $inventoryManagement,
                  permitsSkuSharing: $permitsSkuSharing,
                  trackingSupport: $trackingSupport,
                ) {
                fulfillmentService {
                  id
                  location {
                    id
                  }
                }
                userErrors {
                  field
                  message
                }
              }
            }
        ';

        $variables += [
            'fulfillmentOrdersOptIn' => true,
            'inventoryManagement' => false,
            'permitsSkuSharing' => false,
            'trackingSupport' => false,
        ];

        return ['query' => $query, 'variables' => $variables];
    }
}
