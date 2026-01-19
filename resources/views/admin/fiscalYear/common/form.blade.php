<div class="row align-items-center">
    <div class="col-lg-6 col-md-6 mb-4">
        <label for="year" class="form-label">@lang('index.title') <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="year"
               required
               name="year"
               value="{{ ( isset($fiscalYearDetail) ? ($fiscalYearDetail->year): old('year') )}}"
               autocomplete="off"
               placeholder=""
        >


    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <label for="start_date" class="form-label">@lang('index.start_date')<span style="color: red">*</span></label>
        @if($isBsEnabled)
            <input type="text" class="form-control" id="nepali-datepicker-from"
                   required
                   name="start_date"
                   value="{{ ( isset($fiscalYearDetail) ? \App\Helpers\AppHelper::dateInYmdFormatEngToNep($fiscalYearDetail->start_date): old('start_date') )}}"
                   autocomplete="off"
                   placeholder=""
            >
        @else
            <input type="date" class="form-control" id="start_date"
                   required
                   name="start_date"
                   value="{{ ( isset($fiscalYearDetail) ?  ($fiscalYearDetail->start_date): old('start_date') )}}"
                   autocomplete="off"
                   placeholder=""
            >
        @endif

    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <label for="end_date" class="form-label">@lang('index.end_date')<span style="color: red">*</span></label>
        @if($isBsEnabled)
            <input type="text" class="form-control" id="nepali-datepicker-to"
                   required
                   name="end_date"
                   value="{{ ( isset($fiscalYearDetail) ? \App\Helpers\AppHelper::dateInYmdFormatEngToNep($fiscalYearDetail->end_date): old('end_date') )}}"
                   autocomplete="off"
                   placeholder=""
            >
        @else
            <input type="date" class="form-control" id="end_date"
                   required
                   name="end_date"
                   value="{{ ( isset($fiscalYearDetail) ? ($fiscalYearDetail->end_date): old('end_date') )}}"
                   autocomplete="off"
                   placeholder=""
            >
        @endif

    </div>
    @canany(['create_type','edit_type'])
        <div class="col-lg-12 col-md-6">
            <button type="submit" class="btn btn-primary"><i class="link-icon" data-feather="{{isset($fiscalYearDetail)? 'edit-2':'plus'}}"></i>
                {{isset($fiscalYearDetail)? 'Update':'Create'}}
            </button>
        </div>
    @endcanany
</div>
