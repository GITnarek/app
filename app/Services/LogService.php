<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Throwable;

class LogService
{
    /**
     * @param Throwable $throwable
     * @param string $prefix
     * @return void
     */
    public static function error(Throwable $throwable, string $prefix = '')
    {
        Log::error(
            $prefix . $throwable->getMessage() . PHP_EOL .
            print_r($throwable->getTraceAsString(), true)
        );
    }
}
