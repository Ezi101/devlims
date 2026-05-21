@foreach ($oos as $index => $oosItem)
    <div class="modal fade" id="editOosModal{{ $index }}" tabindex="-1" role="dialog"
        aria-labelledby="editOosModalLabel{{ $index }}" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form class="mt-10" action="{{ route('oos.update', $oosItem->id) }}" method="post">
                    @csrf
                    @method('put')
                    <div class="modal-header">
                        <h3 class="modal-title" id="editOosModalLabel{{ $index }}">@lang('lang_v1.edit_oos')</h3>

                    </div>
                    <div class="modal-body">

                        <div class="form-group mt-5">
                            <label for="product_name">@lang('method.name')</label>
                            <input type="text" class="form-control" name="product_name"
                                value="{{ $oosItem->product_name }}" required>
                        </div>

                        <div class="form-group">
                            <label for="reason">@lang('method.reason')</label>
                            <textarea class="form-control" name="reason" rows="4" required>{{ strip_tags($oosItem->reason) }}</textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button style="margin-left: 10px;" type="button" class="btn btn-default pull-right"
                            data-dismiss="modal">@lang('messages.close')</button>
                        <button type="submit" class="btn btn-primary pull-right">@lang('messages.update')</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endforeach
