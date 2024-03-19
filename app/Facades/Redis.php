<?php

namespace App\Facades;

/**
 * @method static set(string $key, string $value)
 * @method static get(string $key)
 * @method static del(string $key)
 * @method static hSet(string $hash, string $field, string $value)
 * @method static hDel(string $hash, string ...$keys)
 * @method static hGetAll(string $lower)
 * @method static hKeys(string $name)
 * @method static hVals(string $key)
 * @method static expire(string $key, int $expireSeconds)
 *
 * @see \Illuminate\Redis\RedisManager
 */
class Redis extends \Illuminate\Support\Facades\Redis
{
}
