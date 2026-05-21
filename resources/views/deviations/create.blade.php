<div class="modal fade" id="addDeviationModal" tabindex="-1" role="dialog" aria-labelledby="addDeviationModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="deviationForm" action="{{ route('deviations.store') }}" method="post">
                @csrf
                <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">

                <div class="modal-header">
                    <h3 class="modal-title" id="addDeviationModalLabel">
                        <i class="fas fa-exclamation-triangle"></i> @lang('lang_v1.add_deviation')
                    </h3>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="type">@lang('messages.type') <span class="text-danger">*</span></label>
                                <select class="form-control select2" name="type" required>
                                    <option value="">@lang('messages.please_select')</option>
                                    <option value="Technical">@lang('method.technical')</option>
                                    <option value="Process">@lang('method.process')</option>
                                    <option value="Other">@lang('messages.other')</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="device_id_select">@lang('devices.device') <span
                                        class="text-danger">*</span></label>
                                <select class="form-control select2" id="device_id_select" name="device_id" required
                                    disabled></select>
                                <input type="hidden" name="device_id" id="device_id_hidden">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">@lang('method.description')</label>
                        <textarea id="description" class="form-control" name="description" rows="7"></textarea>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
