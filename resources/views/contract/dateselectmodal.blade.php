<div class="modal fade" id="quickAddDateRangeModal" tabindex="-1" role="dialog"
    aria-labelledby="quickAddDateRangeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="quickAddDateRangeModalLabel">@lang('product.add_fisc_yr_d')</h4>

            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="quickName">@lang('messages.name')</label>
                    <input type="text" class="form-control" id="quickName" placeholder="Enter Name"
                        autocomplete="off">
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <div class="form-group">
                            <label for="quickStartDate">@lang('method.st_date')</label>
                            <input type="text" class="form-control datepicker" id="quickStartDate"
                                placeholder="@lang('method.select_st_date')" autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group col-md-6">

                        <div class="form-group">
                            <label for="quickEndDate">@lang('method.end_date')</label>
                            <input type="text" class="form-control datepicker" id="quickEndDate"
                                placeholder="@lang('method.select_end_date')" autocomplete="off">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="saveQuickDateRange">@lang('messages.save')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('messages.close')</button>
            </div>
        </div>
    </div>
</div>
