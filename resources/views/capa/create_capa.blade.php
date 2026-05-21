@extends('layouts.app')
@section('title', __('capa.capa_title'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>{{ __('capa.capa_title') }}
            <small>{{ __('capa.capa_manage') }}</small>
        </h1>

    </section>
    <style>
        .info-box {
            border-radius: 50px;
            /* Adjust this value to control the roundness */
        }

        .card {
            position: relative;
            right: -10px
        }
    </style>
    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            <div class="col-md-4 col-sm-6 col-xs-12 col-custom ">
                <div class="info-box info-box-new-style">
                    <span class="info-box-icon bg-aqua"><i class="fa-solid fa-share-from-square"
                            style="font-size: 2rem"></i></span>

                    <div class="info-box-content3 card">
                        <h5 class=""> {{ __('capa.capa_issue') }}</h5>
                        <h4 class=""> {{ $issues }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12 col-custom">
                <div class="info-box info-box-new-style">
                    <span class="info-box-icon bg-aqua"><i class="fa-solid fa-bars-progress" style="font-size: 2rem"></i></span>

                    <div class="info-box-content3 card">
                        <p class="info-box-number2">
                            <span class="">
                                <h5 class="">{{ __('capa.capa_progress') }}</h5>
                                <h4>{{ $progress }}</h4>

                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-sm-6 col-xs-12 col-custom">
                <div class="info-box info-box-new-style">
                    <span class="info-box-icon bg-aqua"><i class="fa-solid fa-check" style="font-size: 2rem"></i></span>

                    <div class="info-box-content3" style="position: relative; right: -10px">
                        <p class="info-box-number2 card">
                            <span>
                                <h5 class="">{{ __('capa.capa_completed') }}</h5>
                                <h4 class=""> {{ $completed }} </h4>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        @endcomponent

        @component('components.widget', ['class' => 'box-primary'])
            @can('capa.create')
                <div class="col-md-1 pull-right">
                    @if ($markTo && $markTo->markTo == '1')
                        <a class="btn btn-primary pull-right" disabled>
                            <i class="fas fa-add"></i> Add
                        </a>
                    @else
                        <a class="btn btn-primary capa-model pull-right" data-toggle="modal" data-target="#commentModal">
                            <i class="fa fa-plus"></i> Add
                        </a>
                    @endif
                </div>
            @endcan

            @can('capa.view')
                <div class="row" id="printSection">
                    <div class="col-md-12">
                        <div class="nav-tabs-custom">
                            <div class="tab-content">
                                <div class="tab-pane active">


                                    <table class="table dataTable table-striped ajax_view hide-footer">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>{{ __('capa.capa_date') }}</th>
                                                <th>{{ __('capa.capa_type') }}</th>
                                                <th>{{ __('capa.capa_desc') }}</th>
                                                <th>{{ __('capa.capa_assign') }}</th>
                                                <th>{{ __('capa.capa_status') }}</th>
                                                @can('capa.delete')
                                                    <th class="no-print">Action</th>
                                                @endcan
                                            </tr>
                                        </thead>
                                        <tbody>

                                            @foreach ($remarks as $key => $remark)
                                                @if ($remark->status == 'completed')
                                                    @php
                                                        $bg = 'label-success';
                                                    @endphp
                                                @endif

                                                @if ($remark->status == 'pending')
                                                    @php
                                                        $bg = 'label-warning';
                                                    @endphp
                                                @endif
                                                <tr>
                                                    <td>{{ $key + 1 }}</td>
                                                    <td>{{ $remark->created_at->format('d M,Y') }}</td>
                                                    <td>{{ $remark->type }}</td>
                                                    <td>{{ $remark->remarks }}</td>
                                                    <td>{{ optional($remark->user)->username }}</td>
                                                    <td>
                                                        <div class="label  {{ $bg }}">{{ $remark->status }}</div>
                                                    </td>
                                                    <td style="padding: 10px; text-align: left;">
                                                        <div class="dropdown">

                                                            <button class="btn btn-primary btn-xs dropdown-toggle" type="button"
                                                                id="actionDropdown" data-toggle="dropdown" aria-haspopup="true"
                                                                aria-expanded="false">
                                                                Actions <span class="caret"></span>
                                                            </button>
                                                            <div class="dropdown-menu" aria-labelledby="actionDropdown">
                                                                @can('capa.delete')
                                                                    <a class="dropdown-item btn deleteCapa"
                                                                        href="{{ action([\App\Http\Controllers\CapaController::class, 'destroy'], [$remark->id]) }}">
                                                                        <i class="fas fa-trash"></i> Delete
                                                                    </a>
                                                                @endcan
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
                </div>
            @endcan
        @endcomponent

        <div class="modal fade" id="commentModal" tabindex="-1" role="dialog" aria-labelledby="commentModalLabel"
            aria-hidden="true">
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
    <script>
        $(document).ready(function() {
            // Initialize DataTable
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
                                        true; // Set the flag when page break is added
                                }
                            });

                            // Apply fixed position styling to header if page break is added
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
                        },
                        customize: function(xlsx) {
                            var sheet = xlsx.xl.worksheets['sheet1.xml'];
                            $('row c[r^="D"]', sheet).each(function() {
                                if ($(this).text().includes(':')) {
                                    $(this).attr('s', '2');
                                }
                            });
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
                        printedModule: 'Capa'
                    },
                    success: function(response) {},
                    error: function(xhr, status, error) {
                        console.error('Error logging print event:', error);
                    }
                });
            }
        });

        $(document).ready(function() {
            $('.capa-model').on('click', function() {
                $.ajax({
                    url: '{{ action([\App\Http\Controllers\CapaController::class, 'create']) }}',
                    type: 'get', // or 'post' if necessary
                    success: function(response) {
                        $("#commentModal").html(response);
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });

            })
        })

        $(document).ready(function() {
            $('.deleteCapa').on('click', function(e) {
                e.preventDefault();
                swal({
                    title: LANG.sure,
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        var href = $(this).attr('href');
                        $.ajax({
                            url: href,
                            'type': 'get',
                            success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    window.location.href = window.location.href;
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });

                    }
                });
            })
        })
    </script>
@endsection
