<?php

namespace App\Facades;

/**
 * @method static hGetAll(string $lower)
 * @method static hKeys(string $name)
 * @method static hDel(string $name, mixed $hKey)
 * @method static hSet(string $key, string $field, mixed $value)
 * @method static set(string $key, string $value)
 * @method static get(string $key)
 * @method static del(string $key)
 */
class Redis extends \Illuminate\Support\Facades\Redis
{

}