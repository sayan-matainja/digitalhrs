<div class="modal fade" id="assignmentDetail" tabindex="-1" aria-labelledby="assignmentDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-sm">
            <div class="modal-header text-center">
                <h5 class="modal-title assignmentTitle" id="assignmentDetailLabel"></h5>
            </div>
            <div class="modal-body p-4">
                <table class="table table-borderless table-hover">
                    <tbody>
                        <tr>
                            <th scope="row" class="text-muted w-30">{{ __('index.asset') }}</th>
                            <td class="asset fw-medium"></td>
                        </tr>
                        <tr>
                            <th scope="row" class="text-muted w-30">{{ __('index.employee') }}</th>
                            <td class="employee fw-medium"></td>
                        </tr>

                        <tr>
                            <th scope="row" class="text-muted w-30">{{ __('index.assigned_date') }}</th>
                            <td class="assigned_date fw-medium"></td>
                        </tr>

                        <tr>
                            <th scope="row" class="text-muted w-30">{{ __('index.status') }}</th>
                            <td class="status fw-medium"></td>
                        </tr>

                        <tr>
                            <th scope="row" class="text-muted w-30">{{ __('index.returned_date') }}</th>
                            <td class="returned_date fw-medium"></td>
                        </tr>

                        <tr>
                            <th scope="row" class="text-muted w-30">{{ __('index.return_condition') }}</th>
                            <td class="return_condition fw-medium"></td>
                        </tr>

                        <tr>
                            <th scope="row" class="text-muted w-30">{{ __('index.notes') }}</th>
                            <td class="notes"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
