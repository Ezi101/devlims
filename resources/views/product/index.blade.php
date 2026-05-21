@extends('layouts.app')
@section('title', __('product.samples'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('product.samples')
            <small>@lang('lang_v1.manage_products')</small>
        </h1>

    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters')])
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('category_id', __('product.category') . ':') !!}
                            {!! Form::select('category_id', $categories, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%',
                                'id' => 'product_list_filter_category_id',
                                'placeholder' => __('lang_v1.all'),
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('sub_category_id', __('product.sub_category') . ':') !!}
                            {!! Form::select('sub_category_id', $subCategories, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%',
                                'id' => 'product_list_filter_sub_category_id',
                                'placeholder' => __('lang_v1.all'),
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('unit_id', __('product.unit') . ':') !!}
                            {!! Form::select('unit_id', $units, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%',
                                'id' => 'product_list_filter_unit_id',
                                'placeholder' => __('lang_v1.all'),
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('brand_id', __('product.brand') . ':') !!}
                            {!! Form::select('brand_id', $brands, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%',
                                'id' => 'product_list_filter_brand_id',
                                'placeholder' => __('lang_v1.all'),
                            ]) !!}
                        </div>
                    </div>
                    {{-- for the the filter for ptr status --}}
                    <div class="col-md-3">
                        <div class="filter-wrapper">
                            <label for="status-filter">@lang('method.ptr_status')</label>
                            <select id="status-filter" class="form-control select2" style="width: 100%;">
                                <option value="">@lang('lang_v1.all_status')</option>
                                <option value="pending">@lang('lang_v1.in_progerss')</option>
                                <option value="approved">@lang('lang_v1.approved')</option>
                                <option value="rejected">@lang('lang_v1.rejected')</option>
                            </select>
                        </div>
                    </div>
                    @can('others.location_filter_view')
                        <div class="col-md-3" id="location_filter">
                            <div class="form-group">
                                {!! Form::label('location_id', __('purchase.business_location') . ':') !!}
                                {!! Form::select('location_id', $business_locations, $afmsl_location->id, [
                                    'class' => 'form-control select2',
                                    'style' => 'width:100%',
                                    'placeholder' => __('lang_v1.all'),
                                ]) !!}
                            </div>
                        </div>
                    @endcan
                @endcomponent
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <!-- Custom Tabs -->
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#product_list_tab" data-toggle="tab" aria-expanded="true"><i class="fa fa-cubes"
                                    aria-hidden="true"></i> @lang('lang_v1.all_products')</a>
                        </li>
                        @can('stock_report.view')
                            <li>
                                <a href="#product_stock_report" data-toggle="tab" aria-expanded="true"><i
                                        class="fa fa-clipboard" aria-hidden="true"></i> @lang('report.stock_report')</a>
                            </li>
                        @endcan
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane active" id="product_list_tab">
                            @can('activity_log.view')
                                <a class="btn btn-default pull-right no-print" style="margin-left: 10px;"
                                    href="{{ route('logs.index', ['module' => 'sample management']) }}">
                                    <i class="fa-solid fa-clock-rotate-left no-print"></i> @lang('messages.logs')
                                </a>
                            @endcan
                            {{-- @if ($is_admin)
                                <a class="btn btn-success pull-right margin-left-10"
                                    href="{{ action([\App\Http\Controllers\ProductController::class, 'downloadExcel']) }}"><i
                                        class="fa fa-download"></i> @lang('lang_v1.download_excel')</a>
                            @endif --}}
                            @can('product.create')
                                <a class="btn btn-primary pull-right"
                                    href="{{ action([\App\Http\Controllers\ProductController::class, 'create']) }}">
                                    <i class="fa fa-plus"></i> @lang('messages.add')</a>
                                <br><br>
                            @endcan
                            @include('product.partials.product_list')
                        </div>
                        @can('stock_report.view')
                            <div class="tab-pane" id="product_stock_report">
                                @include('report.partials.stock_report_table')
                            </div>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" id="is_rack_enabled" value="{{ $rack_enabled }}">

        <div class="modal fade product_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

        <div class="modal fade" id="view_product_modal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
        </div>

        <div class="modal fade" id="opening_stock_modal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
        </div>

        {{-- @if ($is_woocommerce)
            @include('product.partials.toggle_woocommerce_sync_modal')
        @endif --}}
        @include('product.partials.edit_product_location_modal')

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
@endsection

@section('javascript')
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>

    <script>
        $(document).ready(function() {
            function fetchSubcategories(categoryId) {
                $.ajax({
                    url: '{{ route('fetch-subcategories') }}',
                    method: 'GET',
                    data: {
                        category_id: categoryId
                    },
                    dataType: 'json',
                    success: function(response) {
                        $('#product_list_filter_sub_category_id').empty();
                        $('#product_list_filter_sub_category_id').append($('<option>', {
                            value: '',
                            text: '{{ __('lang_v1.all') }}'
                        }));
                        $.each(response, function(index, subcategory) {
                            $('#product_list_filter_sub_category_id').append($('<option>', {
                                value: subcategory.id,
                                text: subcategory.name
                            }));
                        });
                        $('#product_list_filter_sub_category_id').trigger('change');
                        if (response.length > 0) {
                            $('#product_list_filter_sub_category_id').val(response[0].id).trigger(
                                'change');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching subcategories:', error);
                    }
                });
            }

            $('#product_list_filter_category_id').change(function() {
                var categoryId = $(this).val();
                if (categoryId) {
                    fetchSubcategories(categoryId);
                } else {
                    $('#product_list_filter_sub_category_id').empty();
                }
            });
        });
    </script>


    <script type="text/javascript">
        $(document).ready(function() {
            product_table = $('#product_table').DataTable({
                processing: true,
                serverSide: true,
                aaSorting: [
                    [1, 'desc']
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
                                if (rowCount % 10 === 0) {
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
                ],
                // scrollY: "75vh",
                // scrollX: true,
                // scrollCollapse: true,
                "ajax": {
                    "url": "/samples",
                    "data": function(d) {
                        d.type = $('#product_list_filter_type').val();
                        d.category_id = $('#product_list_filter_category_id').val();
                        d.brand_id = $('#product_list_filter_brand_id').val();
                        d.unit_id = $('#product_list_filter_unit_id').val();
                        d.tax_id = $('#product_list_filter_tax_id').val();
                        d.active_state = $('#active_state').val();
                        d.not_for_selling = $('#not_for_selling').is(':checked');
                        d.location_id = $('#location_id').val();
                        d.ptr_status = $('#status-filter').val(); 
                        if ($('#repair_model_id').length == 1) {
                            d.repair_model_id = $('#repair_model_id').val();
                        }

                        if ($('#woocommerce_enabled').length == 1 && $('#woocommerce_enabled').is(
                                ':checked')) {
                            d.woocommerce_enabled = 1;
                        }

                        d = __datatable_ajax_callback(d);
                    }
                },
                columnDefs: [{
                    "targets": [0],
                    "orderable": false,
                    "searchable": false
                }],
                columns: [
                  
                    {
                        data: 'action',
                        name: 'action',
                        className: 'no-print'
                    },
                    {
                        data: 'date_created',
                        name: 'date_created',
                        visible: false,
                        searchable: false,
                      

                    },
                    {
                        data: 'product',
                        name: 'products.name',
                        searchable: true,

                    },
                    {
                        data: 'generic_names',
                        name: 'g_name.name',
                        searchable: true,

                    },
                    {
                        data: 'product_locations',
                        name: 'product_locations'
                    },
                  
                    {
                        data: 'category',
                        name: 'products.name'
                    },
                   
                    {
                        data: 'sub_category',
                        searchable: false,
                        className: 'no-print',
                    },


                    {
                        name: 'unit',
                        data: 'unit',
                        searchable: false,
                    },
                    {
                        name: 'ptr_status',
                        data: 'ptr_status',
                        render: function(data, type, row) {
                            if (data === 'approved') {
                                return '<span class="badge bg-green">Yes</span>';
                            } else if (data === 'in progress') {
                                return '<span class="badge bg-yellow">In Process</span>';
                            } else if (data === 'rejected') {
                                return '<span class="badge bg-red">No</span>';
                            } else {
                                return '<span class="badge bg-red">No</span>';
                            }
                        },
                        searchable: false
                    },

                   
                ],
                createdRow: function(row, data, dataIndex) {
                    if ($('input#is_rack_enabled').val() == 1) {
                        var target_col = 0;
                        @can('product.delete')
                            target_col = 1;
                        @endcan
                        $(row).find('td:eq(' + target_col + ') div').prepend(
                            '<i style="margin:auto;" class="fa fa-plus-circle text-success cursor-pointer no-print rack-details" title="' +
                            LANG.details + '"></i>&nbsp;&nbsp;');
                    }
                    $(row).find('td:eq(0)').attr('class', 'selectable_td');
                },
                fnDrawCallback: function(oSettings) {
                    __currency_convert_recursively($('#product_table'));
                },
            });
            // Array to track the ids of the details displayed rows
            var detailRows = [];

            $('#product_table tbody').on('click', 'tr i.rack-details', function() {
                var i = $(this);
                var tr = $(this).closest('tr');
                var row = product_table.row(tr);
                var idx = $.inArray(tr.attr('id'), detailRows);

                if (row.child.isShown()) {
                    i.addClass('fa-plus-circle text-success');
                    i.removeClass('fa-minus-circle text-danger');

                    row.child.hide();

                    // Remove from the 'open' array
                    detailRows.splice(idx, 1);
                } else {
                    i.removeClass('fa-plus-circle text-success');
                    i.addClass('fa-minus-circle text-danger');

                    row.child(get_product_details(row.data())).show();

                    // Add to the 'open' array
                    if (idx === -1) {
                        detailRows.push(tr.attr('id'));
                    }
                }
            });

            $('#opening_stock_modal').on('hidden.bs.modal', function(e) {
                product_table.ajax.reload();
            });

            $('table#product_table tbody').on('click', 'a.delete-product', function(e) {
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
                            method: "DELETE",
                            url: href,
                            dataType: "json",
                            success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    location.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    }
                });
            });

            $(document).on('click', '#delete-selected', function(e) {
                e.preventDefault();
                var selected_rows = getSelectedRows();

                if (selected_rows.length > 0) {
                    $('input#selected_rows').val(selected_rows);
                    swal({
                        title: LANG.sure,
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    }).then((willDelete) => {
                        if (willDelete) {
                            $('form#mass_delete_form').submit();
                        }
                    });
                } else {
                    $('input#selected_rows').val('');
                    swal('@lang('lang_v1.no_row_selected')');
                }
            });

            $(document).on('click', '#deactivate-selected', function(e) {
                e.preventDefault();
                var selected_rows = getSelectedRows();

                if (selected_rows.length > 0) {
                    $('input#selected_products').val(selected_rows);
                    swal({
                        title: LANG.sure,
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    }).then((willDelete) => {
                        if (willDelete) {
                            var form = $('form#mass_deactivate_form')

                            var data = form.serialize();
                            $.ajax({
                                method: form.attr('method'),
                                url: form.attr('action'),
                                dataType: 'json',
                                data: data,
                                success: function(result) {
                                    if (result.success == true) {
                                        toastr.success(result.msg);
                                        product_table.ajax.reload();
                                        form
                                            .find('#selected_products')
                                            .val('');
                                    } else {
                                        toastr.error(result.msg);
                                    }
                                },
                            });
                        }
                    });
                } else {
                    $('input#selected_products').val('');
                    swal('@lang('lang_v1.no_row_selected')');
                }
            })

            $(document).on('click', '#edit-selected', function(e) {
                e.preventDefault();
                var selected_rows = getSelectedRows();

                if (selected_rows.length > 0) {
                    $('input#selected_products_for_edit').val(selected_rows);
                    $('form#bulk_edit_form').submit();
                } else {
                    $('input#selected_products').val('');
                    swal('@lang('lang_v1.no_row_selected')');
                }
            })

            $('table#product_table tbody').on('click', 'a.activate-product', function(e) {
                e.preventDefault();
                var href = $(this).attr('href');
                $.ajax({
                    method: "get",
                    url: href,
                    dataType: "json",
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            product_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
            });

            $(document).on('change',
                '#product_list_filter_type, #product_list_filter_category_id, #product_list_filter_brand_id, #product_list_filter_unit_id, #product_list_filter_tax_id, #location_id, #active_state, #repair_model_id',
                function() {
                    if ($("#product_list_tab").hasClass('active')) {
                        product_table.ajax.reload();
                    }

                    if ($("#product_stock_report").hasClass('active')) {
                        stock_report_table.ajax.reload();
                    }
                });

            $(document).on('ifChanged', '#not_for_selling, #woocommerce_enabled', function() {
                if ($("#product_list_tab").hasClass('active')) {
                    product_table.ajax.reload();
                }

                if ($("#product_stock_report").hasClass('active')) {
                    stock_report_table.ajax.reload();
                }
            });

            $('#product_location').select2({
                dropdownParent: $('#product_location').closest('.modal')
            });

        });
     

        $(document).on('shown.bs.modal', 'div.view_product_modal, div.view_modal, #view_product_modal',
            function() {
                var div = $(this).find('#view_product_stock_details');
                if (div.length) {
                    $.ajax({
                        url: "{{ action([\App\Http\Controllers\ReportController::class, 'getStockReport']) }}" +
                            '?for=view_product&product_id=' + div.data('product_id'),
                        dataType: 'html',
                        success: function(result) {
                            div.html(result);
                            __currency_convert_recursively(div);
                        },
                    });
                }
                __currency_convert_recursively($(this));
            });
        var data_table_initailized = false;
        $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
            if ($(e.target).attr('href') == '#product_stock_report') {
                if (!data_table_initailized) {
                    //Stock report table
                    var stock_report_cols = [{
                            data: 'action',
                            name: 'action',
                            searchable: false,
                            orderable: false,
                            visible: false,
                            className: 'no-print',
                        },
                        {
                            data: 'sku',
                            name: 'variations.sub_sku'
                        },
                        {
                            data: 'product',
                            name: 'p.name'
                        },
                        // {
                        //     data: 'variation',
                        //     name: 'variation'
                        // },
                        {
                            data: 'category_name',
                            name: 'c.name'
                        },
                        {
                            data: 'location_name',
                            name: 'l.name'
                        },
                        // { data: 'unit_price', name: 'variations.sell_price_inc_tax' },
                        {
                            data: 'stock',
                            name: 'stock',
                            searchable: false
                        },
                    ];
                    // if ($('th.stock_price').length) {
                    //     stock_report_cols.push({ data: 'stock_price', name: 'stock_price', searchable: false });
                    //     stock_report_cols.push({ data: 'stock_value_by_sale_price', name: 'stock_value_by_sale_price', searchable: false, orderable: false });
                    //     stock_report_cols.push({ data: 'potential_profit', name: 'potential_profit', searchable: false, orderable: false });
                    // }

                    // stock_report_cols.push({ data: 'total_sold', name: 'total_sold', searchable: false });
                    stock_report_cols.push({
                        data: 'total_transfered',
                        name: 'total_transfered',
                        searchable: false,
                        visible: false,

                    });
                    stock_report_cols.push({
                        data: 'total_adjusted',
                        name: 'total_adjusted',
                        searchable: false,
                        visible: false,


                    });
                    // stock_report_cols.push({
                    //     data: 'product_custom_field1',
                    //     name: 'p.product_custom_field1'
                    // });
                    // stock_report_cols.push({
                    //     data: 'product_custom_field2',
                    //     name: 'p.product_custom_field2'
                    // });
                    // stock_report_cols.push({
                    //     data: 'product_custom_field3',
                    //     name: 'p.product_custom_field3'
                    // });
                    // stock_report_cols.push({
                    //     data: 'product_custom_field4',
                    //     name: 'p.product_custom_field4'
                    // });

                    if ($('th.current_stock_mfg').length) {
                        stock_report_cols.push({
                            data: 'total_mfg_stock',
                            name: 'total_mfg_stock',
                            searchable: false
                        });
                    }
                    stock_report_table = $('#stock_report_table').DataTable({
                        order: [
                            [1, 'asc']
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
                                    $(win.document.body).find('.print-table tr').each(function(
                                        index) {
                                        rowCount++;
                                        if (rowCount % 10 === 0) {
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
                        ],
                        processing: true,
                        serverSide: true,
                        scrollY: "75vh",
                        scrollX: true,
                        scrollCollapse: true,
                        ajax: {
                            url: '/reports/stock-report',
                            data: function(d) {
                                d.location_id = $('#location_id').val();
                                d.category_id = $('#product_list_filter_category_id').val();
                                d.brand_id = $('#product_list_filter_brand_id').val();
                                d.unit_id = $('#product_list_filter_unit_id').val();
                                d.type = $('#product_list_filter_type').val();
                                d.active_state = $('#active_state').val();
                                d.not_for_selling = $('#not_for_selling').is(':checked');
                                if ($('#repair_model_id').length == 1) {
                                    d.repair_model_id = $('#repair_model_id').val();
                                }
                            }
                        },
                        columns: stock_report_cols,
                        fnDrawCallback: function(oSettings) {
                            __currency_convert_recursively($('#stock_report_table'));
                        },
                        "footerCallback": function(row, data, start, end, display) {
                            var footer_total_stock = 0;
                            var footer_total_sold = 0;
                            var footer_total_transfered = 0;
                            var total_adjusted = 0;
                            var total_stock_price = 0;
                            var footer_stock_value_by_sale_price = 0;
                            var total_potential_profit = 0;
                            var footer_total_mfg_stock = 0;
                            for (var r in data) {
                                footer_total_stock += $(data[r].stock).data('orig-value') ?
                                    parseFloat($(data[r].stock).data('orig-value')) : 0;

                                footer_total_sold += $(data[r].total_sold).data('orig-value') ?
                                    parseFloat($(data[r].total_sold).data('orig-value')) : 0;

                                footer_total_transfered += $(data[r].total_transfered).data(
                                        'orig-value') ?
                                    parseFloat($(data[r].total_transfered).data('orig-value')) : 0;

                                total_adjusted += $(data[r].total_adjusted).data('orig-value') ?
                                    parseFloat($(data[r].total_adjusted).data('orig-value')) : 0;

                                total_stock_price += $(data[r].stock_price).data('orig-value') ?
                                    parseFloat($(data[r].stock_price).data('orig-value')) : 0;

                                footer_stock_value_by_sale_price += $(data[r].stock_value_by_sale_price)
                                    .data('orig-value') ?
                                    parseFloat($(data[r].stock_value_by_sale_price).data(
                                        'orig-value')) : 0;

                                total_potential_profit += $(data[r].potential_profit).data(
                                        'orig-value') ?
                                    parseFloat($(data[r].potential_profit).data('orig-value')) : 0;

                                footer_total_mfg_stock += $(data[r].total_mfg_stock).data(
                                        'orig-value') ?
                                    parseFloat($(data[r].total_mfg_stock).data('orig-value')) : 0;
                            }

                            $('.footer_total_stock').html(__currency_trans_from_en(footer_total_stock,
                                false));
                            $('.footer_total_stock_price').html(__currency_trans_from_en(
                                total_stock_price));
                            $('.footer_total_sold').html(__currency_trans_from_en(footer_total_sold,
                                false));
                            $('.footer_total_transfered').html(__currency_trans_from_en(
                                footer_total_transfered, false));
                            $('.footer_total_adjusted').html(__currency_trans_from_en(total_adjusted,
                                false));
                            $('.footer_stock_value_by_sale_price').html(__currency_trans_from_en(
                                footer_stock_value_by_sale_price));
                            $('.footer_potential_profit').html(__currency_trans_from_en(
                                total_potential_profit));
                            if ($('th.current_stock_mfg').length) {
                                $('.footer_total_mfg_stock').html(__currency_trans_from_en(
                                    footer_total_mfg_stock, false));
                            }
                        },
                    });
                    data_table_initailized = true;
                } else {
                    stock_report_table.ajax.reload();
                }
            } else {
                product_table.ajax.reload();
            }
        });
        $('#status-filter').on('change', function() {
     product_table.ajax.reload();
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
                    printedModule: 'Sample Management'
                },
                success: function(response) {},
                error: function(xhr, status, error) {
                    console.error('Error logging print event:', error);
                }
            });
        }
        $(document).on('click', '.update_product_location', function(e) {
            e.preventDefault();
            var selected_rows = getSelectedRows();

            if (selected_rows.length > 0) {
                $('input#selected_products').val(selected_rows);
                var type = $(this).data('type');
                var modal = $('#edit_product_location_modal');
                if (type == 'add') {
                    modal.find('.remove_from_location_title').addClass('hide');
                    modal.find('.add_to_location_title').removeClass('hide');
                } else if (type == 'remove') {
                    modal.find('.add_to_location_title').addClass('hide');
                    modal.find('.remove_from_location_title').removeClass('hide');
                }

                modal.modal('show');
                modal.find('#product_location').select2({
                    dropdownParent: modal
                });
                modal.find('#product_location').val('').change();
                modal.find('#update_type').val(type);
                modal.find('#products_to_update_location').val(selected_rows);
            } else {
                $('input#selected_products').val('');
                swal('@lang('lang_v1.no_row_selected')');
            }
        });

        $(document).on('submit', 'form#edit_product_location_form', function(e) {
            e.preventDefault();
            var form = $(this);
            var data = form.serialize();

            $.ajax({
                method: $(this).attr('method'),
                url: $(this).attr('action'),
                dataType: 'json',
                data: data,
                beforeSend: function(xhr) {
                    __disable_submit_button(form.find('button[type="submit"]'));
                },
                success: function(result) {
                    if (result.success == true) {
                        $('div#edit_product_location_modal').modal('hide');
                        toastr.success(result.msg);
                        product_table.ajax.reload();
                        $('form#edit_product_location_form')
                            .find('button[type="submit"]')
                            .attr('disabled', false);
                    } else {
                        toastr.error(result.msg);
                    }
                },
            });
        });
    </script>
@endsection
