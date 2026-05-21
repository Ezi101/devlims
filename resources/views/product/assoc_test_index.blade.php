@extends('layouts.app')
@section('title', __('lang_v1.associated_test'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('lang_v1.associated_test')
            <small>@lang('lang_v1.manage_test')</small>
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
                <div class="col-md-12">
                    <!-- Custom Tabs -->
                    <div class="nav-tabs-custom">
                        <div class="tab-content">
                            <div class="row">
                                <div class="col-md-9">
                                    <div style="display:flex;flex-direction:row;align-items:baseline;margin-top: -20px;">

                                        <h3 style="margin-right: 5px;">{{ $product->name }}</h3>
                                        <span style="font-weight:100;">{{ $product->sku }}</span>
                                        <input type="hidden" name="sample_id" id="sample_id" value="{{ $product->id }}">
                                    </div>

                                </div>
                                <div class="col-md-3">
                                    <div class="tab-pane active" id="product_list_tab">
                                        @can('Sample Tests.associated_test.create')
                                            <a class="btn btn-primary pull-right"
                                                href="{{ action([\App\Http\Controllers\ProductController::class, 'create_associated_test'], [$product->id]) }}">
                                                <i class="fa fa-plus"></i> @lang('messages.add')</a>

                                            <a class="btn btn-secondary pull-right btn-modal" style="margin-right: 5px;"
                                                data-href="{{ route('copytests', ['id' => $product->id]) }}"
                                                data-container=".copy_test">
                                                <i class="fa fa-copy"></i> @lang('messages.copy')
                                            </a>


                                            {{-- <a class="btn btn-info pull-right" data-toggle="modal" data-target="#copyModal">
                                                <i class="fa fa-copy"></i> Copy</a> --}}
                                            <br><br>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12">
                                <table class="table dataTable table-striped ajax_view hide-footer">

                                    <thead>
                                        <tr>
                                            <th style="width: 20%">{{ __('method.test_name') }}</th>
                                            <th style="width: 20%">{{ __('method.sub_test') }}</th>
                                            <th style="width: 30%">{{ __('lang_v1.t_spec') }}</th>
                                            <th style="width: 20%" class="no-print">{{ __('method.test_labs') }}</th>
                                            @can('others.change_test_status')
                                                <th style="width: 5%">@lang('method.status')</th>
                                            @endcan
                                            <th style="5%">{{ __('method.action') }}</th>
                                            <!-- Add a column for toggle button -->

                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($ass_test as $t)
                                            @php
                                                // Fetch data from SampleAndTests with relationships
                                                $data = App\SampleAndTests::with(
                                                    'samples',
                                                    'subTest',
                                                    'testmethod',
                                                    'samplereading',
                                                    'groups',
                                                    'samplereading.groups',
                                                )
                                                    ->where('sample_id', $t->sample_id)
                                                    ->where('test_id', $t->test_id)
                                                    ->get();
                                            @endphp
                                            @foreach ($data as $d)
                                                @php
                                                    $ptrCheck = App\PTR::where('sample_id', $t->sample_id)
                                                        ->where('test_id', $t->test_id)
                                                        ->where('test_specifications', $d->test_specifications)
                                                        ->where('created_at', '>', $d->created_at)
                                                        ->first();
                                                @endphp
                                                <tr>
                                                    @if ($loop->iteration == 1)
                                                        <td rowspan="{{ count($data) }}"
                                                            style="width:20%;vertical-align:middle;text-align:left">
                                                            {{ optional($d->testmethod)->name }}
                                                        </td>
                                                    @endif

                                                    <td style="width:20%">
                                                        {{ $d->subTest ? $d->subTest->name : '--' }}
                                                    </td>
                                                    <td style="width:30%">
                                                        {{ $d->test_specifications }}
                                                    </td>

                                                    <td style="width:20%">
                                                        {{ $d->lab ?? '-' }}
                                                    </td>

                                                    @can('others.change_test_status')
                                                        <td style="width:10%">
                                                            <!-- Custom Toggle Switch -->
                                                            <label class="switch">
                                                                <input type="checkbox" class="toggle-status"
                                                                    data-id="{{ $d->id }}"
                                                                    {{ $d->active_status == 'active' ? 'checked' : '' }}>
                                                                <span class="slider round"></span>
                                                            </label>
                                                        </td>
                                                    @endcan

                                                    @if ($ptrCheck == null || ($ptrCheck->Ptr_status == 'inactive' || $ptrCheck->status == 'rejected'))
                                                        <td style="5%">
                                                            <a type="button" data-edit_test_id="{{ $d->id }}"
                                                                data-test_id="{{ $d->test_id }}"
                                                                data-sub_test_id="{{ $d->sub_test_id }}"
                                                                data-lab="{{ $d->lab }}"
                                                                data-test_specification="{{ $d->test_specifications }}"
                                                                data-toggle="modal" data-target="#exampleModal"
                                                                class="btn btn-sm btn-primary editTest">
                                                                <i class="fa fa-edit"></i>
                                                            </a>
                                                        </td>
                                                    @else
                                                        <td style="5%">
                                                            <a type="button" disabled class="btn btn-sm btn-primary">
                                                                <i class="fa fa-edit"></i>
                                                            </a>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        @endforeach

                                        <style>
                                            /* The switch - the container */
                                            .switch {
                                                position: relative;
                                                display: inline-block;
                                                width: 40px;
                                                /* Reduced size */
                                                height: 20px;
                                                /* Reduced size */
                                            }

                                            /* Hide default HTML checkbox */
                                            .switch input {
                                                opacity: 0;
                                                width: 0;
                                                height: 0;
                                            }

                                            /* The slider */
                                            .slider {
                                                position: absolute;
                                                cursor: pointer;
                                                top: 0;
                                                left: 0;
                                                right: 0;
                                                bottom: 0;
                                                background-color: #ccc;
                                                transition: 0.4s;
                                                border-radius: 20px;
                                                /* Adjust to match the new height */
                                            }

                                            .slider:before {
                                                position: absolute;
                                                content: "";
                                                height: 14px;
                                                /* Reduced size */
                                                width: 14px;
                                                /* Reduced size */
                                                left: 3px;
                                                bottom: 3px;
                                                background-color: white;
                                                transition: 0.4s;
                                                border-radius: 50%;
                                            }

                                            /* When the checkbox is checked */
                                            input:checked+.slider {
                                                background-color: #28a745;
                                            }

                                            input:checked+.slider:before {
                                                transform: translateX(18px);
                                                /* Adjusted to match the new width */
                                            }

                                            /* Rounded slider */
                                            .slider.round {
                                                border-radius: 20px;
                                            }

                                            .slider.round:before {
                                                border-radius: 50%;
                                            }
                                        </style>
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>
                </div>


            </div>
            <div class="row">

                <div class="text-center">
                    @can('methods.create')
                        <a href="{{ route('methods.index') }}" class="btn btn-default"><i
                                class="fas fa-link"></i>{{ __(' Method') }}
                        </a>
                    @endcan
                    @can('ptr.create')
                        <button type="button" class="btn btn-success check-method-btn">
                            <i class="fas fa-plus"></i>{{ __(' PTR') }}
                        </button>
                    @endcan

                </div>
            </div>
            <!-- Modal -->
            <div class="modal fade" id="methodModal" tabindex="-1" role="dialog" aria-labelledby="methodModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <form action="{{ route('link-method', ['id' => $product->id]) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="modal-header">
                                <h3 class="modal-title" id="addMethodModalLabel">Attach Method</h3>
                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <!-- Existing Method Dropdown -->
                                <div class="form-group">
                                    <label for="existing_method">Select Existing Method (Optional)</label>
                                    <select class="form-control select2" id="existing_method" name="existing_method_id">
                                        <option value="">Please Select</option>
                                    </select>
                                </div>

                                <!-- New Method Form -->
                                <div id="newMethodForm">
                                    <div class="form-group">
                                        <label for="method_name">@lang('messages.name')</label>
                                        <input type="text" class="form-control new-method-input" id="method_name"
                                            name="method_name" placeholder="@lang('method.method_name_holder')" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="method_description">@lang('method.description')</label>
                                        <textarea class="form-control new-method-input" name="method_description" rows="4"
                                            placeholder="@lang('method.method_description_holder')"></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label class="custom-file-upload" for="method_files">@lang('method.upload_files')</label>
                                        <input type="file" class="form-control-file new-method-input" id="method_files"
                                            name="method_files[]" multiple required>
                                        <span>@lang('method.no_file_selected')</span>
                                    </div> <!-- Camera Capture -->
                                    <div class="form-group">
                                        <button type="button" class="btn btn-secondary" id="cameraButton"
                                            style="margin-bottom: 10px;">
                                            <i class="fa fa-camera"></i> @lang('messages.c_picture')
                                        </button>
                                        <input type="file" name="picture" id="picture" class="form-control"
                                            accept="image/*" capture="camera" style="display: none;">
                                        <canvas id="canvas"
                                            style="display:none; max-width: 40%; margin-bottom: 10px; border-radius:10px;"></canvas>
                                        <div id="controls" style="display:none; margin-top: 10px;">
                                            <button type="button" class="btn btn-primary" id="captureButton">
                                                <i class="fas fa-camera"></i>
                                            </button>
                                            <button type="button" class="btn btn-default" id="closeCameraButton">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>


                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                                <button type="button" class="btn btn-default"
                                    data-dismiss="modal">@lang('messages.close')</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>


            <div class="modal fade copy_test" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
        @endcomponent

        @include('product.edit_associate_test')
    </section>

    <!-- /.content -->
    <style>
        /* Modal Header Customization */
        #methodModal .modal-header {
            background-color: #f8f9fa;
            /* Light gray */
            color: #343a40;
            /* Dark text */
            border-top-left-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
            border-bottom: 1px solid #dee2e6;
            /* Subtle border */
        }

        #methodModal .modal-header .close {
            color: #343a40;
            /* Dark close button */
            opacity: 1;
        }

        /* Modal Content Customization */
        #methodModal .modal-content {
            border-radius: 0.75rem;
            /* Rounded corners */
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            /* Soft shadow for depth */
            background-color: #fff;
            /* White background */
        }

        /* Custom Input Styles */
        #methodModal input.form-control {
            border: 1px solid #ced4da;
            padding: 0.75rem;
            border-radius: 0.5rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        #methodModal input.form-control:focus {
            border-color: #adb5bd;
            /* Slightly darker gray on focus */
            box-shadow: 0 0 8px rgba(173, 181, 189, 0.3);
            /* Subtle glow on focus */
        }

        /* Buttons Customization */
        #methodModal .modal-footer .btn-primary {
            background-color: #1572E8;
            /* Muted gray for a soft look */
            border-color: #1572E8;
            border-radius: 0.5rem;
            padding: 0.5rem 1.5rem;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        #methodModal .modal-footer .btn-primary:hover {
            background-color: #1060c9;
            /* Darker gray on hover */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            /* Add shadow on hover */
        }

        #methodModal .modal-footer .btn-outline-secondary {
            border-radius: 0.5rem;
            padding: 0.5rem 1.5rem;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        #methodModal .modal-footer .btn-outline-secondary:hover {
            background-color: #e9ecef;
            border-color: #6c757d;
            color: #6c757d;
            /* Muted color on hover */
        }

        /* Modal Size and Spacing Adjustments */
        #methodModal .modal-dialog {
            max-width: 500px;
            /* Restrict the width for a more compact look */
        }

        #methodModal .modal-body {
            padding: 1.5rem;
        }

        /* Optional: Add light background to the form group */
        #methodModal .modal-body .form-group {
            background-color: #f8f9fa;
            /* Light background for input area */
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        /* Typography tweaks for form labels */
        #methodModal .modal-body label {
            font-weight: 600;
            color: #495057;
        }

        .buttons-csv::before,
        .buttons-excel::before {
            content: "\f1c3";
        }

        .buttons-print::before {
            content: "\f02f";
        }

        .buttons-pdf::before {
            content: "\f1c1";
        }

        .buttons-colvis::before {
            content: "\f065";
        }

        .buttons-csv::before,
        .buttons-excel::before,
        .buttons-print::before,
        .buttons-pdf::before,
        .buttons-colvis::before {
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            margin-right: 5px;
            color: grey;
        }

        .buttons-csv,
        .buttons-excel,
        .buttons-print,
        .buttons-pdf,
        .buttons-colvis {
            font-size: 12px;
            padding: 5px 8px;
        }

        .table>tbody>tr>td,
        .table>tbody>tr>th,
        .table>tfoot>tr>td,
        .table>tfoot>tr>th,
        .table>thead>tr>td,
        .table>thead>tr>th {
            padding: 4px;
            line-height: 1.32857143;
            border-top: 1px solid #ddd;
        }

        @media print {

            .page-break {
                page-break-before: always;
            }

            @page {
                margin-top: 20px;
                margin-bottom: 30px;
            }

        }

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
            $('.toggle-status').change(function() {
                var status = $(this).prop('checked') ? 'active' : 'inactive';
                var id = $(this).data('id');
                var sample_id = $('#sample_id').val();
                $.ajax({
                    url: '/update-assoc-test-active-status',
                    method: 'POST',
                    data: {
                        id: id,
                        status: status,
                        sample_id: sample_id, // Correctly sending the sample ID
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        swal({
                            icon: 'success',
                            title: 'Success',
                            text: 'Status updated successfully',
                            buttons: false,
                            timer: 2000,
                        });
                    },
                    error: function(xhr) {
                        swal({
                            icon: 'error',
                            // title: 'Alert',
                            text: xhr.responseJSON.error,
                            buttons: false,
                            timer: 3000,
                        }).then(function() {
                            setTimeout(function() {
                                location
                                    .reload();
                            }, 500);
                        });
                    }

                });
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            // Handle click on the "Create PTR" button
            $('.check-method-btn').click(function(e) {
                e.preventDefault();

                var productId = {{ $product->id }};
                var createUrl = "{{ route('create-pre-test-report', ['id' => $product->id]) }}";

                $.ajax({
                    url: '/check-method/' + productId,
                    method: 'GET',
                    success: function(data) {
                        if (data.ptr_exists) {
                            swal({
                                icon: 'warning',
                                title: 'PTR Already Exists',
                                text: 'A PTR is already present and cannot be created again.',
                                showConfirmButton: true,
                            });
                        } else {
                            // Populate the existing methods dropdown
                            var methodDropdown = $('#existing_method');
                            methodDropdown.empty().append(
                                '<option value="">Please Select</option>');

                            data.methods.forEach(function(method) {
                                methodDropdown.append(
                                    `<option value="${method.id}">${method.method_name}</option>`
                                );
                            });

                            $('#methodModal').modal('show');
                        }
                    },
                    error: function(err) {
                        console.error('Error checking method:', err);
                        swal({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while checking the method. Please try again.',
                            showConfirmButton: true,
                        });
                    }
                });
            });


            // Handle form submission inside the modal for linking the method
            $('#methodModal form').submit(function(e) {
                e.preventDefault();

                var productId = {{ $product->id }};
                var form = $(this)[0];
                var formData = new FormData(form);

                // Capture image from the canvas if available
                var canvas = document.getElementById('canvas');
                if (canvas && canvas.style.display !== 'none') {
                    canvas.toBlob(function(blob) {
                        formData.append('picture', blob, 'captured-image.png');
                        submitFormWithAjax(formData, productId);
                    });
                } else {
                    submitFormWithAjax(formData, productId);
                }
            });

            // AJAX form submission function
            function submitFormWithAjax(formData, productId) {
                $.ajax({
                    url: '/link-method/' + productId,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    processData: false,
                    contentType: false,
                    data: formData,
                    success: function(data) {
                        if (data.success) {
                            swal({
                                icon: 'success',
                                title: 'Success',
                                text: 'Method attached successfully!',
                                buttons: false,
                                timer: 2000,
                            }).then(() => {
                                if (data.redirect) {
                                    window.location.href = data
                                        .redirect; // Redirect using the controller response
                                } else {
                                    location.reload(); // Reload if no redirect URL is provided
                                }
                            });
                        } else {
                            swal({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'Something went wrong!',
                                showConfirmButton: true,
                            });
                        }
                    },
                    error: function(err) {
                        console.error('Error linking method:', err);
                        swal({
                            icon: 'error',
                            title: 'Error',
                            text: err.responseJSON?.message ||
                                'An error occurred. Please try again.',
                            showConfirmButton: true,
                        });
                    }
                });

            }

            // File upload handling
            const fileInput = $('#method_files');
            const label = fileInput.next();
            const originalLabelText = label.text();

            fileInput.on('change', function(event) {
                const files = event.target.files;
                let fileNames = '';

                if (files.length > 0) {
                    for (let i = 0; i < files.length; i++) {
                        fileNames += (i > 0 ? ', ' : '') + files[i].name;
                    }
                    label.text(fileNames); // Update label with selected file names
                } else {
                    label.text(originalLabelText); // Reset label if no files selected
                }
            });


            // Image capture handling
            const captureButton = $('#captureButton');
            const closeCameraButton = $('#closeCameraButton');
            const canvas = $('#canvas')[0];
            const pictureInput = $('#picture');
            let stream, video;

            // Initialize camera capture
            $('#cameraButton').click(function() {
                navigator.mediaDevices.getUserMedia({
                        video: true
                    })
                    .then(strm => {
                        stream = strm;
                        video = $('<video>').hide().appendTo('body')[0];
                        video.srcObject = stream;
                        video.play();

                        video.addEventListener('loadedmetadata', function() {
                            canvas.width = video.videoWidth;
                            canvas.height = video.videoHeight;
                            drawCameraStream();
                        });

                        // Display controls for capturing the image
                        $('#controls').show();
                        $('#canvas').show();
                    })
                    .catch(err => console.error('Camera access error:', err));
            });

            // Capture the current video frame into the canvas
            captureButton.click(function() {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
                $('#controls, #canvas').hide();

                canvas.toBlob(blob => {
                    const file = new File([blob], 'captured-image.png', {
                        type: 'image/png'
                    });
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    pictureInput[0].files = dataTransfer.files;

                    const imgPreview = $('<img>', {
                        id: 'img-preview',
                        src: URL.createObjectURL(file),
                        style: 'max-width: 40%; margin-top: 10px; border-radius: 20px;',
                    });

                    $('#img-preview').remove(); // Remove previous preview if any
                    $('.modal-body').append(imgPreview); // Show image preview
                    $(video).remove(); // Remove the video element
                });
            });

            // Close camera without capturing
            closeCameraButton.click(function() {
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                    stream = null;
                }
                $('#controls, #canvas').hide();
                $(video).remove();
            });

            // Continuously draw the camera stream on the canvas
            function drawCameraStream() {
                if (!stream) return;
                const context = canvas.getContext('2d');
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                requestAnimationFrame(drawCameraStream);
            }
        });

        //Edit Associate Test
        $(document).on('click', '.editTest', function() {
            let id = $(this).data('edit_test_id');
            let test_id = $(this).data('test_id');
            let sub_test_id = $(this).data('sub_test_id');
            let test_specification = $(this).data('test_specification');
            let lab = $(this).data('lab');

            $('#edit_test_id').val(id);
            $('#edit_test').val(test_id).change();
            if (sub_test_id != null) {
                $('#edit_sub_test_id').val(sub_test_id).change();
            }
            $('#edit_test_specification').val(test_specification);
            $('#edit_lab').val(lab).change();
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#existing_method').change(function() {
                var selectedMethod = $(this).val();

                if (selectedMethod) {
                    // If existing method is selected, remove required attributes
                    $('.new-method-input').prop('required', false);
                    $('#newMethodForm').hide();
                } else {
                    // If no existing method is selected, make fields required
                    $('.new-method-input').prop('required', true);
                    $('#newMethodForm').show();
                }
            });

            // Ensure correct form validation on modal open
            $('#methodModal').on('shown.bs.modal', function() {
                $('#existing_method').trigger('change');
            });
        });
    </script>
@endsection
