{{-- 
@extends('layouts.app')
@section('title', __('purchase.update_status'))

@section('content')
    <section class="content-header">
    </section>
    <section class="content">
       
            <div class="row" style="margin-left: 10px; margin-right:10px;">
                @component('components.widget', ['class' => 'box-primary ', 'title' => 'Reference stock details'])

             <div class="">
  
    @php
        $title =
            $purchase->type == 'purchase_order'
                ? __('lang_v1.purchase_order_details')
                : __('purchase.purchase_details');
        $custom_labels = json_decode(session('business.custom_labels'), true);
    @endphp
    
</div>
<div style="margin-top: -45px">
    <div class="row">
        <div class="col-sm-12">
            <div class="pull-right"><b>@lang('messages.date'):</b>
                {{ \Carbon\Carbon::parse($purchase->transaction_date)->format('j F Y') }}   </div>
        </div>
    </div>
    <div class="row invoice-info">
        <div class="col-sm-4 invoice-col">
            @lang('purchase.supplier'):
            <address>
                {!! @$purchase->contact->contact_address !!}
                @if (!empty($purchase->contact->tax_number))
                    <br>@lang('contact.tax_no'): {{ $purchase->contact->tax_number }}
                @endif
                @if (!empty($purchase->contact->mobile))
                    <br>@lang('contact.mobile'): {{ $purchase->contact->mobile }}
                @endif
                @if (!empty($purchase->contact->email))
                    <br>@lang('business.email'): {{ $purchase->contact->email }}
                @endif
                @if (!empty($transaction->delivery_person_id) && !is_null($transaction->delivryperson))
                <br>@lang('purchase.delivery_person'): {{ $transaction->delivryperson->name }}
            @else
                <br>@lang('purchase.delivery_person'): @lang('purchase.not_available')
            @endif
            
            
            </address>
            @if ($purchase->document_path)
                <a href="{{ $purchase->document_path }}" download="{{ $purchase->document_name }}"
                    class="btn btn-sm btn-success pull-left no-print">
              
                    &nbsp;{{ __('purchase.download_document') }}
                </a>
            @endif
        </div>

        <div class="col-sm-4 invoice-col">
            <span id="pv-column" style="display: block; margin-bottom: 5px;"></span>
            <span id="generic-column" style="display: block; margin-bottom: 5px;"></span>
            <span id="pharmacopeia-column" style="display: block; margin-bottom: 5px;"></span>
        </div>

        <div class="col-sm-4 invoice-col">
            <b>@lang('purchase.ref_no'):</b> #{{ $purchase->ref_no }}<br />
            <b>@lang('messages.date'):</b> {{ \Carbon\Carbon::parse($purchase->transaction_date)->format('j F Y') }}<br />
            @if (!empty($purchase->status))
                <b>@lang('purchase.purchase_status'):</b>
                @if ($purchase->type == 'purchase_order')
                    {{ $po_statuses[$purchase->status]['label'] ?? '' }}
                @else
                    {{ __($purchase->status) }}
                @endif
                <br>
            @endif

        </div>
    </div>

    <br>
    <div class="row">
        <div class="col-sm-12 col-xs-12">
            <div class="table-responsive">
                <table class="table ">
                    <thead>
                        <tr class="bg-gray">
                            <th>#</th>
                            <th>@lang('product.sample_name')</th>

                            <th>@lang('batch.b_no')</th>
                            <th>@lang('batch.mfg')</th>
                            <th>@lang('batch.exp')</th> --}}
{{-- <th>@lang('product.sku')</th> --}}
{{-- @if ($purchase->type == 'purchase_order')
                              <th class="text-right">@lang( 'lang_v1.quantity_remaining' )</th>
                            @endif --}}
{{-- <th class="">
                                @if ($purchase->type == 'purchase_order')
                                    @lang('lang_v1.order_quantity')
                                @else
                                    @lang('purchase.purchase_quantity')
                                @endif
                            </th> --}}


{{-- <th> @lang('product.instalments')</th> --}}
{{-- <th> Location ID</th> --}}
{{-- <th> @lang('product.contract_no')</th>
                            <th> @lang('product.method')</th>
                            <th> @lang('product.standard')</th> --}}
{{-- <th class="text-right">@lang( 'lang_v1.unit_cost_before_discount' )</th>

                            <th class="text-right">@lang( 'lang_v1.discount_percent' )</th> --}}
{{-- <th class="no-print text-right">@lang('purchase.unit_cost_before_tax')</th>
                            <th class="no-print text-right">@lang('purchase.subtotal_before_tax')</th> --}}
{{-- <th class="text-right">@lang('sale.tax')</th>
                            <th class="text-right">@lang('purchase.unit_cost_after_tax')</th> --}}
{{-- @if ($purchase->type != 'purchase_order')
                            @if (session('business.enable_lot_number'))
                              <th>@lang('lang_v1.lot_number')</th>
                            @endif
                            @if (session('business.enable_product_expiry'))
                              <th>@lang('product.mfg_date')</th>
                              <th>@lang('product.exp_date')</th>
                            @endif
                            @endif --}}
{{-- <th class="text-right">@lang('sale.subtotal')</th> --}}
{{-- </tr>
                    </thead>
                    @php
                        $total_before_tax = 0.0;
                    @endphp
                    @foreach ($purchase->purchase_lines as $purchase_line)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                {{ $purchase_line->product->name }}
                                @if (isset($purchase_line->product->generic_name))
                                    ({{ $purchase_line->product->generic->name }})
                                @endif
                                @if ($purchase_line->product->type == 'variable')
                                    - {{ $purchase_line->variations->product_variation->name }}
                                    - {{ $purchase_line->variations->name }}
                                @endif
                            </td>
                            <td>
                                @if ($purchase_line->product->type == 'variable')
                                    {{ $purchase_line->variations->sub_sku }}
                                @else
                                    {{ @$purchase_line->batch->code }}
                                @endif
                            </td>
                            <td>

                                {{ @$purchase_line->batch->mfg_date }}
                            </td>
                            <td>

                                {{ @$purchase_line->batch->expiry_date }}
                            </td>
                       
                            <td><span class="display_currency" data-is_quantity="true"
                                    data-currency_symbol="false">{{ $purchase_line->quantity }}</span>
                                @if (!empty($purchase_line->sub_unit))
                                    {{ $purchase_line->sub_unit->short_name }}
                                @else
                                    {{ $purchase_line->product->unit->short_name }}
                                @endif

                                @if (!empty($purchase_line->product->second_unit) && $purchase_line->secondary_unit_quantity != 0)
                                    <br>
                                    <span class="display_currency" data-is_quantity="true"
                                        data-currency_symbol="false">{{ $purchase_line->secondary_unit_quantity }}</span>
                                    {{ $purchase_line->product->second_unit->short_name }}
                                @endif

                            </td>


                            <td>
                                @if ($purchase_line->product->type == 'variable')
                                    {{ $purchase_line->variations->sub_sku }}
                                @else
                                    @if ($purchase_line->instalments == 'instalments_1')
                                       1st instalment 
                                    @elseif($purchase_line->instalments == 'instalments_2')
                                        2nd instalment
                                    @elseif($purchase_line->instalments == 'instalments_3')
                                        3rd instalment
                                    @elseif($purchase_line->instalments == 'instalments_4')
                                        4th instalment
                                    @else
                                        {{ $purchase_line->instalments }}
                                    @endif
                                @endif
                            </td>
                           
                            <td>
                                @if ($purchase_line->product->type == 'variable')
                                    {{ $purchase_line->variations->sub_sku }}
                                @else
                                    {{ @$purchase_line->contract->number??'- }}
                                @endif
                            </td>
                            <td>
                                {{ $transaction->ref_method_check === null || $transaction->ref_method_check == 'no' ? 'No' : 'Yes' }}
                            </td>
                            <td>
                                {{ $transaction->ref_standard_check === null || $transaction->ref_standard_check == 'no' ? 'No' : 'Yes' }}
                            </td>
                            
                            

                 --}}
{{-- <td class="text-right"><span class="display_currency" data-currency_symbol="true">{{ $purchase_line->purchase_price_inc_tax * $purchase_line->quantity }}</span></td> --}}
{{-- </tr>
                        @php
                            $total_before_tax += $purchase_line->quantity * $purchase_line->purchase_price;
                        @endphp
                    @endforeach
                </table>
            </div>
        </div>
    </div>
    <br>
   
  
    @if (!empty($activities))
        <div class="row">
            <div class="col-md-12">
                <strong>{{ __('lang_v1.activities') }}:</strong><br>
                @includeIf('activity_log.activities', ['activity_type' => 'purchase'])
            </div>
        </div>
    @endif --}}

{{-- Barcode --}}
{{-- <div class="row print_section">
        <div class="col-xs-12">
            <img class="center-block"
                src="data:image/png;base64,{{ DNS1D::getBarcodePNG($purchase->ref_no, 'C128', 2, 30, [39, 48, 54], true) }}">
        </div>
    </div>
</div>

             @endcomponent
            </div> --}}
{{-- <form action="{{ action([\App\Http\Controllers\PurchaseController::class, 'updateStatus']) }}" method="POST"
            id="update_purchase_status_form" enctype="multipart/form-data">
            @csrf --}}

{{-- <div class="row">
                <div class="col-md-12">
                    @component('components.widget', ['class' => 'box-primary', 'title' => 'Reference Standard'])
                        <input type="hidden" id="sample_id_field" name="search_nomenclature" value="{{ $sample_id }}">
                        <input type="hidden" id="purchase_id" name="purchase_id" value="{{ $id }}"> --}}

<!-- Table container for standards -->
{{-- <div class="col-sm-12" id="purchaseTableContainerStandards" style="margin-top:-20px;">
                            <table class="table dataTable" id="purchasesTableAddStandards">
                                <thead class="bg-gray" style="font-size: 12px;border-radius:4px;">
                                    <tr>
                                        <th>#</th>
                                        <th>Standard</th>
                                        <th>Batch</th>
                                        <th>Quantity</th>
                                        <th>Acc Unit</th>
                                        <th>Potency</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="tableBodyCreateStandards">
                                    <tr>
                                        <td class="serial-numberSt">1</td>
                                        <td>
                                            {!! Form::text(null, null, [
                                                'class' => 'form-control standard-field',
                                                'id' => 'new_standard_code_1',
                                                'placeholder' => __('Standard Name'),
                                                'style' => 'width:100%;font-size:12px;',
                                                'list' => 'standard_codes',
                                                'autocomplete' => 'off',
                                            ]) !!}
                                            {!! Form::hidden('standards[1][standard_id]', null, ['id' => 'standard_id_1']) !!}
                                            <datalist id="standard_codes" role="listbox">
                                                @foreach ($products as $product)
                                                    @php
                                                        $genericNamesString = $product->generic ? $product->generic->name : '';
                                                    @endphp
                                                    <option value="{{ $genericNamesString }}" data-id="{{ $product->id }}"></option>
                                                @endforeach
                                            </datalist>
                                            <div id="selected_standards_1" class="selected-standards" style="margin-top:10px;"></div>
                                        </td>
                                        <td>
                                            {!! Form::text('standards[1][new_batch_code]', null, [
                                                'class' => 'form-control batch-field',
                                                'id' => 'st_new_batch_code_1',
                                                'placeholder' => __('Batch No'),
                                                'style' => 'width:100%;font-size:12px;',
                                                'list' => 'st_batch_codes',
                                                'autocomplete' => 'off',
                                                'required' => 'required',
                                            ]) !!}
                                            {!! Form::hidden('standards[1][batch_id]', null, ['id' => 'st_batch_id_1']) !!}
                                        </td>
                                        <td>
                                            <input type="number" name="standards[1][st_quantity]"
                                                class="form-control quantity-field" id="st_quantity_1" min="0"
                                                placeholder="Enter Qty" autocomplete="off" value="0" required>
                                        </td>
                                        <td class="input-group">
                                            {!! Form::select('standards[1][unit_id]', $units, $sample_unit_id, [
                                                'class' => 'form-control select2',
                                                'required' => 'required',
                                                'style' => 'width: 100%;',
                                            ]) !!}
                                        </td>
                                        <td>
                                            {!! Form::text('standards[1][potency]', null, [
                                                'class' => 'form-control potency-field',
                                                'id' => 'potency_1',
                                                'placeholder' => __('Enter Potency'),
                                                'style' => 'width:100%;font-size:12px;',
                                                'autocomplete' => 'off',
                                                'required' => 'required',
                                            ]) !!}
                                        </td>
                                        
                                        <td>
                                            <a class="btn btn-sm btn-primary addPurchaseRowCreateStandards"><i class="fa fa-plus"></i></a>
                                        </td>
                                        <td class="hidden">
                                            <input type="hidden" id="generic_id_field_1" name="standards[1][generic_id]"
                                                value="">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @endcomponent

           

            @component('components.widget', ['class' => 'box-primary', 'title' => 'Reference Method'])
            <table class="table dataTable" id="purchasesTableAddMethods" style="width: 100%;">
                <thead class="bg-gray" style="font-size: 12px;border-radius:4px;">
                    <tr>
                        <th style="width:5%">#</th>
                        <th style="width:45%">Method Name</th>
                        <th style="width:40%">Files</th>
                        <th style="width:10%"></th>
                    </tr>
                </thead>
                <tbody id="tableBodyCreateMethods">
                    <tr>
                        <td class="serial-numberMt" style="width:10%">1</td>
                        <td style="width:40%">
                            {!! Form::text('methods[1][method_name]', null, [
                                'class' => 'form-control method-name-field',
                                'id' => 'method_name_1',
                                'placeholder' => __('Method Name'),
                                'style' => 'width:100%;font-size:12px;',
                                'list' => 'method_names',
                                'autocomplete' => 'off',
                                'required' => 'required',
                            ]) !!}
                            <datalist id="method_names" role="listbox">
                                @foreach ($methods as $method)
                                    <option value="{{ $method->method_name }}" data-id="{{ $method->id }}"></option>
                                @endforeach
                            </datalist>
                        </td>
                        <td style="width:40%">
                           
                                <input type="file" class="form-control-field  method-files-field" id="method_files_1"
                                       name="methods[1][method_files][]" multiple>
                            </div>
                        </td>
                        <td class="hidden">
                            <input type="hidden" id="method_product_id_field_1" name="methods[1][product_id]" value="">
                        </td>
                        <td style="width:10%">
                            <a class="btn btn-sm btn-primary addPurchaseRowCreateMethods"><i class="fa fa-plus"></i></a>
                        </td>
                    </tr>
                </tbody>
            </table>
        @endcomponent

           

            <div class="row">
                <div class="col-sm-12 text-center">
                    <input type="hidden" name="status" id="status">
                    <button type="submit" class="btn btn-primary" data-status="received by AFMSL">Received by
                        AFMSL</button>
                </div>
            </div> --}}
{{-- </form> --}}
{{-- </section>
@endsection

@section('javascript') --}}

{{-- <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('new_standard_code_1').addEventListener('input', function () {
            var input = this.value;
            var list = document.getElementById('standard_codes');
            var selectedStandardsDiv = document.getElementById('selected_standards_1');
            var hiddenField = document.getElementById('standard_id_1');

            for (var i = 0; i < list.options.length; i++) {
                if (list.options[i].value === input) {
                    var optionValue = list.options[i].value;
                    var optionId = list.options[i].getAttribute('data-id');

                    // Append selected option to the selected standards div
                    var span = document.createElement('span');
                    span.className = 'selected-standard';
                    span.textContent = optionValue;
                    span.dataset.id = optionId;

                    // Add remove functionality
                    var removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.textContent = '×';
                    removeBtn.className = 'remove-standard';
                    removeBtn.addEventListener('click', function () {
                        selectedStandardsDiv.removeChild(span);
                        updateHiddenField();
                    });

                    span.appendChild(removeBtn);
                    selectedStandardsDiv.appendChild(span);

                    
                    this.value = '';
                    updateHiddenField();

                    break;
                }
            }

            function updateHiddenField() {
                var selectedStandards = selectedStandardsDiv.querySelectorAll('.selected-standard');
                var selectedIds = Array.from(selectedStandards).map(function (standard) {
                    return standard.dataset.id;
                });
                hiddenField.value = selectedIds.join(',');
            }
        });
    });
</script> --}}
{{-- <script>
        $(document).ready(function() {
            var selectedSampleId = $('#sample_id_field').val();

            // Make the AJAX request
            $.ajax({
                url: '/get-sample-info',
                method: 'GET',
                data: {
                    sample_id: selectedSampleId
                },
                success: function(response) {
                    var pvNumber = response.pv_number;
                    var genericName = response.generic_name;
                    var pharmacopeia = response.pharmacopeia;
                    var genericNameId = response.generic_name_id;
                    var contractType = response.contract_type;
                    var variation_id = response.variation_id;
                    var batchesForSample = response.batches_for_sample;
                    var currentQuantity = response.current_quantity;
                    var referenceMethodCheckboxdiv = document.getElementById(
                        'reference_method_label');

                    $('#supplier_id').prop('disabled', false);
                    $('#manufacturer_select_field').prop('disabled', false);
                    $('#product_id_field_1').val(selectedSampleId);
                    $('#variation_id_field_1').val(variation_id);
                    $('#sample_id_contract_tender').val(selectedSampleId);
                    $('#sample_id_contract_supply').val(selectedSampleId);
                    // information line on top of search field
                    $('#pv-column').html('<span style="font-size: 12px;"><strong>PV No:</strong> (<strong>' + (pvNumber ? pvNumber : '-') + '</strong>)  </span>');
                    $('#generic-column').html(
                        '<span style="font-size: 12px;"><strong>Generic Name:</strong> (<strong>' + (
                            genericName ?
                            genericName : '-') + '</strong>)</span>');
                    $('#pharmacopeia-column').html(
                        '<span style="font-size: 12px;"><strong>Pharmacopeia:</strong> (<strong>' + (
                            pharmacopeia ?
                            pharmacopeia : '-') + '</strong>)</span>');
                    if (pharmacopeia === 'Manufacturer spec') {
                        referenceMethodCheckboxdiv.style.display = 'inline';
                    } else {
                        referenceMethodCheckboxdiv.style.display = 'none';
                    }
                    // Populate batches select field for primary field
                    populateBatchDatalist('batch_codes', batchesForSample);



                    // Show the batches select container and hide the add batches container
                    $('#purchaseTableContainer').show();


                },
                error: function(xhr, status, error) {
                    console.error('Error fetching sample info:', error);
                }
            });

            // Add new row for standards
            $(document).on('click', '.addPurchaseRowCreateStandards', function() {
                var table = document.getElementById("purchasesTableAddStandards");
                var tbodyRowCount = table.tBodies[0].rows.length + 1;
                var tr = `
            <tr>
                <td class="serial-numberSt">${tbodyRowCount}</td>
                <td>
       
                            {!! Form::text('standards[${tbodyRowCount}][new_standard_code]', null, [
                                'class' => 'form-control',
                                'id' => 'new_standard_code_${tbodyRowCount}',
                                'placeholder' => __('Standard'),
                                'style' => 'width:100%;font-size:12px;',
                                'list' => 'standard_codes',
                                'autocomplete' => 'off',
                            ]) !!}
                            {!! Form::hidden('standards[${tbodyRowCount}][standard_id]', null, [
                                'id' => 'standard_id_${tbodyRowCount}',
                            ]) !!}
                </td>
                <td>

                            {!! Form::text('standards[${tbodyRowCount}][new_batch_code]', null, [
                                'class' => 'form-control',
                                'id' => 'st_new_batch_code_${tbodyRowCount}',
                                'placeholder' => __('Batch No'),
                                'style' => 'width:100%;font-size:12px;',
                                'list' => 'st_batch_codes',
                                'autocomplete' => 'off',
                            ]) !!}
                            {!! Form::hidden('standards[${tbodyRowCount}][batch_id]', null, [
                                'id' => 'st_batch_id_${tbodyRowCount}',
                            ]) !!}

                </td>
                <td>
            
                            <input type="number" name="standards[${tbodyRowCount}][st_quantity]" class="form-control" id="st_quantity_${tbodyRowCount}" min="0" placeholder="Enter Qty" autocomplete="off" value="0">
      
                </td>
                                      <td class="input-group" >
                                            {!! Form::select('standards[${tbodyRowCount}][unit_id]', $units, $sample_unit_id, [
                                                'placeholder' => __('messages.please_select'),
                                                'class' => 'form-control select2',
                                                'required' => 'required',
                                                'style' => 'width: 90%;',
                                            ]) !!}
                                        </td>
                <td>
       
                        {!! Form::text('standards[${tbodyRowCount}][potency]', null, [
                            'class' => 'form-control',
                            'id' => 'potency_${tbodyRowCount}',
                            'placeholder' => __('Enter Potency'),
                            'style' => 'width:100%;font-size:12px;',
                            'autocomplete' => 'off',
                        ]) !!}
       
                </td>
                <td class="hidden">
                    <input type="hidden" id="generic_id_field_${tbodyRowCount}" name="standards[${tbodyRowCount}][generic_id]" value="">
                </td>
                <td>
                    <a class="btn btn-sm btn-danger remRowSt"><i class="fa fa-minus"></i></a>
                </td>
            </tr>`;
                $('#tableBodyCreateStandards').append(tr);
            });

            $(document).on('click', '.remRowSt', function() {
                $(this).closest('tr').remove();
                updateSerialNumbersSt('#tableBodyCreateStandards');
            });

            // Add new row for methods
            $(document).on('click', '.addPurchaseRowCreateMethods', function() {
                var table = document.getElementById("purchasesTableAddMethods");
                var tbodyRowCount = table.tBodies[0].rows.length + 1;
                var tr = `
            <tr>
                <td class="serial-numberMt" style="width:10%">${tbodyRowCount}</td>
                <td style="width:40%">
                            {!! Form::text('methods[${tbodyRowCount}][method_name]', null, [
                                'class' => 'form-control',
                                'id' => 'method_name_${tbodyRowCount}',
                                'placeholder' => __('Method Name'),
                                'style' => 'width:100%;font-size:12px;',
                                'autocomplete' => 'off',
                            ]) !!}

                </td>
                <td style="width:40%">
                  
                        <input type="file" class="form-control-field" id="method_files_${tbodyRowCount}" name="methods[${tbodyRowCount}][method_files][]" multiple>
                    
                </td>
                <td class="hidden">
                    <input type="hidden" id="method_product_id_field_${tbodyRowCount}" name="methods[${tbodyRowCount}][product_id]" value="">
                </td>
                <td style="width:10%">
                    <a class="btn btn-sm btn-danger remRowMt"><i class="fa fa-minus"></i></a>
                </td>
            </tr>`;
                $('#tableBodyCreateMethods').append(tr);
            });

            $(document).on('click', '.remRowMt', function() {
                $(this).closest('tr').remove();
                updateSerialNumbersMt('#tableBodyCreateMethods');
            });

            // Serial number function for all rows 
            function updateSerialNumbersMt(tableBodySelector) {
                $(tableBodySelector + ' tr').each(function(index, row) {
                    $(row).find('.serial-numberMt').text(index + 1);
                });
            }

            function updateSerialNumbersSt(tableBodySelector) {
                $(tableBodySelector + ' tr').each(function(index, row) {
                    $(row).find('.serial-numberSt').text(index + 1);
                });
            }

    // Handle submission buttons
    $('button[type=submit]').on('click', function(e) {
        e.preventDefault();
        var form = $('#update_purchase_status_form')[0];
        if (form.checkValidity()) {
            var status = $(this).data('status');
            $('#status').val(status);
            form.submit();
        } else {
            $('<input type=" submit">').hide().appendTo(form).click().remove();
        }
    });
});

    </script> --}}

{{-- @endsection --}}
