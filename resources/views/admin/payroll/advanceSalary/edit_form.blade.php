

<div class="col-md-12 grid-margin stretch-card">
    <div class="card">
        <div class="card-body">
            <form class="forms-sample" id="updateAdvanceSalaryRequestStatus"
                  enctype="multipart/form-data"
                  action="{{route('admin.advance-salaries.update',$advanceSalaryDetail->id)}}"
                  method="post" >
                @method('PUT')
                @csrf
                <div class="row">

                    <div class="col-lg-6 mb-3">
                        <label for="status" class="form-label"> {{ __('index.status') }} <span style="color: red">*</span></label>
                        <select class="form-select form-select-lg" name="status" id="status" required>
                            <option value="" >{{ __('index.select_status') }}</option>
                            <option value="processing" {{ $advanceSalaryDetail->status == 'processing' ? 'selected': ''}}>{{ __('index.processing') }}</option>
                            <option value="approved" {{ $advanceSalaryDetail->status == 'approved' ? 'selected': ''}}>{{ __('index.approved') }}</option>
                            <option value="rejected" {{ $advanceSalaryDetail->status == 'rejected' ? 'selected': ''}}>{{ __('index.rejected') }}</option>
                        </select>
                    </div>

                    <div class="col-lg-6 mb-3 releaseAmount">
                        <label for="released_amount" class="form-label"> {{ __('index.released_amount') }} <span style="color: red">*</span></label>
                        <input type="number" min="100" class="form-control amountReleased"  name="released_amount"
                               value="{{ old('released_amount') }}"
                               autocomplete="off" placeholder="Enter Total Released Amount">
                    </div>

                    <div class="col-lg-6 mb-3 reason">
                        <label for="remark" class="form-label">{{ __('index.remark') }}  <span style="color: red">*</span></label>
                        <textarea class="form-control remark"  name="remark" id="remark"  rows="4">{{old('remark')}}</textarea>
                    </div>

                    <div class="mb-3 col-12 document">
                        <h6 class="mb-2">{{ __('index.attachments') }} </h6>
                        <div>
                            <input id="image-uploadify" type="file"  class="attachment"  name="documents[]"
                                   accept=".pdf,.jpg,.jpeg,.png,.docx,.doc,.xls,.txt,.zip"  multiple />
                        </div>
                        @error('documents')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                        @error('documents.*')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="link-icon" data-feather="edit-2"></i>{{ __('index.update') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
