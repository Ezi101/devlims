@extends('layouts.app')
@section('title', __('purchase.str_multi_report_export'))

@section('content')

    <!-- Content Header -->
    <section class="content-header">
        <h1>@lang('purchase.str_multi_report_export')</h1>
    </section>

    <!-- Main content -->
    <section class="content">

        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
                <!-- Left side: Sample and Batch fields -->
                <div class="col-sm-6">
                    <div class="form-group">
                        {!! Form::label('sample', __('product.sample') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-search"></i></span>
                            <select name="search_nomenclature" id="search_nomenclature" class="form-control select2"
                                placeholder="{{ __('lang_v1.search_product_placeholder') }}">
                                <option value="">{{ __('lang_v1.search_product_placeholder') }}</option>
                                @foreach ($samples as $sample)
                                    <option value="{{ $sample->id }}">{{ $sample->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('batch', __('product.batch') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-cubes"></i></span>
                            <select name="batch[]" id="batch" class="form-control select2" multiple="multiple"
                                placeholder="{{ __('lang_v1.select_batch_placeholder') }}">
                                <option value="">{{ __('lang_v1.select_batch_placeholder') }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Right side: STR No fields and Export Button -->
                <div class="col-sm-6">
                    <div class="form-group">
                        {!! Form::label('str_no', __('product.str_no') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-info"></i></span>
                            <div id="str_no" class="form-control str-no-display"
                                style="min-height: 40px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; white-space: wrap; overflow-y: auto; overflow-: none; display: inline-block;">
                                <!-- STR Nos will be displayed here -->
                            </div>
                        </div>
                    </div>

                    <button id="export-btn" class="btn btn-success btn-block" style="margin-top: 30px;">Export PDFs</button>
                </div>
            </div>
        @endcomponent

    </section>

    <!-- Loading Overlay -->
    <div id="loading-overlay" style="display: none;">
        <div class="loading-content">
            <div class="spinner">
                <div class="double-bounce1"></div>
                <div class="double-bounce2"></div>
            </div>
            <h3>Generating PDF Report</h3>
            <p>Please wait while we prepare your documents...</p>
        </div>
    </div>

@endsection

@section('javascript')
    <script>
        $(document).ready(function() {
            // Track if we're downloading
            let isDownloading = false;

            // Original code remains exactly the same
            $('#search_nomenclature').change(function() {
                const sampleId = $(this).val();

                if (sampleId) {
                    showLoading(true, 'Fetching batches...');
                    $.ajax({
                        url: '{{ route('get.batches') }}',
                        type: 'GET',
                        data: {
                            sample_id: sampleId
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            let batchOptions =
                                '<option value="">{{ __('lang_v1.select_batch_placeholder') }}</option>';
                            if (response.batches.length > 0) {
                                response.batches.forEach(function(batch) {
                                    batchOptions +=
                                        `<option value="${batch.batch_id}" data-code="${batch.code}" data-mfg="${batch.mfg_date}" data-expiry="${batch.expiry_date}" data-potency="${batch.potency}">${batch.code}</option>`;
                                });
                            }
                            $('#batch').html(batchOptions).change();
                            showLoading(false);
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            showLoading(false);
                            swal({
                                title: "Error",
                                text: "Failed to load batches. Please try again.",
                                type: "error",
                                confirmButtonText: "OK"
                            });
                        }
                    });
                } else {
                    $('#batch').html(
                        '<option value="">{{ __('lang_v1.select_batch_placeholder') }}</option>');
                }
            });

            $('#batch').change(function() {
                const batchIds = $(this).val();

                if (batchIds && batchIds.length > 0) {
                    showLoading(true, 'Fetching STR numbers...');
                    $.ajax({
                        url: '{{ route('get.str.no') }}',
                        type: 'GET',
                        data: {
                            batch_no: batchIds
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            let strNos = '';
                            if (response.str_records.length > 0) {
                                response.str_records.forEach(function(record) {
                                    strNos +=
                                        `${record.str_no}, `;
                                });
                                strNos = strNos.slice(0, -2);
                                $('#str_no').text(strNos);
                            } else {
                                $('#str_no').text('');
                                swal({
                                    title: "No STR Found",
                                    text: "No STR numbers found for selected batches",
                                    type: "info",
                                    confirmButtonText: "OK"
                                });
                            }
                            showLoading(false);
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            showLoading(false);
                            swal({
                                title: "Error",
                                text: "Failed to load STR numbers. Please try again.",
                                type: "error",
                                confirmButtonText: "OK"
                            });
                        }
                    });
                } else {
                    $('#str_no').text('');
                }
            });

            $('#export-btn').click(function() {
                const strNo = $('#str_no').text();

                if (strNo) {
                    isDownloading = true;
                    showLoading(true, 'Generating PDF report...');

                    // Open download in new tab/hidden iframe
                    const downloadUrl = '{{ route('export.str.pdf', ':sample_testing_report') }}'
                        .replace(':sample_testing_report', strNo);

                    // Create hidden iframe for download
                    const iframe = document.createElement('iframe');
                    iframe.style.display = 'none';
                    iframe.src = downloadUrl;
                    document.body.appendChild(iframe);

                    // Check for download completion periodically
                    const checkInterval = setInterval(function() {
                        if (!isDownloading) {
                            clearInterval(checkInterval);
                            showLoading(false);
                        }
                    }, 1000);

                    // Fallback - hide loader after 30 seconds no matter what
                    setTimeout(function() {
                        if (isDownloading) {
                            isDownloading = false;
                            showLoading(false);
                            swal({
                                title: "Download Complete",
                                text: "Your download should be complete. Refreshing page...",
                                type: "success",
                                confirmButtonText: "OK",
                                timer: 2000
                            }, function() {
                                resetForm();
                            });
                        }
                    }, 30000);
                } else {
                    swal({
                        title: "Selection Required",
                        text: "Please select at least one batch to get the STR.",
                        type: "warning",
                        confirmButtonText: "OK"
                    });
                }
            });

            // Function to reset form
            function resetForm() {
                window.location.reload();
            }

            // New loading overlay functions
            function showLoading(show, message = '') {
                if (show) {
                    $('#loading-overlay').fadeIn(200);
                    if (message) {
                        $('#loading-overlay h3').text(message);
                    }
                } else {
                    $('#loading-overlay').fadeOut(200);
                    isDownloading = false;
                    // Show success message and reset form
                    swal({
                        title: "Download Complete",
                        text: "Your download should be complete. Refreshing page...",
                        type: "success",
                        confirmButtonText: "OK",
                        timer: 2000
                    }, function() {
                        resetForm();
                    });
                }
            }

            // Detect when the window loses focus (likely when download starts)
            window.addEventListener('blur', function() {
                if (isDownloading) {
                    // Assume download started when window loses focus
                    setTimeout(function() {
                        isDownloading = false;
                        showLoading(false);
                    }, 3000); // Hide after 3 seconds
                }
            });
        });
    </script>

    <style>
        /* Loading Overlay Styles */
        #loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .loading-content {
            text-align: center;
            max-width: 400px;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .spinner {
            width: 70px;
            height: 70px;
            margin: 0 auto 20px;
            position: relative;
        }

        .double-bounce1,
        .double-bounce2 {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background-color: #3c8dbc;
            opacity: 0.6;
            position: absolute;
            top: 0;
            left: 0;
            animation: sk-bounce 2.0s infinite ease-in-out;
        }

        .double-bounce2 {
            animation-delay: -1.0s;
        }

        @keyframes sk-bounce {

            0%,
            100% {
                transform: scale(0.0);
            }

            50% {
                transform: scale(1.0);
            }
        }

        #loading-overlay h3 {
            margin-top: 20px;
            color: #333;
            font-weight: 600;
        }

        #loading-overlay p {
            color: #777;
            margin-top: 10px;
        }
    </style>
@endsection
