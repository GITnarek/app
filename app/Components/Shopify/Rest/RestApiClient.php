<?php

namespace App\Components\Shopify\Rest;

use Shopify\Clients\Rest;
use Shopify\Exception\MissingArgumentException;

class RestApiClient extends Rest
{
    /**
     * @param string $domain
     * @param string $accessToken
     * @return RestApiClient
     * @throws MissingArgumentException
     */
    static function getInstance(string $domain, string $accessToken): RestApiClient
    {
        return new self($domain, $accessToken);
    }
}
