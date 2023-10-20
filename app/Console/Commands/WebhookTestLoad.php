<?php

namespace App\Console\Commands;

use GuzzleHttp\Promise\Utils;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class WebhookTestLoad extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mt:webhook-test-load {count : count of requests} {delay : delay between requests}';

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
        'orders/create' => 50,
        'orders/fulfilled' => 40,
        'orders/cancelled' => 10,
    ];

    private array $stores = [];
    private array $topics = [];

    public function __construct()
    {
        parent::__construct();

        foreach (self::STORES_CONFIG as $store => $percent) {
            for($i = 0; $i < $percent; $i++) {
                $this->stores[] = $store;
            }
        }

        foreach (self::TOPICS_CONFIG as $topic => $percent) {
            for($i = 0; $i < $percent; $i++) {
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
        for($i = 0; $i < $this->argument('count'); $i++) {
            $randomStore = $this->stores[rand(0, count($this->stores) - 1)];
            $randomTopic = $this->topics[rand(0, count($this->topics) - 1)];
            $body = '{"order":{"id":4969462923478,"admin_graphql_api_id":"gid:\/\/shopify\/Order\/4969462923478","app_id":580111,"browser_ip":"159.203.68.45","buyer_accepts_marketing":false,"cancel_reason":null,"cancelled_at":null,"cart_token":"d37e79e072e13f5fa93d79f5da2880ca","checkout_id":33160707997910,"checkout_token":"111ae5daa74593e52d015d4a1f4b4629","client_details":{"accept_language":"ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7","browser_height":754,"browser_ip":"159.203.68.45","browser_width":1536,"session_hash":null,"user_agent":"Mozilla\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/108.0.0.0 Safari\/537.36"},"closed_at":"2022-12-07T10:09:28+01:00","confirmed":true,"contact_email":"alex@mtnhausdigital.com","created_at":"2022-12-07T10:09:24+01:00","currency":"USD","current_subtotal_price":"10.00","current_subtotal_price_set":{"shop_money":{"amount":"10.00","currency_code":"USD"},"presentment_money":{"amount":"10.00","currency_code":"USD"}},"current_total_discounts":"0.00","current_total_discounts_set":{"shop_money":{"amount":"0.00","currency_code":"USD"},"presentment_money":{"amount":"0.00","currency_code":"USD"}},"current_total_duties_set":null,"current_total_price":"10.00","current_total_price_set":{"shop_money":{"amount":"10.00","currency_code":"USD"},"presentment_money":{"amount":"10.00","currency_code":"USD"}},"current_total_tax":"0.00","current_total_tax_set":{"shop_money":{"amount":"0.00","currency_code":"USD"},"presentment_money":{"amount":"0.00","currency_code":"USD"}},"customer_locale":"en-US","device_id":null,"discount_codes":[],"email":"alex@mtnhausdigital.com","estimated_taxes":false,"financial_status":"paid","fulfillment_status":"fulfilled","gateway":"bogus","landing_site":"\/?_ab=0\u0026_fd=0\u0026_sc=1","landing_site_ref":null,"location_id":null,"name":"#1001","note":null,"note_attributes":[],"number":1,"order_number":1001,"order_status_url":"https:\/\/mtn-dev-2.myshopify.com\/60827074774\/orders\/1a6a0675923bc1a2b5c6cc3914c61fe8\/authenticate?key=ef0dd0c23e8b7f47bd311bcf139fabb3","original_total_duties_set":null,"payment_gateway_names":["bogus"],"phone":null,"presentment_currency":"USD","processed_at":"2022-12-07T10:09:24+01:00","processing_method":"direct","reference":null,"referring_site":"","source_identifier":null,"source_name":"web","source_url":null,"subtotal_price":"10.00","subtotal_price_set":{"shop_money":{"amount":"10.00","currency_code":"USD"},"presentment_money":{"amount":"10.00","currency_code":"USD"}},"tags":"","tax_lines":[],"taxes_included":false,"test":true,"token":"1a6a0675923bc1a2b5c6cc3914c61fe8","total_discounts":"0.00","total_discounts_set":{"shop_money":{"amount":"0.00","currency_code":"USD"},"presentment_money":{"amount":"0.00","currency_code":"USD"}},"total_line_items_price":"10.00","total_line_items_price_set":{"shop_money":{"amount":"10.00","currency_code":"USD"},"presentment_money":{"amount":"10.00","currency_code":"USD"}},"total_outstanding":"0.00","total_price":"10.00","total_price_set":{"shop_money":{"amount":"10.00","currency_code":"USD"},"presentment_money":{"amount":"10.00","currency_code":"USD"}},"total_price_usd":"10.00","total_shipping_price_set":{"shop_money":{"amount":"0.00","currency_code":"USD"},"presentment_money":{"amount":"0.00","currency_code":"USD"}},"total_tax":"0.00","total_tax_set":{"shop_money":{"amount":"0.00","currency_code":"USD"},"presentment_money":{"amount":"0.00","currency_code":"USD"}},"total_tip_received":"0.00","total_weight":0,"updated_at":"2022-12-07T10:09:28+01:00","user_id":null,"billing_address":{"first_name":"alex","address1":"Ohio State Universty","phone":null,"city":"Columbus","zip":"43210","province":"Ohio","country":"United States","last_name":"mtn","address2":"","company":null,"latitude":40.0066723,"longitude":-83.0304546,"name":"alex mtn","country_code":"US","province_code":"OH"},"customer":{"id":6489110020310,"email":"alex@mtnhausdigital.com","accepts_marketing":false,"created_at":"2022-12-07T10:08:29+01:00","updated_at":"2022-12-07T10:09:25+01:00","first_name":"alex","last_name":"mtn","orders_count":0,"state":"disabled","total_spent":"0.00","last_order_id":null,"note":null,"verified_email":true,"multipass_identifier":null,"tax_exempt":false,"tags":"","last_order_name":null,"currency":"USD","phone":null,"accepts_marketing_updated_at":"2022-12-07T10:08:29+01:00","marketing_opt_in_level":null,"tax_exemptions":[],"email_marketing_consent":{"state":"not_subscribed","opt_in_level":"single_opt_in","consent_updated_at":null},"sms_marketing_consent":null,"admin_graphql_api_id":"gid:\/\/shopify\/Customer\/6489110020310","default_address":{"id":7997242343638,"customer_id":6489110020310,"first_name":"alex","last_name":"mtn","company":null,"address1":"Ohio State Universty","address2":"","city":"Columbus","province":"Ohio","country":"United States","zip":"43210","phone":null,"name":"alex mtn","province_code":"OH","country_code":"US","country_name":"United States","default":true}},"discount_applications":[],"fulfillments":[{"id":4447451218134,"admin_graphql_api_id":"gid:\/\/shopify\/Fulfillment\/4447451218134","created_at":"2022-12-07T10:09:26+01:00","location_id":65986789590,"name":"#1001.1","order_id":4969462923478,"origin_address":{},"receipt":{"gift_cards":[{"id":541143302358,"line_item_id":12552862630102,"masked_code":"•••• •••• •••• 22bf"}]},"service":"gift_card","shipment_status":null,"status":"success","tracking_company":null,"tracking_number":null,"tracking_numbers":[],"tracking_url":null,"tracking_urls":[],"updated_at":"2022-12-07T10:09:27+01:00","line_items":[{"id":12552862630102,"admin_graphql_api_id":"gid:\/\/shopify\/LineItem\/12552862630102","fulfillable_quantity":0,"fulfillment_service":"gift_card","fulfillment_status":"fulfilled","gift_card":true,"grams":0,"name":"Gift Card - $25.00","origin_location":{"id":3535215984854,"country_code":"US","province_code":"CO","name":"MTN Theme","address1":"700 N Colorado Blvd","address2":"","city":"Denver","zip":"80206"},"price":"10.00","price_set":{"shop_money":{"amount":"10.00","currency_code":"USD"},"presentment_money":{"amount":"10.00","currency_code":"USD"}},"product_exists":true,"product_id":7947035082966,"properties":[],"quantity":1,"requires_shipping":false,"sku":"","taxable":false,"title":"Gift Card","total_discount":"0.00","total_discount_set":{"shop_money":{"amount":"0.00","currency_code":"USD"},"presentment_money":{"amount":"0.00","currency_code":"USD"}},"variant_id":43869584064726,"variant_inventory_management":null,"variant_title":"$25.00","vendor":"MTN Theme","tax_lines":[{"channel_liable":false,"price":"0.00","price_set":{"shop_money":{"amount":"0.00","currency_code":"USD"},"presentment_money":{"amount":"0.00","currency_code":"USD"}},"rate":0.0,"title":"Ohio State Tax"}],"duties":[],"discount_allocations":[]}]}],"line_items":[{"id":12552862630102,"admin_graphql_api_id":"gid:\/\/shopify\/LineItem\/12552862630102","fulfillable_quantity":0,"fulfillment_service":"gift_card","fulfillment_status":"fulfilled","gift_card":true,"grams":0,"name":"Gift Card - $25.00","origin_location":{"id":3535215984854,"country_code":"US","province_code":"CO","name":"MTN Theme","address1":"700 N Colorado Blvd","address2":"","city":"Denver","zip":"80206"},"price":"10.00","price_set":{"shop_money":{"amount":"10.00","currency_code":"USD"},"presentment_money":{"amount":"10.00","currency_code":"USD"}},"product_exists":true,"product_id":7947035082966,"properties":[],"quantity":1,"requires_shipping":false,"sku":"","taxable":false,"title":"Gift Card","total_discount":"0.00","total_discount_set":{"shop_money":{"amount":"0.00","currency_code":"USD"},"presentment_money":{"amount":"0.00","currency_code":"USD"}},"variant_id":43869584064726,"variant_inventory_management":null,"variant_title":"$25.00","vendor":"MTN Theme","tax_lines":[{"channel_liable":false,"price":"0.00","price_set":{"shop_money":{"amount":"0.00","currency_code":"USD"},"presentment_money":{"amount":"0.00","currency_code":"USD"}},"rate":0.0,"title":"Ohio State Tax"}],"duties":[],"discount_allocations":[]}],"payment_details":{"credit_card_bin":"1","avs_result_code":null,"cvv_result_code":null,"credit_card_number":"•••• •••• •••• 1","credit_card_company":"Bogus"},"payment_terms":null,"refunds":[],"shipping_address":null,"shipping_lines":[]}}';

            $calculated_hmac = base64_encode(hash_hmac('sha256', $body, config('shopify.client_secret'), true));
            $promises [] = Http::async()
                ->withHeaders(
                    [
                        'topic' => $randomTopic,
                        'X-Shopify-Hmac-SHA256' => $calculated_hmac,
                        'store' => $randomStore,
                        'api_version' => config('shopify.api_version')
                    ]
                )
                ->post(
                    config('mt.services.s2oms.webhook_receiver'),
                    [
                        'body' => json_encode($body)
                    ]
                );

            usleep((float) $this->argument('delay') * 1000000);
        }

        Utils::unwrap($promises);

        return self::SUCCESS;
    }
}
