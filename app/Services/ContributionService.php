<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Account;
use App\Models\Contribution;
use Illuminate\Support\Facades\DB;

class ContributionService
{
    public static function postMonthlyContribution(
        Member $member,
        float $amount,
        string $month,
        int $year,
        string $source = 'manual'
    ) {
        return DB::transaction(function () use ($member, $amount, $month, $year, $source) {

            // 1️⃣ Find member account
            $account = Account::firstOrCreate([
                'owner_type' => 'Member',
                'owner_id' => $member->id,
                'name' => 'member_wallet'
            ]);

            // 2️⃣ Create contribution record
            $contribution = Contribution::create([
                'member_id' => $member->id,
                'amount' => $amount,
                'month' => strtoupper($month),
                'year' => $year,
            ]);

            // 3️⃣ Post to ledger
            $transaction = LedgerService::credit(
                $account,
                $amount,
                [
                    'reference' => "{$month}-{$year}-MEM-{$member->id}",
                    'source' => $source,
                    'related_type' => 'Contribution',
                    'related_id' => $contribution->id
                ]
            );

            // 4️⃣ Link transaction back
            $contribution->update([
                'transaction_id' => $transaction->id
            ]);

            return $contribution;
        });
    }
}

