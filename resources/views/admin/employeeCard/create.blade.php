<!-- resources/views/admin/employee/card/setting.blade.php -->

@extends('layouts.master')

@section('title', __('index.card_template'))

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Create ID Card Template</h3>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.card.save-template') }}" id="idCardForm" enctype="multipart/form-data">
                            @csrf
                            @include('admin.employeeCard.common.form')
                            <button type="submit" class="btn btn-success btn-lg">
                                Save
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    @include('admin.employeeCard.common.scripts')
@endsection
