<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Budget;
use App\Models\BudgetCategory;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get the transactions for the user
        $transactions = Transaction::where('user_id', Auth::user()->id)
            ->with('category')
            ->orderBy('date', 'desc')
            ->get();

        // Group transactions by the date (assuming date is in Y-m-d format)
        $groupedTransactions = $transactions->groupBy(function($transaction) {
            return \Carbon\Carbon::parse($transaction->date)->format('Y-m-d'); // Format the date to group by
        });

        $bankAccounts = BankAccount::where('user_id', Auth::user()->id)
            ->orderBy('account_name', 'asc')
            ->where('account_name', '!=', 'pension')
            ->get();

        return view('customer.pages.transactions.index', compact('groupedTransactions', 'bankAccounts', 'transactions'));
    }

    public function filterByBank($bankName) {
        $bank = BankAccount::whereRaw("LOWER(REPLACE(account_name, ' ', '-')) = ?", [strtolower($bankName)])->firstOrFail();

        $bankId = $bank->id;

        // Get the transactions for the user
        $transactions = Transaction::where('user_id', Auth::user()->id)
            ->with('category')
            ->orderBy('date', 'desc')
            ->where('bank_account_id', $bankId)
            ->get();

        // Group transactions by the date (assuming date is in Y-m-d format)
        $groupedTransactions = $transactions->groupBy(function($transaction) {
            return \Carbon\Carbon::parse($transaction->date)->format('Y-m-d'); // Format the date to group by
        });
        $transactions = Transaction::where('user_id', Auth::user()->id)
            ->with('category')
            ->where('bank_account_id', $bank->id)
            ->orderBy('date', 'desc')
            ->get();

        $bankAccounts = BankAccount::where('user_id', Auth::user()->id)
            ->orderBy('account_name', 'asc')
            ->where('account_name', '!=', 'pension')
            ->get();

        $categories = Budget::where('user_id', Auth::user()->id)
            ->get();

        return view('customer.pages.transactions.filter-by-bank', compact('transactions', 'bank', 'bankAccounts', 'categories', 'groupedTransactions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $bankAccounts = BankAccount::where('user_id', Auth::user()->id)
            ->orderBy('account_name', 'asc')
            ->get();

        $categories = Budget::where('user_id', Auth::user()->id)
            ->get();

            // dd($categories);
        return view('customer.pages.transactions.create', compact('bankAccounts','categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'category' => ['required', 'integer'],
            'bank_account' => ['required', 'integer'],
            // 'category' => ['required', 'integer', 'exists:budget_categories,id'],
            // 'bank_account' => ['required', 'integer', 'exists:bank_accounts,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'transaction_type' => ['required', 'string', 'max:255'],
            'internal_transfer' => ['nullable', 'boolean'],
        ]);

        $isInternalTransfer = $request->has('internal_transfer') ? true : false;


        $relatedCategory = Budget::where('user_id', Auth::user()->id)
            ->where('id', $validated['category'])
            ->first();

        $transaction = Transaction::create([
            'name' => $validated['name'],
            'date' => $validated['date'],
            'category_name' => $relatedCategory->category_name,
            'category_id' => $validated['category'],
            'bank_account_id' => $validated['bank_account'],
            'amount' => $validated['amount'],
            'transaction_type' => $validated['transaction_type'],
            'internal_transfer' => $isInternalTransfer,
            'user_id' => Auth::user()->id,

        ]);

        /*$budget = Budget::where('user_id', Auth::user()->id)
            ->where('category_id', $validated['category'])
            ->first();
        $budgetCurrentBalance = $budget->amount;*/

        $bankAccount = BankAccount::where('id', $validated['bank_account'])->first();
        $currentBalance = $bankAccount->starting_balance;

        if($validated['transaction_type'] == 'income') {
            $amountToAdd = $currentBalance + $validated['amount'];
            $bankAccount->update([
                'starting_balance' => $amountToAdd,
            ]);
            /*$budgetAdd = $budgetCurrentBalance + $validated['amount'];*/
            /*$budget->update([
                'amount' => $budgetAdd,
            ]);*/
        }
        else {
            $amountToTake = $currentBalance - $validated['amount'];
            $bankAccount->update([
                'starting_balance' => $amountToTake,
            ]);
            /*$budgetRemove = $budgetCurrentBalance - $validated['amount'];*/
            /*$budget->update([
                'amount' => $budgetRemove,
            ]);*/
        }

        return redirect()->route('transactions.index')->with('success', 'Transaction recorded successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $transaction = Transaction::findOrFail($id);

        $currentAmount = $transaction->amount;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'category' => ['required', 'integer', 'exists:budget_categories,id'],
            'bank_account' => ['required', 'integer', 'exists:bank_accounts,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'transaction_type' => ['required', 'string', 'max:255'],
            'internal_transfer' => ['nullable', 'boolean'],
        ]);

        $isInternalTransfer = $request->has('internal_transfer') ? true : false;

        $transaction->update([
            'name' => $validated['name'],
            'date' => $validated['date'],
            'category_id' => $validated['category'],
            'bank_account_id' => $validated['bank_account'],
            'amount' => $validated['amount'],
            'transaction_type' => $validated['transaction_type'],
            'internal_transfer' => $isInternalTransfer,
            'user_id' => Auth::user()->id,

        ]);

        $bankAccount = BankAccount::where('id', $validated['bank_account'])->first();
        $currentBalance = $bankAccount->starting_balance;

        if($validated['transaction_type'] == 'income') {
            $amountToAdd = $currentBalance - $currentAmount + $validated['amount'];
            $bankAccount->update([
                'starting_balance' => $amountToAdd,
            ]);
        }
        else {
            $amountToTake = $currentBalance + $currentAmount - $validated['amount'];
            $bankAccount->update([
                'starting_balance' => $amountToTake,
            ]);
        }

        return redirect()->route('transactions.index')->with('success', 'Transaction updated successfully.');
    }

    public function globalAddTransaction(Request $request) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'category' => ['required', 'integer'],
            'bank_account' => ['required', 'integer'],
            // 'category' => ['required', 'integer', 'exists:budget_categories,id'],
            // 'bank_account' => ['required', 'integer', 'exists:bank_accounts,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'transaction_type' => ['required', 'string', 'max:255'],
            'internal_transfer' => ['nullable', 'boolean'],
        ]);

        $isInternalTransfer = $request->has('internal_transfer') ? true : false;


        $relatedCategory = Budget::where('user_id', Auth::user()->id)
            ->where('id', $validated['category'])
            ->first();

        $transaction = Transaction::create([
            'name' => $validated['name'],
            'date' => $validated['date'],
            'category_name' => $relatedCategory->category_name,
            'category_id' => $validated['category'],
            'bank_account_id' => $validated['bank_account'],
            'amount' => $validated['amount'],
            'transaction_type' => $validated['transaction_type'],
            'internal_transfer' => $isInternalTransfer,
            'user_id' => Auth::user()->id,

        ]);

        /*$budget = Budget::where('user_id', Auth::user()->id)
            ->where('category_id', $validated['category_id'])
            ->first();
        $budgetCurrentBalance = $budget->amount;*/

        $bankAccount = BankAccount::where('id', $validated['bank_account'])->first();
        $currentBalance = $bankAccount->starting_balance;

        if($validated['transaction_type'] == 'income') {
            $amountToAdd = $currentBalance + $validated['amount'];
            $bankAccount->update([
                'starting_balance' => $amountToAdd,
            ]);
            /*$budgetAdd = $budgetCurrentBalance + $validated['amount'];*/
            /*$budget->update([
                'amount' => $budgetAdd,
            ]);*/
        }
        else {
            $amountToTake = $currentBalance - $validated['amount'];
            $bankAccount->update([
                'starting_balance' => $amountToTake,
            ]);
            /*$budgetRemove = $budgetCurrentBalance - $validated['amount'];*/
            /*$budget->update([
                'amount' => $budgetRemove,
            ]);*/
        }

        return redirect()->back()->with('success', 'Transaction recorded successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $transaction = Transaction::where('id', $id)
            ->where('user_id', Auth::user()->id)
            ->first();

        $bankAccount = BankAccount::where('id', $transaction->bank_account_id)->first();
        $currentBalance = $bankAccount->starting_balance;


        if($transaction->transaction_type == 'income') {
            $bankAccount->update([
                'starting_balance' => $currentBalance - $transaction->amount,
            ]);
        }
        else {
            $bankAccount->update([
                'starting_balance' => $currentBalance + $transaction->amount,
            ]);
        }

        $transaction->delete();

        return redirect()->route('transactions.index')->with('success', 'Transaction removed successfully.');

    }
}

