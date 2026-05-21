<div class="modal-header">
    <h3 class="modal-title" id="commentModalLabel">@lang('method.Demand_handling')</h3>
</div>

<form action="{{ route('demand.rejected', ['id' => $transaction->id]) }}" method="POST" id="rejected_demand_form">
    @csrf
    @method('POST')

    <div class="modal-body">
        <div class="form-group">
            <input type="hidden" name="product_id" value="{{ $transaction->product_id }}">

            {!! Form::label('status', __('messages.status') . ':') !!}
            {!! Form::select('status', [
                'reject' => __('messages.reject'),
            ], null, [
                'class' => 'form-control select2',
                'placeholder' => __('messages.please_select'),
                'style' => 'width:100%',
            ]) !!}

<div class="form-group" id="remarks_to_group">
    {!! Form::label('remarks_to', __('messages.remarks_to') . ':') !!}
    {!! Form::select('remarks_to', [$transaction->demand_by => App\User::find($transaction->demand_by)->username ], null, [
        'class' => 'form-control select2',
        'placeholder' => __('messages.please_select'),
        'style' => 'width:100%',
    ]) !!}
</div>

            {!! Form::label('remarks_description', __('lang_v1.remarks') . ':') !!}
            {!! Form::textarea('remarks_description', null, [
                'class' => 'form-control',
                'style' => 'resize:none;',
            ]) !!}
        </div>
    </div>
    <div class="modal-footer">
        <button type="submit" class="btn btn-primary">@lang('Send')</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
    </div>
</form>
