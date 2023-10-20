<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Promise\Utils;

class LoadTest extends Command
{
    /**
     * @var string
     */
    protected $signature = 'mt:load-test {count : count of requests} {delay : delay between requests} {single-store? : generate load for single store}';

    /**
     * @var string
     */
    protected $description = 'Generate load for OMS2S flow';

    private const SINGLE_STORE_DOMAIN = 'smscommerce.myshopify.com';

    private const STORES_CONFIG = [
        'store1.myshopify.com' => 60,
        'store2.myshopify.com' => 30,
        'store3.myshopify.com' => 10,
    ];

    private const TOPICS_CONFIG = [
        'Fulfillment' => 50,
        'PartialFulfillment' => 30,
        'Refund' => 15,
        'PartialRefund' => 5,
    ];

    private const TOPIC_URLS = [
        'Fulfillment' => '/api/fulfillment',
        'PartialFulfillment' => '/api/partial-fulfillment',
        'Refund' => '/api/refund',
        'PartialRefund' => '/api/partial-refund',
    ];

    private array $stores = [];
    private array $topics = [];

    public function __construct()
    {
        parent::__construct();

        foreach (self::STORES_CONFIG as $store => $percent) {
            for ($i = 0; $i < $percent; $i++) {
                $this->stores[] = $store;
            }
        }

        foreach (self::TOPICS_CONFIG as $topic => $percent) {
            for ($i = 0; $i < $percent; $i++) {
                $this->topics[] = $topic;
            }
        }
    }


    /**
     * @return int
     */
    public function handle()
    {
        $promises = [];
        for ($i = 0; $i < $this->argument('count'); $i++) {
            $store = $this->argument('single-store')
                ? self::SINGLE_STORE_DOMAIN
                : $this->stores[rand(0, count($this->stores) - 1)];
            $topic = $this->topics[rand(0, count($this->topics) - 1)];

            $promises[] = Http::async()
                ->withHeaders(['Authorization' => config('mt.oms2s.verification_key')])
                ->post(
                    config('mt.services.oms2s.webhook_receiver') . self::TOPIC_URLS[$topic],
                    [
                        'payload' => json_encode(['test' => true]),
                        'store' => $store,
                    ]
                );

            usleep((float) $this->argument('delay') * 1000000);
        }

        Utils::unwrap($promises);

        return self::SUCCESS;
    }
}
