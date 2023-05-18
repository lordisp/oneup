<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static first()
 * @method static paginate(int $param)
 * @method whereId($tokencacheprovider)
 */
class TokenCacheProvider extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'name',
        'auth_url',
        'token_url',
        'auth_endpoint',
        'client',
    ];

    protected $casts = [
        'client' => 'json'
    ];
}
