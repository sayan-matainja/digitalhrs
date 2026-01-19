
<div class="modal fade" id="addslider" tabindex="-1" aria-labelledby="addslider" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-center">
                <h5 class="modal-title" id="exampleModalLabel"></h5>
            </div>
            <div class="modal-body">
                <div class="support-main">
                    <div class="support-list d-flex align-items-center border-bottom pb-3 mb-3 gap-2">
                        <strong>{{ __('index.query_by') }}:</strong> <p class="creator"> </p>
                    </div>

                    <div class="support-list d-flex align-items-center border-bottom pb-3 mb-3 gap-2">
                        <strong>{{ __('index.status') }} :</strong> <p class="status"> </p>
                    </div>

                    <div class="support-list d-flex align-items-center border-bottom pb-3 mb-3 gap-2">
                        <strong>{{ __('index.branch') }}:</strong> <p class="branch"> </p>
                    </div>

                    <div class="support-list d-flex align-items-center border-bottom pb-3 mb-3 gap-2">
                        <strong>{{ __('index.department_support_requested_from') }}:</strong> <p class="department"></p>
                    </div>

                    <div class="support-list d-flex align-items-center border-bottom pb-3 mb-3 gap-2">
                        <strong>{{ __('index.department_support_requested_to') }}:</strong> <p class="requested"></p>
                    </div>
                </div>
                <div class="support-list">
                    <strong>{{ __('index.description') }}:</strong> <p class="description"> </p>
                </div>

                @can('update_query_status')
                    <form class="forms-sample pt-3 mt-3 border-top" id="statusChange" action=""  method="post" >
                        @method('PUT')
                        @csrf
                        <div class="support-main">
                            <label for="" class="form-label d-block">{{ __('index.change_query_status') }}</label>
                            <div class="support-list border-bottom pb-3 mb-3">

                                <select class="form-select form-select-lg" name="status" id="changeStatus" required>
                                    <option value="" >{{ __('index.select_status') }}</option>
                                    @foreach(\App\Models\Support::STATUS as $value)
                                        @if($value != 'pending')
                                            <option value="{{$value}}"> {{removeSpecialChars($value)}} </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary submit">{{ __('index.update') }}</button>
                        </div>
                    </form>
                @endcan
            </div>
        </div>
    </div>
</div>
