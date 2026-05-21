<div class="modal-dialog" role="document">
    <div class="modal-content">
        {!! Form::open([
            'url' => action([\App\Http\Controllers\STRController::class, 'show_report']),
            'method' => 'post',
            'id' => 'section_add_form',
        ]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('lang_v1.manage_str_report')</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                {{-- Left side --}}
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('sample', __('lang_v1.select_sample') . ':*') !!}
                        {!! Form::select('sample', $product, !empty($duplicate_product->product) ? $duplicate_product->product : null, [
                            'id' => 'sample',
                            'class' => 'form-control select2',
                            'required',
                            'style' => 'width:100%',
                            'placeholder' => __('messages.please_select'),
                        ]) !!}
                    </div>

                    <div class="form-group">
                        {!! Form::label('batch', __('lang_v1.select_batch') . ':*') !!}
                        {!! Form::select('batch', $product, !empty($duplicate_product->product) ? $duplicate_product->product : null, [
                            'id' => 'batch',
                            'class' => 'form-control select2',
                            'style' => 'width:100%',
                            'disabled',
                            'required',
                            'placeholder' => __('messages.please_select'),
                        ]) !!}
                    </div>
                </div>

                {{-- Right side --}}
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('ptr_no_for_str', __('lang_v1.ptr_no') . ':*') !!}
                        {!! Form::text('ptr_no_for_str', null, [
                            'class' => 'form-control',
                            'readonly',
                            'required',
                            'placeholder' => __('lang_v1.ptr_no'),
                        ]) !!}
                    </div>

                    <div class="form-group">
                        {!! Form::label('ptr_status_for_str', __('lang_v1.ptr_status') . ':*') !!}
                        {!! Form::text('ptr_status_for_str', null, [
                            'class' => 'form-control',
                            'readonly',
                            'required',
                            'placeholder' => __('lang_v1.ptr_status'),
                        ]) !!}
                    </div>
                </div>
            </div>


        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary" id="strSaveBtn">@lang('messages.print')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>

        {!! Form::close() !!}
    </div>
</div>


<div class="modal fade" id="confirmSTRModal" tabindex="-1" role="dialog" aria-labelledby="confirmSTRModalLabel">
    <div class="modal-dialog modal-sm" role="document" style="margin-top:50px;"> 
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close close-confirm" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="confirmSTRModalLabel">Confirmation</h4>
            </div>
            <div class="modal-body text-center">
                <p>The STR record already exists for the selected batch.<br>
                    Would you like to proceed with creating it again?</p>
            </div>
            <div class="modal-footer text-center">
                <button type="button" class="btn btn-secondary close-confirm">@lang('messages.no')</button>
                <button type="button" class="btn btn-primary" id="confirmYes">@lang('messages.yes')</button>
            </div>
        </div>
    </div>
</div>

<style>
    #confirmSTRModal .modal-content {
        border: 2px solid #3498db;
        border-radius: 6px;
    }

    #confirmSTRModal .modal-body p {
        font-size: 16px;
        margin: 0;
    }
</style>

<script>
    // make sure close button only closes confirm modal
    $(document).on('click', '.close-confirm', function() {
        $('#confirmSTRModal').modal('hide');
    });
</script>


<style>
    #confirmSTRModal .modal-content {
        border: 2px solid #3498db;
        border-radius: 6px;
    }

    #confirmSTRModal .modal-body p {
        font-size: 16px;
        margin: 0;
    }
</style>

<script src="{{ asset('modules/project/js/test.js?v=' . $asset_v) }}"></script>
<script>
    $('.select2').each(function() {
        $(this).select2({
            dropdownParent: $(this).parent(),
        });
    });

    let form = $('#section_add_form');
    let saveBtn = $('#strSaveBtn');
    let errorBox = $('#strErrorBox');

    form.submit(function(event) {
        event.preventDefault();
        saveBtn.prop('disabled', true);

        $.ajax({
            url: '{{ action([\App\Http\Controllers\STRController::class, 'checkSTRExists']) }}',
            type: 'GET',
            data: {
                sample_id: $('#sample').val(),
                batch_no: $('#batch').val()
            },
            success: function(response) {
                if (response.error) {

                    saveBtn.prop('disabled', false);
                } else if (response.exists) {
                    $('#confirmSTRModal').modal('show');
                    saveBtn.prop('disabled', false);
                } else {
                    // proceed with actual form submit
                    form.off('submit').submit();
                }
            },
            error: function() {
                errorBox.removeClass('d-none').text("Something went wrong. Please try again.");
                saveBtn.prop('disabled', false);
            }
        });
    });

    $('#confirmYes').click(function() {
        saveBtn.prop('disabled', true);
        form.off('submit').submit();
        $('#confirmSTRModal').modal('hide');
    });
</script>
<script>
    // make sure close button only closes confirm modal
    $(document).on('click', '.close-confirm', function() {
        $('#confirmSTRModal').modal('hide');
    });
</script>
