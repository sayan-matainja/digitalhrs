
<div class="modal fade" id="updateMemberModal" tabindex="-1" aria-labelledby="updateMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateMemberModalLabel">{{ __('index.update_member') }}</h5>
            </div>
            <form class="forms-sample" id="addMemberToProjectForm" action="" method="post">

            <div class="modal-body">
                    @csrf
                    <input type="hidden" id="member_project_id" name="project_id" value="">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-lg-12">
                            <label for="memberAdd" class="form-label">{{ __('index.project_members') }} <span style="color: red">*</span></label>
                            <select class="w-100 form-select" id="memberAdd" name="employee[]" multiple="multiple" required>
                                @foreach($employees as $key => $value)
                                    <option value="{{ $value->id }}" {{ in_array($value->id, $projectMemberIds) ? 'selected' : '' }}>
                                        {{ ucfirst($value->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">

                <button type="submit" class="btn btn-primary submit">
                    <i class="link-icon" data-feather="plus"></i> @lang('index.update')
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('index.cancel')</button>
            </div>
            </form>

        </div>
    </div>
</div>
