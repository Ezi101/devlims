{!! Form::open([
    'url' => action([\App\Http\Controllers\GenericNameController::class, 'store']),
    'method' => 'post',
    'id' => 'generic_name_form',
    'class' => 'generic_form ',
]) !!}
<div class="modal-dialog">

    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="genericNameModalLabel">@lang('product.add_generic_name')</h3>

        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    {!! Form::label('name', __('product.generic_name') . ':*') !!}
                    {!! Form::text('name', null, [
                        'class' => 'form-control',
                        'required' => 'required',
                        'placeholder' => __('product.generic_name'),
                    ]) !!}
                </div>
            </div><br>
            <div class="row">

                <div class="col-md-12">
                    {!! Form::label('description', __('batch.short_description') . ':') !!}
                    {!! Form::text('description', null, [
                        'class' => 'form-control',
                        'placeholder' => __('batch.short_description'),
                        'required' => 'required',
                    ]) !!}
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
        </div>


    </div><!-- /.modal-content -->
</div><!-- /.modal-content -->
