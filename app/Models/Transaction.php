<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'account_id',
        'type',
        'amount',
        'reference',
        'source',
        'related_type',
        'related_id'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
