@extends('layouts.app')
@section('title', __('lang_v1.str_remark'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        {{-- <h1>@lang('lang_v1.str_remark')
            <small>@lang('')</small>
        </h1> --}}
    </section>
    <style>
        .card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .card-header {
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
        }

        .left-info {
            flex-grow: 1;
        }

        .right-info {
            text-align: right;
        }

        .card-content {
            color: #555;
            font-size: 16px;
            line-height: 1.5;
        }
    </style>
    <!-- Main content -->
    <section class="content">

        {{-- @component('components.widget', ['class' => 'box-primary'])
            <button class="btn btn-primary" data-toggle="collapse" href="#giveRemark" aria-controls="giveRemark">
                Give Remark
            </button>

            <div class="row">
                <div class="col-md-12">
                    {!! Form::open([
                        'url' => action([\App\Http\Controllers\STRController::class, 'remarks_store']),
                        'method' => 'post',
                        'id' => 'section_add_form',
                        'class' => 'collapse multi-collapse',
                        'id' => 'giveRemark',
                    ]) !!}

                    <div class="modal-body">

                        <input type="hidden" name="id" value="{{ (is_object($strs) && isset($strs->str_no)) ? $strs->str_no : '0' }}" >
                        {{-- <input type="hidden" name="remark_on" value="STR"> 
                        <input type="hidden" name="remark_on" value="{{ $remark_on }}">

                        <div class="form-group">
                            {!! Form::label('remarks_to', __('messages.remarks_to') . ':') !!}
                            {!! Form::select(
                                'remarks_to[]',
                                $users->pluck('full_name', 'id'),
                                !empty($duplicate_product->status) ? $duplicate_product->status : null,
                                [
                                    'class' => 'form-control select2',
                                    'required',
                                    'multiple' => 'true',
                                    'placeholder' => __('messages.please_select'),
                                    'style' => 'width:30%;'
                                ],
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

        @component('components.widget', ['class' => 'box-primary'])
            <a data-toggle="modal" data-target="#remarkSTRModal" class="btn btn-primary pull-right remark-model mb-5">
                @lang('lang_v1.str_reply')
            </a>

            <div class="row" id="printSection">
                <div class="col-md-12">
                    <table class="table dataTable ajax_view hide-footer">
                        <thead style="display: none">
                            <tr>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($remarks as $index => $remark)
                                <tr>
                                    <input type="hidden" id="remarkTo"
                                        value="{{ auth::user()->id && auth::user()->id != $remark->remarkTo->id ? $remark->remarkTo->id : $remark->remarkBy->id }}">
                                    <td style="border: none">
                                        <div class="card ">
                                            <div class="card-header">
                                                <div class="left-info">
                                                    <p><b>@lang('lang_v1.str_from')</b>: {{ optional($remark->remarkBy)->first_name }}
                                                    </p>
                                                    <p><b>@lang('lang_v1.str_to')</b>: {{ optional($remark->remarkTo)->first_name }}
                                                    </p>
                                                </div>

                                                <div class="right-info">
                                                    <p><b>@lang('lang_v1.str_date')</b>: {{ $remark->created_at->format('d-m-Y') }}</p>
                                                    <p style="position: relative; right: 15px;"><b>@lang('lang_v1.str_time')</b>:
                                                        {{ $remark->created_at->format('H:m:i') }}</p>
                                                </div>
                                            </div>
                                            <div class="card-content">
                                                <p class="description">{{ substr($remark->remark, 0, 500) }}</p>
                                                <p class="collapse multi-collapse" id="description{{ $index }}">
                                                    {{ substr($remark->remark, 500) }}</p>
                                                <a class="" data-toggle="collapse" href="#description{{ $index }}"
                                                    aria-controls="description{{ $index }}">@lang('lang_v1.str_view_message')</a>
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

@endsection
@section('javascript')
    <script>
        $(document).ready(function() {

            $('.remark-model').on('click', function() {
                var remarkTo = $("#remarkTo").val();
                $.ajax({
                    url: '{{ action([\App\Http\Controllers\STRController::class, 'createRemarkModel'], ['id' => @$strs->str_no]) }}',
                    type: 'GET',
                    data: {
                        remarkTo: remarkTo
                    },
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

            $('.select2').select2();
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
    </script>
@endsection
