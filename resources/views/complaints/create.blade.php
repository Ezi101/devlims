<div class="modal-dialog" role="document">
    <div class="modal-content">
        <form class="mt-10" action="{{ route('complaints.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h3 class="modal-title" id="create_complaint_modal">@lang('lang_v1.add_complaint')</h3>
            </div>
            <div class="modal-body">

                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <input type="hidden" name="status" value="false">
                <input type="hidden" name="assigned_to" value="admin">
                <div class="form-group">
                    <label for="subject">@lang('method.subject')</label>
                    <input type="text" class="form-control" name="subject" required placeholder="@lang('method.complaint_subject')">
                </div>
                <div class="form-group">
                    <label for="description">@lang('method.description')</label>
                    <textarea class="form-control" name="description" id="description" placeholder="@lang('method.complaint_description')"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
            </div>

        </form>
    </div>
</div>
<script>
    tinymce.init({
        selector: '#description',
        plugins: 'advlist autolink lists  charmap print preview hr anchor pagebreak',
        toolbar_mode: 'floating',
    });
</script>
