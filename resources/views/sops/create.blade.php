<div class="modal-dialog" role="document">
    <div class="modal-content">
        <form action="{{ route('sops.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h3 class="modal-title" id="addSopModalLabel">@lang('lang_v1.add_sop')</h3>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="title">@lang('method.title')</label>
                            <input type="text" class="form-control" id="title" name="title"
                                placeholder="@lang('method.sop_title_holder')">
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="category">@lang('method.category')</label>
                            <input type="text" class="form-control" id="category" name="category"
                                placeholder="@lang('method.sop_category_holder')">
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="sop_expiry_date">@lang('method.expiry')</label>
                            <input type="text" class="form-control datepicker" id="sop_expiry_date"
                                name="sop_expiry_date" placeholder="@lang('method.sop_expiry_holder')" autocomplete="off">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="description">@lang('method.description')</label>
                    <textarea id="description" class="form-control" rows="7" name="description"></textarea>
                </div>
                <div class="form-group">
                    <label for="file" class="custom-file-upload">@lang('method.upload_file')</label>
                    <input type="file" class="form-control-file" id="file" name="file">
                    <span>@lang('method.no_file_selected')</span>
                </div>

                <div class="form-group">
                    <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                </div>
            </div>
        </form>
    </div>
    <style>
        .custom-file-upload {
            display: inline-block;
            cursor: pointer;
            padding: 4px 10px;
            /* Reduced padding for less height */
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #e0e0e0;
            /* Lighter background color */
            color: #333;
            /* Text color for better readability */
            font-size: 14px;
            /* Adjust font size if needed */
        }

        .custom-file-upload:hover {
            background-color: #c0c0c0;
            /* Darker shade for hover effect */
        }

        .custom-file-upload:active {
            background-color: #9b9898;
            /* Darker shade for hover effect */
        }

        input[type="file"] {
            display: none;
        }
    </style>
</div>
<script>
    $(document).ready(function() {

        const fileInput = $('#file'); // Select using jQuery selector
        const label = fileInput.next(); // Get next sibling using jQuery method

        const originalLabelText = label.text(); // Get text using jQuery method

        fileInput.on('change', function(event) {
            const fileName = event.target.files.length ? event.target.files[0].name :
                'No file selected';
            label.text(fileName); // Set text using jQuery method
        });

        const observer = new MutationObserver(function() {
            fileInput.val(''); // Reset value using jQuery method
            label.text(originalLabelText); // Reset text again
        });

        observer.observe(fileInput[0], { // Use raw DOM element for mutation observer
            attributes: true,
            attributeFilter: ['value']
        });
        // Set initial text
        label.innerText = 'Upload File';

    });


    $('#sample_id').select2({
        dropdownParent: $('#addSopModal')
    });
    tinymce.init({
        selector: '#description',
        plugins: 'advlist autolink lists charmap print preview hr anchor pagebreak',
        toolbar_mode: 'floating',
    });
    $('.datepicker').datepicker({
        format: 'dd-mm-yyyy',
        startView: 'years',
        minViewMode: 'days',
        autoclose: true
    });
</script>
