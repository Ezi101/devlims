<div class="modal-dialog" role="document">
    <div class="modal-content">
        {!! Form::open(['url' => route('store-pre-test-report'), 'method' => 'post', 'id' => 'section_add_form']) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('lang_v1.manage_Ptr_report')</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('sample', __('lang_v1.select_sample') . ':*') !!}
                        {!! Form::select('sample', $product, null, [
                            'class' => 'form-control select2',
                            'required',
                            'style' => 'width:100%',
                            'placeholder' => __('messages.please_select'),
                            'id' => 'sample-select',
                        ]) !!}
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('method_name', __('lang_v1.method') . ':*') !!}
                        {!! Form::text('method_name', null, [
                            'class' => 'form-control',
                            'id' => 'method_name_input',
                            'readonly',
                            'required',
                            'placeholder' => __('lang_v1.method_name'),
                        ]) !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary" id="save-btn" disabled>@lang('messages.print')</button>
            {{-- <input type="submit" id="farward-btn" disabled class="btn btn-primary" name="forward" value="Forward"> --}}
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.select2').select2({
            dropdownParent: $('#sample-select').parent()
        });

        $('#sample-select').on('change', function() {
            var sampleId = $(this).val();
            $.ajax({
                url: '{{ route('fetch-method-and-test-names') }}',
                method: 'GET',
                data: {
                    sample_id: sampleId
                },
                success: function(response) {
                    if (response.success) {
                        // Populate the input fields with the method name
                        $('#method_name_input').val(response.method_name ? response.method_name : 'No');

                        // Enable save and forward buttons if method is selected
                        if (response.method_id) {
                            $('#save-btn').prop('disabled', false);
                            $('#farward-btn').prop('disabled', false);
                        } else {
                            $('#save-btn').prop('disabled', true);
                            $('#farward-btn').prop('disabled', true);
                        }
                    } else {
                        // If the response is not successful, set input fields to 'No'
                        $('#method_name_input').val('No');
                        $('#save-btn').prop('disabled', true);
                        $('#farward-btn').prop('disabled', true);

                        // Display a warning message using swal
                        swal({
                            title: "Action Required",
                            text: response.message,
                            icon: "warning",
                            button: "OK",
                        });
                    }
                },
                error: function() {
                    // Handle errors and disable buttons
                    $('#method_name_input').val('Error fetching data');
                    $('#save-btn').prop('disabled', true);
                    $('#farward-btn').prop('disabled', true);

                    // Display a generic error message using swal
                    swal({
                        title: "Action Required",
                        text: "An error occurred while fetching method name.",
                        icon: "warning",
                        button: "OK",
                    });
                }
            });

        });
    });
</script>
