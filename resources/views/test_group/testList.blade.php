@extends('layouts.app')
@section('title', __('Associated Test Lists'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('Associated Test Lists')
            <small>@lang('Manage Associated Tests')</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">


        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
                <div class="col-md-12">
                    <!-- Custom Tabs -->
                    <div class="nav-tabs-custom">
                        <div class="tab-content">
                            <div class="tab-pane active">
                                <table id="dataTable" class="table dataTable table-striped ajax_view hide-footer">
                                    <thead>
                                        <tr>
                                            <th style="width:5%">@lang('method.hash_sign')</th>
                                            <th style="width:20%">@lang('method.test_name')</th>
                                            <th style="width:35%">@lang('Test Description')</th>
                                            <th style="width:10%" class="no-print">@lang('messages.action')</th>
                                        </tr>
                                    </thead>
                                    <tbody id="dataTable">
                                        @foreach ($testLists as $key => $testList)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $testList->name }}</td>
                                                <td>{{ $testList->description }}</td>
                                                @can('Sample Tests.associated_test.create')
                                                    <td>

                                                        <div class="btn-group">

                                                            <button type="button" class="btn btn-primary dropdown-toggle btn-xs"
                                                                data-toggle="dropdown" aria-expanded="true">
                                                                Actions <span class="caret"></span><span class="sr-only">Toggle
                                                                    Dropdown</span>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-left" role="menu">
                                                                <li>
                                                                    <a class="test-model" data-toggle="modal"
                                                                        data-target="#testModal" data-id="{{ $testList->id }}"><i
                                                                            class="fa fa-edit"></i> @lang('messages.edit')</a>

                                                                </li>
                                                            </ul>
                                                        </div>

                                                    </td>
                                                @endcan

                                            </tr>
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

    <div class="modal fade" id="testModal" tabindex="-1" role="dialog" aria-labelledby="testModalLabel"
        aria-hidden="true">
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

    <script type="text/javascript">
        $(document).ready(function() {
            $('.test-model').on('click', function() {
                var testId = $(this).data('id');
                $.ajax({
                    url: '{{ url('edit-test') }}',
                    type: 'get',
                    data: {
                        id: testId
                    }, // send the test ID to the controller
                    success: function(response) {
                        $("#testModal").html(response);
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            });
        });

        $(document).ready(function() {
            var table = $('.dataTable').DataTable({
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
                            if (rowCount % 25 === 0) {
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
                    },
                }, {
                    extend: 'csv',
                    text: 'Export to CSV',
                    className: 'buttons-csv',
                    exportOptions: {
                        columns: ':not(.no-print)'
                    }
                }, 'colvis']
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
                        printedModule: 'Tests List'
                    },
                    success: function(response) {},
                    error: function(xhr, status, error) {
                        console.error('Error logging print event:', error);
                    }
                });
            }
        });
    </script>
@endsection
