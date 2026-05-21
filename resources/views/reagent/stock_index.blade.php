@extends('layouts.app')
@section('title', __('reagent.reagent'))

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header no-print">
        <h1>@lang('reagent.receive_stock_log')
            <small></small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content no-print">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('reagent.rec_chem_log_report')])
            @can('purchase.create')
                @slot('tool')
                    <div class="box-tools">
                        <a class="btn btn-block btn-primary"
                            href="{{ action([\App\Http\Controllers\ReagentController::class, 'recevie_stock']) }}">
                            <i class="fa fa-plus"></i> @lang('messages.add')</a>
                    </div>
                @endslot
            @endcan

            <!-- Barcode Search Field -->
            <div class="row mb-3" style="position: relative;">
                <div class="col-md-4" style="position: absolute; right: 20px; top: 0px;">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-barcode"></i>
                        </span>
                        <input type="text" class="form-control" id="barcodeSearch" placeholder="@lang('messages.scan_barcode_or_enter_sample')" autofocus>
                        <span class="input-group-btn">
                            <button class="btn btn-default" type="button" id="clearSearch">
                                <i class="fa fa-times"></i>
                            </button>
                        </span>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="chemicalsTable">
                    <thead>
                        <tr>
                            <th>@lang('messages.action')</th>
                            <th>@lang('messages.date')</th>
                            <th style="display:none;">Transaction ID</th>
                            <th>@lang('product.chemical_name')</th>
                            <th>@lang('purchase.batch')</th>
                            <th>@lang('purchase.quantity')</th>
                            <th>@lang('product.brand')</th>
                            <th>@lang('purchase.supplier')</th>
                            <th>@lang('lang_v1.rec_by')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($received_chemicals as $chemical)
                            <tr>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-primary dropdown-toggle btn-xs"
                                            data-toggle="dropdown" aria-expanded="false">
                                            @lang('messages.actions')
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-left" role="menu">
                                            <li>
                                                <a href="{{ route('chemical.label', ['id' => $chemical->id]) }}"
                                                    target="_blank">
                                                    <i class="fas fa-barcode"></i> Label
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                                <td data-order="{{ $chemical->transaction_date }}">
                                    {{ @format_datetime($chemical->transaction_date) }}
                                </td>
                                <td style="display:none;" class="transaction-id">{{ $chemical->id }}</td>

                                <td>{{ $chemical->product->name ?? '' }}</td>
                                <td>{{ $chemical->batch->code ?? '' }}</td>
                                <td>{{ $chemical->batch->quantity ?? '' }}</td>
                                <td>{{ $chemical->brand->name ?? '' }}</td>
                                <td>{{ $chemical->contact->supplier_business_name ?? '' }}</td>
                                <td>{{ $chemical->status }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endcomponent
    </section>
@endsection


@section('javascript')

    <script>
        $(document).ready(function() {
            var table = $('#chemicalsTable').DataTable({
                "dom": '<"top"l>rt<"bottom"ip>',
                "order": [
                    [1, 'desc']
                ],
                "columnDefs": [{
                        "orderable": false,
                        "targets": 0
                    },
                    {
                        "visible": false,
                        "targets": 2
                    } // Hide Transaction ID column (index 2)
                ]
            });

            $('#barcodeSearch').on('input', function() {
                var input = $(this);
                var searchTerm = input.val().trim();
                table.columns().search(''); // Clear filters

                if ($.isNumeric(searchTerm)) {
                    // Search by Transaction ID (Column 2)
                    table.column(2).search('^' + searchTerm + '$', true, false).draw();

                    // After draw, fetch the chemical name from the visible row and replace the input value
                    table.on('draw', function() {
                        var row = table.row({
                            filter: 'applied'
                        }).node();
                        if (row) {
                            var chemicalName = $(row).find('td').eq(2)
                        .text(); // Column 3 is chemical name
                            input.val(chemicalName); // Replace input with name
                        }
                    });
                } else {
                    // If it's not numeric, search by name (Column 3)
                    table.column(2).search(searchTerm, true, false).draw();
                }
            });



            $('#clearSearch').click(function() {
                $('#barcodeSearch').val('');
                table.search('').columns().search('').draw();
                $('#barcodeSearch').focus();
            });

            // Optional: Trigger search on Enter
            $('#barcodeSearch').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $(this).trigger('input');
                }
            });
        });
    </script>
@endsection
