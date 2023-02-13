<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FavoriteFlat extends Model
{
    protected $fillable = [
        'user_id',
        'flat_id'
    ];

    protected ?string $user_id;
    protected ?string $flat_id;
}
