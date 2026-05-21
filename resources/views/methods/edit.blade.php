@extends('layouts.app')
@section('title', __('lang_v1.edit_method'))

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('lang_v1.edit_method')</h1>
    </section>
    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            <form action="{{ route('methods.update', $method->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="method_name">@lang('method.name')</label>
                    <input type="text" class="form-control" id="method_name" name="method_name"
                        value="{{ $method->method_name }}">
                </div>
                <div class="form-group">
                    <label for="sample_id">@lang('product.product')</label>
                    <select class="form-control select2" id="sample_id" name="sample_id">
                        @foreach ($samples as $sample)
                            <option value="{{ $sample->id }}" {{ $sample->id == $method->sample_id ? 'selected' : '' }}>
                                {{ $sample->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="method_description">@lang('method.description')</label>
                    <textarea class="form-control" id="method_description" name="method_description" rows="4">{{ $method->method_description }}</textarea>
                </div>
                <div class="form-group">
                    <label for="method_files" class="custom-file-upload">@lang('method.upload_files')</label>
                    <input type="file" class="form-control-file" id="method_files" name="method_files[]" multiple>
                    <span></span>

                    @if (!empty($method->files))
                        <p>@lang('messages.already_uploaded_files')</p>
                        @foreach (json_decode($method->files) as $file)
                            <a href="{{ asset('uploads/img/' . $file) }}" target="_blank">{{ $file }}</a><br>
                        @endforeach
                    @endif
                </div>
                <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
                <button type="button" class="btn btn-secondary"
                    onclick="window.location='{{ route('methods.index') }}'">@lang('messages.cancel')</button>
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
            const fileInput = $('#method_files'); // Select using jQuery selector

            const label = fileInput.next(); // Get next sibling using jQuery method

            const originalLabelText = label.text(); // Get text using jQuery method

            fileInput.on('change', function(event) {
                const files = event.target.files;
                let fileNames = '';

                if (files.length > 0) {
                    for (let i = 0; i < files.length; i++) {
                        fileNames += (i > 0 ? ', ' : '') + files[i]
                            .name; // Add comma and space for subsequent files
                    }
                    label.text(fileNames);
                } else {
                    label.text(originalLabelText); // Reset label if no files selected
                }
            });

            const observer = new MutationObserver(function() {
                fileInput.val(''); // Reset value using jQuery method
                label.text(originalLabelText); // Reset text again
            });

            observer.observe(fileInput[0], { // Use raw DOM element for mutation observer
                attributes: true,
                attributeFilter: ['value']
            });
        });



        tinymce.init({
            selector: '#method_description',
            plugins: 'advlist autolink lists charmap print preview hr anchor pagebreak',
            toolbar_mode: 'floating',
        });
    </script>
@endsection
