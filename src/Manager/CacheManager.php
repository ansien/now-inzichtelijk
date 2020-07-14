<?php

declare(strict_types=1);

namespace App\Manager;

use RedisClient\ClientFactory;
use RedisClient\RedisClient;

class CacheManager
{
    private $redisClient;

    public function __construct()
    {
        $this->redisClient = ClientFactory::create();
    }

    public function getClient(): RedisClient
    {
        return $this->redisClient;
    }
}
