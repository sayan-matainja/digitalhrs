@extends('layouts.master')
@section('title',__('index.leave_approval'))
@section('action',__('index.edit'))
@section('styles')
    <style>
        #sortable li {
            list-style: none;
        }
        /* Target odd rows */
        #sortable li:nth-child(odd) {
            background-color: #f9f9f9;
        }

        /* Target even rows */
        #sortable li:nth-child(even) {
            background-color: #e9ecef;
        }

        /* Additional styling */
        #sortable li {
            padding: 10px;
            margin-bottom: 5px;
            border-radius: 4px;
        }
    </style>
@endsection
@section('main-content')

    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.leaveApproval.common.breadcrumb')
        <div class="row">
{{--            <div class="col-lg-2">--}}
{{--                @include('admin.leaveRequest.common.leave_menu')--}}
{{--            </div>--}}
{{--            <div class="col-lg-10">--}}
                <div class="card">
                    <div class="card-body">
                        <form id="" class="forms-sample" action="{{route('admin.leave-approval.update',$leaveApprovalDetail->id)}}" enctype="multipart/form-data"  method="post">
                            @method('PUT')
                            @csrf
                            @include('admin.leaveApproval.common.form')
                        </form>
                    </div>
                </div>
{{--            </div>--}}
        </div>

    </section>
@endsection

@section('scripts')
    @include('admin.leaveApproval.common.scripts')
    @foreach($leaveApprovalDetail->approvalProcess as $index => $process)
        <script>
            $(document).ready(function() {
                let $userSelect = $('[name="user_id[{{ $index }}]"]');
                @if($process->user_id)
                $userSelect.append(new Option('{{ $process->user->name ?? "" }}', '{{ $process->user_id }}', true, true));
                @endif
                $userSelect.select2({
                    width: '100%',
                    placeholder: "{{ __('index.select_employee') }}"
                });
            });
        </script>
    @endforeach
@endsection

