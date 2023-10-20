<?php

namespace App\Services\Shopify;

use Shopify\Auth\Session;
use Shopify\Auth\SessionStorage;
use Illuminate\Support\Facades\Redis;

class RedisSessionStorage implements SessionStorage
{
    public const SHOPIFY_SESSION_PREFIX = 'shopify_session:';

    /**
     * @param string $sessionId
     * @return Session|null
     */
    public function loadSession(string $sessionId): ?Session
    {
        if ($session = Redis::get(self::SHOPIFY_SESSION_PREFIX . $sessionId)) {
            return unserialize($session);
        }

        return null;
    }

    /**
     * @param Session $session
     * @return bool
     */
    public function storeSession(Session $session): bool
    {
        Redis::set(self::SHOPIFY_SESSION_PREFIX . $session->getId(), serialize($session));

        return true;
    }

    /**
     * @param string $sessionId
     * @return bool
     */
    public function deleteSession(string $sessionId): bool
    {
        Redis::del(self::SHOPIFY_SESSION_PREFIX . $sessionId);

        return true;
    }
}
