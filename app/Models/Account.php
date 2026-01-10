<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = ['name', 'owner_type', 'owner_id'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function balance()
    {
        return $this->transactions()
            ->selectRaw("
                SUM(CASE WHEN type='credit' THEN amount ELSE 0 END) -
                SUM(CASE WHEN type='debit' THEN amount ELSE 0 END) as balance
            ")
            ->value('balance') ?? 0;
    }
}

