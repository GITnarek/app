<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ConfigService;
use App\Models\Store;

class ExposeConfig extends Command
{
    /**
     * @var string
     */
    protected $signature = 'mt:expose-config {store? : store domain}';

    /**
     * @var string
     */
    protected $description = 'Expose config values to Redis';

    /**
     * @return int
     */
    public function handle()
    {
        $shop = $this->argument('store');
        if ($shop === null) {
            ConfigService::expose();
        } else {
            $store = Store::where(['shop' => $shop])
                ->orWhere(['shop' => $shop . Store::SHOPIFY_DOMAIN])
                ->first();
            if ($store === null) {
                $this->error(__('Unknown store'));

                return self::FAILURE;
            }
            ConfigService::exposeStore($store);
        }

        return self::SUCCESS;
    }
}
