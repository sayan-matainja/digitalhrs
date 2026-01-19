<div class="row">
    @if(!isset(auth()->user()->branch_id))
        <div class="col-lg-4 col-md-6 mb-4">
            <label for="branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
            <select class="form-select" id="branch_id" name="branch_id">
                <option {{ !isset($loanDetail) || old('branch_id') ? 'selected' : '' }} disabled>{{ __('index.select_branch') }}</option>
                @if(isset($companyDetail))
                    @foreach($companyDetail->branches()->get() as $key => $branch)
                        <option value="{{ $branch->id }}"
                            {{ ((isset($loanDetail) && $loanDetail->branch_id == $branch->id) || old('branch_id') == $branch->id) ? 'selected' : '' }}>
                            {{ ucfirst($branch->name) }}
                        </option>
                    @endforeach
                @endif
            </select>
            @error('branch_id')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    @endif
    @if(!auth('admin')->check() && auth()->check())
        <input type="hidden" id="branch_id" name="branch_id" value="{{ auth()->user()->branch_id }}">
    @endif



    <div class="col-lg-4 col-md-6 mb-4">
        <label for="department_id" class="form-label">{{ __('index.department') }} <span style="color: red">*</span></label>
        <select class="form-select" id="department_id" name="department_id" required>
            <option value="" {{ isset($loanDetail) ? '' : 'selected' }} disabled>{{ __('index.select_department') }}</option>
        </select>
        @error('department_id')
        <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="employee_id" class="form-label">{{ __('index.employee') }} <span style="color: red">*</span></label>
        <select class="form-select" id="employee_id" name="employee_id" required>
            <option value="" {{ isset($loanDetail) ? '' : 'selected' }} disabled>{{ __('index.select_employee') }}</option>
        </select>
        @error('employee_id')
        <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="loan_type_id" class="form-label">{{ __('index.loan_type') }} <span style="color: red">*</span></label>
        <select class="form-select" id="loan_type_id" name="loan_type_id" required>
            <option value="" {{ isset($loanDetail) ? '' : 'selected' }} disabled>{{ __('index.select_loan_type') }}</option>
        </select>
        @error('loan_type_id')
        <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="loan_id" class="form-label">{{ __('index.loan_id') }} <span style="color: red">*</span></label>
        <input type="text" class="form-control"
               id="loan_id"
               name="loan_id"
               readonly
               value="{{ isset($loanDetail) ? $loanDetail->loan_id : $loanId }}"
               autocomplete="off"
        >
        @error('loan_id')
        <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="interest_rate" class="form-label">{{ __('index.interest_rate') }}</label>
        <input type="text" class="form-control" id="interest_rate" readonly>
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="interest_type" class="form-label">{{ __('index.interest_type') }}</label>
        <input type="text" class="form-control" id="interest_type" readonly>
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="term" class="form-label">{{ __('index.term') }}</label>
        <input type="text" class="form-control" id="term" readonly>
    </div>
    @if(isset($loanDetail->repayment_from))
            <div class="col-lg-4 col-md-6 mb-4">
                <label for="repayment_from" class="form-label">{{ __('index.repayment_from') }} <span style="color: red">*</span></label>
                @if(\App\Helpers\AppHelper::ifDateInBsEnabled())
                    <input type="text" class="form-control repayment_from" id="repayment_from" required value="{{ \App\Helpers\AppHelper::dateInYmdFormatEngToNep($loanDetail->repayment_from) ?? old('repayment_from')}}" name="repayment_from" autocomplete="off">
                @else
                    <input type="date"
                           class="form-control"
                           id="repayment_from"
                           name="repayment_from"
                           value="{{ $loanDetail->repayment_from ?? old('repayment_from') }}"
                           required
                           autocomplete="off">
                @endif
                @error('repayment_from')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
    @endif

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="loan_amount" class="form-label">{{ __('index.loan_amount') }} <span style="color: red">*</span></label>
        <input type="number" step="0.01" min="0" class="form-control"
               id="loan_amount"
               name="loan_amount"
               value="{{ isset($loanDetail) ? $loanDetail->loan_amount : old('loan_amount') }}"
               required
               autocomplete="off"
               placeholder="{{ __('index.enter_loan_amount') }}">
        @error('loan_amount')
        <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="monthly_installment" class="form-label">{{ __('index.monthly_installment') }}</label>
        <input type="text" class="form-control" id="monthly_installment" name="monthly_installment" readonly>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="repayment_amount" class="form-label">{{ __('index.repayment_amount') }}</label>
        <input type="text" class="form-control" name="repayment_amount" id="repayment_amount" readonly>
    </div>

    <div class="col-12">
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="loan-title_content mb-4">
                    <label for="loan_purpose" class="form-label">{{ __('index.loan_purpose') }} <span style="color: red">*</span></label>
                    <input type="text" class="form-control"
                        id="loan_purpose"
                        name="loan_purpose"
                        value="{{ isset($loanDetail) ? $loanDetail->loan_purpose : old('loan_purpose') }}"
                        required
                        autocomplete="off"
                        placeholder="{{ __('index.enter_loan_purpose') }}">
                    @error('loan_purpose')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="loan-title_content">
                    <label for="attachment" class="form-label">{{ __('index.attachment') }}</label>
                    <input class="form-control"
                        type="file"
                        id="attachment"
                        name="attachment"
                        accept=".pdf,.jpg,.jpeg,.png"
                    >
                    @if(isset($loanDetail) && $loanDetail->attachment)
                        <a href="{{ asset(\App\Models\Loan::UPLOAD_PATH . $loanDetail->attachment) }}" target="_blank" class="mt-2 d-block">{{ __('index.view_attachment') }}</a>
                    @endif
                    @error('attachment')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <label for="description" class="form-label">{{ __('index.description') }} <span style="color: red">*</span></label>
                <textarea class="form-control" name="description" id="tinymceDescription" rows="3">{{ isset($loanDetail) ? $loanDetail->description : old('description') }}</textarea>
                @error('description')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    @canany(['create_loans', 'edit_loans'])
        <div class="text-start">
            <button type="submit" class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i>
                {{ isset($loanDetail) ? __('index.update') : __('index.create') }}
            </button>
        </div>
    @endcanany
</div>
