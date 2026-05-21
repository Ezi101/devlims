<!-- Modal for editing complaints -->
@foreach ($deviations as $index => $deviation)
    <div class="modal fade" id="editDeviationModal{{ $index }}" tabindex="-1" role="dialog"
        aria-labelledby="editDeviationModalLabel{{ $index }}" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form class="mt-10" action="{{ route('deviations.update', $deviation->id) }}" method="post">
                    @csrf
                    @method('put')
                    <div class="modal-header">
                        <h3 class="modal-title" id="editDeviationModalLabel{{ $index }}">@lang('lang_v1.edit_deviation')</h3>

                    </div>
                    <div class="modal-body">

                        <div class="form-group">
                            <label for="type">@lang('messages.type')</label>
                            <select class="form-control" name="type" required>
                                <option value="Technical" @if ($deviation->type === 'Technical') selected @endif>
                                    @lang('method.technical')
                                </option>
                                <option value="Process" @if ($deviation->type === 'Process') selected @endif>
                                    @lang('method.process')
                                </option>
                                <option value="Other" @if ($deviation->type === 'Other') selected @endif>
                                    @lang('messages.other')</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="description">@lang('method.description')</label>
                            <textarea class="form-control" name="description" rows="4" required>{{ strip_tags($deviation->description) }}</textarea>
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
