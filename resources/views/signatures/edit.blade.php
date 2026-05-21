@foreach ($signatures as $index => $signature)
    <div class="modal fade" id="editSignatureModal{{ $index }}" tabindex="-1" role="dialog"
        aria-labelledby="editSignatureModalLabel{{ $index }}" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('signatures.update', $signature->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h3 class="modal-title" id="editSignatureModalLabel{{ $index }}">@lang('lang_v1.edit_signature')</h3>
                    </div>
                    <div class="modal-body">

                        <div class="form-group mt-10">
                            <label for="name">@lang('method.name')</label>
                            <input type="text" name="name" value="{{ $signature->name }}" class="form-control"
                                readonly>
                        </div>

                        <div class="form-group" hidden>
                            <label for="employee_id">@lang('method.emp_id')</label>
                            <input type="text" name="employee_id" value="{{ $signature->employee_id }}"
                                class="form-control" readonly>
                        </div>

                        <div class="form-group">
                            <label for="designation">@lang('method.role')</label>
                            <input type="text" name="designation" value="{{ $signature->designation }}"
                                class="form-control" readonly>
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
