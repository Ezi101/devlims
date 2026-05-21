<div class="modal-dialog" role="document">
    <div class="modal-content col-sm-12">

        {!! Form::open([
            'url' => action([\App\Http\Controllers\InstrumentsController::class, 'store']),
            'method' => 'post',
            'id' => 'devices_add_modal',
        ]) !!}

        <div class="modal-header">
            <h4 class="modal-title">@lang('devices.add_equipment')</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('sr_no', __('method.sr_no') . ':') !!}
                        {!! Form::text('sr_no', null, [
                            'class' => 'form-control',
                            'required' => 'required',
                            'placeholder' => __('method.equipment_sr_no'),
                        ]) !!}
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('manual_id', __('method.manual_id') . ':') !!}
                        {!! Form::text('manual_id', null, [
                            'class' => 'form-control',
                            'required' => 'required',
                            'placeholder' => __('method.manual_id'),
                        ]) !!}
                    </div>
                </div>

                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('manual_id', __('method.sop') . ':') !!}
                        {!! Form::text('sop', null, [
                            'class' => 'form-control',
                            'required' => 'required',
                            'placeholder' => __('method.sop_number'),
                        ]) !!}
                    </div>
                </div>

                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('name', __('method.name') . ':') !!}
                        {!! Form::text('name', null, [
                            'class' => 'form-control',
                            'required' => 'required',
                            'placeholder' => __('method.equipment_name_holder'),
                        ]) !!}
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('model', __('method.model') . ':') !!}
                        {!! Form::text('model', null, [
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
                            null,
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
                        {!! Form::text('manufacturer', null, [
                            'class' => 'form-control',
                            'required' => 'required',
                            'placeholder' => __('method.equipment_manufacturer_holder'),
                        ]) !!}
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('supplier', __('method.supplier') . ':') !!}
                        {!! Form::text('supplier', null, [
                            'class' => 'form-control',
                            'required' => 'required',
                            'placeholder' => __('method.equipment_supplier_holder'),
                        ]) !!}
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('lab', __('devices.lab') . ':') !!}
                        {!! Form::select('lab', $lab, null, [
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
                        {!! Form::label('description', __('method.description') . ':') !!}
                        {!! Form::textarea('description', null, [
                            'class' => 'form-control',
                            'placeholder' => __('method.equipment_desc_holder'),
                            'id' => 'description',
                            'rows' => 2,
                        ]) !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>

        {!! Form::close() !!}

    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<script>
    $('#lab_field').select2({
        dropdownParent: $('#devices_add_modal')
    });
    $('#categories_field').select2({
        dropdownParent: $('#devices_add_modal')
    });

    tinymce.init({
        selector: '#description',
        plugins: 'advlist autolink lists charmap print preview hr anchor pagebreak',
        toolbar_mode: 'floating',
    });
</script>
