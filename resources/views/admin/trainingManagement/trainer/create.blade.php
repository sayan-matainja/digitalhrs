@extends('layouts.master')

@section('title',__('index.trainer'))

@section('action',__('index.create'))

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.trainingManagement.trainer.common.breadcrumb')

        <div class="card">
            <div class="card-body">
                <form id="" class="forms-sample" action="{{route('admin.trainers.store')}}" enctype="multipart/form-data"  method="POST">
                    @csrf
                    @include('admin.trainingManagement.trainer.common.form')
                </form>
            </div>
        </div>

    </section>
@endsection

@section('scripts')

    @include('admin.trainingManagement.trainer.common.scripts', [
        'internal' => \App\Enum\TrainerTypeEnum::internal->value,
        'external' => \App\Enum\TrainerTypeEnum::external->value
    ])
@endsection
