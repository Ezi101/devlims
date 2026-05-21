<div class="modal fade" id="addDeviceModal" tabindex="-1" role="dialog" aria-labelledby="addDeviceModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="addDeviceModalLabel">@lang('lang_v1.add_calibration')</h3>

            </div>
            <form action="{{ route('callibration.add') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <input type="hidden" name="new_device" id="new_device">
                        {{-- <label for="new_device">{{ __('devices.device') }}</label>
                        <select name="new_device" id="new_device" class="form-control select2" style="width: 100%;">
                            <option value="" disabled selected>@lang('messages.please_select')</option>
                            @foreach ($devices as $device)
                                <option value="{{ $device->id }}" data-id="{{ $device->id }}"
                                    data-model="{{ $device->model }}"
                                    data-lab="{{ str_replace('#15','',$device->lab) }}">
                                    {{ $device->name }} ({{ $device->id }})
                                </option>
                            @endforeach
                        </select> --}}
                    </div>

                    <div class="form-group">
                        <label for="device_id">{{ __('devices.information') }}</label>
                        <div class="information" style="border: 1px solid rgb(213, 207, 207); ">
                            <input type="text" style="border: none;background:none" id="device_id"
                                class="form-control" readonly>
                            <input type="text" style="border: none;background:none" id="modal"
                                class="form-control" readonly>
                            <input type="text" style="border: none;background:none" id="lab"
                                class="form-control" readonly>
                        </div>
                    </div>
                    {{-- <div class="form-group">
                        <label for="device_id">{{__("devices.information")}}</label>
                        <input type="text" name="device_id" id="device_id" class="form-control" readonly>
                    </div> --}}
                    {{-- <div class="form-group">
                        <label for="device_model">Model:</label>
                        <input type="hidden" name="device_model" id="device_model" class="form-control" readonly>
                    </div> --}}
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                </div>
            </form>
        </div>
    </div>
</div>
