<?php

namespace Laravel\Octane\Swoole\Providers\Redis;

use Laravel\Octane\Swoole\Coroutines\RedisConnectionPool;

class RedisManager extends \Illuminate\Redis\RedisManager
{
    public function connection($name = null)
    {
        $name = $name ?: 'default';

        return RedisConnectionPool::getRedis($name, $this);
    }
}
