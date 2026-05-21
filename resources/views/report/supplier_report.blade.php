@extends('layouts.app')
@section('title', __('report.supplier_report'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header no-print">
        <h1>@lang('report.supplier_report')
            <small></small>
        </h1>

    </section>

    <!-- Main content -->
    <section class="content no-print">
        <div class="box box-solid" id="accordion">
            <div class="box-header no-border" style="cursor: pointer;" data-toggle="collapse" data-parent="#accordion"
                href="#collapseFilter">
                <h3 class="box-title">

                    <i class="fa-solid fa-filter"></i>
                    Filters
                </h3>
            </div>
            <div id="collapseFilter" class="panel-collapse collapse">
                <div class="box-body">
                    <div class="row">
                            <div class="filter-wrapper">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        {!! Form::label('purchase_list_filter_supplier_id', __('purchase.supplier') . ':') !!}
                                        {!! Form::select('purchase_list_filter_supplier_id', $suppliers, null, [
                                            'class' => 'form-control select2',
                                            'style' => 'width:100%',
                                            'placeholder' => __('lang_v1.all'),
                                        ]) !!}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        {!! Form::label('purchase_list_filter_report_id', __('report.supplier_report_samples') . ':') !!}
                                        {!! Form::select('purchase_list_filter_report_id', $products, null, [
                                            'class' => 'form-control select2',
                                            'style' => 'width:100%',
                                            'placeholder' => __('lang_v1.all'),
                                        ]) !!}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    @php
                                        $contract_type = [
                                            'tender' => 'Tender',
                                            'supply' => 'Supply',
                                            'other' => 'Other',
                                        ];
                                    @endphp
                                    <div class="form-group">
                                        {!! Form::label('purchase_list_filter_contract_type', __('report.contract') . ':') !!}
                                        {!! Form::select('purchase_list_filter_contract_type', $contract_type, null, [
                                            'class' => 'form-control select2 purchase_list_filter_contract_type',
                                            'style' => 'width:100%',
                                            'placeholder' => __('lang_v1.all'),
                                            'id' => 'contract_type_select',
                                        ]) !!}
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        {!! Form::label('purchase_list_filter_contract_no', __('report.contract_no') . ':') !!}
                                        {!! Form::select('purchase_list_filter_contract_no', $contract_numbers, null, [
                                            'class' => 'form-control select2 purchase_list_filter_contract_no',
                                            'style' => 'width:100%',
                                            'placeholder' => __('lang_v1.all'),
                                            'id' => 'contract_no_select',
                                        ]) !!}
                                    </div>
                                </div>

                                <div class="col-md-3 inst_hide">
                                    <div class="form-group">
                                        @php
                                            $staticOptions_dosage_form = [
                                                '' => 'Please Select',
                                                'no_instalment' => 'No Installment',
                                                'instalments_1' => '1st Installment',
                                                'instalments_1_2' => '1st & 2nd Instalment',
                                                'instalments_1_2_3' => '1st,2nd & 3rd Instalment',
                                                'instalments_2_3' => '2nd & 3rd Instalment',
                                                'instalments_2' => '2nd Installment',
                                                'instalments_3' => '3rd Installment',
                                                'instalments_4' => '4th Installment',
                                                'instalments_3_4' => '3rd & 4th Instalment',
                                            ];
                                        @endphp
                                        {!! Form::label('purchase_list_filter_instalments', __('report.instalments') . ':') !!}
                                        {!! Form::select('purchase_list_filter_instalments', $staticOptions_dosage_form, null, [
                                            'class' => 'form-control select2 purchase_list_filter_instalments',
                                            'style' => 'width:100%',
                                            'placeholder' => __('lang_v1.all'),
                                        ]) !!}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" id="filter_button" class="btn btn-primary"
                                        style="margin-top: 25px;" onclick="applyFilters()">
                                        @lang('lang_v1.filter')
                                    </button>
                                </div>

                            </div>
                    </div>
                </div>
            </div>
        </div>

        @component('components.widget', ['class' => 'box-primary', 'title' => __('purchase.all_purchases')])
            <table class="table table-bordered table-striped" id="purchase_table">
                <thead>
                    <tr>
                        <th>@lang('product.product')</th>
                        <th>@lang('batch.b_no')</th>
                        <th>@lang('report.supplier_name')</th>
                        {{-- <th id="instalments">@lang('report.instalments')</th> --}}
                        <th>@lang('report.contract_no')</th>
                        <th>@lang('batch.b_quantity')</th>
                        <th>@lang('messages.date')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $item)
                    {{-- @dd($item); --}}
            <tr>
                <td>{{ $item->product_name }}</td>
                <td>{{ $item->batch_code }}</td>
                <td>{{ $item->supplier_names ?? '-' }}</td>
                {{-- <td>{{ $item->instalments }}</td> --}}
                <td>
                    {{ $item->contract_number ?? '-' }}
                </td>
                <td>{{ number_format($item->batch_quantity, 0) }}</td>
                <td>{{ \Carbon\Carbon::parse($item->date)->format('d-m-Y') }}</td> 
            </tr>
            @endforeach

                </tbody>
            </table>
        @endcomponent
    </section>

    <section id="receipt_section" class="print_section"></section>

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
            var purchase_table = $('#purchase_table').DataTable({
                processing: true,
                serverSide: false,
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fa fa-file-excel-o"></i> Excel',
                        titleAttr: 'Download as Excel'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fa fa-file-pdf-o"></i> PDF',
                        titleAttr: 'Download as PDF'
                    },
                    {
                        extend: 'csvHtml5',
                        text: '<i class="fa fa-file-text-o"></i> CSV',
                        titleAttr: 'Download as CSV'
                    },
                    {
                        extend: 'print',
                        text: '<i class="fa fa-print"></i> Print',
                        titleAttr: 'Print Table'
                    }
                ],
                paging: true,
                searching: true,
                lengthChange: true,
                columns: [{
                        data: 'product_name',
                        name: 'product_name'
                    },
                    {
                        data: 'batch_code',
                        name: 'batch_code'
                    },
                    {
                        data: 'supplier_name',
                        name: 'supplier_name'
                    },
                    {
                        data: 'instalments',
                        name: 'instalments'
                    },
                    {
                        data: 'contract_number',
                        name: 'contract_number'
                    },
                    {
                        data: 'batch_quantity',
                        name: 'batch_quantity'
                    },
                    {
                        data: 'date',
                        name: 'date'
                    }
                ]
            });
        });
    </script>
    
    <script type="text/javascript">
        $(document).ready(function() {

            $('#filter_button').click(function() {
                var instalments = $('#purchase_list_filter_instalments').val();
                var contract_no = $('.purchase_list_filter_contract_no').val();
                var supplier = $('#purchase_list_filter_supplier_id').val();
                var sample = $('#purchase_list_filter_report_id').val();

                $.ajax({
                    url: "{{ route('reports.supplier-report') }}",
                    method: "GET",
                    data: {
                        instalments: instalments,
                        contract_no: contract_no,
                        supplier: supplier,
                        sample: sample
                    },
                    success: function(response) {
                        console.log(response.data);
                        $('#purchase_table tbody').empty();

                        if (response.data && response.data.length > 0) {
                            $.each(response.data, function(index, purchase) {
                                var row = '<tr>';
                                row += '<td>' + purchase.product_name + '</td>';
                                row += '<td>' + purchase.batch_code + '</td>';
                                row += '<td>' + purchase.supplier_name + '</td>';
                                row += '<td>' + purchase.instalments + '</td>';
                                row += '<td>' + purchase.contract_number + '</td>';
                                row += '<td>' + purchase.batch_quantity + '</td>';
                                row += '<td>' + purchase.date + '</td>';
                                row += '</tr>';

                                $('#purchase_table tbody').append(row);
                            });
                        } else {
                            $('#purchase_table tbody').append(
                                '<tr><td colspan="7" class="text-center">No records found.</td></tr>'
                            );
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log("Error in AJAX request:", error);
                    }
                });
            });
        });
    </script>

    <script>
        $('.select2').select2();

        $('#contract_type_select').change(function() {
            var contractType = $(this).val();

            $.ajax({
                url: "{{ route('reports.supplier-report') }}",
                method: "GET",
                data: {
                    contract_type: contractType
                },
                success: function(response) {
                    if (response.contract_numbers && Object.keys(response.contract_numbers).length >
                        0) {
                        $('#contract_no_select').empty().append(
                            '<option value="">Select Contract No</option>');
                        $.each(response.contract_numbers, function(id, number) {
                            $('#contract_no_select').append('<option value="' + id + '">' +
                                number + '</option>');
                        });

                        $('#contract_no_select').trigger('change');
                    } else {
                        $('#contract_no_select').empty().append(
                            '<option value="">No contracts available</option>');
                    }
                },
                error: function() {
                    alert('Error fetching contract numbers.');
                }
            });
        });
    </script>


@endsection
