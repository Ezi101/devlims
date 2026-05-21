<div class="modal-dialog" role="document">
    <div class="modal-content">

        {!! Form::open(['url' => action([\App\Http\Controllers\RoleController::class, 'permission_store']),'method' => 'post']) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('role.add_permission')</h4>
        </div>

        <div class="modal-body">
            {{-- <div style="border: 1px solid #d2d6de; padding:7px;"> --}}
                <div class="form-group">
                    {!! Form::label('permission', __('role.per_name') . ':*') !!}
                    {!! Form::text('permission', null, ['class' => 'form-control', 'required', 'placeholder' => __('role.per_name')]) !!}
                </div>
            {{-- </div> --}}
        </div>
    

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>

        {!! Form::close() !!}

    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
