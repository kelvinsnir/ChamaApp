<?php

namespace App\Services;
use App\Models\Account;
use App\Models\Transaction;

/**
 * Class LedgerService.
 */
class LedgerService
{
    public static function credit(Account $account, float $amount, array $meta = [])
    {
        return Transaction::create([
            'account_id' => $account->id,
            'type' => 'credit',
            'amount' => $amount,
            ...$meta
        ]);
    }

    public static function debit(Account $account, float $amount, array $meta = [])
    {
        if ($account->balance() < $amount) {
            throw new \Exception('Insufficient funds');
        }

        return Transaction::create([
            'account_id' => $account->id,
            'type' => 'debit',
            'amount' => $amount,
            ...$meta
        ]);
    }
}

