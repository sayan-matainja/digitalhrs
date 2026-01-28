@extends('layouts.master')
@section('title',__('index.award_types'))
@section('action',__('index.lists'))

@section('button')
    @can('create_award_type')
        <button class="btn btn-primary create-awardType mb-3">
            <i class="link-icon" data-feather="plus"></i> {{ __('index.add_award_types') }}
        </button>
    @endcan
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dataTables.dataTables.min.css') }}">
@endsection
@section('main-content')

    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.awardManagement.types.common.breadcrumb')
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">@lang('index.award_type_filter')</h6>
            </div>
            <form class="forms-sample card-body pb-0" action="{{ route('admin.award-types.index') }}" method="get">

                <div class="row align-items-center">
                    @if(!isset(auth()->user()->branch_id))
                        <div class="col-lg-3 col-md-6 mb-4">
                            <select class="form-select" id="branch" name="branch_id">
                                <option  {{ !isset($filterParameters['branch_id']) || old('branch_id') ? 'selected': ''}}  disabled>{{ __('index.select_branch') }}
                                </option>
                                @if(isset($companyDetail))
                                    @foreach($companyDetail->branches()->get() as $key => $branch)
                                        <option value="{{$branch->id}}"
                                            {{ (isset($filterParameters['branch_id']) && $filterParameters['branch_id'] == $branch->id) ? 'selected': '' }}>
                                            {{ucfirst($branch->name)}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    @endif

                    <div class="col-lg-3 col-md-6 mb-4">
                        <input type="text" class="form-control" placeholder="@lang('index.type') " name="type" id="title" value="{{ $filterParameters['type'] }}">
                    </div>

                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="d-flex">
                            <button type="submit" class="btn btn-block btn-success me-2">@lang('index.filter')</button>
                            <a class="btn btn-block btn-primary" href="{{ route('admin.award-types.index') }}">@lang('index.reset')</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.award_type_lists') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('index.name') }}</th>
                            <th class="text-center">{{ __('index.award_distributed') }}</th>
                            <th class="text-center">{{ __('index.status') }}</th>
                            @canany(['update_award_type','delete_award_type'])
                                <th class="text-center">{{ __('index.action') }}</th>
                            @endcanany
                        </tr>
                        </thead>
                        <tbody>

                        @forelse($awardTypes as $key => $value)
                            <tr>
                                <td>{{++$key}}</td>
                                <td>{{ucfirst($value->title)}}</td>
                                <td class="text-center">
                                    <a href="{{route('admin.award-types.show',$value->id)}}"> {{$value->awards_count}}</a>
                                </td>
                                <td class="text-center">
                                    <label class="switch">
                                        <input class="toggleStatus" href="{{route('admin.award-types.toggle-status',$value->id)}}"
                                               type="checkbox" {{($value->status) == 1 ?'checked':''}}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>

                                <td class="text-center">
                                    <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                        @can('update_award_type')
                                            <li class="me-2">
                                                <a class="edit-awardType"  data-id="{{ $value->id }}" data-href="{{ route('admin.award-types.edit', $value->id) }}">
                                                    <i class="link-icon" data-feather="edit"></i>
                                                </a>

                                            </li>
                                        @endcan

                                        @can('delete_award_type')
                                            <li>
                                                <a class="delete"
                                                   data-href="{{route('admin.award-types.delete',$value->id)}}" title="{{ __('index.delete') }}">
                                                    <i class="link-icon"  data-feather="delete"></i>
                                                </a>
                                            </li>
                                        @endcan
                                    </ul>
                                </td>

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
    </section>
    <div class="modal fade" id="awardTypeModal" tabindex="-1" aria-labelledby="awardTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header text-center">
                    <h5 class="modal-title" id="awardTypeModalLabel">{{ __('index.add_award_types') }}</h5>
                </div>
                <div class="modal-body pb-0">
                    <form id="awardTypeForm" class="forms-sample" enctype="multipart/form-data" method="POST">
                        @csrf
                        <input type="hidden" name="_method" id="formMethod" value="POST">
                        <div class="row align-items-center">
                            @if(!isset(auth()->user()->branch_id))
                                <div class="mb-4">
                                    <label for="branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
                                    <select class="form-select" id="branch_id" name="branch_id">
                                        <option selected disabled>{{ __('index.select_branch') }}</option>
                                        @if(isset($companyDetail))
                                            @foreach($companyDetail->branches()->get() as $key => $branch)
                                                <option value="{{$branch->id}}">{{ucfirst($branch->name)}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            @endif

                            <div class="mb-4">
                                <label for="name" class="form-label">{{ __('index.title') }} <span style="color: red">*</span></label>
                                <input type="text" class="form-control" id="name"
                                       required
                                       name="title"
                                       value="{{  old('title') }}"
                                       autocomplete="off"
                                       placeholder=""
                                >
                            </div>

                            <div class="col-lg-6 mb-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="link-icon" data-feather="plus"></i> <span id="submitButtonText">{{ __('index.save') }}</span>
                                </button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                            </div>

                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')

    <script src="{{ asset('assets/js/dataTables.min.js') }}"></script>
    <script>
        @if($awardTypes->isNotEmpty())
        let table = new DataTable('#dataTableExample', {
            pageLength: @json(getRecordPerPage()),
            searching: false,
            paging: true,
        });
        @endif

    </script>
    @include('admin.awardManagement.types.common.scripts')
@endsection






