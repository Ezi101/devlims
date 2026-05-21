@extends('layouts.app')
@section('title', __('user.users'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('user.users')
            <small>@lang('user.manage_users')</small>
        </h1>
        <!-- <ol class="breadcrumb">
                            <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
                            <li class="active">Here</li>
                        </ol> -->
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            @can('user.create')
                @slot('tool')
                    <div class="box-tools">
                        <a class="btn btn-block btn-primary"
                            href="{{ action([\App\Http\Controllers\ManageUserController::class, 'create']) }}">
                            <i class="fa fa-plus"></i> @lang('messages.add')</a>
                    </div>
                @endslot
            @endcan
            @can('user.view')
                <div class="table-responsive">
                    <table class="table table-striped" id="users_table">
                        <thead>
                            <tr>
                                <th>@lang('business.username')</th>
                                <th>@lang('user.name')</th>
                                <th>@lang('user.role')</th>
                                <th>@lang('business.email')</th>
                                <th class="no-print">@lang('messages.action')</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @endcan
        @endcomponent

        <div class="modal fade user_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

    </section>
    <!-- /.content -->
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
@stop
@section('javascript')
    <script type="text/javascript">
        //Roles table
        $(document).ready(function() {
            var users_table = $('#users_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/users',
                columnDefs: [{
                    "targets": [4],
                    "orderable": false,
                    "searchable": false
                }],
                "columns": [{
                        "data": "username"
                    },
                    {
                        "data": "full_name"
                    },
                    {
                        "data": "role"
                    },
                    {
                        "data": "email"
                    },
                    {
                        "data": "action"
                    }
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
                                if (rowCount % 27 === 0) {
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
            $(document).on('click', 'button.delete_user_button', function() {
                swal({
                    title: LANG.sure,
                    text: LANG.confirm_delete_user,
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        var href = $(this).data('href');
                        var data = $(this).serialize();
                        $.ajax({
                            method: "DELETE",
                            url: href,
                            dataType: "json",
                            data: data,
                            success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    users_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
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
                        printedModule: 'User Management'
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
