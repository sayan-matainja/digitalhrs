<div class="modal fade" id="statusUpdate" tabindex="-1" aria-labelledby="addslider" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-center">
                <h5 class="modal-title">Termination Status Update</h5>
            </div>
            <div class="modal-body">
                <div class="container">
                    <form class="forms-sample" id="updateTerminationStatus" action="" method="post">
                        @csrf
                        @method('put')
                        <div class="row">
                            <label for="status" class="form-label">{{ __('index.status') }} </label>
                            <div class="col-lg-12 mb-3">
                                <select class="form-select" id="status" name="status">
                                    @foreach(\App\Enum\TerminationStatusEnum::cases() as $case)
                                        <option value="{{ $case->value }}">{{ ucfirst($case->name) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <label for="admin_remark" class="form-label">{{ __('index.admin_remark') }}</label>
                            <div class="col-lg-12 mb-3">
                                <textarea class="form-select" id="admin_remark" minlength="10" name="admin_remark" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="submit" id="submit-btn" class="btn btn-primary btn-xs"> {{ __('index.submit') }} </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
