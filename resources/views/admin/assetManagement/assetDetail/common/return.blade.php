<div class="modal fade" id="assetReturnModal" tabindex="-1" aria-labelledby="assetReturnModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-center">
                <h5 class="modal-title" id="assetReturnModalLabel">Return Asset</h5>
            </div>
            <div class="modal-body">
                <form id="assetReturnForm" method="POST" action="" class="needs-validation" novalidate>
                    @csrf
                    <div class="mb-3">
                        <label for="is_working" class="form-label">Is Working?</label>
                        <select name="is_working" id="is_working" class="form-select" required>
                            <option selected disabled>Select</option>
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                        <div class="invalid-feedback">
                            Please select whether the asset is working.
                        </div>
                    </div>

                    <div class="mb-3 notes-field">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea name="notes" id="notes" class="form-control" rows="4" placeholder="Enter any notes"></textarea>
                        <div class="invalid-feedback">
                            Please provide notes when the asset is not working.
                        </div>
                    </div>

                    <div class="modal-footer justify-content-start">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Return Asset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
