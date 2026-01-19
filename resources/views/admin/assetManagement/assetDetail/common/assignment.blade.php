<!-- Modal for Asset Assignment -->
<div class="modal fade" id="assetAssignmentModal" tabindex="-1" aria-labelledby="assetAssignmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-center">
                <h5 class="modal-title" id="assetAssignmentModalLabel">{{ __('index.assign_asset') }}</h5>
            </div>
            <div class="modal-body">
                <form id="assetAssignmentForm" method="POST" action="">
                    @csrf
                    <input type="hidden" name="asset_id" id="assignment_asset_id">
                    <input type="hidden" name="branch_id" id="assignment_branch_id">

                    <div class="mb-3">
                        <label for="assignment_department_id" class="form-label">{{ __('index.department') }}</label>
                        <select name="department_id" id="assignment_department_id" class="form-select" required>
                            <option value="">{{ __('index.select_department') }}</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="assignment_user_id" class="form-label">{{ __('index.employee') }}</label>
                        <select name="user_id" id="assignment_user_id" class="form-select" required>
                            <option value="">{{ __('index.select_employee') }}</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="assigned_date" class="form-label">{{ __('index.assigned_date') }}</label>
                        <input type="date" name="assigned_date" id="assigned_date" class="form-control" required
                               value="{{ date('Y-m-d') }}">
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('index.close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('index.assign_asset') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
