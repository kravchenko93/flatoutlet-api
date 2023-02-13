<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LastBanner extends Model
{
    protected $fillable = [
        'number',
    ];

    protected ?int $number;
}
