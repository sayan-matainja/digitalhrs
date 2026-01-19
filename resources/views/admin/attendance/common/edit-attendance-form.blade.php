<div class="modal fade" id="attendanceForm" tabindex="-1" aria-labelledby="addslider" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-center">
                <h5 class="modal-title" id="exampleModalLabel">{{ __('index.edit_attendance') }}</h5>
            </div>
            <div class="modal-body">
                <div class="container">
                    <form class="forms-sample" id="editAttendance" action=""  method="post">
                        @csrf
                        @method('put')
                        <div class="row">
                            <label for="exampleFormControlSelect1" class="form-label">{{ __('index.check_in_at') }}</label>

                            <div class="col-lg-12 mb-3">
                                <input type="time" class="form-select" id="check_in" name="check_in_at"  value="" />
                            </div>

                            <label for="exampleFormControlSelect1" class="form-label">{{ __('index.check_out_at') }}</label>
                            <div class="col-lg-12 mb-3">
                                <input type="time" class="form-select" id="check_out" name="check_out_at"  value="" />
                            </div>

                            <label for="exampleFormControlSelect1" class="form-label">{{ __('index.admin_edit_remark') }}</label>
                            <div class="col-lg-12 mb-3">
                                <textarea class="form-select" id="remark" minlength="10" name="edit_remark" required rows="3"></textarea>
                            </div>

                        </div>
                        <div class="text-center">
                            <button type="submit" id="submit-btn" class="btn btn-primary btn-xs">{{ __('index.submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
