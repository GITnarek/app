<?php

namespace App\Services\Shopify\Carrier;

use App\Components\Shopify\Rest\RestApiClient;
use App\Exceptions\CarrierServiceException;
use App\Models\Store;
use App\Services\LogService;
use Illuminate\Support\Facades\Log;
use Throwable;

use function config;

class CarrierService
{
    /**
     * @param Store $store
     *
     * @return void
     */
    public function prepareCarrierService(Store $store): void
    {
        try {
            $carrierServiceList = $this->getList($store);
            $carrierService = $this->getByServiceNameFromList(
                config('services.carrier.serviceName'),
                $carrierServiceList
            );

            if (!$carrierService) {
                $carrierService = $this->createCarrier($store);
            }

            $store->carrier_service_id = $carrierService['carrier_service']['id'] ?? $carrierService['id'];
        } catch (CarrierServiceException $e) {
            LogService::error($e);
            Log::error('createCarrier: ' . $e->getMessage());
        }
    }

    /**
     * @param Store $store
     *
     * @return array
     * @throws CarrierServiceException
     */
    protected function getList(Store $store): array
    {
        try {
            $client = RestApiClient::getInstance($store->shop, $store->access_token);
            $response = $client->get('/admin/api/' . env('SHOPIFY_API_VERSION') . '/carrier_services.json');

            if ($response->getStatusCode() !== 200) {
                throw new CarrierServiceException($response->getBody()->getContents(), $response->getStatusCode());
            }

            return $response->getDecodedBody()['carrier_services'];
        } catch (Throwable $e) {
            throw new CarrierServiceException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @param string $serviceName
     * @param array  $carrierServiceList
     *
     * @return array|null
     */
    public function getByServiceNameFromList(string $serviceName, array $carrierServiceList): ?array
    {
        foreach ($carrierServiceList as $carrierService) {
            if ($carrierService['name'] === $serviceName) {
                return $carrierService;
            }
        }

        return null;
    }

    /**
     * @param Store $store
     *
     * @return array|string
     * @throws CarrierServiceException
     */
    protected function createCarrier(Store $store): array|string
    {
        try {
            $client = RestApiClient::getInstance($store->shop, $store->access_token);

            $data = [
                'carrier_service' => [
                    'name' => config('services.carrier.serviceName'),
                    'callback_url' => config('services.carrier.callbackUrl'),
                    'service_discovery' => true,
                ],
            ];

            $response = $client->post('/admin/api/' . env('SHOPIFY_API_VERSION') . '/carrier_services.json', $data);

            if ($response->getStatusCode() !== 201) {
                throw new CarrierServiceException($response->getBody()->getContents(), $response->getStatusCode());
            }

            return $response->getDecodedBody();
        } catch (Throwable $e) {
            throw new CarrierServiceException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }
}
