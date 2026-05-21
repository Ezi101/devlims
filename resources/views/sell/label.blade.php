<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('barcode.print_labels') }}</title>
    @include('layouts.partials.css')
    <link rel="stylesheet" src="{{ asset('css/all.css') }}">
</head>

<body>

    <section class="content-header">
        <h1>{{ __('barcode.print_labels') }} </h1>
    </section>

    <section class="content no-print">
        {!! Form::open(['url' => '#', 'method' => 'post', 'id' => 'preview_setting_form', 'onsubmit' => 'return false']) !!}
        <div class="row">
            <div class="col-sm-5">

                @component('components.widget', ['class' => 'box-primary'])
                    <table class="table table-bordered table-striped table-condensed" id="product_table">
                        <thead>
                            <tr>
                                <th>@lang('batch.batches')</th>
                                <th>@lang('barcode.lab_name')</th>
                                <th>@lang('barcode.no_of_labels')
                                    <button class="btn btn-default btn-xs autoFillField" data-field="quantity"
                                        type="button">
                                        <i class="fa-solid fa-arrow-down-wide-short"></i>
                                    </button>
                                </th>


                                {{-- @if (request()->session()->get('business.enable_lot_number') == 1)
								<th>@lang( 'lang_v1.lot_number' )</th>
							@endif
							@if (request()->session()->get('business.enable_product_expiry') == 1) --}}
                                {{-- <th>@lang( 'product.exp_date' )</th> --}}
                                {{-- @endif --}}
                                {{-- <th>@lang('lang_v1.packing_date')</th> --}}
                                {{-- <th>@lang('lang_v1.selling_price_group')</th> --}}
                            </tr>
                        </thead>
                        <tbody id="tableBodyCreate">
                            @include('sell.partials.show_table_row', ['index' => 0])
                        </tbody>
                    </table>
                @endcomponent
            </div>
            <div class="col-sm-7">

                @component('components.widget', ['class' => 'box-primary'])
                    <div class="row">
                        <div class="col-md-12" style="display: none">
                            <table class="table table-bordered">
                                <tr>
                                    <td>
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" checked name="print[name]" value="1">
                                                <b>@lang('barcode.print_name')</b>
                                            </label>
                                        </div>

                                        <div class="input-group">
                                            <div class="input-group-addon"><b>@lang('lang_v1.size')</b></div>
                                            <input type="text" class="form-control" name="print[name_size]"
                                                value="15">
                                        </div>
                                    </td>

                                    <td>
                                        {{-- @if (request()->session()->get('business.enable_product_expiry') == 1) --}}
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" checked name="print[exp_date]" value="1">
                                                <b>@lang('lang_v1.print_exp_date')</b>
                                            </label>
                                        </div>

                                        <div class="input-group">
                                            <div class="input-group-addon"><b>@lang('lang_v1.size')</b></div>
                                            <input type="text" class="form-control" name="print[exp_date_size]"
                                                value="12">
                                        </div>
                                        {{-- @endif --}}
                                    </td>

                                    <td>
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" checked name="print[mfg_date]" value="1">
                                                <b>@lang('lang_v1.print_packing_date')</b>
                                            </label>
                                        </div>

                                        <div class="input-group">
                                            <div class="input-group-addon"><b>@lang('lang_v1.size')</b></div>
                                            <input type="text" class="form-control" name="print[mgf_date_size]"
                                                value="12">
                                        </div>
                                    </td>

                                    {{-- <td>
							<div class="checkbox">
							    <label>
							    	<input type="checkbox" checked name="print[business_name]" value="1"> <b>@lang( 'barcode.organization_name' )</b>
							    </label>
							</div>

							<div class="input-group">
      							<div class="input-group-addon"><b>@lang( 'lang_v1.size' )</b></div>
								<input type="text" class="form-control" 
									name="print[business_name_size]" 
									value="20">
							</div>
						</td> --}}

                                    {{-- <td>
							<div class="checkbox">
							    <label>
							    	<input type="checkbox" checked name="print[variations]" value="1"> <b>@lang( 'barcode.print_variations' )</b>
							    </label>
							</div>

							<div class="input-group">
      							<div class="input-group-addon"><b>@lang( 'lang_v1.size' )</b></div>
								<input type="text" class="form-control" 
									name="print[variations_size]" 
									value="17">
							</div>
						</td> --}}

                                </tr>

                                <tr>




                                    {{-- <td>
							@if (request()->session()->get('business.enable_lot_number') == 1)
							
								<div class="checkbox">
								    <label>
								    	<input type="checkbox" checked name="print[lot_number]" value="1"> <b>@lang( 'lang_v1.print_lot_number' )</b>
								    </label>
								</div>

								<div class="input-group">
      							<div class="input-group-addon"><b>@lang( 'lang_v1.size' )</b></div>
									<input type="text" class="form-control" 
										name="print[lot_number_size]" 
										value="12">
								</div>
							@endif
						</td> --}}


                                </tr>
                                <tr>

                                    @php
                                        $c = 0;
                                        $custom_labels = json_decode(session('business.custom_labels'), true);
                                        $product_custom_fields = !empty($custom_labels['product'])
                                            ? $custom_labels['product']
                                            : [];
                                        $product_cf_details = !empty($custom_labels['product_cf_details'])
                                            ? $custom_labels['product_cf_details']
                                            : [];
                                    @endphp
                                    @foreach ($product_custom_fields as $index => $cf)
                                        @if (!empty($cf))
                                            @php
                                                $field_name = 'product_custom_field' . $loop->iteration;
                                                $cf_type = !empty($product_cf_details[$loop->iteration]['type'])
                                                    ? $product_cf_details[$loop->iteration]['type']
                                                    : 'text';
                                                $dropdown = !empty(
                                                    $product_cf_details[$loop->iteration]['dropdown_options']
                                                )
                                                    ? explode(
                                                        PHP_EOL,
                                                        $product_cf_details[$loop->iteration]['dropdown_options'],
                                                    )
                                                    : [];
                                                $c++;
                                            @endphp
                                            <td>
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" name="print[{{ $field_name }}]"
                                                            value="1">
                                                        <b>{{ $cf }}</b>
                                                    </label>
                                                </div>

                                                <div class="input-group">
                                                    <div class="input-group-addon"><b>@lang('lang_v1.size')</b></div>
                                                    <input type="text" class="form-control"
                                                        name="print[{{ $field_name }}_size]" value="12">
                                                </div>
                                            </td>
                                            @if ($c % 4 == 0)
                                </tr>
                                @endif
                                @endif
                                @endforeach
                                </tr>
                            </table>
                        </div>

                        <div class="col-sm-12" style="display: none">
                            <hr />
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                {!! Form::label('price_type', @trans('barcode.barcode_setting') . ':') !!}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-cog"></i>
                                    </span>
                                    {!! Form::select('barcode_setting', $barcode_settings, !empty($default) ? $default->id : null, [
                                        'class' => 'form-control select2',
                                    ]) !!}
                                </div>
                            </div>
                        </div>

                        <div class="clearfix"></div>

                        <div class="col-sm-12 text-center">
                            <button type="button" id="labels_preview"
                                class="btn btn-primary btn-big">@lang('barcode.preview')</button>
                        </div>
                    @endcomponent
                </div>
            </div>






            {!! Form::close() !!}

            <div class="col-sm-8 hide display_label_div">
                <h3 class="box-title">@lang('barcode.preview')</h3>
                <button type="button" class="col-sm-offset-2 btn btn-success btn-block" id="print_label">Print</button>
            </div>
            <div class="clearfix"></div>
    </section>

    <!-- Preview section -->
    <div id="preview_box"></div>

    @include('layouts.partials.javascripts')
    <script src="{{ asset('js/kit.fontawesome.com/58d91d1e4e.js') }}" crossorigin="anonymous"></script>

    <!-- Include your JavaScript files here -->
    <script src="{{ asset('js/labels.js?v=' . $asset_v) }}"></script>
    <script>
        function autoFillField(field) {
            var firstRow = $('#tableBodyCreate tr:first');
            var valueToCopy = firstRow.find(`[name^="products"][name*="[${field}]"]`).val();

            $('#tableBodyCreate tr').each(function(index, row) {
                if (index > 0) { // Skip the first row
                    $(row).find(`[name^="products"][name*="[${field}]"]`).val(valueToCopy).trigger('change');
                }
            });
        }

        // Attach the auto-fill function to the individual auto-fill buttons
        $(document).on('click', '.autoFillField', function(e) {
            e.preventDefault();
            var field = $(this).data('field');
            autoFillField(field);
        });
    </script>

</body>

</html>
