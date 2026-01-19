<div class="modal fade" id="cancelRequestModal" tabindex="-1" aria-labelledby="cancelRequestLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-center">
                <h5 class="modal-title" id="cancelRequestLabel">{{ __('index.cancel_request_approval') }}</h5>
            </div>
            <div class="modal-body">
                <div id="cancelReasonDisplay"></div>

                <div class="container">
                    <form class="forms-sample" id="cancelRequestForm" action="" method="post">
                        @csrf
                        @method('put')
                        <div class="row">
                            <label for="exampleFormControlSelect1" class="form-label">{{__('index.status')}} </label>
                            <div class="col-lg-12 mb-3">
                                <select class="form-select" id="status" name="status">
                                    <option value="{{ \App\Enum\LeaveStatusEnum::approved->value }}" >{{__('index.approve')}}</option>
                                    <option value="{{ \App\Enum\LeaveStatusEnum::rejected->value }}" >{{__('index.reject')}}</option>
                                </select>
                            </div>

                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-xs">{{ __('index.submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
