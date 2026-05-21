@extends('layouts.app')
@section('title', __('product.demand_req'))

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('product.demand_req')
            <small>@lang('product.demand_req')</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-solid', 'box-primary'])
            <div class="table-filter mb-3">
                <a class="btn btn-lg btn-secondary mb-10" data-toggle="collapse" href="#filterCollapse" role="button"
                    aria-expanded="false" aria-controls="filterCollapse">
                    <i class="fas fa-filter"></i> @lang('method.filters')
                </a>
                @can('demand.create')
                    <a class="btn btn-primary pull-right"
                        href="{{ action([\App\Http\Controllers\DemandController::class, 'create']) }}">
                        <i class="fa fa-plus"></i> @lang('messages.add')</a>
                @endcan
                <br><br>
            </div>
        @endcomponent

        @component('components.widget', ['class' => 'box-primary'])
            <div class="row" id="printSection">
                <div class="col-md-12">
                    <div class="nav-tabs-custom">
                        <div class="tab-content">
                            <div class="tab-pane active">
                                <table class="table dataTable table-striped ajax_view hide-footer" id="demand_table">
                                    <thead>
                                        <tr>
                                            <th class="no-print" style="display: none;">@lang('method.id')</th>
                                            <th>@lang('method.date_time')</th>
                                            <th>@lang('method.standard')</th>
                                            <th>@lang('method.chemical')</th>
                                            <th>@lang('method.quantity')</th>
                                            <th>@lang('method.potency')</th>
                                            <th>@lang('method.status')</th>
                                            {{-- <th>Created at</th> --}}
                                            <th class="no-print">@lang('lang_v1.actions')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($transactions as $transaction)
                                            @foreach ($transaction->purchase_lines as $purchase_line)
                                                <tr>
                                                    <td style="display: none;">{{ $transaction->id }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($transaction->created_at)->format('M d, Y H:i:s') }}
                                                    </td>
                                                    <td>
                                                        @if ($transaction->product_type == 'standard')
                                                            {{ @$transaction->product->name ?? '-' }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($transaction->product_type == 'reagent')
                                                            {{ @$transaction->product->name ?? '-' }}
                                                        @endif
                                                    </td>
                                                    <td>{{ $purchase_line->quantity }}</td>

                                                    <td>{{ $transaction->potency ?? '-' }}</td>
                                                    <td>
                                                        @if ($transaction->status == 'pending')
                                                            <span class="label bg-aqua">@lang('lang_v1.pending')</span>
                                                        @elseif($transaction->status == 'approved')
                                                            <span class="label bg-green">@lang('lang_v1.approved')</span>
                                                        @elseif($transaction->status == 'rejected')
                                                            <span class="label bg-red">@lang('lang_v1.rejected')</span>
                                                        @else
                                                            <span class="label bg-aqua">@lang('lang_v1.pending')</span>
                                                        @endif
                                                    </td>

                                                    {{-- <td>{{ $transaction->created_at }}</td> --}}

                                                    <td style="padding: 10px; text-align: left;">
                                                        <div class="dropdown">
                                                            <button class="btn btn-primary btn-xs dropdown-toggle"
                                                                type="button" id="actionMenu_{{ $transaction->id }}"
                                                                data-toggle="dropdown" aria-haspopup="true"
                                                                aria-expanded="false">
                                                                @lang('lang_v1.actions') <span class="caret"></span>
                                                            </button>
                                                            <div class="dropdown-menu"
                                                                aria-labelledby="actionMenu_{{ $transaction->id }}">
                                                                @can('demand.update')
                                                                    <a class="dropdown-item"
                                                                        href="{{ route('demands.edit', ['id' => $transaction->id]) }}">
                                                                        <i class="fas fa-edit"></i> View
                                                                    </a>
                                                                @endcan
                                                                @can('demand.approve')
                                                                    <a class="dropdown-item"
                                                                        href="{{ route('demands.approve', ['id' => $transaction->id]) }}">
                                                                        <i class="fas fa-signature"></i> Approve
                                                                    </a>
                                                                @endcan
                                                                @can('demand.remark')
                                                                    <a class="dropdown-item remark-button"
                                                                        href="{{ route('demand.reject', ['id' => $transaction->id]) }}"
                                                                        data-id="{{ $transaction->id }}">
                                                                        <i class="fa fa-message"></i> @lang('Remark')
                                                                    </a>
                                                                @endcan

                                                                <!-- Add more actions here as needed -->
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endcomponent
    </section>

    <!-- Modal Structure -->
    <div class="modal fade" id="remarkModal" tabindex="-1" role="dialog" aria-labelledby="remarkModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <!-- Content will be injected here by AJAX -->
            </div>
        </div>
    </div>
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
            var table = $('#demand_table').DataTable({
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
                ]
            });
            $(document).on('click', '.remark-button', function(e) {
                e.preventDefault();
                var transactionId = $(this).data('id');

                $.ajax({
                    url: '{{ route('demand.reject', ['id' => ':id']) }}'.replace(':id',
                        transactionId),
                    method: 'GET',
                    success: function(response) {
                        $('#remarkModal .modal-content').html(response);
                        $('#remarkModal').modal('show');
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
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
                        printedModule: 'Demand Request'
                    },
                    success: function(response) {

                    },
                    error: function(xhr, status, error) {
                        console.error('Error logging print event:', error);
                    }
                });
            }
            // Initialize Select2
            $('.select2').each(function() {
                $(this).select2({
                    dropdownParent: $(this).parent(),
                });
            });
        });
    </script>
@endsection
