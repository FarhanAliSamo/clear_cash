@extends('layouts.customer')
@section('content')
    <section class="pageTitleBanner">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1>Bank Accounts</h1>
                </div>
            </div>
        </div>
    </section>
    @if($bankAccounts->isNotEmpty())
        <section class="addBanner">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="btn-group">
                            <button type="button" class="twoToneBlueGreenBtn" data-bs-toggle="modal" data-bs-target="#addBankAccountModal">
                                Add Bank Account
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <section class="bankAccountsMainList">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    @if($bankAccounts->isNotEmpty())
                        @foreach($bankAccounts as $account)
                            <div class="bankItem">
                                <button class="modalBtn" type="button" data-bs-toggle="modal" data-bs-target="#{{ str_replace(' ', '_', $account->account_name) }}" >
                                    <div class="row align-items-center">
                                        <div class="col-8">
                                            <h4>{{ $account->account_name }}</h4>
                                            <h6>{{ str_replace('_', ' ', $account->account_type) }}</h6>
                                        </div>
                                        <div class="col-4" style="text-align: right">
                                            <div class="balance">
                                                £{{ number_format($account->starting_balance, 2) }}
                                            </div>
                                        </div>
                                    </div>
                                </button>
                                <div class="modal fade" id="{{ str_replace(' ', '_', $account->account_name) }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <h1>Edit {{ $account->account_name }} Bank Account</h1>
                                                <form action="{{ route('bank-accounts.update', $account->id) }}" method="post">
                                                    @csrf
                                                    @method('put')
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <label for="name_of_bank_account">Name of bank</label>
                                                            <input type="text" name="name_of_bank_account" id="name_of_bank_account" value="{{ old('name_of_bank_account', $account->account_name) }}">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <label for="bank_account_type">Account type</label>
                                                            <select name="bank_account_type" id="bank_account_type">
                                                                <option value="current_account" @if($account->account_type == 'current_account') selected @endif>Current Account</option>
                                                                <option value="savings_account" @if($account->account_type == 'savings_account') selected @endif>Savings Account</option>
                                                                <option value="isa_account" @if($account->account_type == 'isa_account') selected @endif>ISA Account</option>
                                                                <option value="investment_account" @if($account->account_type == 'investment_account') selected @endif>Investment Account</option>
                                                                <option value="pension"  @if($account->account_type == 'pension') selected @endif>Pension</option>
                                                                <option value="investment"  @if($account->account_type == 'investment') selected @endif>Investments</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <label for="bank_account_starting_balance">Starting balance</label>
                                                            <input type="number" name="bank_account_starting_balance" id="bank_account_starting_balance" value="{{ old('bank_account_starting_balance', $account->starting_balance) }}">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-4 offset-md-8 d-md-flex justify-content-md-end">
                                                            <button type="submit" class="twoToneBlueGreenBtn text-center py-2">Update</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="modal-footer">

                                                <form action="{{ route('bank-accounts.destroy', $account->id) }}" method="post">
                                                    @csrf
                                                    @method('delete')
                                                    <button type="submit" class="dangerBtn">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="noAccountNotice text-center">
                            <i class="fa-solid fa-building-columns"></i>
                            <h2>No bank accounts added yet</h2>
                            <p>
                                Add each of your bank accounts so you can easily keep track of all your spending and current balances.
                            </p>
                            <button type="button" class="twoToneBlueGreenBtn" data-bs-toggle="modal" data-bs-target="#addBankAccountModal">
                                Add Bank Account
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="addBankAccountModal" tabindex="-1" aria-labelledby="addBankAccountModal" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <h1>Add Bank Account</h1>
                    <form action="{{ route('bank-accounts.store') }}" method="post">
                        @csrf
                        <div class="row">
                            <div class="col-12">
                                <label for="name_of_bank_account">Name of bank</label>
                                <input type="text" name="name_of_bank_account" id="name_of_bank_account" >
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <label for="bank_account_type">Account type span</label>
                                <select name="bank_account_type" id="bank_account_type">
                                    <option value="" disabled selected>Select an option...</option>
                                    <option value="current_account">Current Account</option>
                                    <option value="savings_account">Savings Account</option>
                                    <option value="isa_account">ISA Account</option>
                                    <option value="investment_account">Investment Account</option>
                                    <option value="pension">Pension</option>
                                    <option value="investment">Investments</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <label for="bank_account_starting_balance">Starting balance</label>
                                <input type="number" name="bank_account_starting_balance" id="bank_account_starting_balance" step="any">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="twoToneBlueGreenBtn">Add Bank Account</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
