<?php

namespace App\Services;

use App\Models\Contribution;
use App\Models\Loan;
use App\Models\Member;
use App\Models\LoanGuarantor;
use Illuminate\Support\Facades\DB;

/**
 * Class LoanService.
 */
class LoanService
{
    public static function apply(
        Member $member,
        float $amount,
        int $duration,
        float $interestRate
    ) {
        // 1️⃣ Must be active
        if ($member->status !== 'active') {
            throw new \Exception('Member not active');
        }

        // 2️⃣ No active loan
        if (Loan::where('member_id', $member->id)
            ->whereIn('status', ['applied', 'guaranteed', 'approved', 'disbursed'])
            ->exists()
        ) {
            throw new \Exception('Member has an active loan');
        }

        // 3️⃣ Contribution limit
        $totalContributions = Contribution::where('member_id', $member->id)->sum('amount');

        if ($amount > $totalContributions * 3) {
            throw new \Exception('Loan exceeds allowed limit');
        }

        return DB::transaction(function () use ($member, $amount, $duration, $interestRate) {
            return Loan::create([
                'member_id' => $member->id,
                'amount' => $amount,
                'interest_rate' => $interestRate,
                'duration_months' => $duration,
                'status' => 'applied'
            ]);
        });
    }


    public static function addGuarantor(
        Loan $loan,
        Member $guarantor,
        float $amount
    ) {
        if ($loan->status !== 'applied') {
            throw new \Exception('Cannot guarantee this loan');
        }

        return LoanGuarantor::create([
            'loan_id' => $loan->id,
            'member_id' => $guarantor->id,
            'amount' => $amount,
            'approved' => false
        ]);
    }


    public static function approveGuarantor(
        Loan $loan,
        Member $guarantor
    ) {
        if ($loan->status !== 'applied') {
            throw new \Exception('Loan not in guarantor stage');
        }

        $record = LoanGuarantor::where('loan_id', $loan->id)
            ->where('member_id', $guarantor->id)
            ->firstOrFail();

        if ($record->approved) {
            throw new \Exception('Guarantor already approved');
        }

        return DB::transaction(function () use ($loan, $record) {

            // 1️⃣ Approve guarantor
            $record->update(['approved' => true]);

            // 2️⃣ Check total coverage
            $totalGuaranteed = $loan->approvedGuarantors()->sum('amount');

            if ($totalGuaranteed >= $loan->amount) {
                $loan->update(['status' => 'guaranteed']);
            }

            return $record;
        });
    }
}
