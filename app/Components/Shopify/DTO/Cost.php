<?php

namespace App\Components\Shopify\DTO;

/**
 * Cost class
 *
 * @package App\Components\Shopify\DTO
 */
class Cost
{
    public int $requestedQueryCost = 0;
    public ?int $actualQueryCost = null;
    public ?CostThrottleStatus $throttleStatus = null;

    /**
     * @param  array $cost
     */
    public function __construct(array $cost)
    {
        $this->requestedQueryCost = $cost['requestedQueryCost'] ?? 0;
        $this->actualQueryCost = $cost['actualQueryCost'] ?? null;
        $this->throttleStatus = new CostThrottleStatus($cost['throttleStatus'] ?? []);
    }
}
