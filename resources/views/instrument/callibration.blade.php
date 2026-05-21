@extends('layouts.app')

@section('title', __('lang_v1.calibrations'))

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('lang_v1.calibrations')
            <small>@lang('lang_v1.manage_calibrations')</small>
        </h1>
    </section>
    @if (isset($selectedDevice))
        {{-- device details form --}}
        <section class="content">
            @component('components.widget', ['class' => 'box-primary'])
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="table-heading">Equipment Details</h3>
                            </div>
                            <div class="card-body">

                                <div class="device-details-grid">
                                    <!-- Display existing device details -->
                                    <div class="device-detail">
                                        <div class="detail-label">Equipment ID:</div>
                                        <div class="detail-value">{{ $selectedDevice->id }}</div>
                                    </div>
                                    <div class="device-detail">
                                        <div class="detail-label">Name:</div>
                                        <div class="detail-value">{{ $selectedDevice->name }}</div>
                                    </div>
                                    <div class="device-detail">
                                        <div class="detail-label">Model:</div>
                                        <div class="detail-value">{{ $selectedDevice->model }}</div>
                                    </div>
                                    <div class="device-detail">
                                        <div class="detail-label">Manufacturer:</div>
                                        <div class="detail-value">{{ $selectedDevice->manufacturer }}</div>
                                    </div>
                                    <div class="device-detail">
                                        <div class="detail-label">Supplier:</div>
                                        <div class="detail-value">{{ $selectedDevice->supplier }}</div>
                                    </div>
                                    <div class="device-detail">
                                        <div class="detail-label">Equipment Description:</div>
                                        <div class="detail-value">{{ strip_tags($selectedDevice->description) }}</div>
                                    </div>
                                    {{-- <div class="device-detail">
                                        <div class="detail-label">Last Calibration Date:</div>
                                        <div class="detail-value">{{ @$lastCalibration->calibration_date }}</div>
                                    </div> --}}
                                    <div class="device-detail">
                                        <div class="detail-label">Equipment Lab:</div>
                                        <div class="detail-value">{{ str_replace('#15', '', $selectedDevice->lab) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endcomponent
        </section>
        <section class="content">
            @component('components.widget', ['class' => 'box-primary'])
                @include('instrument.calibrator_details_form')
            @endcomponent
        </section>

    @endif
    {{-- calibration details table --}}
    <section class="content calibration-table">

        @component('components.widget', ['class' => 'box-primary'])
            <div class="tab-content">
                <div class="tab-pane active" id="">
                    @can('Devices.callibration.add')
                        <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#addDeviceModal">
                            <i class="fa-solid fa-plus"></i> Add
                        </button>
                    @endcan

                </div>
            </div>
            <div class="row" id="printSection">
                <div class="col-md-12">
                    <div class="nav-tabs-custom">
                        <div class="tab-content">
                            <div class="tab-pane active">


                                <table class="table dataTable table-striped ajax_view hide-footer">
                                    <thead>
                                        <tr>
                                            <th style="display: none;" class="no-print">#</th>
                                            <th>Equipment ID</th>
                                            <th>Name</th>
                                            <th>Model</th>
                                            <th>Manufacturer</th>
                                            <th>Calibration Date</th>
                                            <th>Valid Till</th>
                                            <th>Lab</th>
                                            <th class="no-print">@lang('lang_v1.actions')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($calibratorDetails as $index => $calibrator)
                                            <tr>
                                                <td style="display: none;">{{ $index + 1 }}</td>
                                                <td>{{ $calibrator->device_id }}</td>
                                                <td>{{ $calibrator->device->name }}</td>
                                                <td>{{ $calibrator->device->model }}</td>
                                                <td>{{ $calibrator->device->manufacturer }}</td>
                                                <td>{{ \Carbon\Carbon::parse($calibrator->calibration_date)->format('d-m-Y') }}
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($calibrator->guaranteed_date)->format('d-m-Y') }}
                                                </td>
                                                <td>
                                                    @isset($calibrator->device->lab)
                                                        {{ str_replace('#15', '', $calibrator->device->lab) }}
                                                    @else
                                                        ---
                                                    @endisset
                                                </td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-primary btn-xs dropdown-toggle" type="button"
                                                            id="actionMenu{{ $calibrator->id }}" data-toggle="dropdown"
                                                            aria-haspopup="true" aria-expanded="false">
                                                            Actions <span class="caret"></span>
                                                        </button>
                                                        <div class="dropdown-menu"
                                                            aria-labelledby="actionMenu{{ $calibrator->id }}">
                                                            <a class="dropdown-item"
                                                                href="{{ route('instrument.calibrator.show', ['id' => $calibrator->id]) }}">
                                                                <i class="fas fa-eye"></i> View
                                                            </a>
                                                            @can('Devices.callibration.edit')
                                                                <a class="dropdown-item" href="#" data-toggle="modal"
                                                                    data-target="#editModal{{ $index }}">
                                                                    <i class="fas fa-edit"></i> Edit
                                                                </a>
                                                            @endcan

                                                            @can('Devices.callibration.delete')
                                                                <form action="{{ route('calibrator.delete', $calibrator->id) }}"
                                                                    method="POST">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="dropdown-item delete-calibration">
                                                                        <i class="fas fa-trash"></i> Delete
                                                                    </button>
                                                                </form>
                                                            @endcan
                                                            <a class="dropdown-item"
                                                                href="{{ route('logs.index', ['module' => 'calibration']) }}">
                                                                <i class="fa-solid fa-clock-rotate-left"></i> Logs
                                                            </a>
                                                        </div>
                                                    </div>
                                                    @include('instrument.edit_calibrator_details_form')

                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                            </div>
                        </div>
                        @include('instrument.add_device_for_calibration_form')

                    </div>
                </div>
            </div>
        @endcomponent
    </section>
    <style>
        .device-details-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            padding: 10px;
        }

        .device-detail {
            display: flex;
            flex-direction: column;
            padding: 20px;
            background: #f2f2f2;
            border-radius: 10px;

        }

        .detail-label {
            font-weight: bold;
        }

        .detail-value {
            margin-top: 5px;
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
    </style>
@endsection

@section('javascript')
    <script>
        $(document).ready(function() {
            var formPresent = {!! isset($selectedDevice) ? 'true' : 'false' !!};

            if (formPresent) {
                $('.calibration-table').hide();
            } else {
                $('.calibration-table').show();
            }
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#new_device').change(function() {
                var selectedDevice = $(this).find('option:selected');
                var deviceId = selectedDevice.data('id');
                var deviceModel = selectedDevice.data('model');
                var devicelab = selectedDevice.data('lab');
                console.log(deviceId, deviceModel, devicelab);
                $('#device_id').val('Equipment Id : ' + deviceId);
                $('#modal').val('Equipment Modal : ' + deviceModel);
                $('#lab').val('Equipment Lab : ' + devicelab);
                $('#device_model').val(deviceModel);
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('.dataTable').DataTable({
                order: [
                    [0, 'desc']
                ],
                buttons: [{
                        extend: 'print',
                        text: 'Print',
                        className: 'buttons-print',
                        exportOptions: {
                            columns: ':not(.no-print)'
                        },
                        customize: function(win) {
                            logPrintEvent();

                            $(win.document.body).find('h1').remove();

                            var defaultTitle = $('title').text();
                            var reportTitle = defaultTitle.split(' - ')[0] + ' Report';

                            var pageBreakAdded = false;

                            var header = $(`
                                <header style="padding: 10px; z-index: 1000;">
                                    <div class="row header" style="display: flex; justify-content: space-between; align-items: center;">
                                        <div class="col-md-2 mt-3">
                                            <img src="{{ asset('dummy/paklogo4.png') }}" width="100px" />
                                        </div>
                                        <div class="col-md-8" style="text-align: center;">
                                            <h4 style="font-weight: bold;">ARMED FORCES MEDICAL STORES LABORATORY</h4>
                                            <hr style="margin: 5px 0;"> <!-- Add horizontal line here -->
                                            <h5 style="font-weight: bold;">${reportTitle}</h5> <!-- Add dynamic report title here -->
                                        </div>
                                        <div class="col-md-2 mt-3" style="text-align: end;">
                                            <img src="{{ asset('dummy/AFMS LOGO-01.png') }}" width="110px" />
                                           
                                        </div>
                                    </div>
                                </header>
                            `);

                            $(win.document.body).prepend(header);

                            $.get('/get-footer', function(footerContent) {
                                $(win.document.body).append(footerContent);
                            });

                            var currentPage = 0;
                            var rowCount = 0;

                            $(win.document.body).find('table').addClass('print-table');
                            $(win.document.body).find('.print-table tr').each(function(index) {
                                rowCount++;
                                if (rowCount % 22 === 0) {
                                    currentPage++;
                                    $(this).after('<div class="page-break"></div>');
                                    pageBreakAdded =
                                        true;
                                }
                            });

                            if (pageBreakAdded) {
                                header.css('position', 'fixed');
                                header.css('left', '0');
                                header.css('right', '0');
                                header.css('background-color', '#fff');
                                $('<style>.print-table { position: relative; top: 150px; bottom: 150px; }</style>')
                                    .appendTo(win.document.head);

                            }

                        }
                    },
                    {
                        extend: 'excel',
                        text: 'Export to Excel',
                        className: 'buttons-excel',
                        exportOptions: {
                            columns: ':not(.no-print)'
                        }
                    },
                    {
                        extend: 'pdf',
                        text: 'Export to PDF',
                        className: 'buttons-pdf',
                        exportOptions: {
                            columns: ':not(.no-print)'
                        },
                    },
                    {
                        extend: 'csv',
                        text: 'Export to CSV',
                        className: 'buttons-csv',
                        exportOptions: {
                            columns: ':not(.no-print)'
                        }
                    }, 'colvis'
                ]
            });
            $(document).on('click', '.delete-calibration', function(e) {
                e.preventDefault();
                var deleteButton = $(this);
                swal({
                    title: "@lang('lang_v1.are_you_sure')",
                    icon: "warning",
                    buttons: ["@lang('messages.cancel')", "@lang('lang_v1.yes_delete_it')"],
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        deleteButton.closest('form').submit();
                    }
                });
            });

            function logPrintEvent() {
                var defaultTitle = $('title').text();
                var reportTitle = defaultTitle.split(' - ')[0] + ' Report';
                var randomID = Math.floor(Math.random() * 100000);
                var documentID = reportTitle + ' - ' + randomID;

                $.ajax({
                    url: '/print-event',
                    method: 'post',
                    data: {
                        documentID: documentID,
                        printedModule: 'Calibration'
                    },
                    success: function(response) {},
                    error: function(xhr, status, error) {
                        console.error('Error logging print event:', error);
                    }
                });
            }
            $('.datepicker').datetimepicker({
                format: 'YYYY-MM-DD',
            });
        });
    </script>
@endsection
