@extends('layouts.app')

@section('title', __('reagent.standard'))

@section('content')

<section class="content-header no-print">
    <h1>@lang('purchase.standard_log')</h1>
</section>
<style>.top-bar {
    display: flex !important;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap; 
    gap: 10px;
}

.dataTables_length,
.dt-buttons,
.dataTables_filter {
    display: flex !important;
    align-items: center;
}

.dataTables_length {
    margin-right: 15px;
}

.dataTables_filter {
    margin-left: 15px;
}


</style>
<section class="content no-print">
    @component('components.widget', ['class' => 'box-primary', 'title' => __('reagent.standard_stock_log_report')])
        @can('Standard.view')
            @slot('tool')
                <div class="box-tools">
                    <a class="btn btn-block btn-primary"
                        href="{{ action([\App\Http\Controllers\StandardController::class, 'recevie_stock']) }}">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </a>
                </div>
            @endslot
        @endcan

      <div style="overflow-y: auto; ">
    <table class="table table-bordered table-striped ajax_view" id="purchase_table" style="width: 100%;">
        <thead>
            <tr>
                <th>@lang('messages.date')</th>
                <th>@lang('product.working_standard')</th>
                <th>@lang('batch.id')</th>
                <th>@lang('purchase.potency ')</th>
                <th>@lang('purchase.exp_date')</th>
                <th>@lang('batch.firm_supplier')</th>
                <th>@lang('purchase.storage_condition')</th>
                <th>@lang('purchase.location')</th>
                <th>@lang('purchase.standard_type')</th>
                <th>@lang('purchase.transability')</th>
                <th>@lang('purchase.status')</th>
                <th class="no-print">@lang('purchase.action')</th>
            </tr>
        </thead>
    </table>
</div>


    @endcomponent

    <div class="modal fade product_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
    <div class="modal fade payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
    <div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>

    @include('purchase.partials.update_purchase_status_modal')

</section>

<section id="receipt_section" class="print_section"></section>

@stop

@section('javascript')
<script>
  $(document).ready(function() {
    var table = $('#purchase_table').DataTable({
        processing: true,
        serverSide: true,
        order: [[0, 'desc']],
        ajax: "{{ route('stock.index') }}",
        dom: '<"top-bar d-flex justify-content-between align-items-center"lBf>rtip',
        buttons: [
            {
                extend: 'print',
                text: '<i class="fa fa-print"></i> Print',
                className: 'btn btn-sm btn-secondary',
                exportOptions: {
                    columns: ':not(.no-print)'
                },
                customize: function(win) {
                    $(win.document.body).find('h1').remove();
                    var defaultTitle = $('title').text();
                    var reportTitle = defaultTitle.split(' - ')[0] + ' Report';

                    var header = $(`
                        <header style="padding: 10px;">
                            <div class="row header" style="display: flex; justify-content: space-between; align-items: center;">
                                <div class="col-md-2 mt-3">
                                    <img src="{{ asset('dummy/paklogo4.png') }}" width="100px" />
                                </div>
                                <div class="col-md-8" style="text-align: center;">
                                    <h4 style="font-weight: bold;">ARMED FORCES MEDICAL STORES LABORATORY</h4>
                                    <hr style="margin: 5px 0;">
                                    <h5 style="font-weight: bold;">${reportTitle}</h5>
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
                }
            },
            { extend: 'excel', text: '<i class="fa fa-file-excel"></i> Export to Excel', className: 'btn btn-sm btn-secondary', exportOptions: { columns: ':not(.no-print)' } },
            { extend: 'pdf', text: '<i class="fa fa-file-pdf"></i> Export to PDF', className: 'btn btn-sm btn-secondary', exportOptions: { columns: ':not(.no-print)' } },
            { extend: 'csv', text: '<i class="fa fa-file-csv"></i> Export to CSV', className: 'btn btn-sm btn-secondary', exportOptions: { columns: ':not(.no-print)' } },
            { extend: 'colvis', text: '<i class="fa fa-columns"></i> Column Visibility', className: 'btn btn-sm btn-secondary' }
        ], 
        columns: [
            { data: 'created_at', name: 'created_at' },
            { data: 'name', name: 'name' },
            { data: 'transaction_code', name: 'transaction_code' },
              { data: 'potency', name: 'potency' },
            { data: 'expiry_date', name: 'expiry_date' },
            { data: 'supplier_business_name', name: 'supplier_business_name' },
            { data: 'item_type', name: 'item_type' },
            { data: 'location', name: 'location' },
             { data : 'standard_type' , name : 'standard_type'},
            { data: 'transability', name: 'transability' },
           
            { 
                data: 'status',
                name: 'status',
                render: function(data, type, row) {
                    return data;
                }
            },
            {
    data: 'status',
    name: 'status',
    className: 'no-print',
    render: function(data, type, row) {
        if (data === "draft") {
            return `
            <div class="dropdown">
                <button class="btn btn-primary btn-xs dropdown-toggle" type="button" data-toggle="dropdown" 
                        aria-haspopup="true" aria-expanded="false" 
                        style=" font-size: 12px;">
                    Actions <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" style="min-width: 100px;">
                    <li>
                        <a class="dropdown-item edit-btn" href="#" data-id="${row.id}" style="padding: 5px 10px;">
                            <i class="fa fa-edit"></i> Edit
                        </a>
                    </li>
                </ul>
            </div>`;
        } else if (data === "Received by AFMSL") {
            return `
            <div class="dropdown">
                <button class="btn btn-primary btn-xs dropdown-toggle" type="button" data-toggle="dropdown" 
                        aria-haspopup="true" aria-expanded="false" 
                        style=" font-size: 12px;">
                    Actions <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" style="min-width: 100px;">
                    <!-- No options available -->
                </ul>
            </div>`;
        }
        return '';
    }
}

        ],
        language: {
            emptyTable: "No Data Available",
            processing: "Loading..."
        }
    });

    $('.dropdown-toggle').dropdown();
});

$(document).on('click', '.edit-btn', function(e) {
    e.preventDefault();
    var stockId = $(this).data('id');
    window.location.href = "{{ url('stock/edit') }}/" + stockId;
});










</script>

@endsection

