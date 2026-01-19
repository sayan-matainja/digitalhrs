@extends('layouts.master')

@section('title',__('index.leave'))

@section('action',__('index.balance'))


@section('main-content')
    <?php
    if (\App\Helpers\AppHelper::ifDateInBsEnabled()) {
        $filterData['min_year'] = '2076';
        $filterData['max_year'] = '2089';
        $filterData['month'] = 'np';
    } else {
        $filterData['min_year'] = '2020';
        $filterData['max_year'] = '2033';
        $filterData['month'] = 'en';
    }
    ?>

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.leaveRequest.common.breadcrumb')

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.leave_balance_filter') }}</h6>
            </div>
            <form class="forms-sample card-body pb-0" action="{{route('admin.leaveBalance.index')}}" method="get">

                <div class="row align-items-center">

                    @if(!isset(auth()->user()->branch_id))
                        <div class="col-xxl col-xl-3 col-md-6 mb-4">
                            <select class="form-select" id="branch_id" name="branch_id" required>
                                <option selected disabled>{{ __('index.select_branch') }}
                                </option>
                                @if(isset($companyDetail))
                                    @foreach($companyDetail->branches()->get() as $key => $branch)
                                        <option {{ $filterParameters['branch_id'] == $branch->id ? 'selected' : '' }} value="{{$branch->id}}">{{ucfirst($branch->name)}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    @endif
                    <!-- Departments Field -->
                    <div class="col-xxl col-xl-3 col-md-6 mb-4">
                        <select class="form-select" id="department_id" name="department_id" required>
                            <option selected disabled>{{ __('index.select_department') }}</option>

                        </select>
                    </div>
                    <div class="col-xxl col-xl-3 col-md-6 mb-4">
                        <select class="form-select" id="requestedBy" name="requested_by" required>
                            <option selected disabled>{{ __('index.select_employee') }}</option>

                        </select>

                    </div>

                    <div class="col-xxl col-xl-3 col-md-6 mb-4">
                        <select class="form-select form-select-lg" name="leave_type" id="leaveType">
                            <option value="" {{!isset($filterParameters['leave_type']) ? 'selected': ''}} >{{ __('index.all_leave_type') }}</option>

                        </select>
                    </div>

                    <div class="col-xxl col-xl-3 col-md-6 mb-4">
                        <input type="number" min="{{ $filterData['min_year']}}"
                               max="{{ $filterData['max_year']}}" step="1"
                               placeholder="{{ __('index.leave_requested_year') }} : {{$filterData['min_year']}}"
                               id="year"
                               name="year" value="{{$filterParameters['year']}}"
                               class="form-control">
                    </div>


                    <div class="col-xxl col-xl-3 mb-4">
                        <div class="d-flex">
                            <button type="submit"
                                    class="btn btn-block btn-secondary me-2">{{ __('index.filter') }}</button>
                            <a class="btn btn-block btn-primary"
                               href="{{route('admin.leaveBalance.index')}}">{{ __('index.reset') }}</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-body">

                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th>{{ __('index.employee') }}</th>
                            <th class="text-center">{{ __('index.year') }}</th>
                            <th class="text-center">{{ __('index.allocated') }}</th>
                            <th class="text-center">{{ __('index.used') }}</th>
                            <th class="text-center">{{ __('index.remaining') }}</th>
                            <th class="text-center">{{ __('index.action') }}</th>
                        </tr>
                        </thead>
                        <tbody>


                        @forelse($leaveBalances->groupBy('employee_id') as $key => $value)

                            @php
                                $employeeRecord = $value->first();
                                $employeeName = $employeeRecord->employee_name;
                                $year = $employeeRecord->year;
                                $totalAllocated = $value->sum('allocated');
                                $totalUsed = $value->sum('used');
                                $totalRemaining = $value->sum('remaining');
                                $details = $value->map(function($item){
                                    return [
                                        'leave_type' => $item->leave_type_name,
                                        'allocated' => $item->allocated,
                                        'used' => $item->used,
                                        'remaining' => $item->remaining
                                    ];
                                })->toArray();
                                $detailsJson = json_encode($details);
                            @endphp

                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $employeeName ? ucfirst($employeeName) : 'N/A'}} </td>
                                <td class="text-center"> {{ $year }}</td>


                                <td class="text-center text-primary">
                                    {{ $totalAllocated }}

                                </td>
                                <td class="text-center text-danger">{{ $totalUsed }}</td>

                                <td class="text-center text-success">{{ $totalRemaining }}</td>
                                <td class="text-center">
                                    <a class="view-details" title="View Details"
                                            data-employee-name="{{ $employeeName }}"
                                            data-details="{{ $detailsJson }}"> <i class="link-icon" data-feather="eye"></i></a>
                                </td>
                            </tr>



                        @empty
                            <tr>
                                <td colspan="100%">
                                    <p class="text-center"><b>{{ __('index.no_records_found') }}</b></p>
                                </td>
                            </tr>
                        @endforelse

                        </tbody>
                    </table>
                </div>
            </div>
        </div>


        <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header text-center">
                        <h5 class="modal-title" id="detailsModalLabel">Leave Details</h5>
                    </div>
                    <div class="modal-body">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Leave Type</th>
                                <th class="text-center text-primary">Allocated</th>
                                <th class="text-center text-danger">Used</th>
                                <th class="text-center text-success">Remaining</th>
                            </tr>
                            </thead>
                            <tbody id="detailsTableBody">
                            <!-- Dynamic content will be inserted here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </section>


@endsection

@section('scripts')
    @include('admin.leaveRequest.common.scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const viewButtons = document.querySelectorAll('.view-details');
            const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
            const modalTitle = document.getElementById('detailsModalLabel');
            const tableBody = document.getElementById('detailsTableBody');

            viewButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    const employeeName = this.dataset.employeeName;
                    const details = JSON.parse(this.dataset.details);

                    // Update modal title
                    modalTitle.textContent = `Leave Balance Details for ${employeeName || 'Employee'}`;

                    // Clear and populate table body
                    tableBody.innerHTML = '';
                    details.forEach(function (detail) {
                        const row = `
                            <tr>
                                <td>${detail.leave_type || 'N/A'}</td>
                                <td class="text-center text-primary">${detail.allocated || 0}</td>
                                <td class="text-center text-danger">${detail.used || 0}</td>
                                <td class="text-center text-success">${detail.remaining || 0}</td>
                            </tr>
                        `;
                        tableBody.innerHTML += row;
                    });

                    // Show modal
                    modal.show();
                });
            });
        });
    </script>
@endsection
