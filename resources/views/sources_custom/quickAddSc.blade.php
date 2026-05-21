<div class="modal-dialog" role="document">
    <div class="modal-content">

        {!! Form::open([
            'url' => action([\App\Http\Controllers\SourceCustomerController::class, 'storeQuick']),
            'method' => 'post',
            'id' => 'source_customer_quick_add_form',
            'enctype' => 'multipart/form-data',
        ]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">@lang('method.add_source')</h4>
        </div>

        <div class="modal-body">
            <div class="form-group">
                {!! Form::label('name', __('method.name')) !!}
                {!! Form::text('name', null, [
                    'class' => 'form-control',
                    'required',
                    'placeholder' => __('method.enter_source_name_holder'),
                    'style' => 'margin-bottom: 15px;',
                ]) !!}
            </div>

            {{-- <div class="form-group">
                {!! Form::label('cnic', 'CNIC:*') !!}
                {!! Form::text('cnic', null, [
                    'class' => 'form-control',
                    'required',
                    'placeholder' => 'Enter CNIC',
                    'style' => 'margin-bottom: 15px;',
                ]) !!}
            </div> --}}

            <div class="form-group">
                {!! Form::label('phone', __('method.phone')) !!}
                {!! Form::text('phone', null, [
                    'class' => 'form-control',
                    'required',
                    'placeholder' => __('method.enter_phone_holder'),
                    'style' => 'margin-bottom: 15px;',
                ]) !!}
            </div>


        </div>

        <div class="modal-footer">
            <button type="button" id="sc_qaf_save_button" class="btn btn-primary">@lang('messages.save')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>

        {!! Form::close() !!}
    </div>
</div>
