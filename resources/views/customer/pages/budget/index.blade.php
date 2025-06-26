@extends('layouts.customer')



@section('content')
    <section class="pageTitleBanner">
        <div class="container">
            <div class="row">
                <div class="col-8">
                    <h1>Budget</h1>
                </div>
                <div class="col-4 d-flex justify-content-end">
                    {{-- <a href="" class="editItemBtn">
                        <i class="fas fa-cog"></i>
                    </a> --}}
                </div>
            </div>
        </div>
    </section>

    <section class="budgetTotalBudgetBanner">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2>Total Budget</h2>
                </div>
            </div>
            <div class="budgetChartWrapper text-center my-5">
                <canvas id="budgetChart" width="300" height="300"></canvas>
                <h4 class="mt-3 fw-bold">Clearcash
                    left <span id="remainingAmount">£{{ number_format($remainingBudget, 2) }}</span>
                </h4>
                <p class="text-muted">Spent £{{ number_format($amountSpent, 2) }} out of
                    £{{ number_format($totalBudget, 2) }}</p>
            </div>

            <style>
                .budgetChartWrapper {
                    position: relative;
                    width: 300px;
                    margin: 0 auto;
                }

                .budgetChartWrapper h4,
                .budgetChartWrapper p {
                    color: white !important;
                }

                .budgetChartWrapper h4,
                .budgetChartWrapper p {
                    color: white !important;
                }
            </style>

            {{-- <style>
                .budgetChartWrapper {
                    position: relative;
                    width: 300px;
                    margin: 0 auto;
                }
            </style> --}}

            <script>
                const categoryLabels = [
                    @foreach ($categoryDetails as $item)
                        "{{ str_replace('_', ' ', $item['budgetItem']->category_name) }}",
                    @endforeach
                    "Remaining"
                ];

                const categorySpentAmounts = [
                    @foreach ($categoryDetails as $item)
                        {{ $item['totalSpent'] }},
                    @endforeach
                    {{ $remainingBudget }}
                ];

                // Nice pastel colors
                // const categoryColors = [
                //     '#FF6B6B', '#FFD93D', '#', '#4D96FF', '#c', '#F38BA0', '#FFAC81', '#33BBC5', '#44E0AC'
                // ];

                const baseColors = ['#FF6B6B', '#FFD93D', '#6BCB77', '#A66DD4', '#F38BA0', '#FFAC81', '#33BBC5', '#44E0AC'];
                const categoryColors = [];

                @foreach ($categoryDetails as $index => $item)
                    categoryColors.push(baseColors[{{ $loop->index }} % baseColors.length]);
                @endforeach

                categoryColors.push("#183236"); // last me remaining ka color


                // 183236
                // while (categoryColors.length < categoryLabels.length - 1) {
                //     categoryColors.push('#CCCCCC'); // ya koi default color dal do jo categories ke liye ho
                // }

                // categoryColors.push("#4D96FF"); // Remaining ka color fixed

                // // Remaining ka color fix: #183236
                // categoryColors.push("#183236");

                const totalAmount = {{ $totalBudget }};
                const amountSpent = {{ $amountSpent }};
                const remainingAmount = totalAmount - amountSpent;

                // Chart.js Doughnut Chart
                const ctx = document.getElementById("budgetChart").getContext("2d");
                const budgetChart = new Chart(ctx, {
                    type: "doughnut",
                    data: {
                        labels: categoryLabels,
                        datasets: [{
                            data: categorySpentAmounts,
                            backgroundColor: categoryColors,
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        cutout: '70%', // Chart.js 3+ uses 'cutout' instead of 'cutoutPercentage'
                        plugins: {
                            legend: {
                                position: "bottom",
                                labels: {
                                    color: "#ffffff", // updated for Chart.js 3+
                                    boxWidth: 15,
                                    padding: 20
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const value = context.raw;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = ((value / total) * 100).toFixed(1);
                                        return `${context.label}: £${value.toFixed(2)} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });




                // Remaining amount ka color update
                document.getElementById("remainingAmount").style.color = remainingAmount < 0 ? "#183236" : "#44E0AC";
            </script>

        </div>
    </section>


    <section class="categoryBudgetsWrapper">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="row align-items-center">
                        <div class="col-9">
                            <h2>Category Budgets</h2>
                        </div>
                        <div class="col-3 d-md-flex justify-content-md-end">
                            <a class="editCatListBtn" href="{{ route('budget.edit-category-list') }}"><i
                                    class="fas fa-pencil"></i>Edit</a>
                        </div>
                    </div>
                    <div class="inner">


                        @foreach ($categoryDetails as $item)
                            <div class="catItem">
                                <button type="button" class="modalBtn" data-bs-toggle="modal"
                                    data-bs-target="#modal-{{ str_replace(' ', '-', $item['budgetItem']->category_name) }}">
                                    <div class="row px-0 align-items-start">
                                        <div class="col-8">
                                            <h5>{{ str_replace('_', ' ', $item['budgetItem']->category_name) }}</h5>
                                            <h6>
                                                £@if ($item['totalSpent'] == '0')
                                                    {{ number_format($item['budget']->amount, 2) }}
                                                @elseif($item['totalSpent'] > $item['budget']->amount)
                                                    0.00 @else{{ number_format($item['remainingAmount'], 2) }}
                                                @endif <span class="px-1 opacity-75"> left of </span>
                                                £{{ number_format($item['budget']->amount, 2) }}
                                            </h6>
                                        </div>
                                        <div class="col-4" style="text-align: right;">
                                            <span class="spentAmount"
                                                @if ($item['totalSpent'] >= $item['budget']->amount) style="color: #D21414;" @endif>
                                                £{{ number_format($item['totalSpent'], 2) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="row px-0">
                                        <div class="col-12">
                                            <div class="progress" role="progressbar" aria-label=""
                                                aria-valuenow="{{ $item['startingBudgetAmount'] }}" aria-valuemin="0"
                                                aria-valuemax="{{ $item['startingBudgetAmount'] }}">
                                                <div class="progress-bar"
                                                    style=" @if ($item['totalSpent'] >= $item['budget']->amount) background: linear-gradient(to top right, #D21414, #F96565); width: 100%; @else width: {{ $item['spentPercentage'] }}% @endif">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </button>


                                {{-- // to change  --}}
                                <div class="modal fade"
                                    id="modal-{{ str_replace(' ', '-', $item['budgetItem']->category_name) }}"
                                    tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                        <div class="modal-content ">
                                            <div class="modal-header">
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            <div class="modal-body">


                                                <div class="transactionList mb-3 pt-0 mt-0">

                                                    <ul class="list-group mt-0 pt-0">

                                                        <li class="list-group-item d-flex justify-content-between align-items-center my-1"
                                                            style="background-color: #d1f9ff0d ;border:none;">
                                                            <div class="d-flex flex-column">
                                                                <span class="fs-5 fw-semibold text-white">

                                                                    You have <span style="color:#31d2f7"> £


                                                                        @if ($item['totalSpent'] == '0')
                                                                        {{ number_format($item['budget']->amount, 2) }}
                                                                        @elseif($item['totalSpent'] > $item['budget']->amount)
                                                                        0.00
                                                                        @else{{ number_format($item['remainingAmount'], 2) }}
                                                                        @endif

                                                                    </span>

                                                                     left for this category on your budget - ( £ {{ number_format($item['budget']->amount, 2) }} )




                                                                </span>

                                                            </div>
                                                        </li>

                                                    </ul>
                                                </div>


                                                {{-- Transaction List --}}
                                                @if (count($item['transactions']) > 0)
                                                    <div class="transactionList">
                                                        <h4 class="mb-3 fw-semibold text-white">Recent Expenses</h4>

                                                        <ul class="list-group">
                                                            @foreach ($item['transactions'] as $transaction)
                                                                <li class="list-group-item d-flex justify-content-between align-items-center my-1"
                                                                    style="background-color: #d1f9ff0d ;border:none;">
                                                                    <div class="d-flex flex-column">
                                                                        <span
                                                                            class="fs-5 fw-semibold text-white">{{ $transaction->name ?? 'No Name' }}</span>
                                                                        <small
                                                                            class="text-white">{{ \Carbon\Carbon::parse($transaction->date)->format('d M, Y') }}</small>
                                                                    </div>
                                                                    <span
                                                                        class="badge bg-danger fs-6">£{{ number_format($transaction->amount, 2) }}</span>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @else
                                                    <ul class="list-group">

                                                        <li class="list-group-item d-flex justify-content-between align-items-center"
                                                            style="background-color: #d1f9ff0d ;border:none;">
                                                            <div class="d-flex flex-column">
                                                                <span class="text-white">No expenses recorded yet for this
                                                                    category</span>
                                                                {{-- <p class=" text-white">No expenses recorded yet for this category.</p> --}}

                                                            </div>

                                                        </li>

                                                    </ul>
                                                    {{-- <p class="mt-4 text-muted text-white">No expenses recorded yet for this category.</p> --}}
                                                @endif

                                                {{-- Edit Budget Section --}}
                                                <div class="edit-budget-section mt-4">
                                                    <h4 class="fw-bold text-white mb-3">Edit
                                                        {{ $item['budgetItem']->category_name }} Budget</h4>

                                                    <form action="{{ route('budget.update', $item['budget']->id) }}"
                                                        method="post">
                                                        @csrf
                                                        @method('put')

                                                        <div class="mb-3">
                                                            <label for="amount" class="theme_label">Amount (£)</label>
                                                            <input type="number" step="0.01" name="amount"
                                                                id="amount" class="theme_input"
                                                                value="{{ old('amount', $item['budget']->amount) }}"
                                                                required>
                                                        </div>

                                                        <div class="d-flex justify-content-end">
                                                            <button type="submit"
                                                                class="twoToneBlueGreenBtn text-center py-2">Update
                                                                Budget</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>

                                            <div class="modal-footer">
                                                <form action="{{ route('budget.reset-budget', $item['budget']->id) }}"
                                                    method="post">
                                                    @csrf
                                                    @method('put')
                                                    <button type="submit"
                                                        class="twoToneBlueGreenBtn text-center py-2">Reset</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        @endforeach


                    </div>
                </div>
            </div>
        </div>
    </section>
    <script>
        @if ($totalBudget)
            var totalAmount = {{ $totalBudget }};
        @else
            var totalAmount = 1000;
        @endif

        @if ($amountSpent)
            var amountSpent = {{ $amountSpent }};
        @else
            var amountSpent = 0;
        @endif

        // Calculate remaining amount
        var remainingAmount = totalAmount - amountSpent;

        // Handle percentage logic
        var spentPercentage = (remainingAmount < 0) ? 100 : ((amountSpent / totalAmount) * 100);
        spentPercentage = Math.min(Math.max(spentPercentage, 0), 100); // Ensure between 0-100

        var ctx = document.getElementById('myDoughnutChart').getContext('2d');

        var myDoughnutChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [spentPercentage, 100 - spentPercentage], // Adjusted data values
                    backgroundColor: ['#44E0AC', 'rgba(209, 249, 255, 0.05)'],
                    borderColor: ['transparent', 'transparent'],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                cutout: '70%', // Adjusted cutout for better appearance
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: true
                    }
                }
            }
        });
        document.getElementById("remainingAmount").style.color = remainingAmount < 0 ? "#D21414" : "#33BBC5";
    </script>
@endsection
