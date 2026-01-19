{{-- status modal --}}
<div class="modal fade" id="updateLoanStatus" tabindex="-1" aria-labelledby="updateLoanStatusLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-center">
                <h5 class="modal-title" id="updateLoanStatusLabel">Update Loan Status</h5>
            </div>
            <div class="modal-body">
                <form class="forms-sample" id="updateLoanStatusForm" action="" method="post">
                    @method('PUT')
                    @csrf
                    <input type="hidden" name="current_status" id="currentStatus" value="">
                    <div class="row">

                        <div class="col-lg-12 mb-3">
                            <label for="status" class="form-label">{{ __('index.status') }}</label>
                            <select class="form-select form-select-lg" name="status" id="loan_status" required>
                                <option value="{{ \App\Enum\LoanStatusEnum::approve->value }}" data-requires-remark="0">
                                    {{ ucfirst(\App\Enum\LoanStatusEnum::approve->name) }}
                                </option>
                                <option value="{{ \App\Enum\LoanStatusEnum::reject->value }}" data-requires-remark="1">
                                    {{ ucfirst(\App\Enum\LoanStatusEnum::reject->name) }}
                                </option>
                            </select>
                        </div>

                        {{-- Approve Fields --}}
                        <div class="col-lg-12 mb-3" id="approveGroup" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="repayment_date" class="form-label">{{ __('index.repayment_date') }}</label>
                                    @if(\App\Helpers\AppHelper::ifDateInBsEnabled())
                                        <input type="text" class="form-control repayment_from" id="repayment_date" value="" name="repayment_from" autocomplete="off">
                                    @else
                                        <input type="date" class="form-control" name="repayment_from" id="repayment_date" value="{{ date('Y-m-d') }}">
                                    @endif
                                    <small id="repaymentDateError" class="validation-message text-danger mt-1"></small>
                                </div>
                                <div class="col-md-6">
                                    <label for="payment_method" class="form-label">{{ __('index.payment_method') }}</label>
                                    <select class="form-select" name="payment_method_id" id="payment_method">
                                        <option selected disabled>{{ __('index.select_payment_method') }}</option>

                                        @foreach($paymentMethods as $method)
                                            <option value="{{ $method['id'] }}"> {{ $method['name'] }}</option>
                                        @endforeach
                                    </select>
                                    <small id="paymentMethodError" class="validation-message text-danger mt-1"></small>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-12 mb-3" id="remarkGroup" style="display: none;">
                            <label for="remark" class="form-label">{{ __('index.remark') }}</label>
                            <textarea class="form-control" name="remarks" id="remark" rows="4" placeholder="Required for cancellation"></textarea>
                            <small id="remarkError" class="validation-message text-danger mt-1"></small>
                        </div>

                        <div class="col-lg-12 text-center">
                            <button type="submit" class="btn btn-primary updateStatus" disabled id="updateBtn">{{ __('index.update') }}</button>
                            <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

