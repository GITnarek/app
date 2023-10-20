<?php

namespace App\Console\Commands;

use App\Services\Shopify\WebhookService;
use Illuminate\Console\Command;
use App\Models\Store;

class VerifyWebhooks extends Command
{
    /**
     * @var string
     */
    protected $signature = 'mt:verify-webhooks {store? : store domain}';

    /**
     * @var string
     */
    protected $description = 'Check/create webhooks in shopify store';

    /**
     * @param  \App\Services\Shopify\WebhookService $webhookService
     *
     * @return int
     */
    public function handle(WebhookService $webhookService)
    {
        $storeParam = $this->argument('store');
        if ($storeParam === null) {
            $activeStores = Store::where(['status' => 1])->get();
            foreach ($activeStores as $store) {
                $webhookService($store)->verifyWebhooks();
            }

            $inactiveStores = Store::where(['status' => 0])->get();
            foreach ($inactiveStores as $store) {
                $webhookService($store)->verifyWebhooks(false);
            }
        } else {
            $store = Store::where(['shop' => $storeParam])
                ->orWhere(['shop' => $storeParam . Store::SHOPIFY_DOMAIN])
                ->first();
            if ($store === null) {
                $this->error(__('Unknown store'));

                return self::FAILURE;
            }

            $webhookService($store)->verifyWebhooks((bool) $store->status);
        }

        return self::SUCCESS;
    }
}
