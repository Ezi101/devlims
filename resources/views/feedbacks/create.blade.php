<!-- Add Feedback Modal -->
<div class="modal fade" id="addFeedbackModal" tabindex="-1" role="dialog" aria-labelledby="addFeedbackModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="addFeedbackModalLabel">@lang('lang_v1.add_feedback')</h3>

            </div>
            <form action="{{ route('feedbacks.store') }}" method="post">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                    <div class="form-group">
                        <label for="title">@lang('method.subject')</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="rating">@lang('method.rating')</label>
                        <select class="form-control select2" name="rating" style="width: 100%;">
                            @for ($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}">
                                    @for ($j = 1; $j <= $i; $j++)
                                        &#9733;
                                    @endfor
                                    @for ($j = $i + 1; $j <= 5; $j++)
                                        &#9734;
                                    @endfor
                                </option>
                            @endfor
                        </select>
                    </div>


                    <div class="form-group">
                        <label for="description">@lang('method.description')</label>
                        <textarea class="form-control" name="description" id="description"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                </div>
            </form>
        </div>
    </div>
</div>
