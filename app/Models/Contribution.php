<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contribution extends Model
{
    protected $fillable = [
        'member_id',
        'amount',
        'month',
        'year',
        'transaction_id'
    ];
}

