
<div class="modal fade" id="assetDetail" tabindex="-1" aria-labelledby="assetDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-sm">
            <div class="modal-header text-center">
                <h5 class="modal-title assetTitle" id="assetDetailLabel"></h5>
            </div>
            <div class="modal-body p-4">
                <table class="table table-borderless table-hover">
                    <tbody>
                        <tr>
                            <th scope="row" class="text-muted w-30">{{ __('index.name') }}</th>
                            <td class="name fw-medium"></td>
                        </tr>
                        <tr>
                            <th scope="row" class="text-muted w-30">{{ __('index.type') }}</th>
                            <td class="type fw-medium"></td>
                        </tr>

                        <tr>
                            <th scope="row" class="text-muted w-30">{{ __('index.asset_code') }}</th>
                            <td class="asset_code fw-medium"></td>
                        </tr>

                        <tr>
                            <th scope="row" class="text-muted w-30">{{ __('index.asset_serial_number') }}</th>
                            <td class="asset_serial_no fw-medium"></td>
                        </tr>

                        <tr>
                            <th scope="row" class="text-muted w-30">{{ __('index.is_working') }}</th>
                            <td class="is_working fw-medium"></td>
                        </tr>

                        <tr>
                            <th scope="row" class="text-muted w-30">{{ __('index.purchased_date') }}</th>
                            <td class="purchased_date"></td>
                        </tr>
                        <tr>
                            <th scope="row" class="text-muted w-30">{{ __('index.is_available_for_employee') }}</th>
                            <td class="is_available"></td>
                        </tr>
                        <tr>
                            <th scope="row" class="text-muted w-30">{{ __('index.description') }}</th>
                            <td class="note"></td>
                        </tr>
                        <tr>
                            <th scope="row" class="text-muted w-30">{{ __('index.asset_image') }}</th>
                            <td><img class="image" src="" alt="alt" style="object-fit: contain"> </td>
                        </tr>
                        <tr>
                            <th scope="row" class="text-muted w-30">{{ __('index.used_for') }}</th>
                            <td class="used_for"></td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

