@extends('layouts.app')
@section('title', __('lang_v1.logs'))

@section('content')
    <section class="content-header">
        <h1>
            @if (request()->route()->hasParameter('module'))
                @lang('lang_v1.logs')
                <small>Activity Log for {{ ucfirst(request()->route('module')) }} </small>
            @else
                @lang('lang_v1.logs')
                <small>@lang('lang_v1.manage_logs')</small>
            @endif
        </h1>
    </section>

    <section class="content">
        @component('components.widget', ['class' => 'box-solid'])
            <div class="table-filter mb-10">
                <a class="btn btn-lg btn-secondary mb-10" data-toggle="collapse" href="#filterCollapse" role="button"
                    aria-expanded="false" aria-controls="filterCollapse">
                    <i class="fas fa-filter"></i> @lang('method.filters')
                </a>
                <div class="collapse" id="filterCollapse">
                    <div class="row">
                        <div class="col-md-3" @if (request()->route()->hasParameter('module')) style="display: none;" @endif>
                            <div class="filter-wrapper">
                                <label for="module-filter">Module:</label>
                                <select id="module-filter" class="form-control select2" style="width: 100%;">
                                    <option value="">All Modules</option>
                                    @foreach ($modules as $module)
                                        <option value="{{ $module }}">{{ $module }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="filter-wrapper">
                                <label for="action-filter">Action:</label>
                                <select id="action-filter" class="form-control select2" style="width: 100%;">
                                    <option value="">All Actions</option>
                                    @foreach ($actions as $action)
                                        <option value="{{ $action }}">{{ ucfirst(strtolower($action)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="filter-wrapper">
                                <label for="users-filter">User:</label>
                                <select id="users-filter" class="form-control select2" style="width: 100%;">
                                    <option value="">All Users</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user }}">{{ $user }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="filter-wrapper">
                                <label for="date-range-filter">Date Range:</label>
                                <input type="text" id="date-range-filter" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endcomponent
        @component('components.widget', ['class' => 'box-primary'])
            <div class="row" id="printSection">
                <div class="col-md-12">
                    <div class="nav-tabs-custom">
                        <div class="tab-content">
                            <div class="tab-pane active">
                                <table class="table dataTable table-striped ajax_view hide-footer">
                                    <thead>
                                        <tr>
                                            <th>@lang('method.date_time')</th>
                                            <th>@lang('method.action')</th>
                                            <th @if (request()->route()->hasParameter('module')) style="display: none;" @endif>Location</th>
                                            <th>@lang('method.details')</th>
                                            <th class="no-print" hidden>@lang('method.hash_sign')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($logs as $log)
                                            <tr data-module="{{ $log->module ?? 'N/A' }}">
                                                <td>{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                                                <td>
                                                    @if ($log->event === 'deleted')
                                                        <span class="badge bg-red">Deleted</span>
                                                    @elseif ($log->event === 'created')
                                                        <span class="badge bg-green">Created</span>
                                                    @elseif ($log->event === 'taskCreated')
                                                        <span class="badge bg-blue">Task Created</span>
                                                    @elseif ($log->event === 'taskPerformed')
                                                        <span class="badge bg-orange">Task Performed</span>
                                                    @elseif ($log->event === 'taskApproved')
                                                        <span class="badge bg-green">Task Approved</span>
                                                    @elseif ($log->event === 'printed')
                                                        <span class="badge bg-gray">Printed</span>
                                                    @elseif ($log->event === 'TestStatusChanged')
                                                        <span class="badge bg-yellow">Test Status</span>
                                                    @elseif ($log->event === 'received')
                                                        <span class="badge bg-olive">Received</span>
                                                    @elseif ($log->event === 'demanded')
                                                        <span class="badge bg-navy">Demand</span>
                                                    @elseif ($log->event === 'updated')
                                                        <span class="badge bg-orange">Updated</span>
                                                    @elseif ($log->event === 'responded')
                                                        <span class="badge bg-yellow">Responded</span>
                                                    @elseif ($log->event === 'issued')
                                                        <span class="badge bg-light-blue">Issued</span>
                                                    @elseif ($log->event === 'approved')
                                                        <span class="badge bg-green">Approved</span>
                                                    @elseif ($log->event === 'verified')
                                                        <span class="badge bg-blue">Verified</span>
                                                    @elseif ($log->event === 'rejected' || $log->event === 'rejectd')
                                                        <span class="badge bg-maroon">Rejected</span>
                                                    @elseif ($log->event === 'remarks')
                                                        <span class="badge badge-success">Remarks</span>
                                                    @elseif ($log->event === 'sampleused')
                                                        <span class="badge bg-cyan">SampleUsed</span>
                                                    @elseif ($log->event === 'labelPrint')
                                                        <span class="badge bg-orange">LabelPrint</span>
                                                    @elseif ($log->event === 'login')
                                                        <span class="badge bg-black" data-toggle="tooltip"
                                                            title="Login - {{ isset($log->user) ? $log->user->getUserFullNameAttribute() : 'System' }}">Login</span>
                                                    @elseif ($log->event === 'logout')
                                                        <span class="badge bg-dark" data-toggle="tooltip"
                                                            title="Logout - {{ isset($log->user) ? $log->user->getUserFullNameAttribute() : 'System' }}">Logout</span>
                                                    @endif
                                                </td>

                                                <td @if (request()->route()->hasParameter('module')) style="display: none;" @endif>
                                                    {{ $log->module ?? 'N/A' }}</td>

                                                <td>
                                                    @if ($log->event === 'deleted')
                                                        Record with <span
                                                            style="font-weight: bold;">{{ $log->details ?? 'N/A' }}</span>
                                                        was <span style="font-weight:bold;">deleted by
                                                            {{ isset($log->user) ? $log->user->getUserFullNameAttribute() : 'System' }}
                                                        @elseif ($log->event === 'created')
                                                            A new record was <span style="font-weight:bold;">created</span>
                                                            having
                                                            <span
                                                                style="font-weight: bold;">{{ $log->details ?? 'N/A' }}</span>
                                                            by
                                                            {{ isset($log->user) ? $log->user->getUserFullNameAttribute() : 'System' }}
                                                        @elseif ($log->event === 'taskCreated')
                                                            <span>{!! $log->details ?? 'N/A' !!}</span> by
                                                            {{ isset($log->user) ? $log->user->getUserFullNameAttribute() : 'System' }}
                                                        @elseif ($log->event === 'taskPerformed')
                                                            <span>{!! $log->details ?? 'N/A' !!}</span> by
                                                            {{ isset($log->user) ? $log->user->getUserFullNameAttribute() : 'System' }}
                                                        @elseif ($log->event === 'taskApproved')
                                                            <span>{!! $log->details ?? 'N/A' !!}</span> by
                                                            {{ isset($log->user) ? $log->user->getUserFullNameAttribute() : 'System' }}
                                                        @elseif ($log->event === 'received')
                                                            A Sample having <span
                                                                style="font-weight: bold;">{!! $log->details ?? 'N/A' !!}</span>
                                                            <span></span>
                                                            by
                                                            {{ isset($log->user) ? $log->user->getUserFullNameAttribute() : 'System' }}.
                                                        @elseif ($log->event === 'demanded')
                                                            A Demand having <span
                                                                style="font-weight: bold;">{!! $log->details ?? 'N/A' !!}</span>
                                                            <span></span>
                                                            by
                                                            {{ isset($log->user) ? $log->user->getUserFullNameAttribute() : 'System' }}.
                                                        @elseif ($log->event === 'issued')
                                                            A Sample having <span
                                                                style="font-weight: bold;">{!! $log->details ?? 'N/A' !!}</span>
                                                            <span></span>
                                                            by
                                                            {{ isset($log->user) ? $log->user->getUserFullNameAttribute() : 'System' }}.
                                                        @elseif ($log->event === 'verified')
                                                            An entry having <span
                                                                style="font-weight: bold;">{{ $log->details ?? 'N/A' }}</span>
                                                            was
                                                            <span style="font-weight: bold;">verified</span>
                                                            by
                                                            {{ isset($log->user) ? $log->user->getUserFullNameAttribute() : 'System' }}.
                                                        @elseif ($log->event === 'approved')
                                                            An entry having <span
                                                                style="font-weight: bold;">{{ $log->details ?? 'N/A' }}</span>
                                                            was
                                                            <span style="font-weight: bold;">approved</span>
                                                            by
                                                            {{ isset($log->user) ? $log->user->getUserFullNameAttribute() : 'System' }}.
                                                        @elseif ($log->event === 'TestStatusChanged')
                                                            Test status of <span>{!! $log->details ?? 'N/A' !!}</span>
                                                            by
                                                            {{ isset($log->user) ? $log->user->getUserFullNameAttribute() : 'System' }}.
                                                        @elseif ($log->event === 'rejected' || $log->event === 'rejectd')
                                                            An entry having <span
                                                                style="font-weight: bold;">{{ $log->details ?? 'N/A' }}</span>
                                                            was
                                                            <span style="font-weight: bold;">rejected</span>
                                                            by
                                                            {{ isset($log->user) ? $log->user->getUserFullNameAttribute() : 'System' }}.
                                                        @elseif ($log->event === 'remarks')
                                                            <span
                                                                style="font-weight: bold;">{{ $log->details ?? 'N/A' }}</span>
                                                            by
                                                            {{ isset($log->user) ? $log->user->getUserFullNameAttribute() : 'System' }}
                                                        @elseif ($log->event === 'printed')
                                                            A Document having <span
                                                                style="font-weight: bold;">{{ $log->details ?? 'N/A' }}</span>
                                                            by
                                                            {{ isset($log->user) ? $log->user->getUserFullNameAttribute() : 'System' }}.
                                                        @elseif ($log->event === 'updated')
                                                            Record with <span>{!! $log->details ?? 'N/A' !!}</span>
                                                            by <span
                                                                style="font-weight: bold;">{{ isset($log->user) ? $log->user->getUserFullNameAttribute() : 'System' }}</span>
                                                        @elseif ($log->event === 'responded')
                                                            Entry with <span>{!! $log->details ?? 'N/A' !!}</span>
                                                            by
                                                            {{ isset($log->user) ? $log->user->getUserFullNameAttribute() : 'System' }}
                                                        @elseif ($log->event === 'sampleused')
                                                            Entry with <span
                                                                style="font-weight: bold;">{{ $log->details ?? 'N/A' }}</span>
                                                            <span style="font-weight:bold;"></span> by
                                                            {{ isset($log->user) ? $log->user->getUserFullNameAttribute() : 'System' }}
                                                        @elseif ($log->event === 'labelPrint')
                                                            <span
                                                                style="font-weight: bold;">{{ $log->details ?? 'N/A' }}</span>
                                                            <span style="font-weight:bold;"></span> by
                                                            {{ isset($log->user) ? $log->user->getUserFullNameAttribute() : 'System' }}
                                                        @elseif ($log->event === 'login')
                                                            <span
                                                                style="font-weight: bold;">{{ isset($log->user) ? $log->user->getUserFullNameAttribute() : 'System' }}</span>
                                                            logged in <i style="color:green; font-size:12px"
                                                                class="fa-solid fa-right-to-bracket"></i>
                                                        @elseif ($log->event === 'logout')
                                                            <span
                                                                style="font-weight: bold;">{{ isset($log->user) ? $log->user->getUserFullNameAttribute() : 'System' }}</span>
                                                            logged out <i style="color:red; font-size:12px;"
                                                                class="fa-solid fa-right-from-bracket"></i>
                                                    @endif
                                                </td>
                                                <td hidden>{{ $log->id }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <div>
                                            {{ $logs->links() }} <!-- This will render pagination links -->
                                        </div>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endcomponent

    </section>
    <style>
        .table-filter select {
            padding: 6px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 10px;
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
            var table = $('.dataTable').DataTable({
                order: [
                    [4, 'desc']
                ],
                buttons: [
                    // {
                    //     extend: 'excel',
                    //     text: 'Export to Excel',
                    //     className: 'buttons-excel',
                    //     exportOptions: {
                    //         columns: ':not(.no-print)'
                    //     }
                    // },
                    // {
                    //     extend: 'pdf',
                    //     text: 'Export to PDF',
                    //     className: 'buttons-pdf',
                    //     exportOptions: {
                    //         columns: ':not(.no-print)'
                    //     },
                    // },
                    // {
                    //     extend: 'csv',
                    //     text: 'Export to CSV',
                    //     className: 'buttons-csv',
                    //     exportOptions: {
                    //         columns: ':not(.no-print)'
                    //     }
                    // }, 
                    'colvis'
                ]
            });

            // Initialize Bootstrap tooltip
            $('[data-toggle="tooltip"]').tooltip();

            // Add event listener for module filter
            $('#module-filter').on('change', function() {
                var module = $(this).val();
                if (module) {
                    table.columns(2).search(module).draw();
                } else {
                    table.columns(2).search('').draw();
                }
            });
            // Add event listener for action filter
            $('#action-filter').on('change', function() {
                var action = $(this).val();
                if (action) {
                    table.columns(1).search(action).draw();
                } else {
                    table.columns(1).search('').draw();
                }
            });
            $('#users-filter').on('change', function() {
                var username = $(this).val();
                if (username) {
                    table.columns(3).search(username).draw();
                } else {
                    table.columns(3).search('').draw();
                }
            });
            $(document).ready(function() {
                // Initialize datepicker
                $('#date-range-filter').daterangepicker({
                    autoUpdateInput: false,
                    locale: {
                        cancelLabel: 'Clear'
                    }
                });

                // On apply, set the value of the input field
                $('#date-range-filter').on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(picker.startDate.format('DD-MM-YYYY') + ' TO ' + picker.endDate
                        .format('DD-MM-YYYY'));
                });

                // On clear, reset the value of the input field
                $('#date-range-filter').on('cancel.daterangepicker', function(ev, picker) {
                    $(this).val('');
                });
            });

        });
    </script>
@endsection
