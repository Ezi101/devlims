@foreach ($feedbacks as $index => $feedback)
    <div class="modal fade" id="editFeedbackModal{{ $index }}" tabindex="-1" role="dialog"
        aria-labelledby="editFeedbackModalLabel{{ $index }}" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form class="mt-10" action="{{ route('feedbacks.update', $feedback->id) }}" method="post">
                    @csrf
                    @method('put')
                    <div class="modal-header">
                        <h3 class="modal-title" id="editFeedbackModalLabel{{ $index }}">@lang('lang_v1.edit_feedback')</h3>

                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="title">@lang('method.subject')</label>
                            <input type="text" class="form-control" name="title" value="{{ $feedback->title }}"
                                required>
                        </div>



                        <div class="form-group">
                            <label for="rating">@lang('messages.rating')</label>
                            <select class="form-control select2" name="rating" style="width: 100%;">
                                @for ($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}" {{ $feedback->rating == $i ? 'selected' : '' }}>
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
                            <textarea class="form-control" name="description" rows="4">{{ strip_tags($feedback->description) }}</textarea>
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
