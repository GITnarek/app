<?php

namespace App\Console\Commands;

use App\Services\StoreService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FullInventorySync extends Command
{
    protected $signature = 'mt:inventory-sync';

    protected $description = 'Updates inventory database for all registered stores';

    /**
     * @param  \App\Services\StoreService  $storeService
     *
     * @return int
     * @throws \Throwable
     */
    public function handle(StoreService $storeService): int
    {
        $stores = $storeService->getStores(true);
        $url = config('mt.services.oms2s.webhook_receiver') . '/api/inventory-sync';
        $headers = ['Authorization' => config('mt.oms2s.verification_key')];

        foreach ($stores as $store) {
            if (!$store->uninstalled) {
                Http::withHeaders($headers)
                    ->post($url, [
                        'body' => json_encode(['storeId' => $store->store_id]),
                    ]);
            }
        }

        return self::SUCCESS;
    }
}
