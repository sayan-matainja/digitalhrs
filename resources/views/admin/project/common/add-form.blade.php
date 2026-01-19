<div class="modal fade" id="assignEmployee" tabindex="-1" aria-labelledby="addslider" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-center">
                <h5 class="modal-title" id="exampleModalLabel">{{ __('index.assign_employee') }}</h5>
            </div>
            <div class="modal-body">
                <div id="showErrorMessageResponse" class="d-none">
                    <div class="alert alert-danger errorClient">
                        <p class="errorClientMessage">{{ __('index.error_message') }}</p>
                    </div>
                </div>

                <form class="forms-sample addMember" id="addLeaderForm" action="" method="post">
                    {{ csrf_field() }}
                    <div class="row">
                        <div class="col-lg-12 mb-3">
                            <select class="col-md-12 from-select" id="add" name="add[]" multiple="multiple" required>
                                <!-- Options will be loaded here -->
                            </select>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary submit">
                                <i class="link-icon" data-feather="plus"></i> {{ __('index.add') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
