<div class="modal fade" id="testAddModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    @lang('Add Associated Test')
                </h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    {!! Form::label('name', __('method.test_name') . ':*') !!}
                    {!! Form::text('name', null, [
                        'class' => 'form-control name',
                        'required',
                        'placeholder' => __('method.test_group_name'),
                    ]) !!}
                </div>

                <div class="form-group">
                    {!! Form::label('description', __('batch.short_description') . ':') !!}
                    {!! Form::text('description', null, ['class' => 'form-control description', 'placeholder' => __('batch.short_description')]) !!}
                </div>

            </div>
            <br>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm ladda-button saveAssociatedTest" data-style="expand-right"
                    data-dismiss="modal">
                    <span class="ladda-label">@lang('messages.save')</span>
                </button>

                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">
                    @lang('messages.close')
                </button>
            </div>
        </div>
    </div>
</div>
