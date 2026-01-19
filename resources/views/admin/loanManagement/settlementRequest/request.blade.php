
@extends('layouts.master')

@section('title', __('index.loan_settlement_request'))

@section('action', __('index.request'))

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.loanManagement.settlementRequest.common.breadcrumb')
        <div class="card">
            <div class="card-body">
                <form id="loan-form" class="forms-sample" action="{{ route('admin.settlementRequest.store') }}" enctype="multipart/form-data" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-lg-4 col-md-6 mb-4">
                            <label for="loan_id" class="form-label">{{ __('index.loan') }} <span style="color: red">*</span></label>
                            <select class="form-select" id="loan_id" name="loan_id" required>
                                <option selected disabled>{{ __('index.select_loan') }}</option>
                                @foreach($loans as $loan)
                                    <option {{ old('loan_id') == $loan->id ? 'selected' : ''  }} value="{{ $loan->id }}" >{{ $loan->loan_id }}</option>
                                @endforeach
                            </select>
                            @error('loan_id')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <label for="settlement_type" class="form-label">{{ __('index.settlement_type') }} <span
                                    style="color: red">*</span></label>
                            <select class="form-select" id="settlement_type" name="settlement_type" required>
                                <option selected disabled>{{ __('index.select_settlement_type') }}</option>
                                <option
                                    value="partial"{{ (old('settlement_type') == 'partial') ? 'selected' : '' }}>
                                    Partial
                                </option>
                                <option
                                    value="full"{{ (old('settlement_type') == 'full') ? 'selected' : '' }}>
                                    Full
                                </option>
                            </select>
                            @error('settlement_type')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <label for="settlement_method" class="form-label">{{ __('index.settlement_method') }} <span
                                    style="color: red">*</span></label>
                            <select class="form-select" id="settlement_method" name="settlement_method" required>
                                <option selected disabled>{{ __('index.select_settlement_method') }}</option>
                                <option
                                    value="manual"{{ (old('settlement_type') == 'manual') ? 'selected' : '' }}>
                                    Manual
                                </option>
                                <option
                                    value="salary"{{ ( old('settlement_type') == 'salary') ? 'selected' : '' }}>
                                    Salary
                                </option>
                            </select>
                            @error('settlement_method')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-4 col-md-6 mb-4 d-none">
                            <label for="amount" class="form-label">{{ __('index.amount') }}</label>
                            <input type="number" name="amount" id="amount" class="form-control" value="{{ old('amount') }}">
                            @error('amount')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-6 mb-4">
                            <label for="tinymceDescription" class="form-label">{{ __('index.reason') }} <span
                                    style="color: red">*</span></label>
                            <textarea name="reason" id="tinymceDescription" required rows="3">
                                {!! old('reason') !!}
                            </textarea>
                            @error('reason')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>


                        <div class="text-start">
                            <button type="submit" class="btn btn-primary">
                                <i class="link-icon" data-feather="plus"></i>
                                {{ __('index.create') }}
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

            $("#loan_id").select2({
                'placeholder': '{{ __("index.select_loan") }}'
            });

            tinymce.init({
                selector: '#tinymceDescription',
                height: 200,
                menubar: false,
                plugins: ['advlist autolink lists link image charmap print preview anchor', 'searchreplace visualblocks code fullscreen', 'insertdatetime media table paste code help wordcount'],
                toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | removeformat | help',
            });
        });

        $(document).on('change', '#settlement_type', function(e) {
            var type = $(this).val();

            if (type == 'partial') {
                $('#amount').parent().removeClass('d-none');
            } else {
                $('#amount').parent().addClass('d-none');
            }
        });
    </script>

@endsection
