## Chama App

Chama App Implementation concept Tinkering around


This cover and understanding on **(Ledger Foundation)**, **(Contributions)**, and ** (Loans & Guarantors)** into a single, end-to-end **`php artisan tinker` execution guide**.

> Goal: for the chama system to **register members, post contributions, issue loans, attach guarantors, and maintain a clean financial ledger**.

---

## DEVTEST 1: Ledger Foundation

### 1. Open Tinker

```bash
php artisan tinker
```

### 2. Create Accounts

```php
use App\Models\Account;

$cash = Account::create([
    'name' => 'Cash Account',
    'type' => 'asset',
    'balance' => 0
]);

$contributions = Account::create([
    'name' => 'Member Contributions',
    'type' => 'liability',
    'balance' => 0
]);

$loanReceivable = Account::create([
    'name' => 'Loan Receivable',
    'type' => 'asset',
    'balance' => 0
]);
```

### 3. Verify Accounts

```php
Account::all();
```

---

## DEVTEST 2: Members & Contributions

### 4. Create Members

```php
use App\Models\Member;

$m1 = Member::create([
    'name' => 'Kelvin Barry',
    'email' => 'kelvin@example.com'
]);

$m2 = Member::create([
    'name' => 'Jane Doe',
    'email' => 'jane@example.com'
]);
```

### 5. Post Monthly Contributions

```php
use App\Services\ContributionService;

ContributionService::postMonthlyContribution($m1, 5000, 'JAN', 2026);
ContributionService::postMonthlyContribution($m2, 5000, 'JAN', 2026);
```

### 6. Verify Contributions

```php
use App\Models\Contribution;
Contribution::with('transaction')->get();
```

### 7. Verify Ledger Impact

```php
use App\Models\Transaction;
Transaction::all();

Account::all();
```

---

## DEVTEST 3: Loans & Guarantors

### 8. Create a Loan

```php
use App\Services\LoanService;

$loan = LoanService::applyLoan(
    member: $m1,
    amount: 20000,
    months: 6
);
```

### 9. Add Guarantors

```php
LoanService::addGuarantor($loan, $m2, 10000);
```

### 10. Approve Loan

```php
LoanService::approveLoan($loan);
```

### 11. Verify Loan & Guarantors

```php
use App\Models\Loan;

Loan::with(['member', 'guarantors'])->get();
```

### 12. Verify Ledger Entries

```php
Transaction::where('reference_type', 'loan')->get();
Account::all();
```

---

## Completion Checklist

* [x] Members created
* [x] Contributions posted
* [x] Ledger balanced
* [x] Loan issued
* [x] Guarantors attached
* [x] Accounts updated correctly

---

## For Noting

* `transaction_id` on contributions **must be nullable** for this tinker tests
* All money movement must go through the ledger
* Never update account balances manually

---
