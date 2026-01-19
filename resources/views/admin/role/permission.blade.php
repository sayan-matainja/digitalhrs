@extends('layouts.master')

@section('title',__('index.permission_setting'))

@section('action',__('index.assign'))

@section('button')
    <a href="{{route('admin.roles.index')}}" class="btn btn-primary btn-sm"> <i class="link-icon" data-feather="arrow-left"></i> @lang('index.back') </a>
@endsection
@section('styles')

    <style>
        li.item{position: relative;}
        li.item label p.grant_leave {
            visibility: hidden;
            transition: all ease-in-out 0.3s;
            width:550px;
            background-color: #fcfcfc;
            border:1px solid #f23e6d;
            padding: 10px;
            position: absolute;
            top: -20px;
            z-index: 1;
            left: 100%;
            border-radius: 10px;
            line-height: 1.5;
        }

        li.item label:hover p.grant_leave {
            visibility: visible;
            transition: all ease-in-out 0.3s;
        }
    </style>
@endsection
@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        @include('admin.role.common.breadcrumb')

        <div class="card">
            <div class="card-header card-nav">
                <ul class="nav nav-tabs d-md-flex d-block text-center">
                    @foreach($allRoles as $key => $value)
                        <a class="nav-link my-md-0 my-1 d-inline-block {{$value->id == $role->id ? 'active': ''}}" href="{{route('admin.roles.permission',$value->id)}}">
                            <button class="btn btn-md btn-{{$value->id == $role->id ? 'primary':'secondary'}}">{{ucfirst($value->name)}} </button>
                        </a>
                    @endforeach
                </ul>
            </div>
            <div class="card-body card-nav-content">
                <form class="forms-sample" action="{{route('admin.role.assign-permissions',$role->id)}}" method="post">
                    @method('PUT')
                    @csrf
                    @include('admin.role.common.permission')
                </form>
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

            $(function() {
                $('.js-check-all').on('click', function() {
                    // Get the checked state of the "check all" checkbox itself
                    let isChecked = $(this).prop('checked');

                    // Apply this state to all child checkboxes
                    $(this).parent().parent().siblings().children('.item').children()
                        .find('.module_checkbox').prop('checked', isChecked);
                });
            });

        });

    </script>
@endsection






