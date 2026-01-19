@extends('layouts.master')

@section('title', __('index.biometric_device'))

@section('action', __('index.create'))

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.device.common.breadcrumb')

        <div class="card">
            <div class="card-body">
                <div class="note-box bg-info bg-opacity-25 p-3 pb-0 rounded mb-4">
                    <div class="note-toggle mb-3">
                        <h5>How to Find Your Device Serial Number</h5>
                        <p><em>Note: The serial number can be found in your device by navigating to Menu -> System Info -> Device Info</em></p>
                    </div>
                    <div id="note-content" class="create-bio" style="display: block;">
                        <div class="row">
                            <div class="col-lg col-md mb-3 text-center">
                                <img src="{{ asset('assets/images/zkteco-menu.png') }}" alt="Step 1" class="rounded">
                                <p class="mt-2 fw-bold">Step 1: Access the Menu</p>
                            </div>
                            <div class="col-lg col-md mb-3 text-center">
                                <img src="{{ asset('assets/images/zkteco-sr.png') }}" alt="Step 2" class="rounded">
                                <p class="mt-2 fw-bold">Step 2: Select System Info</p>
                            </div>
                            <div class="col-lg col-md mb-3 text-center">
                                <img src="{{ asset('assets/images/zkteco-system-info.png') }}" alt="Step 3" class="rounded">
                                <p class="mt-2 fw-bold">Step 3: Find Serial Number in Device Info</p>
                            </div>
                        </div>
                    </div>
                </div>

                <form id="" class="forms-sample" action="{{ route('admin.biometric-devices.store') }}" enctype="multipart/form-data" method="POST">
                    @csrf

                    <div class="row">
                        @if(!isset(auth()->user()->branch_id))
                            <div class="col-lg-4 mb-4">
                                <label for="branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
                                <select class="form-select" id="branch_id" name="branch_id">
                                    <option {{ !isset($assetDetail) || old('branch_id') ? 'selected' : '' }} disabled>{{ __('index.select_branch') }}
                                    </option>
                                    @if(isset($companyDetail))
                                        @foreach($companyDetail->branches()->get() as $key => $branch)
                                            <option value="{{$branch->id}}">
                                                {{ ucfirst($branch->name) }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        @endif
                        @if(!auth('admin')->check() && auth()->check())
                            <input type="hidden" disabled readonly id="branch_id" name="branch_id" value="{{ auth()->user()->branch_id }}">
                        @endif

                        <div class="col-lg-4 col-md-6 mb-4">
                            <label for="name" class="form-label">{{ __('index.name') }} <span style="color: red">*</span></label>
                            <input type="text" class="form-control"
                                   id="name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   required autocomplete="off"
                                   placeholder="e.g. ZKTeco iClock, Anviz VF30 etc.">
                            <small class="form-text text-muted">* Provide a unique identifiable name for this device.</small>
                        </div>

                        <div class="col-lg-4 col-md-6 mb-4">
                            <label for="serial_number" class="form-label">{{ __('index.serial_number') }}</label>
                            <input type="text" class="form-control"
                                   id="serial_number"
                                   name="serial_number"
                                   value="{{ old('serial_number') }}"
                                   autocomplete="off"
                                   placeholder="e.g. GED7241800000">
                            <small class="form-text text-muted">* Enter the device serial number exactly as shown on the device label. This will be used to verify the device</small>
                        </div>

                        @canany(['edit_device','create_device'])
                            <div class="text-start">
                                <button type="submit" class="btn btn-primary">
                                    <i class="link-icon" data-feather="plus"></i>
                                    {{ __('index.create') }}
                                </button>
                            </div>
                        @endcanany
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    @include('admin.device.common.form_scripts')

@endsection
