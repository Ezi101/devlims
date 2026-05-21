<!-- Add Feedback Modal -->
<div class="modal fade" id="editAnnouncementModal" tabindex="-1" role="dialog" aria-labelledby="addFeedbackModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="addFeedbackModalLabel">@lang('lang_v1.add_feedback')</h3>

            </div>
            <form action="{{ route('announcement.update') }}" method="post">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="announcement_id" id="edit_announcement_id">
                    <div class="form-group">
                        <label for="date" class="form-label">Date</label>
                        <input type="datetime-local" name="date" id="announcement_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="announcement" class="form-label">Announcement</label>
                        <input type="text" name="announcement" id="edit_announcement" class="form-control"
                            placeholder="Enter Announcement" required>
                    </div>
                </div>
                <br>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                </div>
            </form>
        </div>
    </div>
</div>
