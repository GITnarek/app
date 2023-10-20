<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ConfigService;
use App\Models\Store;

class DeleteStore extends Command
{
    /**
     * @var string
     */
    protected $signature = 'mt:delete-store {store : store domain}';

    /**
     * @var string
     */
    protected $description = 'Delete store from the system';

    /**
     * @return int
     */
    public function handle()
    {
        $shop = $this->argument('store');

        $store = Store::where(['shop' => $shop])
            ->orWhere(['shop' => $shop . Store::SHOPIFY_DOMAIN])
            ->first();
        if ($store !== null) {
            ConfigService::deleteStore($store);
            $store->delete();
            $this->line('Store deleted successfully.');
        }

        return self::SUCCESS;
    }
}
