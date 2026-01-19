
@extends('layouts.master')

@section('title', __('index.loan'))

@section('action', __('index.request'))

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.loanManagement.loan.common.breadcrumb')
        <div class="card">
            <div class="card-body">
                <form id="loan-form" class="forms-sample" action="{{ route('admin.loan-request.store') }}" enctype="multipart/form-data" method="POST">
                    @csrf
                    <div class="row">

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

                        <div class="col-lg-4 col-md-6 mb-4">
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
                        <div class="col-lg-4 col-md-6 mb-4">
                            <label for="monthly_installment" class="form-label">{{ __('index.monthly_installment') }}</label>
                            <input type="text" class="form-control" id="monthly_installment" name="monthly_installment" readonly>
                        </div>

                        <div class="col-lg-4 col-md-6 mb-4">
                            <label for="repayment_amount" class="form-label">{{ __('index.repayment_amount') }}</label>
                            <input type="text" class="form-control" name="repayment_amount" id="repayment_amount" readonly>
                        </div>

                        <div class="col-lg-6 mb-4">
                            <label for="description" class="form-label">{{ __('index.description') }}</label>
                            <textarea class="form-control" name="description" id="tinymceDescription" rows="3">{{ isset($loanDetail) ? $loanDetail->description : old('description') }}</textarea>
                            @error('description')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>


                        <div class="text-start">
                            <button type="submit" class="btn btn-primary">
                                <i class="link-icon" data-feather="plus"></i>
                                {{ isset($loanDetail) ? __('index.update') : __('index.create') }}
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script src="{{ asset('assets/vendors/tinymce/tinymce.min.js') }}"></script>
    <script src="{{ asset('assets/js/tinymce.js') }}"></script>

    <script>
        $(document).ready(function() {

            $("#loan_type_id").select2({
                'placeholder': '{{ __("index.select_loan_type") }}'
            });

            $('#attachment').change(function() {
            });

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let types = []; // Global to store loan types data

            const $loadLoanTypes = async (branchId) => {
                if (!branchId) return;

                let loanTypeId = "{{ $loanDetail->loan_type_id ?? old('loan_type_id') ?? '' }}";

                try {
                    const response = await $.ajax({
                        type: 'GET',
                        url: `{{ url('admin/loan/get-branch-loan-type') }}/${branchId}`,
                    });

                    types = response.types || []; // Store types globally

                    // Clear loan types select
                    $('#loan_type_id').empty();
                    let placeholderSelected = !loanTypeId;
                    var placeholderOpt = new Option('{{ __('index.select_loan_type') }}', '', placeholderSelected, placeholderSelected);
                    $('#loan_type_id').append(placeholderOpt);

                    if (response.types && response.types.length > 0) {
                        response.types.forEach(type => {
                            var opt = new Option(type.name, type.id, false, type.id == loanTypeId);
                            $('#loan_type_id').append(opt);
                        });
                    } else {
                        var noTypeOpt = new Option('{{ __("index.loan_type_not_found") }}', '', false, false);
                        $('#loan_type_id').append(noTypeOpt);
                    }
                    $('#loan_type_id').trigger('change');

                } catch (error) {
                    console.error('Error loading types and departments:', error);
                    // Clear and set error for loan types
                    $('#loan_type_id').empty();
                    var errorOpt = new Option('{{ __("index.error_loading_loan_types") }}', '', true, true);
                    $('#loan_type_id').append(errorOpt).trigger('change');

                }
            };



            // Function to populate readonly fields based on selected loan type
            const populateLoanTypeDetails = (loanTypeId) => {
                const selectedType = types.find(t => t.id == loanTypeId);
                if (selectedType) {
                    $('#interest_rate').val(selectedType.interest_rate);
                    $('#interest_type').val(selectedType.interest_type);
                    $('#term').val(selectedType.term);
                } else {
                    $('#interest_rate').val('');
                    $('#interest_type').val('');
                    $('#term').val('');
                }
                calculateInstallments();
            };

            // Function to calculate monthly installment and total repayment
            const calculateInstallments = () => {
                const loanAmount = parseFloat($('#loan_amount').val()) || 0;
                const loanTypeId = $('#loan_type_id').val();
                if (!loanAmount || !loanTypeId || loanAmount <= 0) {
                    $('#monthly_installment').val('');
                    $('#repayment_amount').val('');
                    return;
                }
                const selectedType = types.find(t => t.id == loanTypeId);
                if (!selectedType) {
                    $('#monthly_installment').val('');
                    $('#repayment_amount').val('');
                    return;
                }
                const principal = loanAmount;
                const interestRate = parseFloat(selectedType.interest_rate) || 0;
                const tenureMonths = parseInt(selectedType.term) || 0;
                const interestType = selectedType.interest_type || 'fixed';
                if (tenureMonths <= 0) {
                    $('#monthly_installment').val('');
                    $('#repayment_amount').val('');
                    return;
                }
                const monthlyPrincipal = principal / tenureMonths;
                $('#monthly_installment').val(monthlyPrincipal.toFixed(2));
                // Calculate total repayment
                let totalInterest = 0;
                let remainingPrincipal = principal;
                for (let month = 1; month <= tenureMonths; month++) {
                    let interestAmount;
                    if (interestType === 'fixed') {
                        interestAmount = (principal * interestRate / 100) / 12;
                    } else {
                        interestAmount = (remainingPrincipal * interestRate / 100) / 12;
                    }
                    totalInterest += interestAmount;
                    remainingPrincipal -= monthlyPrincipal;
                }
                const totalRepayment = principal + totalInterest;
                $('#repayment_amount').val(totalRepayment.toFixed(2));
            };

            $('#loan_type_id').on('change', function() {
                const loanTypeId = $(this).val();
                populateLoanTypeDetails(loanTypeId);
            });

            $('#loan_amount').on('input', calculateInstallments);

            const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};

            $loadLoanTypes(defaultBranchId);



            if ("{{ $loanDetail->department_id ?? '' }}") {
                loadEmployees("{{ $loanDetail->department_id ?? '' }}");
            }

            // Initial calculation for editing mode
            if ("{{ isset($loanDetail) && $loanDetail->loan_type_id }}") {
                setTimeout(calculateInstallments, 500); // Delay to ensure selects are populated
            }

            tinymce.init({
                selector: '#tinymceDescription',
                height: 200,
                menubar: false,
                plugins: ['advlist autolink lists link image charmap print preview anchor', 'searchreplace visualblocks code fullscreen', 'insertdatetime media table paste code help wordcount'],
                toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | removeformat | help',
            });
        });


    </script>

@endsection
