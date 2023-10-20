<?php

namespace App\Console\Commands;

use App\Services\Shopify\Carrier\CarrierService;
use App\Services\StoreService;
use Illuminate\Console\Command;

class VerifyCarrierService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mt:verify-carrier-service {store? : store domain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check/create carrier service in shopify store';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(StoreService $storeService, CarrierService $carrierService)
    {
        $storeName = $this->argument('store');

        $storeNames = $storeName ? [$storeName] : [];

        $stores = $storeService->getStores(true, $storeNames);

        if ($stores->isEmpty()) {
            $this->alert('No active stores');
        }

        foreach ($stores as $store) {
            $carrierService->prepareCarrierService($store);
        }
    }
}
