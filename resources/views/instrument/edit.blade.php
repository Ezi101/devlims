<div class="modal-dialog" role="document">
    <div class="modal-content">

        {!! Form::open([
            'url' => action([\App\Http\Controllers\InstrumentsController::class, 'update'], [$device->id]),
            'method' => 'PUT',
            'id' => 'device_edit_form',
        ]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h3 class="modal-title">@lang('devices.edit_equipment')</h3>
        </div>

        <div class="modal-body">
            <div class="row">

                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('sr_no', __('method.sr_no') . ':') !!}
                        {!! Form::text('sr_no', $device->sr_no, [
                            'class' => 'form-control',
                            'required' => 'required',
                            'placeholder' => __('method.equipment_sr_no'),
                        ]) !!}
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('manual_id', __('method.manual_id') . ':') !!}
                        {!! Form::text('manual_id', $device->manual_id, [
                            'class' => 'form-control',
                            'required' => 'required',
                            'placeholder' => __('method.equipment_id'),
                        ]) !!}
                    </div>
                </div>

                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('sop', __('method.sop') . ':') !!}
                        {!! Form::text('sop', $device->sop, [
                            'class' => 'form-control',
                            'required' => 'required',
                            'placeholder' => __('method.equipment_id'),
                        ]) !!}
                    </div>
                </div>

                <div class="col-sm-4">

                    <div class="form-group">
                        {!! Form::label('name', __('method.name') . ':') !!}
                        {!! Form::text('name', $device->name, [
                            'class' => 'form-control',
                            'required' => 'required',
                            'placeholder' => __('method.equipment_name_holder'),
                        ]) !!}
                    </div>
                </div>
                <div class="col-sm-4">

                    <div class="form-group">
                        {!! Form::label('model', __('method.model') . ':') !!}
                        {!! Form::text('model', $device->model, [
                            'class' => 'form-control',
                            'required' => 'required',
                            'placeholder' => __('method.equipment_model_holder'),
                        ]) !!}
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('categories', __('method.category') . ':') !!}
                        {!! Form::select(
                            'categories',
                            [
                                'Testing Equipment' => 'Testing Equipment',
                                'Computer Systems' => 'Computer Systems',
                                'Environment Storage' => 'Environment Storage',
                            ],
                            !empty($device->category) ? $device->category : null,
                            [
                                'placeholder' => __('messages.please_select'),
                                'class' => 'form-control select2',
                                'id' => 'categories_field',
                                'style' => 'width:100%;',
                                'required' => 'required',
                            ],
                        ) !!}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-4">

                    <div class="form-group">
                        {!! Form::label('manufacturer', __('method.manufacturer') . ':') !!}
                        {!! Form::text('manufacturer', $device->manufacturer, [
                            'class' => 'form-control',
                            'required' => 'required',
                            'placeholder' => __('method.equipment_manufacturer_holder'),
                        ]) !!}
                    </div>
                </div>
                <div class="col-sm-4">

                    <div class="form-group">
                        {!! Form::label('supplier', __('method.supplier') . ':') !!}
                        {!! Form::text('supplier', $device->supplier, [
                            'class' => 'form-control',
                            'required' => 'required',
                            'placeholder' => __('method.equipment_supplier_holder'),
                        ]) !!}
                    </div>
                </div>
                <div class="col-sm-4">

                    <div class="form-group">
                        {!! Form::label('Lab', __('devices.lab') . ':') !!}

                        {!! Form::select('lab', $lab, !empty($device->lab) ? $device->lab : null, [
                            'placeholder' => __('messages.please_select'),
                            'class' => 'form-control select2',
                            'id' => 'lab_field',
                            'style' => 'width:100%;',
                            'required' => 'required',
                        ]) !!}

                    </div>
                </div>


            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        {!! Form::label('description', __('Description') . ':') !!}
                        {!! Form::textarea('description', strip_tags($device->description), [
                            'class' => 'form-control',
                            'placeholder' => __('Device Description'),
                            'rows' => 4,
                            'style' => 'resize:none;',
                        ]) !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>

        {!! Form::close() !!}

    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
<script>
    $('#lab_field').select2({
        dropdownParent: $('#device_edit_form')
    });
    $('#categories_field').select2({
        dropdownParent: $('#device_edit_form')
    });
</script>
