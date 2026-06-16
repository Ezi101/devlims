@extends('layouts.app')
@section('title', __('lang_v1.str'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('lang_v1.str')
            <small>@lang('lang_v1.manage_str_report')</small>
        </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        @include('str.partials._str_nav')

        @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
            <div class="col-md-3">
                <div class="form-group">
                    <label for="sample" class="form-label">Samples</label>
                    <select name="sample" id="sampleFilter" class="form-control select2">
                        <option value="" selected disabled>Select Sample</option>
                        @foreach ($sample as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="batch" class="form-label">Batch</label>
                    <select name="batch" id="batchFilter" class="form-control select2">
                        <option value="" selected disabled>Select Batch</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="contract_type" class="form-label">Contract Type</label>
                    <select name="contract_type" id="contract_type" class="form-control select2">
                        <option value="" selected disabled>Select Option</option>
                        <option value="all">All</option>
                        <option value="tender">Tender</option>
                        <option value="supply">Supply</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="contract_no" class="form-label">Contract #</label>
                    <select name="contract_no" id="contract_no" class="form-control select2">
                        <option value="" selected disabled>Select Option</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="filter-wrapper">
                    <label for="status-filter">Status:</label>
                    <select id="str-status-filter" class="form-control select2" style="width: 100%;">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3" style="margin-top:25px;">
                <div class="filter-wrapper">
                    <button class="searchBtn-custom-str btn btn-default" id="searchBtn-custom-str" style="float: right;"><i
                            class="fa fa-filter"></i>&nbsp;Apply Filters</button>

                </div>
            </div>
            <div class="col-md-3" style="margin-top:25px;">

                <button type="button" class="btn btn-default" id="dashboard_date_filter">
                    <span>
                        <i class="fa fa-calendar"></i> {{ __('messages.filter_by_date') }}
                    </span>
                    <i class="fa fa-caret-down"></i>
                </button>
            </div>
        @endcomponent
        @component('components.widget', ['class' => 'box-primary'])
            <div class="tab-content">
                <div class="tab-pane active" id="">
                    <button class="printButton-custom-str" id="printButton-custom-str"><i
                            class="fa-solid fa-arrow-up-from-bracket"></i> PDF</button>
                    @can('str.create')
                        <a class="btn btn-primary pull-right btn-modal "
                            data-href="{{ action([\App\Http\Controllers\STRController::class, 'create']) }}"
                            data-container=".str_report_create">
                            <i class="fa fa-plus"></i> @lang('messages.add')</a>
                    @endcan
                    @can('activity_log.view')
                        <a class="btn btn-default pull-right" style="margin-right: 5px;"
                            href="{{ route('logs.index', ['module' => 'STR', 'sample management']) }}">
                            <i class="fa-solid fa-clock-rotate-left"></i> @lang('messages.logs')
                        </a>
                    @endcan
                    <br><br>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="nav-tabs-custom">
                        <div class="tab-content">
                            <div class="tab-pane active">
                                <div class="table-responsive">

                                    <table class="table dataTable table-striped ajax_view hide-footer" style="display: none;"
                                        id="myTable">
                                        <thead>
                                            <tr>
                                                <th>
                                                    <label class="custom-checkbox">
                                                        <input id="select-all" name="dummy" type="checkbox">
                                                        <span class="checkmark"></span>
                                                    </label>
                                                </th>
                                                <th>@lang('method.date')</th>
                                                <th>@lang('product.sample')</th>
                                                <th>@lang('product.generic')</th>
                                                <th>@lang('product.batch')</th>
                                                <th>@lang('product.str_no')</th>
                                                <th>@lang('product.contract_no')</th>
                                                <th>@lang('method.created_by')</th>

                                                <th>@lang('sale.status')</th>
                                                <th class="no-print">@lang('messages.action')</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- Data AJAX filter --}}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade str_report_create" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            </div>
            <div class="modal fade str_report_remark" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            </div>
            <div class="modal fade ptr_str_approval" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            </div>
        @endcomponent
    </section>


    <style>
        .printButton-custom-str {
            padding: 1.1em 2.7em;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 2.3px;
            font-weight: 500;
            color: #000;
            background-color: #fff;
            border: none;
            border-radius: 45px;
            box-shadow: 0px 8px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease 0s;
            cursor: pointer;
            outline: none;
        }

        .printButton-custom-str:hover {
            background-color: #1367D1;
            box-shadow: 0px 15px 20px rgba(19, 103, 209, 0.4);
            color: #fff;
            transform: translateY(-7px);
        }

        .printButton-custom-str:active {
            transform: translateY(-1px);
        }

        .custom-checkbox {
            display: inline-flex;
            align-items: center;
            cursor: pointer;
            user-select: none;
            font-size: 16px;
            color: #333;
            transition: color 0.3s;
        }

        .custom-checkbox input[type="checkbox"] {
            display: none;
        }

        .custom-checkbox .checkmark {
            width: 24px;
            height: 24px;
            border: 2px solid #333;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            transition: background-color 0.3s, border-color 0.3s, transform 0.3s;
            transform-style: preserve-3d;
        }

        .custom-checkbox .checkmark::before {
            content: "\2713";
            font-size: 16px;
            color: transparent;
            transition: color 0.3s, transform 0.3s;
        }

        .custom-checkbox input[type="checkbox"]:checked+.checkmark {
            background-color: #333;
            border-color: #333;
            transform: scale(1.1) rotateZ(360deg) rotateY(360deg);
        }

        .custom-checkbox input[type="checkbox"]:checked+.checkmark::before {
            color: #fff;
        }

        .custom-checkbox:hover {
            color: #666;
        }

        .custom-checkbox:hover .checkmark {
            border-color: #666;
            background-color: #f0f0f0;
            transform: scale(1.05);
        }

        .custom-checkbox input[type="checkbox"]:focus+.checkmark {
            box-shadow: 0 0 3px 2px rgba(0, 0, 0, 0.2);
            outline: none;
        }

        .custom-checkbox .checkmark,
        .custom-checkbox input[type="checkbox"]:checked+.checkmark {
            transition: background-color 1.3s, border-color 1.3s, color 1.3s, transform 0.3s;
        }

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
        var userPermissions = {
            canViewSTR: @json(auth()->user()->can('str.view')),
            canEditSTR: @json(auth()->user()->can('str.edit')),
            canApproveSTR: @json(auth()->user()->can('str.approve')),
            canRejectSTR: @json(auth()->user()->can('str.reject')),
            canRemarkSTR: @json(auth()->user()->can('str.remark')),
            canApproveWithRemarks: @json(auth()->user()->can('str.approve_with_remarks'))
        };
    </script>

    <script>
        $(document).ready(function() {
            var startDate = null;
            var endDate = null;
            var strStatusFilterId = null;
            var sample = null;
            var batch = null;
            var contract = null;

            // ✅ DataTable ek baar initialize karein
            var table = $('#myTable').DataTable({
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
                                    <hr style="margin: 5px 0;">
                                    <h5 style="font-weight: bold;">${reportTitle}</h5>
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
                            if (rowCount % 16 === 0) {
                                currentPage++;
                                $(this).after('<div class="page-break"></div>');
                                pageBreakAdded = true;
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
                }, {
                    extend: 'excel',
                    text: 'Export to Excel',
                    className: 'buttons-excel',
                    exportOptions: {
                        columns: ':not(.no-print)'
                    }
                }, {
                    extend: 'pdf',
                    text: 'Export to PDF',
                    className: 'buttons-pdf',
                    exportOptions: {
                        columns: ':not(.no-print)'
                    }
                }, {
                    extend: 'csv',
                    text: 'Export to CSV',
                    className: 'buttons-csv',
                    exportOptions: {
                        columns: ':not(.no-print)'
                    }
                }, 'colvis']
            });

            // ✅ Page load pe data fetch karein
            // fetchFilteredData();

            // Date range settings
            var dateRangeSettings = {
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month')
                        .endOf('month')
                    ],
                    'This month last year': [moment().subtract(1, 'year').startOf('month').add(1, 'month'),
                        moment().subtract(1, 'year').endOf('month').add(1, 'month')
                    ],
                    'This Year': [moment().startOf('year'), moment()],
                    'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year')
                        .endOf('year')
                    ]
                },
                locale: {
                    format: 'YYYY-MM-DD',
                    applyLabel: 'Apply',
                    cancelLabel: 'Cancel',
                    customRangeLabel: 'Custom Range'
                }
            };

            $('#dashboard_date_filter').daterangepicker(dateRangeSettings, function(start, end) {
                startDate = start;
                endDate = end;
                $('#dashboard_date_filter span').html(start.format('YYYY-MM-DD') + ' - ' + end.format(
                    'YYYY-MM-DD'));
                fetchFilteredData();
            });

            $('#dashboard_date_filter').on('cancel.daterangepicker', function(ev, picker) {
                startDate = null;
                endDate = null;
                $('#dashboard_date_filter span').html(
                    '<i class="fa fa-calendar"></i> {{ __('messages.filter_by_date') }}');
                fetchFilteredData();
            });

            $(document).on('click', '#searchBtn-custom-str', function() {
                strStatusFilterId = $('#str-status-filter').val();
                sample = $('#sampleFilter').val();
                batch = $('#batchFilter').val();
                contract = $('#contract_no').val();
                fetchFilteredData();
            });

            function fetchFilteredData() {
                var data = {
                    status: strStatusFilterId,
                    sample: sample,
                    batch: batch,
                    contract: contract,
                    limit: 200,
                };

                if (startDate && endDate) {
                    data.start_date = startDate.format('YYYY-MM-DD');
                    data.end_date = endDate.format('YYYY-MM-DD');
                }

                $('#myTable').show();

                $.ajax({
                    url: '/str-filter',
                    type: 'get',
                    data: data,
                    beforeSend: function() {
                        $('#myTable tbody').html(
                            '<tr><td colspan="10" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>'
                        );
                    },
                    success: function(response) {
                        $('#myTable').show();
                        updateTable(response);
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            }

            const routes = {
                view: '{{ url('sample-testing-reports') }}/:str_no',
                edit: '{{ url('sample-testing-reports') }}/:str_no/edit',
                remark: '{{ url('remarks') }}/:str_no',
                logs: '{{ url('logs') }}?module=str'
            };

            function updateTable(data) {
                table.clear();
                var rows = [];

                data.forEach(function(item) {
                    const statusClasses = {
                        'approved': {
                            class: 'bg-green',
                            text: 'Approved'
                        },
                        'rejected': {
                            class: 'bg-red',
                            text: 'Rejected'
                        },
                        'pending': {
                            class: 'bg-info',
                            text: 'Pending'
                        }
                    };
                    const status = statusClasses[item.status] || {
                        class: '',
                        text: ''
                    };
                    const created_at = item.created_at ?
                        new Date(item.created_at).toLocaleDateString('en-GB', {
                            day: 'numeric',
                            month: 'short',
                            year: 'numeric',
                        }) : '--';
                    const product = item.product ? item.product.name : '--';
                    // const genericNames = item.product ? item.product.generic_names : '--';
                    const genericNames = item.generic_names ?? '--';
                    const batchCode = item.batch ? item.batch.code : '';
                    const contractNumber = item.contract ? item.contract.number : '';
                    const createdBy = item.created_by || '--';
                    const viewUrl = routes.view.replace(':str_no', item.str_no);

                    const actionDropdown = `
                    <button type="button" class="btn btn-default dropdown-toggle btn-xs" data-toggle="dropdown" aria-expanded="true">
                        Actions <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-left" role="menu">
                        <li><a class="dropdown-item" href="${viewUrl}"><i class="fa fa-eye"></i> View</a></li>
                    </ul>
                `;

                    rows.push([
                        `<label class="custom-checkbox">
                        <input class="select-row" name="str_no[]" value="${item.str_no}" type="checkbox">
                        <span class="checkmark"></span>
                    </label>`,
                        created_at,
                        product + `<input class="sample" value="${item.sample_id}" type="hidden">`,
                        genericNames,
                        batchCode + `<input class="batch" value="${item.batch_no}" type="hidden">`,
                        item.str_no,
                        contractNumber +
                        `<input class="contract" value="${item.sample_id}" type="hidden">`,
                        createdBy,
                        `<span class="label ${status.class}">${status.text}</span>`,
                        `<div class="btn-group">
                        ${item.status === 'approved' || (item.status === 'rejected' && userPermissions.canViewSTR) ?
                            `<a class="btn btn-default btn-xs" href="${viewUrl}"><i class="fa fa-eye"></i> View</a>` :
                            actionDropdown}
                    </div>`
                    ]);
                });

                table.rows.add(rows).draw();
            }

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
                        printedModule: 'STR'
                    },
                    success: function(response) {},
                    error: function(xhr, status, error) {
                        console.error('Error logging print event:', error);
                    }
                });
            }

            // Select all
            $(document).on('click', '#select-all', function() {
                var rows = $('#myTable tbody tr:visible');
                var isChecked = $(this).is(':checked');
                rows.each(function() {
                    $(this).find('.select-row').prop('checked', isChecked);
                });
            });

            // Export STR as PDF
            $('#printButton-custom-str').click(function() {
                const selectedStrNos = [];
                $('input.select-row:checked').each(function() {
                    selectedStrNos.push($(this).val());
                });
                if (selectedStrNos.length > 0) {
                    window.location.href = '{{ route('export.str.pdf', ':sample_testing_report') }}'
                        .replace(':sample_testing_report', selectedStrNos.join(','));
                } else {
                    toastr.error('Please select at least one to get the STR PDF.');
                }
            });
        });
    </script>

    <script>
        $(document).on('change', '#sampleFilter', function() {
            var sample_id = $(this).val();
            $.ajax({
                url: "{{ route('getSampleBatch') }}",
                type: "GET",
                data: {
                    'token': '{{ csrf_token() }}',
                    sample_id: sample_id
                },
                success: function(response) {
                    if (response.success == true) {
                        $('#batchFilter').empty();
                        $('#batchFilter').append(
                            '<option value="" selected disabled>Select Batch</option>');
                        $.each(response.data, function(key, value) {
                            $('#batchFilter').append('<option value="' + value.id + '">' + value
                                .code + '</option>');
                        });
                    }
                }
            });
        });
    </script>

    <script>
        function loadContracts() {
            var type = $('#contract_type').val();
            var sample_id = $('#sampleFilter').val();
            $.ajax({
                url: "{{ route('getContract') }}",
                type: "GET",
                data: {
                    'token': '{{ csrf_token() }}',
                    'type': type,
                    'sample_id': sample_id
                },
                success: function(response) {
                    if (response.success == true) {
                        $('#contract_no').empty();
                        $('#contract_no').append('<option value="" selected disabled>Select Contract</option>');
                        $.each(response.data, function(key, value) {
                            $('#contract_no').append('<option value="' + value.id + '">' + value
                                .number + '</option>');
                        });
                    }
                }
            });
        }
        $(document).on('change', '#contract_type', loadContracts);
        $(document).on('change', '#sampleFilter', loadContracts);
    </script>
@endsection
