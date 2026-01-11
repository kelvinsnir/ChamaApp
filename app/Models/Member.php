<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
        'user_id',
        'national_id',
        'phone',
        'role',
        'status',
        'joined_at'
    ];

    public function contributions()
    {
        return $this->hasMany(Contribution::class);
    }
}

