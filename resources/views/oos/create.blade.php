<div class="modal fade" id="addOOSModal" tabindex="-1" role="dialog" aria-labelledby="addOOSModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="addOOSModalLabel">@lang('lang_v1.add_oos')</h3>

            </div>
            <div class="modal-body">
                <form class="mt-10" action="{{ route('oos.store') }}" method="post">
                    @csrf

                    <div class="form-group">
                        <label for="product_name">@lang('method.name')</label>
                        <input type="text" class="form-control" name="product_name" required placeholder="@lang('method.name_of_item')">
                    </div>

                    <div class="form-group">
                        <label for="reason">@lang('method.reason')</label>
                        <textarea class="form-control" name="reason" id="description"></textarea>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

