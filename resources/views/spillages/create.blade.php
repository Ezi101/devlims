<div class="modal-dialog" role="document">
    <div class="modal-content">

        <form action="{{ route('spillages.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h3 class="modal-title" id="addSpillageModalLabel">@lang('lang_v1.add_spillage_unforeseen_details')</h3>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label for="chemical_id">@lang('method.chemical')</label>
                            <select name="chemical_id" id="chemical_id" class="form-control" required
                                style="width: 100%;">
                                <option value="">@lang('messages.please_select')</option>
                                @foreach ($chemicals as $chemical)
                                    <option value="{{ $chemical->id }}">{{ $chemical->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                {{-- <div class="col-sm-4">
                        <div class="form-group">
                            <label for="standard_id">Standard</label>
                            <select name="standard_id" class="form-control" required>
                                @foreach ($standards as $standard)
                                    <option value="{{ $standard->id }}">{{ $standard->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div> --}}
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="spillage_quantity">@lang('method.quantity')</label>
                            <input type="number" name="spillage_quantity" class="form-control" required placeholder="Enter Quanity...">
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="spillage_date_time">@lang('method.date_time')</label>
                            <input type="datetime-local" name="spillage_date_time" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label for="spillage_remarks">@lang('method.remarks')</label>
                            <textarea name="spillage_remarks" class="form-control" id="remarks"></textarea>
                        </div>
                    </div>
                    <input type="hidden" name="reported_by" value="{{ auth()->user()->id }}">
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    $('#chemical_id').select2({
        dropdownParent: $('#addSpillageModal')
    });
    tinymce.init({
        selector: '#remarks',
        plugins: 'advlist autolink lists charmap print preview hr anchor pagebreak',
        toolbar_mode: 'floating',
    });
   
</script>
