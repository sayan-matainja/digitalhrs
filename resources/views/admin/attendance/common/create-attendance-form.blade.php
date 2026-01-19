<div class="modal fade" id="attendanceCreateForm" tabindex="-1" aria-labelledby="addslider" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-center">
                <h5 class="add-modal-title" id="exampleModalLabel1">{{ __('index.create_attendance') }}</h5>
            </div>
            <div class="modal-body">
                <div class="container">
                    <form class="forms-sample" id="createAttendance" action=""  method="post">
                        @csrf
                        <div class="row">
                            <input type="hidden" readonly class="form-select" id="empId" name="user_id"  value="" />


                            <div class="col-lg-12 mb-3 d-none">
                                <label for="exampleFormControlSelect1" class="form-label">{{ __('index.date') }}</label>
                                <input type="hidden" readonly class="form-select" id="addDate" name="attendance_date"  value="" />
                            </div>

                            <label for="exampleFormControlSelect1" class="form-label">{{ __('index.check_in_at') }}</label>

                            <div class="col-lg-12 mb-3">
                                <input type="time" class="form-select" required id="checkAddIn" name="check_in_at"  value="" />
                            </div>

                            <label for="exampleFormControlSelect1" class="form-label">{{ __('index.check_out_at') }}</label>
                            <div class="col-lg-12 mb-3">
                                <input type="time" class="form-select" required id="checkAddOut" name="check_out_at"  value="" />
                            </div>

                            <label for="exampleFormControlSelect1" class="form-label">{{ __('index.admin_remark') }}</label>
                            <div class="col-lg-12 mb-3">
                                <textarea class="form-select" id="createRemark" minlength="10" name="edit_remark" required rows="3"></textarea>
                            </div>

                        </div>
                        <div class="text-center">
                            <button type="submit" id="save-btn" class="btn btn-primary btn-xs">{{ __('index.submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
