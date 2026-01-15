<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    protected $fillable = [
        'member_id',
        'amount',
        'interest_rate',
        'duration_months',
        'status'
    ];

    public function guarantors()
    {
        return $this->hasMany(LoanGuarantor::class);
    }

    public function approvedGuarantors()
    {
        return $this->guarantors()->where('approved', true);
    }

    public function repayments()
    {
        return $this->hasMany(LoanRepayment::class);
    }
}
