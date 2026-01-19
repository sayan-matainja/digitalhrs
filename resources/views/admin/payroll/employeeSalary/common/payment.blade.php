

<div class="modal fade" id="paymentForm" tabindex="-1" aria-labelledby="addslider" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-center">
                <h5 class="modal-title" id="exampleModalLabel">{{ __('index.payment_title') }}</h5>
            </div>
            <div class="modal-body">
                <div class="container">
                    <div id="modal-errors" class="text-white"></div>

                    <form class="forms-sample" id="payrollPayment" action=""  method="post" >
                        @csrf
                        @method('put')
                        <div class="row">
                            <label for="exampleFormControlSelect1" class="form-label">{{ __('index.payment_method') }}<span style="color: red">*</span></label>
                            <div class="col-lg-12 mb-3">
                                <div class="col-lg-12 mb-3">
                                    <select name="payment_method_id" id="payment_method_id" class="form-control" required>
                                        <option disabled selected>{{ __('index.payment_method_placeholder') }}</option>
                                        @foreach($paymentMethods as $method)
                                            <option value="{{ $method['id'] }}"> {{ $method['name'] }}</option>
                                        @endforeach

                                    </select>
                                </div>
                            </div>

                            <label for="paid_on" class="form-label"> {{ __('index.payment_date') }}<span style="color: red">*</span></label>
                            <div class="col-lg-12 mb-3">
                                <input type="date" class="form-control" id="paid_on" name="paid_on" value="" required />
                            </div>

                        </div>
                        <div class="text-center">
                            <button type="submit" id="submit-btn" class="btn btn-primary btn-xs"> {{ __('index.submit') }} </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>






