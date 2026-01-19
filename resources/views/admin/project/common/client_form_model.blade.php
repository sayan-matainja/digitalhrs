<div class="modal fade" id="addslider" tabindex="-1" aria-labelledby="addslider" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-center">
                <h5 class="modal-title" id="exampleModalLabel">{{ __('index.add_client') }}</h5>
            </div>
            <div class="modal-body">
                <div id="showErrorMessageResponse" class="d-none">
                    <div class="alert alert-danger errorClient">
                        <p class="errorClientMessage"></p>
                    </div>
                </div>

                <form class="forms-sample" id="client_form" action="{{route('admin.clients.ajax-store')}}"
                      enctype="multipart/form-data" method="post">
                    {{ csrf_field() }}
                    <div class="row">
                        @if(!isset(auth()->user()->branch_id))
                            <div class="col-lg-6 mb-3">
                                <label for="branchId" class="form-label">{{ __('index.branch') }} <span
                                        style="color: red">*</span></label>
                                <select class="form-select" id="branchId" required name="branch_id">
                                    <option selected disabled>{{ __('index.select_branch') }}</option>
                                    @foreach($branch as $value)
                                        <option value="{{ $value->id }}">
                                            {{ ucfirst($value->name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-lg-6 mb-3">
                            <label for="clientName" class="form-label">{{ __('index.client_name') }} <span
                                    style="color: red">*</span></label>
                            <input type="text" class="form-control" id="clientName" name="name" required value=""
                                   autocomplete="off" placeholder="{{ __('index.client_name') }}">
                        </div>

                        <div class="col-lg-6 mb-3">
                            <label for="email" class="form-label">{{ __('index.client_email') }} <span
                                    style="color: red">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required value=""
                                   autocomplete="off" placeholder="{{ __('index.client_email') }}">
                        </div>

                        <div class="col-lg-6 mb-3">
                            <label for="contact_no" class="form-label">{{ __('index.client_contact_number') }} <span
                                    style="color: red">*</span></label>
                            <input type="text" class="form-control" id="contact_no" name="contact_no" required value=""
                                   autocomplete="off" placeholder="{{ __('index.client_contact_number') }}">
                        </div>

                        <div class="col-lg-6 mb-3">
                            <label for="address" class="form-label">{{ __('index.address') }}</label>
                            <input type="text" class="form-control" id="address" name="address" value=""
                                   autocomplete="off" placeholder="{{ __('index.address') }}">
                        </div>

                        <div class="col-lg-6 mb-3">
                            <label for="country" class="form-label">{{ __('index.country') }} <span
                                    style="color: red">*</span></label>
                            <input type="text" class="form-control" id="country" name="country" required value=""
                                   autocomplete="off" placeholder="{{ __('index.country') }}">
                        </div>

                        <div class="col-lg-6 mb-3">
                            <label for="avatar" class="form-label">{{ __('index.upload_client_profile') }} <span
                                    style="color: red">*</span></label>
                            <input class="form-control" type="file" id="avatar" name="avatar" value="">
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary submit">
                                <i class="link-icon" data-feather="plus"></i> {{ __('index.create') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
