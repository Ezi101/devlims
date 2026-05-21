<section class="content">
    {{-- @component('components.widget', ['class' => 'box-primary']) --}}
    <div class="tab-content">
        <div class="tab-pane active" id="">
            @can('str.create')
                <a class="btn btn-primary pull-right btn-modal "
                    data-href="{{ action([\App\Http\Controllers\STRController::class, 'create']) }}"
                    data-container=".str_report_create">
                    <i class="fa fa-plus"></i> @lang('messages.add')</a>
                <br><br>
            @endcan
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <div class="tab-content">
                    <div class="tab-pane active">
                        <table class="table dataTable ajax_view hide-footer">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>@lang('product.product')</th>
                                    <th>@lang('product.batch_no')</th>
                                    <th>@lang('product.str_no')</th>
                                    <th>@lang('product.contract_no')</th>
                                    <th>@lang('product.type')</th>
                                    <th>@lang('sale.status')</th>
                                    {{-- <th class="no-print">@lang('messages.action')</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rejectedStrs as $s)
                                    <tr
                                        data-url="{{ action([\App\Http\Controllers\STRController::class, 'show'], ['sample_testing_report' => $s->str_no]) }}">
                                        <td class="view">{{ $loop->iteration }}</td>
                                        <td class="view">{{ @$s->product->name }}</td>
                                        <td class="view">{{ @$s->batch->code }}</td>
                                        <td class="view">{{ @$s->str_no }}</td>
                                        <td class="view">{{ @$s->contract->number }}</td>
                                        <td class="view">
                                            @if (@$s->contract->type === 'supply')
                                                @php
                                                    $transactionCollection = collect($transaction_str);
                                                    // Get the instalments for the specific sample_id
                                                    $sampleTransaction = $transactionCollection->get($s->sample_id); // Use get() to fetch by key
                                                    $instalment = $sampleTransaction ? $sampleTransaction[0] : null; // Get the first instalment, if exists
                                                @endphp

                                                {{ ucwords(@$s->contract->type) }}
                                                (
                                                @if ($sampleTransaction)
                                                    @if ($instalment === 'instalments_1')
                                                        1st
                                                    @elseif($instalment === 'instalments_1_2')
                                                        1st & 2nd
                                                    @elseif($instalment === 'instalments_1_2_3')
                                                        1st,2nd & 3rd
                                                    @elseif($instalment === 'instalments_2_3')
                                                        2nd & 3rd
                                                    @elseif($instalment === 'instalments_2')
                                                        2nd
                                                    @elseif($instalment === 'instalments_3')
                                                        3rd
                                                    @elseif($instalment === 'instalments_4')
                                                        4th
                                                    @elseif($instalment === 'instalments_3_4')
                                                        3rd & 4th
                                                    @elseif($instalment === 'no_instalments')
                                                        No Inst
                                                    @else
                                                        No Inst
                                                    @endif
                                                @else
                                                    {{ ucwords(@$s->contract->type) }} {{-- Fallback to contract type if no instalment --}}
                                                @endif
                                                )
                                            @else
                                                {{ ucwords(@$s->contract->type) ?? '-' }}
                                            @endif
                                        </td>


                                        <td class="view">
                                            @if ($s->status == 'approved')
                                                @php
                                                    $status = __('lang_v1.approved');
                                                    $bg = 'bg-green';
                                                @endphp
                                            @elseif ($s->status == 'rejectd')
                                                @php
                                                    $status = __('lang_v1.rejected');
                                                    $bg = 'bg-red';
                                                @endphp
                                            @elseif ($s->status == 'pending')
                                                @php
                                                    $status = __('lang_v1.pending');
                                                    $bg = 'bg-info';
                                                @endphp
                                            @endif

                                            <span class="label {{ @$bg }}">{{ @$status }}</span>
                                        </td>

                                        {{-- <td>
                                        <div class="btn-group">
                                            @if ($s->status == 'approved' || ($s->status == 'rejected' && auth()->user()->can('str.view')))
                                            <a class="btn btn-primary btn-xs" href="{{ action([\App\Http\Controllers\STRController::class, 'show'], ['sample_testing_report' => $s->str_no]) }}">
                                                <i class="fa fa-eye"></i> @lang('messages.view')
                                            </a>
                                            @else
                                            <button type="button" class="btn btn-primary dropdown-toggle btn-xs" data-toggle="dropdown" aria-expanded="true">
                                                Actions <span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-left" role="menu">
                                                <li>
                                                    <a class="dropdown-item" href="{{ action([\App\Http\Controllers\STRController::class, 'show'], ['sample_testing_report' => $s->str_no]) }}">
                                                        <i class="fa fa-eye"></i> @lang('messages.view')
                                                    </a>
                                                </li>
                                                <li>
                                                    @if (auth()->user()->can('str.edit'))
                                                    <a class="dropdown-item" href="{{ action([\App\Http\Controllers\STRController::class, 'edit'], ['sample_testing_report' => $s->str_no]) }}">
                                                        <i class="fa fa-edit"></i> @lang('messages.edit')
                                                    </a>
                                                    @endif
                                                </li> --}}
                                        {{-- <li>
                                                    @if (auth()->user()->can('str.approve'))
                                                    <a class="dropdown-item" href="{{ route('str.status_update', ['str_no' => $s->str_no, 'status' => 'approved']) }}">
                                                        <i class="fa fa-check"></i> @lang('messages.approve')
                                                    </a>
                                                    @endif
                                                </li>
                                                <li>
                                                    @if (auth()->user()->can('str.reject'))
                                                    <a class="dropdown-item" href="{{ route('str.status_update', ['str_no' => $s->str_no, 'status' => 'rejected']) }}">
                                                        <i class="fa fa-multiply"></i> @lang('messages.reject')
                                                    </a>
                                                    @endif
                                                </li>
                                                <li>
                                                    @if (auth()->user()->can('str.remark'))
                                                    <a class="dropdown-item" href="{{ route('remarks', ['str_no' => $s->str_no]) }}">
                                                        <i class="fa fa-message"></i> @lang('messages.remark')
                                                    </a>
                                                    @endif
                                                </li> --}}

                                        {{-- @php
                                                $business_id = request()->session()->get('user.business_id');

                                                if (auth()->user()->hasRole('OC' . '#' . $business_id)) {
                                                $ptr_str_approval = \App\PTR_STR_Approval::with(['user' => function ($query) {
                                                $query->where('is_cmmsn_agnt', 0)
                                                ->select('id', DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"))
                                                ->whereHas("roles", function ($query) {
                                                $query->where(function ($subquery) {
                                                $subquery->where("name", 'like', "%Quality control%");
                                                });
                                                });
                                                }])
                                                ->where('remark_status', 'approved')
                                                ->where('ptr/str_no', $s->str_no)
                                                ->get();
                                                }  elseif (auth()->user()->hasRole('Report Compiler' . '#' . $business_id)) {
                                                $ptr_str_approval = \App\PTR_STR_Approval::with(['user' => function ($query) {
                                                $query->where('is_cmmsn_agnt', 0)
                                                ->select('id', DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"))
                                                ->whereHas("roles", function ($query) {
                                                $query->where(function ($subquery) {
                                                $subquery->where("name", 'like', "%Quality Assurance%");
                                                });
                                                });
                                                }])
                                                ->where('remark_status', 'approved')
                                                ->where('ptr/str_no', $s->str_no)
                                                ->get();
                                                } else {
                                                $ptr_str_approval = \App\PTR_STR_Approval::where('remark_status', 'approved')->where('ptr/str_no', $s->str_no)->get();
                                                }

                                                $ptr_str_approval = $ptr_str_approval->filter(function ($item) {
                                                return $item->user !== null;
                                                });

                                                @endphp

                                                @if (($ptr_str_approval->isNotEmpty() && !$ptr_str_approval->isEmpty()) ||
    auth()->user()->hasRole('Report Compiler' . '#' . $business_id))
                                                <li>
                                                    @if (auth()->user()->can('str.approve_with_remarks'))
                                                    <a class="dropdown-item btn btn-modal" data-href="{{ route('str_ptr_approval', ['ptr_str_no' => $s->str_no]) }}" data-container=".ptr_str_approval">
                                                        <i class="fa fa-message"></i> @lang('STR APPROVAL')
                                                    </a>
                                                    @endif
                                                </li>
                                                @endif

                                                <li>
                                                    <a class="dropdown-item" href="{{ route('logs.index', ['module' => 'str']) }}">
                                                        <i class="fa-solid fa-clock-rotate-left"></i> Logs
                                                    </a>
                                                </li>

                                            </ul>
                                            @endif
                                        </div>
                                    </td> --}}

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- @endcomponent --}}
</section>

<div class="modal fade str_report_create" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
<div class="modal fade str_report_remark" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
<div class="modal fade ptr_str_approval" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
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

        .page-break {
            page-break-before: always;
        }

        @page {
            margin-top: 20px;
            margin-bottom: 30px;
        }

    }
</style>

@section('javascript')
    <script>
        $(document).ready(function() {
            var table = $('.dataTable').DataTable({
                // order: [
                //     [0, 'desc']
                // ],
                buttons: [{
                    extend: 'print',
                    text: 'Print',
                    className: 'buttons-print',
                    exportOptions: {
                        columns: ':not(.no-print)'
                    },
                    customize: function(win) {
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
                                            <img style="margin-top:40px;" src="{{ asset('dummy/AFMS LOGO-01.png') }}" width="100px" />
                                            <div class="mt-5">
                                                <img src="data:image/png;base64,{{ DNS2D::getBarcodePNG(route('sample-testing-reports.index'), 'QRCODE', 3, 3, [39, 48, 54]) }}" style="width: 60px;margin-right:20px;margin-top:10px;">
                                            </div>
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
                            header.css('top', '-50px');
                            header.css('left', '0');
                            header.css('right', '0');
                            header.css('background-color', '#fff');
                            $('<style>.print-table { position: relative; top: 200px; bottom: 150px; }</style>')
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
                }]
            });
        });
    </script>
@endsection
