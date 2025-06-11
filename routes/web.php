<?php

use App\Http\Controllers\AccountSetupController;
use App\Http\Controllers\Admin\AdminActivityLogController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminImageUploadController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Customer\CustomerBankAccountController;
use App\Http\Controllers\Customer\CustomerBudgetController;
use App\Http\Controllers\Customer\CustomerDashboardController;
use App\Http\Controllers\Customer\CustomerMyAccountController;
use App\Http\Controllers\Customer\CustomerRecurringPayments;
use App\Http\Controllers\Customer\CustomerTransactionController;
use App\Http\Controllers\Staff\StaffDashboardController;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

//Account Setup
Route::middleware(['auth', 'verified'])->group(function(){
    Route::get('account-setup-step-one', [AccountSetupController::class, 'index'])->name('account-setup.step-one');
    Route::post('account-setup-step-one-store', [AccountSetupController::class, 'indexStore'])->name('account-setup.step-one-store');
    Route::get('account-setup-step-two', [AccountSetupController::class, 'stepTwoShow'])->name('account-setup.step-two');
    Route::post('account-setup-step-two-store', [AccountSetupController::class,'stepTwoStore'])->name('account-setup-step-two-store');
    Route::get('account-setup-step-three', [AccountSetupController::class, 'stepThreeShow'])->name('account-setup.step-three');
    Route::post('account-setup-step-three-store', [AccountSetupController::class, 'stepThreeStore'])->name('account-setup-step-three-store');
    Route::get('account-setup-step-four', [AccountSetupController::class, 'stepFourShow'])->name('account-setup.step-four');
    Route::post('account-setup-step-four-store', [AccountSetupController::class, 'stepFourStore'])->name('account-setup-step-four-store');
    Route::get('account-setup-step-five', [AccountSetupController::class, 'stepFiveShow'])->name('account-setup.step-five');
    Route::post('account-setup-step-five-store', [AccountSetupController::class, 'stepFiveStore'])->name('account-setup-step-five-store');
    Route::get('account-setup-step-six', [AccountSetupController::class, 'stepSixShow'])->name('account-setup.step-six');
    Route::get('account-setup-step-six-investments', [AccountSetupController::class, 'stepSixInvestmentsShow'])->name('account-setup.step-six-investments');
    Route::post('account-setup-step-six-investments-store', [AccountSetupController::class, 'stepSixInvestmentsStore'])->name('account-setup.step-six-investments-store');
    Route::get('account-setup-step-seven', [AccountSetupController::class, 'stepSevenShow'])->name('account-setup.step-seven');
});

//Client / Customer Routes
Route::middleware(['auth', 'role:super admin|customer', 'verified'])->group(function () {
    
   
    Route::post('reset-account', [CustomerMyAccountController::class, 'resetAccount'])->name('reset-account');
    
    Route::get('dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');

    //Bank Account
    Route::resource('bank-accounts', CustomerBankAccountController::class);
    Route::post('bank-accounts/global-add-bank-account', [CustomerBankAccountController::class, 'globalAddBankAccount'])->name('bank-accounts.global-add-bank-account');

    //Budget
    Route::prefix('budget')->name('budget.')->group(function(){
        Route::get('/', [CustomerBudgetController::class, 'index'])->name('index');
        Route::put('update/{id}', [CustomerBudgetController::class, 'update'])->name('update');
        Route::put('reset-budget/{id}', [CustomerBudgetController::class, 'resetBudget'])->name('reset-budget');
        Route::get('edit-category-list', [CustomerBudgetController::class, 'editCategoryList'])->name('edit-category-list');
        Route::put('update-category-list', [CustomerBudgetController::class, 'updateCategoryList'])->name('update-category-list');
        Route::post('global-add-budget', [CustomerBudgetController::class, 'globalAddNewBudget'])->name('global-add-budget');
    });

    //My Account
    Route::name('my-account.')->prefix('my-account')->group(function(){
        Route::get('/', [CustomerMyAccountController::class, 'index'])->name('index');
        Route::put('main-details-store/{id}', [CustomerMyAccountController::class, 'mainDetailsStore'])->name('main-details-store');
        Route::put('password-update-store/{id}', [CustomerMyAccountController::class, 'passwordUpdateStore'])->name('password-update-store');
        Route::delete('delete-account/{id}', [CustomerMyAccountController::class, 'destroy'])->name('delete-account');
    });

    //Recurring Payments
    Route::resource('recurring-payments', CustomerRecurringPayments::class);
    Route::post('recurring-payment/add-recurring-payment', [CustomerRecurringPayments::class, 'globalAddRecurringPayments'])->name('recurring-payment.add-recurring-payment');

    //transactions
    Route::resource('transactions', CustomerTransactionController::class);
    Route::post('transactions/global-add-transaction', [CustomerTransactionController::class, 'globalAddTransaction'])->name('transactions.global-add-transaction');

    Route::get('transactions-filter-by-bank/{bank}', [CustomerTransactionController::class, 'filterByBank'])->name('transactions.filter-by-bank');
});

//Admin Routes
Route::middleware(['auth', 'role:super admin|admin'])->name('admin.')->prefix('admin')->group(function () {
    Route::get('dashboard', [AdminDashboardController::class,'index'])->name('dashboard');

    //Activity Log
    Route::name('activity-log.')->prefix('activity-log')->group(function () {
        Route::get('/', [AdminActivityLogController::class, 'index'])->name('index');
        Route::post('clear', [AdminActivityLogController::class, 'clearActivityLog'])->name('clear');

    });

    //File/image upload for TINYMCE WYSIWYG Editor
    ROute::post('upload-image', [AdminImageUploadController::class, 'upload'])
        ->name('upload-image')
        ->middleware('web');
});

//Staff Routes
Route::middleware(['auth', 'role:super admin|staff'])->name('staff.')->prefix('staff')->group(function () {
    Route::get('dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');
});




//Auth Routes
Auth::routes(['verify' => true]);

//Routes to handle the email Authentication redirects
Route::get('/email/verify', function(){
    return view('auth.verify-email');
})->middleware('guest')->name('verification.notice');

Route::get('email-verified', function(){
    return view('auth.email-verified');
})->middleware(['auth','guest'])->name('email-verified');

/*Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/email-verified');
})->middleware(['auth', 'signed'])->name('verification.verify');*/

Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = User::findOrFail($id);

    if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        abort(403); // Prevent unauthorized verification
    }

    if (!$user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
        event(new Verified($user)); // Trigger event
    }

    // Automatically log in the user after verification
    Auth::login($user);


    // return redirect()->route('account-setup.step-one');
    return redirect('/email-verified');
})->middleware(['signed'])->name('verification.verify');

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
//Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


