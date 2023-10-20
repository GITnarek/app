<?php

namespace App\Console\Commands;

use GuzzleHttp\Promise\Utils;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class WebhookTestLoadOms2s extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mt:webhook-test-load-oms2s {count : count of requests} {delay : delay between requests}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scripts that will generate load on our stores';

    private const STORES_CONFIG = [
        'store1.myshopify.com' => 60,
        'store2.myshopify.com' => 30,
        'store3.myshopify.com' => 10,
    ];

    private const TOPICS_CONFIG = [
        'Fulfillment' => 40,
        'PartialFulfillment' => 40,
        'Refund' => 10,
        'PartialRefund' => 10,
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
     * Execute the console command.
     *
     *
     * @throws \Throwable
     */
    public function handle()
    {
        $promises = [];
        for ($i = 0; $i < $this->argument('count'); $i++) {
            $randomStore = $this->stores[rand(0, count($this->stores) - 1)];
            $randomTopic = $this->topics[rand(0, count($this->topics) - 1)];

            switch ($randomTopic) {
                case 'Fulfillment':
                    $body = [
                        'fulfillmentOrderId' => 'XXXXX',
                        'notifyCustomer' => true,
                        'originAddress' => [
                            'address1' => 'XXXXX',
                            'address2' => 'XXXXX',
                            'city' => 'XXXXX',
                            'countryCode' => 'US',
                            'provinceCode' => 'XXXXX',
                            'zip' => 'XXXXX'
                        ],
                        'trackingInfo' => [
                            'company' => 'UPS',
                            'number' => 'XXXXX',
                            'url' => 'XXXXX',
                        ],
                        'store' => $randomStore,
                    ];
                    break;
                case 'PartialFulfillment':
                    $body = [
                        'fulfillmentOrderId' => 'XXXXX',
                        'line_items' => [
                            [
                                'id' => 'XXXXX',
                                'qty' => '1'
                            ],
                            [
                                'id' => 'YYYYY',
                                'qty' => 2
                            ],
                        ],
                        'notifyCustomer' => true,
                        'originAddress' => [
                            'address1' => 'XXXXX',
                            'address2' => 'XXXXX',
                            'city' => 'XXXXX',
                            'countryCode' => 'US',
                            'provinceCode' => 'XXXXX',
                            'zip' => 'XXXXX'
                        ],
                        'trackingInfo' => [
                            'company' => 'UPS',
                            'number' => 'XXXXX',
                            'url' => 'XXXXX',
                        ],
                        'store' => $randomStore,
                    ];
                    break;
                case 'Refund':
                    $body = [
                        'orderId' => 'XXXXX',
                        'parentTransactionId' => 'XXXXX',
                        'note' => 'XXXXX',
                        'notifyCustomer' => true,
                        'store' => $randomStore
                    ];
                    break;
                case 'PartialRefund':
                    $body = [
                        'orderId' => 'XXXXX',
                        'parentTransactionId' => 'XXXXX',
                        'currency' => 'USD',
                        'note' => 'XXXXX',
                        'notifyCustomer' => true,
                        'refundLineItems' => [
                            [
                                'id' => 'XXXXX',
                                'quantity' => 1,
                                'locationId' => 'XXXXX',
                                'restockType' => 'RETURN'
                            ],
                            [
                                'id' => 'YYYYY',
                                'quantity' => 2,
                                'restockType' => 'NO_RESTOCK',
                            ],
                            'shipping' => [
                                'amount' => 100,
                                'fullRefund' => false,
                            ],
                            'amount' => 100
                        ],
                        'store' => $randomStore,
                    ];
                    break;
            }

            $promises [] = Http::async()
                ->withHeaders(
                    [
                    'Authorization' => config('mt.oms2s.verification_key')
                    ]
                )
                ->post(
                    config('mt.services.oms2s.webhook_receiver'),
                    [
                        'body' => $body
                    ]
                );

            usleep((float)$this->argument('delay') * 1000000);
        }

        Utils::unwrap($promises);

        return self::SUCCESS;
    }
}
