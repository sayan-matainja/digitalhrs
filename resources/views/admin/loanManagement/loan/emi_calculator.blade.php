@extends('layouts.master')

@section('title', 'EMI Calculator')
@section('styles')
    <style>
        .radio-group { display: flex; gap: 20px; align-items: center; }
        .radio-group div { display: flex; align-items: center; }
        .radio-group label { margin-left: 5px; margin-bottom: 0; }
        @media (max-width: 600px) {
            .radio-group { flex-direction: column; gap: 10px; align-items: flex-start; }
        }
    </style>
@endsection

@section('main-content')
    <section class="content" x-data="emiCalculator">
        <div class="card support-main mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">EMI Calculator</h6>
            </div>
            <div class="card-body pb-0">
                <div class="row">
                    <div class="col-lg-4 mb-4">
                        <div class="form-column">
                            <div class="form-group mb-3">
                                <label for="principal" class="mb-1">Principal Amount:</label>
                                <input type="number" id="principal" x-model="principal" placeholder="Enter principal amount" class="w-100 form-control">
                            </div>

                            <div class="form-group mb-3">
                                <label for="rate" class="mb-1">Annual Interest Rate (%):</label>
                                <input type="number" id="rate" x-model="rate" step="0.01" placeholder="Enter annual interest rate" class="w-100 form-control">
                            </div>

                            <div class="form-group mb-3">
                                <label for="tenure" class="mb-1">Installment Period (in months):</label>
                                <input type="number" id="tenure" x-model="tenure" placeholder="Enter tenure in months" class="w-100 form-control">
                            </div>
                            <div class="form-group mb-3">
                                <label class="mb-2">Interest Type:</label>
                                <div class="radio-group">
                                    <div>
                                        <input type="radio" id="fixed" value="fixed" x-model="interestType" checked name="interestType">
                                        <label for="fixed">Fixed (Flat)</label>
                                    </div>
                                    <div>
                                        <input type="radio" id="declining" value="declining" x-model="interestType" name="interestType">
                                        <label for="declining">Declining (Reducing Balance)</label>
                                    </div>
                                </div>
                            </div>

                            <button @click="calculateEMI" class="btn btn-primary">Calculate</button>
                            <p class="text-danger mt-2" x-text="error" x-show="error"></p>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="row align-items-center">
                            <div class="col-lg-7 col-md-7 mb-4">
                                <div class="summary-card" x-transition>
                                    <p><i class="fas fa-calendar-alt text-primary"></i> <strong>{{ __('index.monthly_emi') }}:</strong> <span x-text="formatNumber(emi)"></span></p>
                                    <p><i class="fas fa-building text-success"></i> <strong>{{ __('index.principal') }}:</strong> <span x-text="formatNumber(principal)"></span></p>
                                    <p><i class="fas fa-percentage text-warning"></i> <strong>{{ __('index.interest_payable') }}:</strong> <span x-text="formatNumber(totalInterest)"></span></p>
                                    <p><i class="fas fa-calculator text-info"></i> <strong>{{ __('index.total_amount_payable') }}:</strong> <span x-text="formatNumber(totalPayment)"></span></p>
                                </div>
                            </div>
                            <div class="col-lg-5 col-md-5 mb-4">
                                <div class="chart-container">
                                    <canvas id="emiChart"></canvas>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="year_breakdown" x-show="sortedYears().length > 0" x-transition>
            <div class="card support-main">
                <div class="card-header">
                    <h6 class="card-title mb-0">Yearly Breakdown</h6>
                </div>
                <div class="card-body pb-0">
                    <template x-for="year in sortedYears()" :key="year">
                        <div class="year-section mb-4">
                            <h5 class="year-header mb-2"><span x-text="year"></span></h5>
                            <div class="table-responsive">
                                <table class="table mt-0">
                                    <thead>
                                    <tr>
                                        <th>{{ __('index.month') }}</th>
                                        <th>{{ __('index.schedule_payment') }}</th>
                                        <th>{{ __('index.total_interest') }}</th>
                                        <th>{{ __('index.principal') }}</th>
                                        <th>{{ __('index.balance') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <template x-for="row in yearlySchedule[year].rows" :key="row.month">
                                        <tr>
                                            <td x-text="row.month"></td>
                                            <td x-text="formatNumber(row.schedule)"></td>
                                            <td x-text="formatNumber(row.interest)"></td>
                                            <td x-text="formatNumber(row.principal)"></td>
                                            <td x-text="formatNumber(row.balance)"></td>
                                        </tr>
                                    </template>
                                    </tbody>
                                    <tfoot>
                                    <tr class="table-info">
                                        <th>Total</th>
                                        <th x-text="formatNumber(yearlySchedule[year].totals.schedule)"></th>
                                        <th x-text="formatNumber(yearlySchedule[year].totals.interest)"></th>
                                        <th x-text="formatNumber(yearlySchedule[year].totals.principal)"></th>
                                        <th></th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

    </section>
@endsection
@section('scripts')
    <script src="{{asset('assets/vendors/chartjs/Chart.min.js')}}"></script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('emiCalculator', () => {
                const currencySymbol = '{{ $currency ?? "Rs. " }}';
                let chartInstance = null;
                return {
                    principal: 0,
                    rate: 0,
                    tenure: 0,
                    interestType: 'fixed',
                    emi: 0,
                    totalPayment: 0,
                    totalInterest: 0,
                    yearlySchedule: {},
                    error: '',

                    sortedYears() {
                        return Object.keys(this.yearlySchedule).sort((a, b) => Number(a) - Number(b));
                    },

                    updateChart() {
                        if (!chartInstance) return;

                        const principalNum = parseFloat(this.principal) || 0;
                        const totalInterestNum = this.totalInterest || 0;

                        const labels = ['Principal Amount', 'Interest Amount'];
                        const data = [principalNum, totalInterestNum];

                        chartInstance.data.labels = labels;
                        chartInstance.data.datasets[0].data = data;

                        chartInstance.update();
                    },

                    calculateEMI() {
                        this.error = '';
                        this.yearlySchedule = {};
                        const principalNum = parseFloat(this.principal);
                        const rateNum = parseFloat(this.rate);
                        const tenureNum = parseInt(this.tenure);

                        if (isNaN(principalNum) || principalNum <= 0 || isNaN(rateNum) || rateNum <= 0 || isNaN(tenureNum) || tenureNum <= 0) {
                            this.error = 'Please enter valid positive values for principal, rate, and tenure.';
                            this.emi = 0;
                            this.updateChart();
                            return;
                        }

                        const today = new Date(2025, 9, 28); // October 28, 2025
                        const firstPaymentDate = new Date(today.getFullYear(), today.getMonth() + 1, 1);

                        const monthlyRate = rateNum / 12 / 100;
                        let fixedEMI = 0;
                        let monthlyInterest = 0;

                        if (this.interestType === 'fixed') {
                            monthlyInterest = principalNum * (rateNum / 100) / 12;
                            const monthlyPrincipal = principalNum / tenureNum;
                            fixedEMI = ((monthlyPrincipal + monthlyInterest) * 100) / 100;
                        } else {
                            const power = Math.pow(1 + monthlyRate, tenureNum);
                            fixedEMI = ((principalNum * monthlyRate * power / (power - 1)) * 100) / 100;
                        }

                        this.emi = fixedEMI;

                        let remaining = principalNum;
                        let totalInterest = 0;
                        let totalPayment = 0;
                        let yearlySchedule = {};

                        for (let i = 0; i < tenureNum; i++) {
                            const paymentDate = new Date(firstPaymentDate.getFullYear(), firstPaymentDate.getMonth() + i, 1);
                            const year = paymentDate.getFullYear().toString();
                            const month = paymentDate.toLocaleDateString('en-US', { month: 'long' });

                            if (!yearlySchedule[year]) {
                                yearlySchedule[year] = {
                                    rows: [],
                                    totals: { schedule: 0, interest: 0, principal: 0 }
                                };
                            }

                            let interest;
                            if (this.interestType === 'fixed') {
                                interest = (monthlyInterest * 100) / 100;
                            } else {
                                interest = (remaining * monthlyRate * 100) / 100;
                            }

                            let principalPay;
                            let scheduleAmt;
                            if (i < tenureNum - 1) {
                                principalPay = fixedEMI - interest;
                                principalPay = (principalPay * 100) / 100;
                                scheduleAmt = fixedEMI;
                            } else {
                                principalPay = remaining;
                                scheduleAmt = interest + principalPay;
                                scheduleAmt = (scheduleAmt * 100) / 100;
                            }

                            remaining -= principalPay;
                            remaining = Math.max(0, (remaining * 100) / 100);

                            const row = {
                                month: month,
                                schedule: scheduleAmt,
                                interest: interest,
                                principal: principalPay,
                                balance: remaining
                            };

                            yearlySchedule[year].rows.push(row);
                            yearlySchedule[year].totals.schedule += scheduleAmt;
                            yearlySchedule[year].totals.interest += interest;
                            yearlySchedule[year].totals.principal += principalPay;

                            totalInterest += interest;
                            totalPayment += scheduleAmt;
                        }

                        // Round totals
                        totalInterest = (totalInterest * 100) / 100;
                        totalPayment = (totalPayment * 100) / 100;

                        this.totalInterest = totalInterest;
                        this.totalPayment = totalPayment;
                        this.yearlySchedule = yearlySchedule;

                        // Update chart after calculation
                        this.$nextTick(() => {
                            this.updateChart();
                        });
                    },

                    formatNumber(value) {
                        const formatted = Number(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        return currencySymbol + formatted;
                    },

                    init() {

                        const ctx = document.getElementById('emiChart').getContext('2d');
                        chartInstance = new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: [],
                                datasets: [{
                                    label: 'Breakdown',
                                    backgroundColor: ['#007bff', '#dc3545'],
                                    borderColor: ['#0056b3', '#c82333'],
                                    data: [],
                                    borderWidth: 2
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
                                        position: 'bottom'
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
                                                const percent = ((data / totalData) * 100).toFixed(1) + '%';
                                                ctx.save();
                                                const fontSize = (chartInstance.height / 140).toFixed(2);
                                                ctx.font = fontSize + "em sans-serif";
                                                ctx.textBaseline = 'middle';
                                                const pos = element.tooltipPosition();
                                                ctx.textAlign = 'center';
                                                ctx.fillStyle = '#000';
                                                ctx.fillText(percent, pos.x, pos.y);
                                                ctx.restore();
                                            });
                                        }
                                    });
                                }
                            }
                        });

                        // Initial calculation with default values
                        this.$nextTick(() => {
                            this.calculateEMI();
                        });
                    }
                }
            });
        });
    </script>
@endsection
