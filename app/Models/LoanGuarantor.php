<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanGuarantor extends Model
{
    protected $fillable = [
        'loan_id',
        'member_id',
        'amount',
        'approved'
    ];
}

