@extends('layouts.app')
@section('title', __('lang_v1.edit_sop'))

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('lang_v1.edit_sop')
        </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            <form action="{{ route('sops.update', $sop->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group mt-10">
                            <label for="title">@lang('method.title')</label>
                            <input type="text" class="form-control" id="title" name="title" value="{{ $sop->title }}">
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group mt-10">
                            <label for="category">@lang('method.category')</label>
                            <input type="text" class="form-control" id="category" name="category"
                                value="{{ $sop->category }}">
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group mt-10">
                            <label for="sop_expiry_date">@lang('method.expiry')</label>
                            <input type="text" class="form-control datepicker" id="sop_expiry_date" name="sop_expiry_date"
                                value="{{ $sop->sop_expiry_date }}">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="reference_code">@lang('method.reference_no')</label>
                    <input type="text" class="form-control" id="reference_code" name="reference_code"
                        value="{{ $sop->reference_code }}">
                </div>
                <div class="form-group">
                    <label for="description">@lang('method.description')</label>
                    <textarea class="form-control" id="description" rows="7" name="description">{{ $sop->description }}</textarea>
                </div>
                <div class="row">

                    <div class="form-group col-sm-4">
                        <label for="file" class="custom-file-upload">@lang('method.upload_file')</label>
                        <input type="file" class="form-control-file" id="file" name="file">
                        <span></span>
                        @if ($sop->file)
                            <br>
                            <span>

                                <a href="{{ asset('uploads/' . $sop->file) }}" target="_blank">@lang('messages.already_uploaded_files')</a>

                            </span>
                            <br>
                        @endif
                    </div>
                </div>
                <div class="form-group" hidden>
                    <label for="user_id">@lang('method.user_id')</label>
                    <input type="text" class="form-control" id="user_id" name="user_id" value="{{ $sop->user_id }}">
                </div>
                <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
                <button type="button" class="btn btn-secondary"
                    onclick="window.location='{{ route('sops.index') }}'">@lang('messages.cancel')</button>
            </form>
        @endcomponent
    </section>
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
@endsection

@section('javascript')
    <script>
        $(document).ready(function() {

            const fileInput = $('#file'); // Select using jQuery selector
            const label = fileInput.next(); // Get next sibling using jQuery method

            const originalLabelText = label.text(); // Get text using jQuery method

            fileInput.on('change', function(event) {
                const fileName = event.target.files.length ? event.target.files[0].name :
                    'No file selected';
                label.text(fileName); // Set text using jQuery method
                console.log('dg');
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
@endsection
