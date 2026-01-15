<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Exception;

class LoanRepaymentService
{
    public static function repay(Loan $loan, float $amount): LoanRepayment
    {
        return DB::transaction(function () use ($loan, $amount) {

            if ($loan->status !== 'approved') {
                throw new Exception('Loan is not active');
            }

            if ($amount <= 0) {
                throw new Exception('Invalid repayment amount');
            }

            $cash = Account::where('name', 'Cash Account')->firstOrFail();
            $loanReceivable = Account::where('name', 'Loan Receivable')->firstOrFail();
            $interestIncome = Account::where('name', 'Interest Income')->firstOrFail();

            // 🔢 Interest logic (flat monthly)
            $monthlyInterestRate = $loan->interest_rate / 100 / 12;
            $interestDue = round($loan->outstanding_balance * $monthlyInterestRate, 2);

            $principalPaid = max(0, $amount - $interestDue);

            if ($principalPaid > $loan->outstanding_balance) {
                $principalPaid = $loan->outstanding_balance;
            }

            // 🧾 Ledger transaction
            $transaction = Transaction::create([
                'reference' => 'LOAN-REPAY-' . now()->timestamp,
                'description' => 'Loan Repayment',
            ]);

            // Debit Cash
            $transaction->entries()->create([
                'account_id' => $cash->id,
                'type' => 'debit',
                'amount' => $amount,
            ]);

            // Credit Loan Receivable (principal)
            if ($principalPaid > 0) {
                $transaction->entries()->create([
                    'account_id' => $loanReceivable->id,
                    'type' => 'credit',
                    'amount' => $principalPaid,
                ]);
            }

            // Credit Interest Income
            if ($interestDue > 0) {
                $transaction->entries()->create([
                    'account_id' => $interestIncome->id,
                    'type' => 'credit',
                    'amount' => $interestDue,
                ]);
            }

            // 🧠 Save repayment record
            $repayment = LoanRepayment::create([
                'loan_id' => $loan->id,
                'amount' => $amount,
                'principal' => $principalPaid,
                'interest' => $interestDue,
                'transaction_id' => $transaction->id,
            ]);

            // 🔻 Reduce outstanding balance
            $loan->decrement('outstanding_balance', $principalPaid);

            // 🟢 Close loan if fully paid
            if ($loan->outstanding_balance <= 0) {
                $loan->update(['status' => 'closed']);
            }

            return $repayment;
        });
    }
}
