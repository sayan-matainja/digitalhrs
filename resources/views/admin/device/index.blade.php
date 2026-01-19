@extends('layouts.master')

@section('title', __('index.biometric_device'))

@section('action', __('index.lists'))

@section('button')
    @can('create_device')
        <a href="{{ route('admin.biometric-devices.create') }}">
            <button class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i>{{ __('index.add_device') }}
            </button>
        </a>
    @endcan
@endsection
@section('styles')
    <style>
        .config-guide .collapse-content {
            display: none;
        }

        .config-guide.expanded .collapse-content {
            display: block;
        }
    </style>
@endsection
@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.device.common.breadcrumb')

        <!-- Unified Device Configuration & List -->
        <div class="card support-main mb-4 config-guide">
            <div class="card-header d-md-flex justify-content-between align-items-center pb-1">
                <h6 class="card-title mb-2">Device Configuration Guide</h6>
                <a href="#" class="btn btn-sm btn-light toggle-btn mb-2" onclick="toggleContent(this); return false;">Click
                    to Expand</a>
            </div>
            <div class="card-body collapse-content">
                <!-- Configuration Guide -->
                <div class="device-table">

                    <div class="alert alert-warning">
                        <strong>Important Note:</strong> This configuration guide is currently being tested with ZKTeco
                        devices that support ADMS mode...
                    </div>
                    <h6 class="text-success mb-2">✓ Check Device ADMS Support</h6>
                    <p>Your ZKTeco must support ADMS mode...</p>
                    <div class="row">
                       <div class="col-lg-12"><h6 class="text-primary mt-4 mb-2">⚙️ Configure ADMS Settings</h6></div>
                        <div class="col-lg col-md mb-4 text-center">
                            <img src="{{ asset('assets/images/step1.jpg') }}" alt="Step 1" style="width: 100%;" class="rounded">
                            <p class="mt-2 fw-bold">Step 1: Access the Menu</p>
                        </div>
                        <div class="col-lg col-md mb-4 text-center">
                            <img src="{{ asset('assets/images/step2.jpg') }}" alt="Step 2" style="width: 100%;" class="rounded">
                            <p class="mt-2 fw-bold">Step 2: Select System Info</p>
                        </div>
                        <div class="col-lg col-md mb-4 text-center">
                            <img src="{{ asset('assets/images/step3.jpg') }}" alt="Step 3" style="width: 100%;" class="rounded">
                            <p class="mt-2 fw-bold">Step 3: Find Serial Number in Device Info</p>
                        </div>
                    </div>
                    <div class="alert alert-info mb-0">
                        <strong>Note:</strong> After adding your device, simply clock in using your device so that it
                        recognizes your system.
                    </div>

                </div>
            </div>
        </div>

        <!-- Device Lists Section -->
        <div class="card support-main">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.device_lists') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th>{{ __('index.device_name') }}</th>
                            <th class="text-center">{{ __('index.serial_number') }}</th>
                            <th class="text-center">{{ __('index.device_ip') }}</th>
                            <th class="text-center">{{ __('index.last_online') }}</th>
                            <th class="text-center">{{ __('index.status') }}</th>
                            @can('delete_devices')
                                <th class="text-center">{{ __('index.action') }}</th>
                            @endcan
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($assetLists as $key => $value)
                            <tr>
                                <td class="text-center">{{ $key + 1 }}</td>
                                <td>{{ $value->name }}</td>
                                <td class="text-center">{{ $value->serial_number }}</td>
                                <td class="text-center">{{ $value->ip_address }}</td>
                                <td class="text-center">{{ $value->last_online }}</td>
                                <td class="text-center">{{ $value->status }}</td>
                                @can('delete_assets')
                                    <td class="text-center">

                                        <ul class="d-flex list-unstyled mb-0 justify-content-center align-items-center">
                                            <li>
                                                <a class="deleteDevice"
                                                   data-href="{{ route('admin.biometric-devices.delete', $value->id) }}"
                                                   title="{{ __('index.delete_detail') }}">
                                                    <i class="link-icon" data-feather="delete"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </td>
                                @endcan
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%" class="text-center">
                                    <div class="alert alert-warning mb-0">
                                        <strong>{{ __('index.no_records_found') }}</strong>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        function toggleContent(button) {
            const card = button.closest('.config-guide');
            card.classList.toggle('expanded');
            // Update button text based on state
            button.textContent = card.classList.contains('expanded') ? "Click to Collapse" : "Click to Expand";
        }

        $('.deleteDevice').click(function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: @json(__('index.delete_confirm', ['title' => 'Device'])) ,
                showDenyButton: true,
                confirmButtonText: `{{ __('index.yes') }}`,
                denyButtonText: `{{ __('index.no') }}`,
                padding: '10px 50px 10px 50px',
                // width:'1000px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        });
    </script>
@endsection
