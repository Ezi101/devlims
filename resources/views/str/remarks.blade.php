{{-- @component('components.widget', ['class' => 'box-primary'])
    <div class="row">
        <div class="col-md-12">
            {!! Form::open([
                'url' => action([\App\Http\Controllers\STRController::class, 'remarks_store']),
                'method' => 'post',
                'id' => 'section_add_form',
            ]) !!}

            <div class="modal-body">

                <input type="hidden" name="str_no" value="{{ $strs->str_no }}">

                <div class="form-group">
                    {!! Form::label('remarks_to', __('messages.remarks_to') . ':') !!}
                    {!! Form::select(
                        'remarks_to',
                        $users->pluck('full_name', 'id'),
                        !empty($duplicate_product->status) ? $duplicate_product->status : null,
                        ['class' => 'form-control', 'required', 'placeholder' => __('messages.please_select')],
                    ) !!}
                </div>

                <div class="form-group">
                    {!! Form::label('remarks_description', __('lang_v1.description') . ':') !!}
                    {!! Form::textarea(
                        'remarks_description',
                        !empty($duplicate_product->product_description) ? $duplicate_product->product_description : null,
                        ['class' => 'form-control', 'required'],
                    ) !!}
                </div>

            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">@lang('Send')</button>
            </div>

            {!! Form::close() !!}
        </div>
    </div>
@endcomponent --}}
@extends('layouts.app')
@section('title', __('lang_v1.str_remark_on'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('lang_v1.str_remark_on')
            <small>{{ $strs->str_no }}</small>
        </h1>

    </section>
    <!-- Main content -->
    <section class="content">

        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
                <div class="col-md-12">
                    <a data-toggle="modal" data-target="#remarkSTRModal" class="btn btn-primary pull-right remark-model mb-5">
                        <i class="fas fa-add"></i> @lang('lang_v1.str_add')
                    </a>


                    {{-- <a class="btn btn-primary mb-5 pull-right" href="{{ url('/view/remark/message/' . $strs->str_no) }}" >
                    Remark
                </a> --}}
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <table class="table dataTable ajax_view hide-footer">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>@lang('lang_v1.str_date_time')</th>
                                {{-- <th>Type</th> --}}
                                {{-- <th>Description</th> --}}
                                <th>@lang('lang_v1.str_remark_by')</th>
                                <th>@lang('lang_v1.str_remark_to')</th>
                                <th>@lang('lang_v1.str_action')</th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach ($remarks as $key => $remark)
                                <tr>

                                    <td>{{ $key + 1 }}</td>
                                    <td class="date-cell">{{ $remark->created_at->format('d M,Y') }}
                                        {{ $remark->created_at->format('h:m:i') }}</td>
                                    {{-- <td class="remark-cell">{{ $strs->str_no }}</td> --}}
                                    {{-- <td class="remark-cell">{{ substr($remark->remark, 0, 50) }}{{ strlen($remark->remark) > 50 ? "..." : "" }}</td> --}}
                                    <td class="remark-by-cell">{{ optional($remark->remarkBy)->first_name }}</td>
                                    <td class="remark-by-cell">{{ optional($remark->remarkTo)->first_name }}</td>

                                    <td style="padding: 10px; text-align: left;">
                                        <div class="dropdown">

                                            <button class="btn btn-primary btn-xs dropdown-toggle" type="button"
                                                id="actionDropdown" data-toggle="dropdown" aria-haspopup="true"
                                                aria-expanded="false">
                                                Actions <span class="caret"></span>
                                            </button>
                                            <div class="dropdown-menu" aria-labelledby="actionDropdown">
                                                <a href="{{ url('/view/remark/message/' . $remark->remark_to . '/by' . '/' . $remark->remark_by . '/' . $strs->str_no) }}"
                                                    class="dropdown-item btn">
                                                    <i class="fas fa-eye"></i> @lang('lang_v1.str_view')
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
            <div class="modal fade" id="remarkSTRModal" tabindex="-1" role="dialog" aria-labelledby="remarkSTRModalLabel"
                aria-hidden="true">
            </div>
        @endcomponent
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
            .dataTable {
                position: relative;
                top: 200px;

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
@stop
@section('javascript')
    <script>
        $(document).ready(function() {

            $('.remark-model').on('click', function() {
                $.ajax({
                    url: '{{ action([\App\Http\Controllers\STRController::class, 'createRemarkModel'], ['id' => $strs->str_no]) }}',
                    type: 'GET',
                    success: function(response) {
                        $("#remarkSTRModal").html(response);
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            });
        });
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
                            $(win.document.body).find('h1').remove();

                            var defaultTitle = $('title').text();
                            var reportTitle = defaultTitle.split(' - ')[0] + ' Report';

                            $(win.document.body).prepend(
                                `
                                <header style="position:fixed; top: -50px; left: 0; right: 0; background-color: #fff; padding: 10px; z-index: 1000;">
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
                                                <img src="data:image/png;base64,{{ DNS2D::getBarcodePNG(route('Capa.index'), 'QRCODE', 3, 3, [39, 48, 54]) }}" style="width: 60px;margin-right:20px;margin-top:10px;">
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
                                if (rowCount % 20 === 0) {
                                    currentPage++;
                                    $(this).after('<div class="page-break"></div>');
                                }
                            });

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
            $('.select2').select2();
        })
    </script>
@endsection


{{-- <div class="modal-dialog" role="document">
    <div class="modal-content">

        {!! Form::open([
            'url' => action([\App\Http\Controllers\STRController::class, 'remarks_store']),
            'method' => 'post',
            'id' => 'section_add_form',
            ]) !!}

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">@lang('lang_v1.remark_str_report')</h4>
            </div>

            <div class="modal-body">

                <input type="hidden" name="str_no" value="{{ $strs->str_no }}">
                @if (auth()->user()->can('update-str'))
                <div class="form-group">
                    {!! Form::label('status', __('messages.status') . ':') !!}
                    {!! Form::select(
                        'status',
                        ['approved' => __('messages.approve'), 'rejected' => __('messages.rejected')],
                        !empty($duplicate_product->status) ? $duplicate_product->status : null,
                        ['class' => 'form-control' , 'placeholder' => __('messages.please_select')],
                    ) !!}
                </div>
                @endif

                <div class="form-group">
                    {!! Form::label('remarks_to', __('messages.remarks_to') . ':') !!}
                    {!! Form::select(
                        'remarks_to',
                        $users->pluck('full_name', 'id'),
                        !empty($duplicate_product->status) ? $duplicate_product->status : null,
                        ['class' => 'form-control' , 'required' ,'placeholder' => __('messages.please_select')]
                    ) !!}
                </div>

                <div class="form-group">
                    {!! Form::label('remarks_description', __('lang_v1.description') . ':') !!}
                    {!! Form::textarea(
                        'remarks_description',
                        !empty($duplicate_product->product_description) ? $duplicate_product->product_description : null,
                        ['class' => 'form-control' , 'required']
                    ) !!}
                </div>





            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">@lang('messages.print')</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
            </div>

        {!! Form::close() !!}

    </div><!-- /.modal-content -->
</div> --}}
<!-- /.modal-dialog -->
