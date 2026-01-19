{{-- resources/views/admin/employeeCard/common/form.blade.php --}}


<div class="template-main">
    <div class="row">
        <div class="col-md-4 mb-4">
            <label class="form-label d-block">Card Orientation <span class="text-danger">*</span></label>
            <div class="btn-group" role="group" aria-label="Card orientation">
                <input type="radio" class="btn-check" name="orientation" id="landscape" value="landscape"
                    {{ old('orientation', $setting->orientation ?? 'landscape') === 'landscape' ? 'checked' : '' }} required>
                <label class="btn btn-outline-primary mb-0" for="landscape">Landscape (85.6 × 54 mm)</label>

                <input type="radio" class="btn-check" name="orientation" id="portrait" value="portrait"
                    {{ old('orientation', $setting->orientation ?? '') === 'portrait' ? 'checked' : '' }}>
                <label class="btn btn-outline-primary mb-0" for="portrait">Portrait (54 × 85.6 mm)</label>
            </div>
        </div>
        <!-- Template Title -->
        <div class="col-md-4 mb-4">
            <label for="title" class="form-label">
                Template Name <span class="text-danger">*</span>
            </label>
            <input type="text" name="title" id="title" class="form-control" required
                value="{{ old('title', $setting->title ?? '') }}"
                placeholder="e.g., Standard Employee ID Card 2025">
            @error('title') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="col-md-4 mb-4">
            <label class="form-label d-block">Background Color (Both Sides)</label>
            <input type="color" name="background_color" class="border-0 form-control-color"
                value="{{ old('background_color', $setting->background_color ?? '#ffffff') }}">
        </div>
    {{--    <div class="col-md-6 mb-4">--}}
    {{--        <label>Text Color (Both Sides)</label>--}}
    {{--        <input type="color" name="text_color" class="form-control form-control-color w-100"--}}
    {{--               value="{{ old('text_color', $setting->text_color ?? '#ffffff') }}">--}}
    {{--    </div>--}}

    </div>
    <div class="front-back-main">
        <div class="front-main mb-4">
            <div class="front-info d-flex gap-2 align-items-center border-bottom pb-2 mb-4">
                <h5 class="mb-2">Front Side</h5>
                <p class="text-info small fst-italic mb-2">
                    <strong>Always shown on front:</strong> Photo • Full Name • Designation
                </p>
            </div>
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="front-item">
                        <h6 class="mb-2">Extra Fields on Front (Select 1–4)</h6>
                        <p class="text-muted small mb-3">Select fields in desired order</p>
                            <div class="row g-4">
                                @php
                                    $extraOrder = old('extra_fields_order', $setting->extra_fields_order ?? []);
                                    $availableFields = [
                                        'employee_code'     => 'Employee Code',
                                        'department'        => 'Department',
                                        'joining_date'         => 'Joining Date',
                                        'phone'             => 'Phone',
                                        'email'             => 'Email Address',
                                    ];
                                @endphp

                                @for($i = 1; $i <= 4; $i++)
                                <div class="col-lg-6">
                                    <div class="border p-3 rounded">
                                        <h6 class="mb-2">Position {{ $i }}</h6>
                                        <select name="extra_fields_order[{{ $i }}]" class="form-select field-select">
                                            <option value="">Select</option>
                                            @foreach($availableFields as $key => $label)
                                                <option value="{{ $key }}"
                                                    {{ isset($extraOrder[$i]) && $extraOrder[$i] === $key ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="front-item">
                        <!-- QR/Barcode Type -->
                        <div class="front-item-qr mb-3">
                            <label class="form-label h6">QR Code / Barcode</label>
                            <br>
                            @foreach(['qr' => 'QR Code', 'barcode' => 'Barcode', 'none' => 'None'] as $val => $txt)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input graph-type" type="radio" name="graph_type" value="{{ $val }}"
                                        id="graph_{{ $val }}" {{ old('graph_type', $setting->graph_type ?? 'none') == $val ? 'checked' : '' }}>
                                    <label class="form-check-label" for="graph_{{ $val }}">{{ $txt }}</label>
                                </div>
                            @endforeach
                        </div>

                        <!-- QR/Barcode Content (Conditional) -->
                        <div class="front-item-qr mb-3" id="graph-content-wrapper" style="{{ old('graph_type', $setting->graph_type ?? 'none') === 'none' ? 'display:none' : '' }}">
                            <label for="graph_content" class="form-label">Data to Encode in QR/Barcode <span class="text-danger">*</span></label>
                            <select name="graph_content" id="graph_content" class="form-select">
                                <option value="">-- Select Data --</option>
                                <option value="employee_code" {{ old('graph_content', $setting->graph_content ?? '') === 'employee_code' ? 'selected' : '' }}>Employee Code</option>
                                <option value="custom_text" {{ old('graph_content', $setting->graph_content ?? '') === 'custom_text' ? 'selected' : '' }}>Custom Text (enter below)</option>
                            </select>

                            <input type="text" name="custom_graph_text" class="form-control mt-2"
                                placeholder="Enter custom text or URL"
                                value="{{ old('custom_graph_text', $setting->custom_graph_text ?? '') }}"
                                style="{{ old('graph_content', $setting->graph_content ?? '') === 'custom_text' ? '' : 'display:none' }}"
                                id="custom-graph-text">
                        </div>
                {{--    <div class="front-item-qr mb-4" id="graph-content-wrapper" style="{{ old('graph_type', $setting->graph_type ?? 'none') === 'none' ? 'display:none' : '' }}">--}}
                {{--        <label>Graph Color</label>--}}
                {{--        <input type="color" name="graph_color" id="graph_color" class="form-control form-control-color w-100"--}}
                {{--               value="{{ old('graph_color', $setting->graph_color ?? '#ffffff') }}">--}}
                {{--    </div>--}}

                        <!-- Front Logo -->
                        <div class="front-item-logo">
                            <label class="form-label">Front Logo</label>
                            <input type="file" name="front_logo" class="form-control" accept="image/*">
                            @if(isset($setting) && $setting->front_logo)
                                <div class="mt-2">
                                    <img src="{{ asset('uploads/card/' . $setting->front_logo) }}" class="img-thumbnail" width="180" alt="Current front logo">
                                    <br><small class="text-muted">Upload new to replace</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="back-main mb-4">
            <h5 class=" border-bottom pb-3 mb-4">Back Side</h5>
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="back-title mb-3">
                        <label>Back Title</label>
                        <input type="text" name="back_title" class="form-control"
                            value="{{ old('back_title', $setting->back_title ?? '') }}"
                            placeholder="e.g., Terms & Conditions">
                    </div>

                    <div class="back-desc mb-3">
                        <label>Back Content</label>
                        <textarea name="back_text" rows="6" class="form-control"
                                placeholder="Company policy, emergency contacts, etc.">{{ old('back_text', $setting->back_text ?? '') }}</textarea>
                    </div>

                    <div class="footer-text">
                        <label>Footer Text</label>
                        <input type="text" name="footer_text" class="form-control"
                            value="{{ old('footer_text', $setting->footer_text ?? '') }}"
                            placeholder="www.yourcompany.com | Emergency: 999">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="back-logo mb-3">
                        <label>Back Logo</label>
                        <input type="file" name="back_logo" class="form-control" accept="image/*">
                        @if(isset($setting) && $setting->back_logo)
                            <img src="{{ asset('uploads/card/' . $setting->back_logo) }}" class="img-thumbnail mt-2" width="180">
                        @endif
                    </div>

                    <div class="back-sign">
                        <label>Authorized Signature <small>(Optional)</small></label>
                        <input type="file" name="signature_image" class="form-control" accept="image/*">
                        @if(isset($setting) && $setting->signature_image)
                            <img src="{{ asset('uploads/card/' . $setting->signature_image) }}" class="img-thumbnail mt-2" width="220">
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Extra Fields Order (1–4) -->
<!-- <div class="mb-5">

    <div class="row g-4">
        @php
            $extraOrder = old('extra_fields_order', $setting->extra_fields_order ?? []);
            $availableFields = [
                'employee_code'     => 'Employee Code',
                'department'        => 'Department',
                'joining_date'         => 'Joining Date',
                'phone'             => 'Phone',
                'email'             => 'Email Address',
            ];
        @endphp

        @for($i = 1; $i <= 4; $i++)
            <div class="col-md-6 col-lg-3">
                <div class="card border shadow-sm">
                    <div class="card-header bg-light text-center py-2">
                        <strong>Position {{ $i }}</strong>
                    </div>
                    <div class="card-body p-3">
                        <select name="extra_fields_order[{{ $i }}]" class="form-select field-select">
                            <option value="">Select</option>
                            @foreach($availableFields as $key => $label)
                                <option value="{{ $key }}"
                                    {{ isset($extraOrder[$i]) && $extraOrder[$i] === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        @endfor
    </div>

    <div class="mt-3">
        <div class="alert alert-danger d-none" id="field-error">
            Please select between 1 and 4 <strong>unique</strong> fields.
        </div>
    </div>
</div>

<hr class="my-5">

<h4 class="mb-4 text-primary">Back Side</h4>

<div class="row">
    <div class="col-md-6 mb-4">
        <label>Back Title</label>
        <input type="text" name="back_title" class="form-control"
               value="{{ old('back_title', $setting->back_title ?? '') }}"
               placeholder="e.g., Terms & Conditions">
    </div>

    <div class="col-md-6 mb-4">
        <label>Back Content</label>
        <textarea name="back_text" rows="6" class="form-control"
                  placeholder="Company policy, emergency contacts, etc.">{{ old('back_text', $setting->back_text ?? '') }}</textarea>
    </div>

    <div class="col-md-6 mb-4">
        <label>Back Logo</label>
        <input type="file" name="back_logo" class="form-control" accept="image/*">
        @if(isset($setting) && $setting->back_logo)
            <img src="{{ asset('uploads/card/' . $setting->back_logo) }}" class="img-thumbnail mt-2" width="180">
        @endif
    </div>

    <div class="col-md-6 mb-4">
        <label>Authorized Signature <small>(Optional)</small></label>
        <input type="file" name="signature_image" class="form-control" accept="image/*">
        @if(isset($setting) && $setting->signature_image)
            <img src="{{ asset('uploads/card/' . $setting->signature_image) }}" class="img-thumbnail mt-2" width="220">
        @endif
    </div>

    <div class="col-md-6 mb-4">
        <label>Footer Text</label>
        <input type="text" name="footer_text" class="form-control"
               value="{{ old('footer_text', $setting->footer_text ?? '') }}"
               placeholder="www.yourcompany.com | Emergency: 999">
    </div>

    <div class="col-md-6 mb-4">
        <label>Background Color (Both Sides)</label>
        <input type="color" name="background_color" class="form-control form-control-color w-100"
               value="{{ old('background_color', $setting->background_color ?? '#ffffff') }}">
    </div>
{{--    <div class="col-md-6 mb-4">--}}
{{--        <label>Text Color (Both Sides)</label>--}}
{{--        <input type="color" name="text_color" class="form-control form-control-color w-100"--}}
{{--               value="{{ old('text_color', $setting->text_color ?? '#ffffff') }}">--}}
{{--    </div>--}} -->






