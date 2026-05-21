<!-- Modal for editing complaints -->
@foreach ($complaints as $index => $complaint)
    <div class="modal fade" id="editComplaintModal{{ $index }}" tabindex="-1" role="dialog"
        aria-labelledby="editComplaintModalLabel{{ $index }}" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('complaints.update', $complaint->id) }}" method="post">
                    @csrf
                    @method('put')
                    <div class="modal-header">
                        <h3 class="modal-title" id="editComplaintModalLabel{{ $index }}">@lang('lang_v1.edit_complaint')</h3>

                    </div>
                    <div class="modal-body">

                        <div class="form-group">
                            <label for="subject">@lang('method.subject'):</label>
                            <input type="text" class="form-control" name="subject" value="{{ $complaint->subject }}"
                                required>
                        </div>
                        <div class="form-group">
                            <label for="description">@lang('method.description'):</label>
                            <textarea class="form-control" name="description" rows="4" required>{{ strip_tags($complaint->description) }}</textarea>
                        </div>
                        <!-- Hidden fields for status and assigned_to -->
                        <input type="hidden" name="status" value="{{ $complaint->status }}">
                        <input type="hidden" name="assigned_to" value="{{ $complaint->assigned_to }}">
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
