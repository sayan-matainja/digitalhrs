@extends('layouts.master')
@section('title',__('index.salary_components'))
@section('sub_page',__('index.lists'))
@section('page')
        <a href="{{ route('admin.salary-components.index')}}">
            {{ __('index.salary_components') }}
        </a>
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        @include('admin.payrollSetting.common.breadcrumb')

        <div class="row">
            <div class="col-lg-2 mb-4">
                @include('admin.payrollSetting.common.setting_menu')
            </div>
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header">
                        <div class="justify-content-end">
                            @can('salary_component')
                                <a class="btn btn-success"
                                   href="{{ route('admin.salary-components.create')}}">
                                    <i class="link-icon" data-feather="plus"></i> {{ __('index.add_salary_component') }}
                                </a>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="dataTableExample" class="table">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th class="text-center">{{ __('index.component_type') }}</th>
                                    <th class="text-center">{{ __('index.component_value') }}</th>
                                    <th class="text-center">{{ __('index.value_type') }}</th>

                                    <th class="text-center">{{ __('index.taxable') }}</th>

                                    <th class="text-center">{{ __('index.status') }}</th>
                                    <th class="text-center">{{ __('index.action') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                @forelse($salaryComponentLists as $key => $value)
                                    <tr>
                                        <td>{{++$key}}</td>
                                        <td>{{ucfirst($value->name)}}</td>

                                        <td class="text-center">
                                            {{ ucfirst($value->component_type) }}
                                        </td>
                                        <td class="text-center">
                                            {{ ucfirst(\App\Models\SalaryComponent::VALUE_TYPE[$value->value_type])}}
                                        </td>
                                        <td class="text-center">{{ $value->annual_component_value  }}{{$value->value_type == 'fixed' ? '': '%'}}</td>

                                        {{-- added --}}
                                        <td class="text-center">
                                            <label class="switch">
                                                <input class="toggleTaxable" href="{{route('admin.salary-components.toggle-taxable',$value->id)}}"                                                    type="checkbox" {{($value->taxable) == 1 ?'checked':''}}>
                                                <span class="slider round"></span>
                                            </label>
                                        </td>

                                        <td class="text-center">
                                            <label class="switch">
                                                <input class="toggleStatus" href="{{route('admin.salary-components.toggle-status',$value->id)}}"
                                                       type="checkbox" {{($value->status) == 1 ?'checked':''}}>
                                                <span class="slider round"></span>
                                            </label>
                                        </td>

                                        <td class="text-center">
                                            <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                                @can('salary_component')
                                                    <li class="me-2">
                                                        <a href="{{route('admin.salary-components.edit',$value->id)}}"
                                                           title="Edit Detail">
                                                            <i class="link-icon" data-feather="edit"></i>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="delete"
                                                           data-href="{{route('admin.salary-components.delete',$value->id)}}"
                                                           title="Delete">
                                                            <i class="link-icon" data-feather="delete"></i>
                                                        </a>
                                                    </li>
                                                @endcan
                                            </ul>
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
            </div>
        </div>

    </section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });


            // added
            $('.toggleTaxable').change(function (event) {
                event.preventDefault();
                var taxable = $(this).prop('checked') === true ? 1 : 0;
                var href = $(this).attr('href');
                Swal.fire({
                    title: 'Are you sure you want to change taxable status?',
                    showDenyButton: true,
                    confirmButtonText: `Yes`,
                    denyButtonText: `No`,
                    padding:'10px 50px 10px 50px',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }else if (result.isDenied) {
                        (taxable === 0)? $(this).prop('checked', true) :  $(this).prop('checked', false)
                    }
                })
            })

            $('.toggleStatus').change(function (event) {
                event.preventDefault();
                var status = $(this).prop('checked') === true ? 1 : 0;
                var href = $(this).attr('href');
                Swal.fire({
                    title: 'Are you sure you want to change status ?',
                    showDenyButton: true,
                    confirmButtonText: `Yes`,
                    denyButtonText: `No`,
                    padding:'10px 50px 10px 50px',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }else if (result.isDenied) {
                        (status === 0)? $(this).prop('checked', true) :  $(this).prop('checked', false)
                    }
                })
            })

            $('.delete').click(function (event) {
                event.preventDefault();
                let href = $(this).data('href');
                Swal.fire({
                    title: 'Are you sure you want to Delete ?',
                    showDenyButton: true,
                    confirmButtonText: `Yes`,
                    denyButtonText: `No`,
                    padding:'10px 50px 10px 50px',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                })
            })
        });
    </script>
@endsection






