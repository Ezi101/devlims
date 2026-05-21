@extends('layouts.app')
@section('title', __('method.batchtests'))

@section('content')

    <section class="content-header">
        <h1>@lang('method.tests')
            <small>@lang('method.managebatchtest') ({{ $product->code }})</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">

        @can('Sample Tests.issue_test_view')
            @component('components.widget', ['class' => 'box-primary'])
                <div class="row">
                    <div class="col-md-12">

                        <!-- Custom Tabs -->
                        <div class="nav-tabs-custom">
                            <div class="tab-content">
                                <div class="tab-pane active">
                                    <table class="table dataTable table-bordered table-striped ajax_view hide-footer">
                                        <thead>
                                            <tr>
                                                <th style="width:5%">@lang('method.hash_sign')</th>
                                                <th style="width:10%">@lang('business.product')</th>
                                                <th style="width:20%">@lang('method.test_name')</th>
                                                <th style="width:10%">@lang('method.test_id')</th>
                                                <th style="width:35%">@lang('method.sub_test')</th>
                                                <th style="width:10%">@lang('method.status')</th>
                                                {{-- <th style="width:10%" class="no-print">@lang('messages.action')</th> --}}
                                            </tr>
                                        </thead>
                                        <tbody id="dataTableBody">
                                            {{-- @dd($tests); --}}
                                            @foreach ($tasks as $t)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $t->samplereading->samples->name }}</td>
                                                    <td>{{ $t->samplereading->testmethod->name }}</td>
                                                    <td>{{ $t->samplereading->test }}</td>
                                                    <td>@php
                                                        $group = App\SampleReading::with('groups')
                                                            ->where('test', $t->samplereading->test)
                                                            ->get();
                                                    @endphp
                                                        @foreach ($group as $g)
                                                            {{ $g->groups->name }} ,
                                                        @endforeach
                                                    </td>
                                                    <td>
                                                        @if ($t->samplereading->status == 'completed')
                                                            @php
                                                                $status = __('project::lang.completed');
                                                                $bg = 'bg-green';
                                                            @endphp
                                                        @elseif ($t->samplereading->status == 'cancelled')
                                                            @php
                                                                $status = __('project::lang.cancelled');
                                                                $bg = 'bg-red';
                                                            @endphp
                                                        @elseif ($t->samplereading->status == 'on_hold')
                                                            @php
                                                                $status = __('project::lang.on_hold');
                                                                $bg = 'bg-yellow';
                                                            @endphp
                                                        @elseif ($t->samplereading->status == 'in_progress')
                                                            @php
                                                                $status = __('project::lang.in_progress');
                                                                $bg = 'bg-info';
                                                            @endphp
                                                        @elseif ($t->samplereading->status == 'not_started')
                                                            @php
                                                                $status = __('project::lang.not_started');
                                                                $bg = 'bg-red';
                                                            @endphp
                                                        @endif

                                                        <span class="label {{ @$bg }}">{{ @$status }}</span>
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
            @endcomponent
        @endcan
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

        .buttons-csv::before,
        .buttons-excel::before,
        .buttons-print::before,
        .buttons-pdf::before {
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            margin-right: 5px;
            color: grey;
        }

        .buttons-csv,
        .buttons-excel,
        .buttons-print,
        .buttons-pdf {
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
            .dataTable {
                position: relative;
                top: 200px;
                bottom: 150px;
            }

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
    @php $asset_v = env('APP_VERSION'); @endphp

    <script type="text/javascript">
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
                        $(win.document.body).find('h1').remove();
                        var batch_code =
                            "{{ $product->code }}"; // Ensure to enclose PHP variable within double quotes
                        var defaultTitle = $('title').text();
                        var reportTitle = defaultTitle.split(' - ')[0] + ' Report - ' +
                            batch_code;

                        $(win.document.body).prepend(
                            `
                                <header style="position:fixed; top: -50px; left: 0; right: 0; background-color: #fff; padding: 10px; z-index: 1000;">
                                    <div class="row header" style="display: flex; justify-content:       space-between; align-items: center;">
                                        <div class="col-md-2 mt-3">
                                            <img src="{{ asset('dummy/paklogo4.png') }}" width="100px" />
                                        </div>
                                        <div class="col-md-8" style="text-align: center;">
                                            <h4 style="font-weight: bold;">ARMED FORCES MEDICAL STORES LABORATORY</h4>
                                            <hr style="margin: 5px 0;"> <!-- Add horizontal line here -->
                                            <h5 style="font-weight: bold;">${reportTitle} </h5> <!-- Add dynamic report title here -->
                                        </div>
                                        <div class="col-md-2 mt-3" style="text-align: end;">
                                            <img style="margin-top:40px;" src="{{ asset('dummy/AFMS LOGO-01.png') }}" width="100px" />
                                            <div class="mt-5">
                                                <img src="data:image/png;base64,{{ DNS2D::getBarcodePNG(route('oos.index'), 'QRCODE', 3, 3, [39, 48, 54]) }}" style="width: 60px;margin-right:20px;margin-top:10px;">
                                            </div>
                                        </div>
                                    </div>

                                </header>

                                `
                        );


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
                            }
                        });

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
                }]
            });
        });
    </script>
@endsection
