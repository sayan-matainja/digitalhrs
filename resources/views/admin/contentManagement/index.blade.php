
@extends('layouts.master')

@section('title',__('index.company_static_content'))

@section('action',__('index.lists'))

@section('button')
    @can('create_content')
        <a href="{{ route('admin.static-page-contents.create')}}">
            <button class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i>{{ __('index.add_content') }}
            </button>
        </a>
    @endcan
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.contentManagement.common.breadcrumb')

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('index.title') }}</th>
                            <th>{{ __('index.type') }}</th>

                            @can('show_content')
                                <th class="text-center">{{ __('index.content') }}</th>
                            @endcan

                            <th class="text-center">{{ __('index.status') }}</th>

                            @canany(['edit_content','delete_content'])
                                <th class="text-center">{{ __('index.action') }}</th>
                            @endcanany
                        </tr>
                        </thead>
                        <tbody>
                        <tr>

                        @forelse($staticPageContents as $key => $value)
                            <tr>
                                <td>{{++$key}}</td>
                                <td>{{removeSpecialChars($value->title)}}</td>
                                <td>{{removeSpecialChars($value->content_type)}}</td>

                                @can('show_content')
                                    <td class="text-center">
                                        <a href=""
                                           id="showStaticPageDescription"
                                           data-href="{{route('admin.static-page-contents.show',$value->id)}}"
                                           data-id="{{ $value->id }}" title="{{ __('index.show_detail') }}">
                                            <i class="link-icon" data-feather="eye"></i>

                                        </a>
                                    </td>
                                @endcan

                                <td class="text-center">
                                    <label class="switch">
                                        <input class="toggleStatus" href="{{route('admin.static-page-contents.toggle-status',$value->id)}}"
                                               type="checkbox" {{($value->is_active) == 1 ?'checked':''}}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>

                                @canany(['edit_content','delete_content'])
                                <td class="text-center">
                                    <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                        @can('edit_content')
                                            <li class="me-2">
                                                <a href="{{route('admin.static-page-contents.edit',$value->id)}}" title="{{ __('index.edit') }}">
                                                    <i class="link-icon" data-feather="edit"></i>
                                                </a>
                                            </li>
                                        @endcan

                                        @can('delete_content')
                                            <li>
                                                <a class="deleteStaticPageContent"
                                                   data-href="{{route('admin.static-page-contents.delete',$value->id)}}" title="{{ __('index.delete') }}">
                                                    <i class="link-icon"  data-feather="delete"></i>
                                                </a>
                                            </li>
                                        @endcan
                                    </ul>
                                </td>
                                @endcan


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
    </section>
    @include('admin.contentManagement.show')
@endsection

@section('scripts')

   @include('admin.contentManagement.common.scripts')
@endsection






