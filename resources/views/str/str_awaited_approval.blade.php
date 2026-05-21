@extends('layouts.app')
@section('title', __('Awaited Approval STRs'))

@section('content')
    <section class="content-header">
        <h1>@lang('lang_v1.str')
            <small>@lang('lang_v1.manage_awaited_approval_str_report')</small>
        </h1>
    </section>

    <section class="content">
        <div class="box-body">
            @include('str.partials._str_nav')

            @include('str.partials.strs_table', ['strs' => $awaitedApprovalStrs])
        </div>
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
    var table = $('.dataTable').DataTable({
        "order": [[0, "desc"]],
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
                        if (rowCount % 16 === 0) {
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
            },
            'colvis'
        ],
        "drawCallback": function(settings) {
            // Reapply click event listener after pagination
            $('tr').off('click').on('click', function() {
                var url = $(this).data('url');
                if (url) {
                    window.location.href = url;
                }
            });
        }
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
                        printedModule: 'STR'
                    },
                    success: function(response) {},
                    error: function(xhr, status, error) {
                        console.error('Error logging print event:', error);
                    }
                });
            }
});
    </script>
   
    <script>
        $(document).ready(function() {
    $('tr').click(function() {
        var url = $(this).data('url');
        if (url) {
            window.location.href = url;
        }
    });

    $(document).click(() => $('.dropdown-menu').hide());

    $('.action-button').click(function() {
        $('.dropdown-menu').hide();
        $(this).next('.dropdown-menu').toggle();
    });
});


    </script>
    
@endsection
