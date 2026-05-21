<div class="modal-dialog" role="document" id="mymodal">
    <div class="modal-content">

        {!! Form::open([
            'url' => action([\App\Http\Controllers\ProductController::class, 'copy_associated_test'], $p_id),
            'method' => 'GET',
            'id' => 'product_add_form',
            'class' => 'product_form',
            'files' => true,
        ]) !!}

        <div class="modal-header">
            <h4 class="modal-title" id="copyModalLabel">Copy Data</h4>
        </div>
        <div class="modal-body">
            @csrf
            <div class="form-group">
                {!! Form::label('generic_name', 'Select Generics:') !!}
                {!! Form::select('generic_name[]', $generics, null, [
                    'class' => 'form-control select2',
                    'style' => 'width: 100%',
                    'id' => 'select_generic',
                    'multiple' => 'multiple', // Allow multiple selections
                ]) !!}

            </div>
            {{-- <h5 class="text-center">OR</h5> --}}
            <div class="form-group">
                {!! Form::label('sample_name', 'Select Sample:') !!}
                {!! Form::select('sample_name', $samples, null, [
                    'placeholder' => __('messages.please_select'),
                    'class' => 'form-control select2',
                    'style' => 'width: 100%',
                    'id' => 'sample_select',
                ]) !!}
            </div>
        </div>
        <div class="modal-footer">
            {{-- <button type="button" class="btn btn-xs btn-default" id="resetSelection">Reset</button> --}}
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Copy</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>
{{-- @section('javascript') --}}
<script>
    $(document).ready(function() {
        $('#select_generic').change(function() {
            var generic_ids = $(this).val(); // Get all selected values as an array

            $.ajax({
                url: '/get/samples/by/generic',
                type: 'GET',
                data: {
                    generic: generic_ids // Send the array of selected generic IDs
                },
                success: function(response) {
                    $('#sample_select').empty();
                    // Append new options
                    $.each(response, function(id, name) {
                        $('#sample_select').append('<option value="' + id + '">' +
                            name + '</option>');
                    });
                },
            });
        });
    });
</script>



<script>
    $(document).ready(function() {
        $('.select2').select2({
            dropdownParent: $('#mymodal')

        });
    });
</script>
{{-- @endsection --}}
