<div class="modal-dialog" role="document">
    <div class="modal-content">

        {!! Form::open([
            'url' => action([\App\Http\Controllers\DeliveryPersonController::class, 'storeQuick']),
            'method' => 'post',
            'id' => 'delivery_person_quick_add_form',
            'enctype' => 'multipart/form-data',
        ]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">@lang('method.add_dp')</h4>
        </div>

        <div class="modal-body">
            <div class="form-group">
                {!! Form::label('name',  __('messages.name') . ':*')!!}
                {!! Form::text('name', null, [
                    'class' => 'form-control',
                    'required',
                    'placeholder' =>  __('method.dp_name_holder'),
                    'style' => 'margin-bottom: 15px;',
                ]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('cnic', __('messages.cnic') . ':*')!!}
                {!! Form::text('cnic', null, [
                    'class' => 'form-control',
                    'required',
                    'placeholder' => __('method.dp_cnic_holder'),
                    'style' => 'margin-bottom: 15px;',
                ]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('phone', __('messages.phone') . ':*') !!}
                {!! Form::text('phone', null, [
                    'class' => 'form-control',
                    'required',
                    'placeholder' => __('method.dp_phone_holder'),
                    'style' => 'margin-bottom: 15px;',
                ]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('picture',  __('messages.picture') . ':*')!!}
                <br>
                <button type="button" class="btn btn-secondary" id="cameraButton" style="margin-bottom: 10px;">
                    <i class="fa fa-camera"></i> @lang('messages.c_picture')
                </button>
                <input type="file" name="picture" id="picture" class="form-control" accept="image/*"
                    capture="camera" style="display: none;">
                <canvas id="canvas"
                    style="display:none; max-width: 40%; margin-bottom: 10px; border-radius:20px;"></canvas>
                <div id="controls" style="display:none; margin-top: 10px;">
                    <button type="button" class="btn btn-primary" id="captureButton"><i
                            class="fas fa-camera"></i></button>
                    <button type="button" class="btn btn-default" id="closeCameraButton"><i
                            class="fas fa-times"></i></button>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" id="dp_qaf_save_button" class="btn btn-primary">@lang('messages.save')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>

        {!! Form::close() !!}
    </div>
</div>

