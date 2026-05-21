<div class="modal fade" id="addDeviationModal" tabindex="-1" role="dialog" aria-labelledby="addDeviationModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="addDeviationModalLabel">@lang('lang_v1.add_deviation')</h3>

            </div>
            <div class="modal-body">
                <form action="{{ route('deviations.store') }}" method="post">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6 cal-lg-6">
                            <label>@lang('product.sample')</label>
                            <input type="hidden" readonly name="sample_id" id="sample_id" class="form-control">
                            <input type="text" readonly id="sample" class="form-control">
                        </div>
                        <div class="col-md-6 cal-lg-6">
                            <label>@lang('method.test_id')</label>
                            <input type="hidden" id="test_id" name="test_id" class="form-control">
                            <input type="text" readonly id="test" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6 cal-lg-6">
                            <label>@lang('batch.batch_no')</label>

                            <select name="batch_no" class="form-control select2" required style="width: 100%;"
                                id="batch">
                                <option value="">@lang('messages.please_select')</option>
                            </select>
                        </div>
                        <div class="col-md-6 cal-lg-6">
                            <label>@lang('method.type')</label>
                            <select class="form-control select2" name="type" required style="width: 100%;">
                                <option value="">@lang('messages.please_select')</option>
                                <option value="Technical">@lang('method.technical')</option>
                                <option value="Process">@lang('method.process')</option>
                                <option value="Other">@lang('messages.other')</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6 cal-lg-6">
                            <label for="description">@lang('devices.device')</label>
                            <input type="hidden" name="equipment" id="equipment_id" class="form-control">
                            <input type="text" readonly id="equipment" class="form-control">
                        </div>
                        <div class="col-md-6 cal-lg-6">
                            <label for="description">@lang('method.lab')</label>
                            <input type="text" readonly id="lab" name="lab" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="description">@lang('method.description')</label>
                        <textarea id="description" class="form-control" name="description"></textarea>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
