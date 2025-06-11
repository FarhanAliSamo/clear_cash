@extends('layouts.customer')
@section('styles_in_head')
    {{-- Add your link below --}}
    <link rel="stylesheet" href="{{asset('build/assets/account-setup.css')}}">
@endsection
@section('content')
    <style>
        header,
        aside.sidebar {
            display: none;
        }
        main.dashboardMain {
            padding-top: 2rem;
            width: 100%;
        }
        main.dashboardMain.full {
            padding-top: 2rem;
        }
    </style>

    <section class="setupStepsWrapper">
        <div class="container">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="setupStepsWrap">
                        <div class="titles">
                            <div class="item ">Create your budget</div>
                            <div class="sep"></div>
                            <div class="item active">Add bank accounts</div>
                            <div class="sep"></div>
                            <div class="item">Add your investments and pensions</div>
                            <div class="sep"></div>
                            <div class="item">Done</div>
                        </div>
                        <div class="boxes">
                            <div class="box active"></div>
                            <div class="box active "></div>
                            <div class="box active"></div>
                            <div class="box active"></div>
                            <div class="box"></div>
                            <div class="box"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row  ">
                <div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
                    <h1>Add bank accounts</h1>
                    <p>
                        Add each of your bank accounts so you can easily keep track of all your spending and current balances.
                    </p>
                </div>
            </div>
           
            <div class="row mt-md-4 mt-0">
                <div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
                    <form action="{{ route('account-setup-step-five-store') }}" method="post">
                        @csrf
                        <div class="bankDetailsInputMainWrap">
                            <div class="bankItem">
                                <div class="row">
                                    <div class="col-12">
                                        <label for="name_of_bank_account">Name of bank</label>
                                        <input type="text" name="name_of_bank_account[]" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <label for="bank_account_type">Account type</label>
                                        <select name="bank_account_type[]" required>
                                            <option value="" disabled selected>Select an option...</option>
                                            <option value="current_account">Current Account</option>
                                            <option value="savings_account">Savings Account</option>
                                            <option value="isa_account">ISA Account</option>
                                            <option value="investment_account">Investment Account</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <label for="bank_account_starting_balance">Starting balance</label>
                                        <input type="number" name="bank_account_starting_balance[]" step="any" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 d-flex justify-content-end">
                                        <button type="button" class="removeBankBtn">Remove</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row my-4">
                            <div class="col-12 text-center">
                                <button type="button" class="addAnotherBankBtn">
                                    <i class="fas fa-plus-circle"></i> Add another bank account
                                </button>
                            </div>
                        </div>

                        <div class="row align-items-center my-4">
                            <div class="col-6 d-flex justify-content-start">
                                <a class="setupStepsBackButton" href="{{ route('account-setup.step-four') }}">Back</a>
                            </div>
                            <div class="col-6 d-flex justify-content-end">
                                <button type="submit" class="twoToneBlueGreenBtn">Continue</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const addAnotherBankBtn = document.querySelector(".addAnotherBankBtn");
            const bankDetailsWrapper = document.querySelector(".bankDetailsInputMainWrap");

            addAnotherBankBtn.addEventListener("click", function () {
                const firstBankItem = bankDetailsWrapper.querySelector(".bankItem");
                const newBankItem = firstBankItem.cloneNode(true);

                // Clear input values in the cloned bankItem
                newBankItem.querySelectorAll("input, select").forEach(field => {
                    if (field.tagName === "SELECT") {
                        field.selectedIndex = 0;
                    } else {
                        field.value = "";
                    }
                });

                // Add a remove button if it doesn’t exist
                let removeBtn = newBankItem.querySelector(".removeBankBtn");
                if (!removeBtn) {
                    removeBtn = document.createElement("button");
                    removeBtn.type = "button";
                    removeBtn.className = "removeBankBtn";
                    removeBtn.textContent = "Remove";
                    newBankItem.appendChild(removeBtn);
                }

                bankDetailsWrapper.appendChild(newBankItem);
            });

            // Remove bank item when the remove button is clicked
            bankDetailsWrapper.addEventListener("click", function (event) {
                if (event.target.classList.contains("removeBankBtn")) {
                    const bankItem = event.target.closest(".bankItem");
                    if (bankDetailsWrapper.querySelectorAll(".bankItem").length > 1) {
                        bankItem.remove();
                    }
                }
            });
        });
    </script>

@endsection
