@extends('layouts.app')

@section('title', __('lang_v1.instrument'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('devices.device')
            <small>@lang('lang_v1.manage_equipment')</small>
        </h1>
    </section>


    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-solid'])
            <div class="table-filter mb-10">
                <a class="btn btn-lg btn-secondary mb-10" data-toggle="collapse" href="#filterCollapse" role="button"
                    aria-expanded="false" aria-controls="filterCollapse">
                    <i class="fas fa-filter"></i> @lang('method.filters')
                </a>
                <div class="collapse" id="filterCollapse">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="filter-wrapper">
                                <label for="categories-filter">@lang('method.category')</label>
                                <select id="categories-filter" class="form-control select2" style="width: 100%;">
                                    <option value="">@lang('method.all_categories')</option>
                                    @foreach ($devices->unique('category') as $equipment)
                                        <option value="{{ $equipment->category }}">{{ $equipment->category }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        @endcomponent
        @component('components.widget', ['class' => 'box-primary'])
            <div class="tab-content">
                <div class="tab-pane active" id="">
                    @can('Devices.create')
                        <a class="btn btn-primary pull-right btn-modal "
                            data-href="{{ action([\App\Http\Controllers\InstrumentsController::class, 'create']) }}"
                            data-container=".devices_add_modal">
                            <i class="fa fa-plus"></i> @lang('messages.add')</a>
                    @endcan
                </div>
            </div>
            {{-- index table for devices  --}}
            <div class="row" id="printSection">
                <div class="col-md-12">
                    <div class="nav-tabs-custom">
                        <div class="tab-content">
                            <div class="tab-pane active">
                                <table class="table dataTable table-striped ajax_view hide-footer">
                                    <thead>
                                        <tr>
                                            <th style="display: none;" class="no-print">@lang('method.id')</th>
                                            {{-- <th>@lang('method.date')</th> --}}
                                            {{-- <th>@lang('method.id')</th> --}}
                                            <th>@lang('method.name')</th>
                                            <th>@lang('method.description')</th>
                                            <th>@lang('method.model')</th>
                                            <th>@lang('method.category')</th>
                                            @if (count(array_intersect($role, $targetRoles)) > 0)
                                            @else
                                                <th>@lang('method.lab')</th>
                                            @endif
                                            <th>@lang('method.manufacturer')</th>
                                            <th>@lang('method.supplier')</th>
                                            <th class="no-print">@lang('lang_v1.actions')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($devices as $d)
                                            <tr>
                                                <td style="display: none;">{{ $loop->iteration }}</td>
                                                {{-- <td>{{ $d->created_at->format('d-m-y') }}</td> --}}
                                                {{-- <td>{{ $d->id }}</td> --}}
                                                <td>{{ $d->name ?? '--' }}</td>
                                                <td
                                                    style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                    {{ substr(strip_tags($d->description), 0, 50) }}
                                                </td>
                                                <td>{{ $d->model ?? '--' }}</td>
                                                <td>{{ $d->category ?? '--' }}</td>
                                                @if (count(array_intersect($role, $targetRoles)) > 0)
                                                @else
                                                    <td>
                                                        @isset($d->lab)
                                                            {{ str_replace('#' . $business_id, '', $d->lab) }}
                                                        @else
                                                            ---
                                                        @endisset
                                                    </td>
                                                @endif


                                                <td>{{ $d->manufacturer ?? '--' }}</td>
                                                <td>{{ $d->supplier ?? '--' }}</td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-primary btn-xs dropdown-toggle" type="button"
                                                            id="actionMenu{{ $d->id }}" data-toggle="dropdown"
                                                            aria-haspopup="true" aria-expanded="false">
                                                            Actions <span class="caret"></span>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-right"
                                                            aria-labelledby="actionMenu{{ $d->id }}">

                                                            @can('Devices.view')
                                                                <a class="dropdown-item"
                                                                    href="{{ route('instrument.information', ['id' => $d->id]) }}">
                                                                    <i class="fa-solid fa-eye"></i> View
                                                                </a>
                                                            @endcan

                                                            @can('Devices.edit')
                                                                <a data-href="{{ action([\App\Http\Controllers\InstrumentsController::class, 'edit'], ['instruments' => $d->id]) }}"
                                                                    class="btn-modal dropdown-item"
                                                                    data-container=".device_edit_form">
                                                                    <i class="fas fa-edit"></i>
                                                                    @lang('messages.edit')
                                                                </a>
                                                            @endcan

                                                            @can('Devices.delete')
                                                                <form action="{{ route('instrument.destroy', $d->id) }}"
                                                                    method="POST">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="dropdown-item delete-device">
                                                                        <i class="fas fa-trash"></i> Delete
                                                                    </button>
                                                                </form>
                                                            @endcan
                                                            @can('capa.create')
                                                                <a class="dropdown-item capa-model" data-lab="{{ $d->lab }}"
                                                                    data-modal="{{ $d->model }}"
                                                                    data-name="{{ $d->name }}" data-id="{{ $d->id }}"
                                                                    data-toggle="modal" data-target="#commentModal">
                                                                    <i class="fa-solid fa-sitemap"></i> @lang('messages.add') Capa
                                                                </a>
                                                            @endcan
                                                            @can('deviation.create')
                                                                <a href="javascript:void(0);"
                                                                    class="dropdown-item open-deviation-modal"
                                                                    data-device-id="{{ $d->id }}"
                                                                    data-device-name="{{ $d->name }}">
                                                                    <i class="fa-solid fa-circle-exclamation"></i> Add Deviation
                                                                </a>
                                                            @endcan

                                                            @can('Devices.callibration.add')
                                                                <a href="{{ url('/device/callibration/add', ['id' => $d->id]) }}"
                                                                    class="dropdown-item capa-model" data-toggle="modal">
                                                                    <i class="fa-solid fa-gauge-high"></i> @lang('messages.add')
                                                                    Callibration
                                                                </a>
                                                            @endcan
                                                            {{-- @can('Devices.Utilizations.add')
                                                                <a class="dropdown-item capa-model" href="{{ route('utilizations.create') }}">
                                                                    <i class="fa fa-plus"></i> @lang('messages.add') Utilization
                                                                </a>
                                                            @endcan --}}
                                                            {{-- <a class="dropdown-item"
                                                                href="{{ route('logs.index', ['module' => 'equipment']) }}">
                                                                <i class="fa-solid fa-clock-rotate-left"></i> Logs
                                                            </a> --}}
                                                            <a class="dropdown-item"
                                                                href="{{ route('equipment.tests', ['id' => $d->id]) }}">
                                                                <i class="fa-solid fa-clipboard-check"></i> Usage
                                                            </a>
                                                            <a href="javascript:void(0);" class="dropdown-item print-label"
                                                                data-id="{{ $d->id }}" data-name="{{ $d->name }}"
                                                                data-model="{{ $d->model }}">
                                                                <i class="fa-solid fa-print"></i> Print Label
                                                            </a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal fade device_edit_form" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
                </div>
                <div class="modal fade devices_add_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
                </div>
                <div class="modal fade" id="commentModal" tabindex="-1" role="dialog" aria-labelledby="commentModalLabel"
                    aria-hidden="true">
                </div>
                @include('instrument.add_device_for_calibration_form')
                @include('deviations.create')
                <!-- Modal for QR Code -->
                <div id="qrModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="qrModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close close-btn" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <h5 class="modal-title" id="qrModalLabel">QR Code</h5>
                            </div>
                            <div class="modal-body">
                                <!-- QR Code will be inserted dynamically -->
                                <div class="qr-container text-center">
                                    <div class="qr-code">
                                        <!-- The QR Code will be inserted here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        @endcomponent
    </section>
    <style>
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

            $(document).on('click', '#addcollibration', function() {
                var id = $(this).data('id');
                var lab = $(this).data('lab');
                var model = $(this).data('model');
                var name = $(this).data('name');

                $.ajax({
                    type: "get",
                    url: '{{ route('callibration.add') }}',
                    data: {
                        device_id: id
                    },
                })

                // $(`#addDeviceModal`).modal('show');

                // $('#new_device').val(id);
                // $('#device_id').val('Equipment Id : ' + name);
                // $('#modal').val('Equipment Modal : ' + model);
                // $('#lab').val('Equipment Lab : ' + lab.replace(/#\d+$/, ''));

            });



            $(document).ready(function() {
                $('.capa-model').on('click', function() {
                    var device_id = $(this).data('id');

                    $.ajax({
                        url: '{{ action([\App\Http\Controllers\CapaController::class, 'create']) }}',
                        type: 'get',
                        data: {
                            device_id: device_id
                        }, // or 'post' if necessary
                        success: function(response) {
                            $("#commentModal").html(response);
                        },
                        error: function(xhr, status, error) {
                            console.error(xhr.responseText);
                        }
                    });

                })
            })

            $('.select2').select2();

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
                                if (rowCount % 20 === 0) {
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
                ],
                columnDefs: [{
                    targets: 4,
                    visible: true
                }]
            });
            $(document).on('click', '.delete-device', function(e) {
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
            $('#categories-filter').change(function() {
                var category = $(this).val();
                table.columns(4).search(category).draw();
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
                    printedModule: 'equipment'
                },
                success: function(response) {},
                error: function(xhr, status, error) {
                    console.error('Error logging print event:', error);
                }
            });
        }
    </script>

    <script>
        tinymce.init({
            selector: '#description',
            plugins: 'advlist autolink lists  charmap print preview hr anchor pagebreak',
            toolbar_mode: 'floating',
        });
    </script>
    <script>
        $(document).ready(function() {
            $('.print-label').on('click', function() {
                // Get device details from data attributes
                const deviceId = $(this).data('id');

                // Redirect to the print label route using Laravel's URL helper
                const baseUrl = '{{ url('/') }}';
                window.open(baseUrl + '/print-label/' + deviceId, '_blank');
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('.open-deviation-modal').on('click', function() {
                const deviceId = $(this).data('device-id');
                const deviceName = $(this).data('device-name');

                // Set hidden input value
                $('#device_id_hidden').val(deviceId);

                // Populate and disable the dropdown
                const $deviceSelect = $('#device_id_select');
                $deviceSelect.empty().append(
                    $('<option>', {
                        value: deviceId,
                        text: deviceName,
                        selected: true
                    })
                );

                // Trigger select2 update (if used)
                $deviceSelect.trigger('change');

                // Open the modal
                $('#addDeviationModal').modal('show');
            });
            $('#addDeviationModal').on('hidden.bs.modal', function() {
                $('#device_id_select').empty();
                $('#device_id_hidden').val('');
            });

        });
    </script>


@endsection
