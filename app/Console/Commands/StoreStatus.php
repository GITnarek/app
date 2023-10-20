<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ConfigService;
use App\Models\Store;

class StoreStatus extends Command
{
    /**
     * @var string
     */
    protected $signature = 'mt:store-status {store : store domain} {status? : status to set}';

    /**
     * @var string
     */
    protected $description = 'Check/set store status';

    /**
     * @return int
     */
    public function handle()
    {
        $shop = $this->argument('store');
        $status = $this->argument('status');

        $store = Store::where(['shop' => $shop])
            ->orWhere(['shop' => $shop . Store::SHOPIFY_DOMAIN])
            ->first();
        if ($store === null) {
            $this->error(__('Unknown store'));

            return self::FAILURE;
        }
        if ($status === null) {
            $this->line('Status: ' . $store->status);
        } else {
            $store->status = (int)(bool) $status;
            $store->save();
            ConfigService::exposeStore($store);
            $this->line('Status set to ' . $store->status);
        }

        return self::SUCCESS;
    }
}
