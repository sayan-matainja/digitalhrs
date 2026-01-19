@php use App\Models\LoanRepayment; @endphp
@php use App\Helpers\AppHelper; @endphp
@extends('layouts.master')

@section('title', __('index.loan_repayment'))

@section('action', __('index.repayment_detail'))

@section('styles')
    <style>
        .badge {
            font-size: 0.75em;
            padding: 0.5em 0.75em;
        }
    </style>
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        @include('admin.loanManagement.loan.common.breadcrumb')


        <div class="card support-main">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.loan_repayment_detail_list') }}</h6>
            </div>
            <div class="card-body pb-0">
                @php
                    $currency = AppHelper::getCompanyPaymentCurrencySymbol();
                    $totalPrincipal = $loan->loan_amount;
                    $totalSettlement = $loanLists->sum('settlement_amount');
                    $totalInterest = $loanLists->sum('interest_amount');
                    $totalPaidPrincipal = $loanLists->where('status',\App\Enum\LoanRepaymentStatusEnum::paid->value)->sum('principal_amount');
                    $totalPaidSettlement = $loanLists->where('status',\App\Enum\LoanRepaymentStatusEnum::paid->value)->sum('settlement_amount');
                    $totalPaidInterest = $loanLists->where('status',\App\Enum\LoanRepaymentStatusEnum::paid->value)->sum('interest_amount');
                    $totalPayment = $totalPrincipal + $totalInterest;
                    $totalPaidAmount = $totalPaidPrincipal + $totalPaidSettlement;
                    $totalRemaining = max(0, $totalPayment-$totalPaidAmount -$totalPaidInterest);

                    $firstRepayment = $loanLists->first();
                    $loan = $firstRepayment ? $firstRepayment->loan : null;
                    $loanType = $loan ? $loan->loanType : null;
                    $interestType = $loanType ? $loanType->interest_type ?? 'fixed' : 'fixed';
                    $interestRate = $loanType ? $loanType->interest_rate : 0;
                    $tenureMonths = $loan ? $loan->tenure : 0;
                    $principal = $loan ? $loan->loan_amount : 0;

                    $monthlyEMI = $firstRepayment ? $firstRepayment->principal_amount + $firstRepayment->interest_amount : 0;

                    $remainingBalance = $principal;
                    $yearlyData = [];
                    foreach ($loanLists->sortBy('payment_date') as $repayment) {
                        $year = AppHelper::getYearValue($repayment->payment_date);
                        if (!isset($yearlyData[$year])) {
                            $yearlyData[$year] = [
                                'rows' => [],
                                'totals' => ['schedule' => 0, 'interest' => 0, 'principal' => 0, 'settlement' => 0]
                            ];
                        }
                        $schedule = $repayment->principal_amount + $repayment->interest_amount;
                        $interest = $repayment->interest_amount;
                        $status = $repayment->status;
                        $principalPaid = $repayment->principal_amount;
                        $interestPaid = $repayment->interest_amount;
                        $settlementAmount = $repayment->settlement_amount;
                        $totalPaid = $principalPaid + $settlementAmount;
                        $balance = max(0, $remainingBalance - $totalPaid);

                        $yearlyData[$year]['rows'][] = [
                            'payment_date' => $repayment->payment_date,
                            'month' => AppHelper::getMonth($repayment->payment_date),
                            'schedule' => $schedule,
                            'interest' => $interest,
                            'principal' => $principalPaid,
                            'settlement_amount' => $settlementAmount > 0 ? $totalPaid + $interestPaid :  $settlementAmount,
                            'balance' => $balance,
                            'status' => $status,
                        ];

                        $yearlyData[$year]['totals']['schedule'] += $schedule;
                        $yearlyData[$year]['totals']['interest'] += $interest;
                        $yearlyData[$year]['totals']['principal'] += $principalPaid;
                        $yearlyData[$year]['totals']['settlement'] += ($settlementAmount > 0 ? $totalPaid + $interestPaid :  $settlementAmount);

                        $remainingBalance = $balance;
                    }
                    ksort($yearlyData);
                    $key = 0;
                    $status = [
                                \App\Enum\LoanRepaymentStatusEnum::active->value => 'primary',
                                \App\Enum\LoanRepaymentStatusEnum::upcoming->value => 'info',
                                \App\Enum\LoanRepaymentStatusEnum::paid->value => 'success',
                            ];
                @endphp

                <div class="row align-items-center">
                    <div class="col-md-6 mb-4">
                        <div class="summary-header">
                            <i class="fas fa-chart-line"></i>
                            {{ __('index.loan_summary') }}
                        </div>
                        <div class="summary-card">
                            <p><i class="fas fa-calendar-alt text-primary"></i><strong>{{ __('index.monthly_emi') }}:</strong> {{ AppHelper::formatCurrencyAmount($monthlyEMI) }}</p>
                            <p><i class="fas fa-building text-success"></i><strong>{{ __('index.principal') }}:</strong> {{ AppHelper::formatCurrencyAmount($principal) }}</p>
                            <p><i class="fas fa-percentage text-warning"></i><strong>{{ __('index.interest_payable') }}:</strong> {{ AppHelper::formatCurrencyAmount($totalInterest) }}</p>
                            <p><i class="fas fa-calculator text-info"></i><strong>{{ __('index.total_amount_payable') }}:</strong> {{ AppHelper::formatCurrencyAmount($totalPayment) }}</p>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="chart-container">
                            <canvas id="repaymentChart"></canvas>
                        </div>
                    </div>
                </div>

                @if (!empty($yearlyData))
                    @foreach ($yearlyData as $year => $data)
                        <div class="year-section mb-4">
                            <h5 class="mb-2">{{ $year }}</h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('index.month') }}</th>
                                        <th class="text-center">{{ __('index.schedule_payment') }} ({{ $currency }})</th>
                                        <th class="text-center">{{ __('index.total_interest') }} ({{ $currency }})</th>
                                        <th class="text-center">{{ __('index.principal') }} ({{ $currency }})</th>
                                        <th class="text-center">{{ __('index.settlement_amount') }} ({{ $currency }})</th>
                                        <th class="text-center">{{ __('index.balance') }} ({{ $currency }})</th>
                                        <th class="text-center">{{ __('index.status') }}</th>
                                        <th class="text-center">{{ __('index.remarks') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($data['rows'] as $row)
                                        <tr>
                                            <td>{{ ++$key }}</td>
                                            <td>{{ $row['month'] }}</td>
                                            <td class="text-center">{{ $row['schedule'] }}</td>
                                            <td class="text-center">{{ bcdiv($row['interest'],1,2) }}</td>
                                            <td class="text-center">{{ bcdiv($row['principal'],1,2) }}</td>
                                            <td class="text-center">{{ bcdiv($row['settlement_amount'],1,2) }}</td>
                                            <td class="text-center">{{ bcdiv($row['balance'],1,2) }}</td>
                                            <td class="text-center">
                                                <span class="badge p-2 bg-{{ $status[$row['status']] ?? 'secondary' }}">{{ ucfirst($row['status']) }}</span>
                                            </td>
                                            <td class="text-center">
                                                @php
                                                    $matchedRemarks = collect();

                                                    if ($loan->settlementRequest && $loan->settlementRequest->count() > 0) {
                                                        foreach ($loan->settlementRequest as $settlement) {
                                                            $settlementMonth = \Carbon\Carbon::parse($settlement->created_at)->format('F');
                                                            $settlementYear  = \Carbon\Carbon::parse($settlement->created_at)->format('Y');

                                                            try {
                                                                $rowDate = \Carbon\Carbon::parse($row['payment_date']);
                                                                $rowMonth = $rowDate->format('F');
                                                                $rowYear  = $rowDate->format('Y');
                                                            } catch (\Exception $e) {
                                                                $rowMonth = $rowYear = null;
                                                            }

                                                            if ($rowMonth === $settlementMonth && $rowYear == $settlementYear) {
                                                                $createdDateTime = \Carbon\Carbon::parse($settlement->created_at)->format('M d, Y H:i');
                                                                $rStatus = ucfirst($settlement->status);
                                                                $matchedRemarks->push([
                                                                    'datetime' => $createdDateTime,
                                                                    'status' => $rStatus,
                                                                    'remarks' => $settlement->remarks
                                                                ]);
                                                            }
                                                        }
                                                    }
                                                @endphp
                                                <button type="button" class="btn text-{{ $matchedRemarks->isNotEmpty() ? 'primary' :'secondary' }} p-0"
                                                        title="{{ __('index.view_description') }}"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#contentModal"
                                                        data-title="{{ __('index.description') }}"
                                                        data-content-target="#loan-description-{{ $key }}">
                                                    <i class="link-icon" data-feather="eye"></i>
                                                </button>

                                                <div id="loan-description-{{ $key }}" class="d-none">
                                                    @if($matchedRemarks->isNotEmpty())
                                                        <ul class="list-unstyled mb-0">
                                                            @foreach ($matchedRemarks as $remark)
                                                                <li class="mb-2">
                                                                    <div class="d-flex align-items-start">
                                                                        <i class="fas fa-comment text-primary me-2 mt-1"></i>
                                                                        <div>
                                                                            <div class="text-muted small">{{ $remark['datetime'] }}</div>
                                                                            <div>Status: {{ $remark['status'] }} : {{ $remark['remarks'] }}</div>
                                                                        </div>
                                                                    </div>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <em class="text-muted">{{ __('No remarks for this month') }}</em>
                                                    @endif
                                                </div>

                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                    <tfoot>
                                    <tr class="table-info">
                                        <th colspan="2">{{ __('index.total') }}</th>
                                        <th class="text-center">{{ $data['totals']['schedule'] }}</th>
                                        <th class="text-center">{{ $data['totals']['interest'] }}</th>
                                        <th class="text-center">{{ $data['totals']['principal'] }}</th>
                                        <th class="text-center">{{ $data['totals']['settlement'] }}</th>
                                        <th colspan="3"></th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th colspan="7" class="text-center">{{ __('index.no_records_found') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td colspan="7" class="text-center">
                                    <p class="text-center"><b>{{ __('index.no_records_found') }}</b></p>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                @endif

            </div>
        </div>


        <div class="modal fade" id="contentModal" tabindex="-1" aria-labelledby="contentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header text-center">
                        <h5 class="modal-title" id="contentModalLabel">Details</h5>
                    </div>
                    <div class="modal-body" id="modalBody"></div>
                </div>
            </div>
        </div>

    </section>
@endsection
@section('scripts')
    <script src="{{asset('assets/vendors/chartjs/Chart.min.js')}}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('repaymentChart').getContext('2d');
            const principalPaid = parseFloat(`{{ $totalPaidAmount }}`) || 0;
            const interestPaid = parseFloat(`{{ $totalPaidInterest }}`) || 0;
            const remaining = parseFloat(`{{ $totalRemaining }}`) || 0;


            const total = principalPaid + interestPaid + remaining;
            const data = total > 0 ? [principalPaid, interestPaid, remaining] : [0, 0, 0];
            const labels = total > 0 ? ['Principal Paid', 'Interest Paid', 'Remaining'] : [];
            const currencySymbol = '{{ config("app.currency_symbol", "Rs. ") }}';

            const chart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: ['#007bff', '#dc3545', 'rgba(213,209,209,0.25)'],
                        borderColor: ['#0056b3', '#c82333', 'rgba(213,209,209,0.25)'],
                        borderWidth: 1,
                        borderRadius:5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    rotation: -90,
                    circumference: 180,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 15,
                                font: {
                                    size: 11
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    const value = context.parsed;
                                    const formattedValue = value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return label + currencySymbol + formattedValue + ' (' + percentage + '%)';
                                }
                            }
                        }
                    },
                    afterDraw: function(chartInstance) {
                        const ctx = chartInstance.ctx;
                        const totalData = chartInstance.data.datasets[0].data.reduce((a, b) => a + b, 0);
                        if (totalData === 0) return;
                        chartInstance.data.datasets.forEach((dataset, i) => {
                            const meta = chartInstance.getDatasetMeta(i);
                            if (!meta.hidden) {
                                meta.data.forEach((element, index) => {
                                    const data = dataset.data[index];
                                    const percent = totalData > 0 ? ((data / totalData) * 100).toFixed(1) + '%' : '0%';
                                    ctx.save();

                                    const fontSize = Math.max((chartInstance.height / 200), 10);
                                    ctx.font = fontSize + 'px sans-serif';
                                    ctx.textBaseline = 'middle';
                                    const pos = element.tooltipPosition();
                                    ctx.textAlign = 'center';
                                    ctx.fillStyle = '#000';

                                    ctx.fillText(percent, pos.x, pos.y + 2);
                                    ctx.restore();
                                });
                            }
                        });
                    }
                }
            });
        });


        document.addEventListener('DOMContentLoaded', function() {
            const contentModal = document.getElementById('contentModal');

            contentModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget;
                if (!button) return;

                const title = button.getAttribute('data-title') || 'Details';
                const contentTarget = button.getAttribute('data-content-target');

                const modalTitle = contentModal.querySelector('.modal-title');
                const modalBody = contentModal.querySelector('#modalBody');

                modalTitle.textContent = title;

                const contentElement = document.querySelector(contentTarget);
                modalBody.innerHTML = contentElement ? contentElement.innerHTML : '<em>No content available</em>';
            });
        });
    </script>
@endsection
