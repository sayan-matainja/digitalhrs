{{-- status modal --}}
<div class="modal fade" id="updateLoanStatus" tabindex="-1" aria-labelledby="updateLoanStatusLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-center">
                <h5 class="modal-title" id="updateLoanStatusLabel">Update Loan Settlement Request Status</h5>
            </div>
            <div class="modal-body">
                <form class="forms-sample" id="updateLoanStatusForm" action="" method="post">
                    @method('PUT')
                    @csrf
                    <input type="hidden" name="current_status" id="currentStatus" value="">
                    <div class="row">

                        <div class="col-lg-12 mb-3">
                            <label for="status" class="form-label">{{ __('index.status') }} <span style="color: red">*</span></label>
                            <select class="form-select form-select-lg" name="status" id="loan_status" required>
                                <option value="{{ \App\Enum\LoanStatusEnum::approve->value }}" data-requires-remark="0">
                                    {{ ucfirst(\App\Enum\LoanStatusEnum::approve->name) }}
                                </option>
                                <option value="{{ \App\Enum\LoanStatusEnum::reject->value }}" data-requires-remark="1">
                                    {{ ucfirst(\App\Enum\LoanStatusEnum::reject->name) }}
                                </option>
                            </select>
                        </div>

                        <div class="col-lg-12 mb-3">
                            <label for="remark" class="form-label">{{ __('index.remark') }} <span style="color: red">*</span></label>
                            <textarea class="form-control" name="remarks" id="remark" required rows="4" placeholder="Enter remark"></textarea>
                            <div class="invalid-feedback"></div>
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
