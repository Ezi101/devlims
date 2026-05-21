<div class="modal-dialog" role="document">
    <div class="modal-content">
        {!! Form::open(['url' => action([\App\Http\Controllers\BatchController::class, 'store']), 'method' => 'post', 'id' => $quick_add ? 'quick_batch_add_form' : 'batch_add_form' ]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang( 'reagent.lot_no' )</h4>
        </div>

        <div class="modal-body">
            <input type="hidden" name="sample_id" value="{{ $product_id }}">
            <div class="form-group">
                {!! Form::label('code', __( 'reagent.lot_no' ) . ':*') !!}
                {!! Form::text('code', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'reagent.lot_no' ) ]); !!}
            </div>

            <div class="form-group">
                {!! Form::label('mfg_date', __( 'reagent.mfg_date' ) . ':') !!}
                {!! Form::text('mfg_date', null, ['id' => 'mfg_date', 'class' => 'form-control', 'required','readonly','placeholder' => __( 'batch.manufacture_date' )]); !!}
            </div>

            <div class="form-group">
                {!! Form::label('exp_date', __( 'reagent.expiry_date' ) . ':') !!}
                {!! Form::text('exp_date', null, ['id' => 'expiry_date', 'class' => 'form-control', 'required','readonly','placeholder' => __( 'batch.expiration_date' )]); !!}
            </div>

            <div class="form-group">
                {!! Form::label('description', __( 'reagent.short_description' ) . ':') !!}
                {!! Form::text('description', null, ['class' => 'form-control','placeholder' => __( 'reagent.short_description' )]); !!}
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
        </div>

        {!! Form::close() !!}

    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
<script>
    $('#mfg_date').datetimepicker({
        format: moment_date_format + ' ' + moment_time_format
        , ignoreReadonly: true
    , });

    // Expiry DateTime Picker
    $('#expiry_date').datetimepicker({
        format: moment_date_format + ' ' + moment_time_format
        , ignoreReadonly: true
    , });

</script>
