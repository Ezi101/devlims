<div id="accordion">
    {!! Form::open([
    'url' => '#',
    'method' => 'post',
    'id' => 'preview_setting_form_label_on_sampledashbord',
    'onsubmit' => 'return false',
    ]) !!}
    @component('components.widget', ['class' => 'box-primary', 'title' => 'Generate Lables Against batches'])
    <div class="row">
        <div class="col-sm-12">
            <table class="table table-bordered table-striped table-condensed print_label_table" id="print_label_table">
                <thead>
                    <tr>
                        <th style="width:10%">#</th>
                        <th style="width:20%">Batch</th>
                        <th style="width:20%">Mfg Date</th>
                        <th style="width:20%">Exp Date</th>
                        <th style="width:20%">No of Labels</th>
                        <th style="width:10%">Action</th>

                    </tr>
                </thead>
                <tbody>
                    @foreach ($batches as $index => $bat)
                    <tr>
                        <td style="width:10%">
                            {{ $loop->iteration }}
                        </td>
                        <td  style="width:20%">
                            {{ $bat->code }}

                            <input type="hidden" name="products[{{ $index }}][product_id]" value="{{ $product->id }}">
                            <input type="hidden" name="products[{{ $index }}][variation_id]" value="{{ $product->variations[0]->id }}">
                            <input type="hidden" name="products[{{ $index }}][batch_code]" value="{{ $bat->code }}">
                        </td>
                        <td style="width:20%">
                            {{ $bat->mfg_date }}

                            <input type="hidden" class="form-control" readonly name="products[{{ $index }}][entry_date]" value="{{ @format_date($bat->mfg_date) }}">
                        </td>
                        <td style="width:20%">
                            {{ $bat->expiry_date }}

                            <input type="hidden" class="form-control" readonly name="products[{{ $index }}][expiry_date]" value="@if (isset($bat->expiry_date)) {{ @format_date($bat->expiry_date) }} @endif">
                        </td>
                        <td style="width:20%">
                            <input type="number" class="form-control" min="1" name="products[{{ $index }}][quantity]" value="@if (isset($product->quantity)) {{ trim($product->quantity) }}@else{{ '1' }} @endif">
                        </td>
                        <td style="width:10%">
                            <button type="button" class="btn btn-default btn-sm labels-preview" data-batch-id="{{ $bat->id }}">
                                {{-- @lang('barcode.preview') --}}
                                <i class="fas fa-print"></i>
                            </button>
                        </td>
                        {{-- <td>
                            <button type="button" id="labels_preview_on_sample_dashbord_" value="{{ $bat->id }}"
                                class="btn btn-primary btn-sm">@lang('barcode.preview')</button>
                        </td> --}}

                    </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
    </div>
    @endcomponent



    <div class="row" style="display: none">
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
                            <input type="text" class="form-control" name="print[name_size]" value="15">
                        </div>
                    </td>

                    <td>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" checked name="print[expiry_date]" value="1">
                                <b>@lang('lang_v1.print_exp_date')</b>
                            </label>
                        </div>

                        <div class="input-group">
                            <div class="input-group-addon"><b>@lang('lang_v1.size')</b></div>
                            <input type="text" class="form-control" name="print[expiry_date_size]" value="12">
                        </div>
                    </td>

                    <td>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" checked name="print[entry_date]" value="1">
                                <b>@lang('lang_v1.print_packing_date')</b>
                            </label>
                        </div>

                        <div class="input-group">
                            <div class="input-group-addon"><b>@lang('lang_v1.size')</b></div>
                            <input type="text" class="form-control" name="print[entry_date_size]" value="12">
                        </div>
                    </td>

                </tr>

                <tr>
                    @php
                    $c = 0;
                    $custom_labels = json_decode(session('business.custom_labels'), true);
                    $product_custom_fields = !empty($custom_labels['product']) ? $custom_labels['product'] : [];
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
                    $dropdown = !empty($product_cf_details[$loop->iteration]['dropdown_options'])
                    ? explode(PHP_EOL, $product_cf_details[$loop->iteration]['dropdown_options'])
                    : [];
                    $c++;
                    @endphp
                    <td>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="print[{{ $field_name }}]" value="1">
                                <b>{{ $cf }}</b>
                            </label>
                        </div>

                        <div class="input-group">
                            <div class="input-group-addon"><b>@lang('lang_v1.size')</b></div>
                            <input type="text" class="form-control" name="print[{{ $field_name }}_size]" value="12">
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



        {{--
            <div class="col-sm-12">
                <hr />
            </div> --}}
        {{-- @dd($barcode_settings[6]); --}}
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('price_type', @trans('barcode.barcode_setting') . ':') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-cog"></i>
                    </span>
                    {!! Form::text('barcode_setting', $barcode_settings[6], !empty($default) ? $default->id : null, [
                    'class' => 'form-control',
                    'readonly',
                    ]) !!}
                </div>
            </div>
        </div>

        <div class="clearfix"></div>

        <div class="col-sm-12 text-center">
            <button type="button" id="labels_preview_on_sample_dashbord_" class="btn btn-primary btn-big">@lang('barcode.preview')</button>
        </div>
    </div>
    {!! Form::close() !!}

    <div class="col-sm-8 hide display_label_div">
        <h3 class="box-title">@lang('barcode.preview')</h3>
        <button type="button" class="col-sm-offset-2 btn btn-success btn-block" id="print_labelof_sample_dashbord">Print</button>
    </div>
    <div class="clearfix"></div>


    <!-- Preview section-->
    <div id="sample_dashbord_preview_body">
    </div>

</div>
