<div class="theme-color">
    <div class="row">
        <div class="col-lg-6 col-md-6 pe-5">
            <div class="theme-color-list">
                <h5 class="border-bottom pb-3 mb-3">Light Theme</h5>
                <div class="theme-primary-color mb-3">
                    <label for="primary_color" class="form-label d-block">{{ __('index.primary_color') }}</label>
                    <input type="color" class="form-control form-control-color @error('primary_color') is-invalid @enderror"
                        name="primary_color" id="primary_color"
                        value="{{ isset($themeDetail) ? $themeDetail->primary_color : old('primary_color', '#000000') }}"
                        title="Choose your color" />
                    @error('primary_color')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="theme-hover-color mb-3">
                    <label for="hover_color" class="form-label d-block">{{ __('index.hover_color') }}</label>
                    <input type="color" class="form-control form-control-color @error('hover_color') is-invalid @enderror"
                        name="hover_color" id="hover_color"
                        value="{{ isset($themeDetail) ? $themeDetail->hover_color : old('hover_color', '#000000') }}"
                        title="Choose your color" />
                    @error('hover_color')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 ps-5">
            <div class="theme-color-list">
                <h5 class="border-bottom pb-3 mb-3">Dark Theme</h5>
                <div class="theme-primary-color mb-3">
                    <label for="dark_primary_color" class="form-label d-block">{{ __('index.dark_primary_color') }}</label>
                    <input type="color" class="form-control form-control-color @error('dark_primary_color') is-invalid @enderror"
                        name="dark_primary_color" id="dark_primary_color"
                        value="{{ isset($themeDetail) ? $themeDetail->dark_primary_color : old('dark_primary_color', '#000000') }}"
                        title="Choose your color" />
                    @error('dark_primary_color')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="theme-primary-color mb-3">
                    <label for="dark_hover_color" class="form-label d-block">{{ __('index.dark_hover_color') }}</label>
                    <input type="color" class="form-control form-control-color @error('dark_hover_color') is-invalid @enderror"
                        name="dark_hover_color" id="dark_hover_color"
                        value="{{ isset($themeDetail) ? $themeDetail->dark_hover_color : old('dark_hover_color', '#000000') }}"
                        title="Choose your color" />
                    @error('dark_hover_color')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div> 
        <div class="col-lg-12">
            <div class="border-top pt-4 mb-4">
                @can('theme_setting')
                    <button type="submit" class="btn btn-primary">
                        <i class="link-icon" data-feather="plus"></i>
                        {{ $themeDetail ? __('index.update') : __('index.save') }}
                    </button>
                @endcan
            </div>
        </div>   
    </div>
</div>

