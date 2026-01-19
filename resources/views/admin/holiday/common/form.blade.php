<div class="row">
    <div class="col-lg-6">
        <div class="row">
            <div class="col-lg-12 mb-4">
                <label for="event" class="form-label">{{ __('index.event') }}<span style="color: red">*</span></label>
                <input type="text" class="form-control" id="event" required name="event" value="{{ ( isset($holidayDetail) ? ($holidayDetail->event): old('event') )}}" autocomplete="off" placeholder="">
            </div>

            <div class="col-lg-12 mb-4">
                <label for="event_date" class="form-label">{{ __('index.event_date') }}<span style="color: red">*</span></label>
                <input class="form-control" name="event_date" value="{{(isset($holidayDetail) ? ($holidayDetail->event_date): old('event_date') )}}" required
                    @if(\App\Helpers\AppHelper::ifDateInBsEnabled())
                        type="text"
                    id="eventDate"
                    placeholder="yyyy/mm/dd"
                    @else
                        type="date"
                    @endif
                />
            </div>

            <div class="col-lg-12 mb-4">
                <label for="exampleFormControlSelect1" class="form-label">{{ __('index.is_public_holiday') }}</label>
                <select class="form-select" id="exampleFormControlSelect1" name="is_public_holiday">
                    <option value="" {{isset($holidayDetail) ? '': 'selected'}} disabled>{{ __('index.select_status') }}</option>
                    <option value="1" @selected( old('is_public_holiday', isset($holidayDetail) && $holidayDetail->is_public_holiday ) == 1)>{{ __('index.yes') }}</option>
                    <option value="0" @selected( old('is_public_holiday', isset($holidayDetail) && $holidayDetail->is_public_holiday ) == 0)>{{ __('index.no') }}</option>
                </select>
            </div>
        </div>
    </div>    


    <div class="col-lg-6 mb-4">
        <label for="note" class="form-label">{{ __('index.description') }}</label>
        <textarea class="form-control" name="note" id="tinymceExample">{{ ( isset($holidayDetail) ? $holidayDetail->note: old('note') )}}</textarea>
    </div>

    <div class="col-lg-12">
        <button type="submit" class="btn btn-primary"><i class="link-icon" data-feather="{{isset($holidayDetail)? 'edit-2':'plus'}}"></i>
            {{isset($holidayDetail)? __('index.update') : __('index.create')}} {{ __('index.holiday') }}
        </button>
    </div>
</div>
