<div class="modal-header">
    <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">&times;</span></button>

    <div class="row" style="margin-left: 20px; margin-right:20px;">
        @component('components.widget', ['class' => 'box-solid ', 'title' => 'Received stock details'])




            <div style="margin-top: -45px">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="pull-right"><b>@lang('messages.date'):</b>
                            {{ \Carbon\Carbon::parse(@$purchase->created_at)->format('j F Y') }} </div>
                    </div>
                </div>
                <div class="row invoice-info">
                    <div class="col-sm-3 invoice-col">
                        <address>
                            @if (isset($purchase->brand->name))
                                @lang('purchase.manufacturer'): {!! $purchase->brand->name !!}
                                <br>
                            @endif

                            @if (isset($purchase->contact->contact_address))
                                @lang('purchase.supplier'): {!! $purchase->contact->contact_address !!}
                                <br>
                            @endif

                            @if (isset($purchase->contact->mobile) && !empty($purchase->contact->mobile))
                                @lang('contact.mobile'): {{ $purchase->contact->mobile }}
                                <br>
                            @endif

                            @if (isset($purchase->contact->email) && !empty($purchase->contact->email))
                                @lang('business.email'): {{ $purchase->contact->email }}
                                <br>
                            @endif

                            @if (isset($transaction->delivery_person_id) && isset($transaction->delivryperson))
                                @lang('purchase.delivery_person'): {{ $transaction->delivryperson->name }}
                                <br>
                            @endif
                        </address>


                    </div>

                    <div class="col-sm-3 invoice-col">
                        <span id="pv-column" style="display: block; margin-bottom: 5px;"></span>
                        <span id="generic-column" style="display: block; margin-bottom: 5px;"></span>
                        <span id="pharmacopeia-column" style="display: block; margin-bottom: 5px;"></span>

                    </div>

                    <div class="col-sm-3 invoice-col">
                        <b>@lang('purchase.ref_no'):</b> #{{ @$purchase->ref_no }}<br />

                        @if (!empty($purchase->status))
                            <b>@lang('purchase.purchase_status'):</b>
                            @if ($purchase->type == 'purchase_order')
                                {{ $po_statuses[$purchase->status]['label'] ?? '' }}
                            @else
                                {{ ucfirst(__($purchase->status)) }}
                            @endif
                            <br>
                        @endif

                    </div>
                    @if (!empty($purchase->not_rec_reason) && $purchase->status != 'Received by AFMSL')
                        <div class="col-sm-3 invoice-col">
                            <b>@lang('product.not_rec_reason'):</b><br>
                            {{ $purchase->not_rec_reason }}
                        </div>
                    @endif

                </div>

                <br>
                <div class="row">
                    <div class="col-sm-12 col-xs-12">
                        <div class="table-responsive">
                            <table class="table ">
                                <thead>
                                    <tr class="bg-gray">
                                        <th>@lang('method.hash_sign')</th>
                                        <th>@lang('product.sample_name')</th>
                                        <th>@lang('batch.b_no')</th>
                                        <th>@lang('batch.mfg')</th>
                                        <th>@lang('batch.exp')</th>

                                        <th class="">
                                            @if ($purchase->type == 'purchase_order')
                                                @lang('lang_v1.order_quantity')
                                            @else
                                                @lang('purchase.purchase_qty')
                                            @endif
                                        </th>


                                        <th> @lang('product.instalments')</th>
                                        {{-- <th> Location ID</th> --}}
                                        <th> @lang('product.contract_no')</th>
                                        <th> @lang('product.method')</th>
                                        <th> @lang('product.ref_std')</th>

                                    </tr>
                                </thead>
                                @php
                                    $total_before_tax = 0.0;
                                @endphp
                                @foreach ($purchase->purchase_lines as $purchase_line)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            @php
                                                $product = $purchase_line->product ?? null;
                                                $productName = $product->name ?? '-';
                                                $productSku = $product->sku ?? null;
                                                $isVariable = $product && $product->type == 'variable';
                                                $variationName = $isVariable
                                                    ? $purchase_line->variations->product_variation->name ?? '-'
                                                    : null;
                                                $variationValue = $isVariable
                                                    ? $purchase_line->variations->name ?? '-'
                                                    : null;
                                            @endphp

                                            @if ($product && $product->id)
                                                <a href="{{ route('samples.view.dashboard', ['id' => $product->id]) }}">
                                                    {{ $productName }}
                                                </a>
                                            @else
                                                {{ $productName }}
                                            @endif

                                            @if ($productSku)
                                                ({{ $productSku }})
                                            @endif

                                            @if ($isVariable)
                                                - {{ $variationName }} - {{ $variationValue }}
                                            @endif
                                        </td>

                                        <td>
                                            @if (@$purchase_line->product->type == 'variable')
                                                {{ @$purchase_line->variations->sub_sku }}
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
                                                {{ @$purchase_line->sub_unit->short_name }}
                                            @else
                                                {{ @$purchase_line->product->unit->short_name }}
                                            @endif

                                            @if (!empty($purchase_line->product->second_unit) && $purchase_line->secondary_unit_quantity != 0)
                                                <br>
                                                <span class="display_currency" data-is_quantity="true"
                                                    data-currency_symbol="false">{{ @$purchase_line->secondary_unit_quantity }}</span>
                                                {{ @$purchase_line->product->second_unit->short_name }}
                                            @endif

                                        </td>


                                        <td>
                                            @if ($purchase_line->product->type == 'variable')
                                                {{ @$purchase_line->variations->sub_sku }}
                                            @else
                                                @if ($purchase_line->instalments == 'instalments_1')
                                                    1st installment
                                                @elseif($purchase_line->instalments == 'instalments_1_2')
                                                    1st & 2nd installment
                                                @elseif($purchase_line->instalments == 'instalments_1_2_3')
                                                    1st,2nd & 3rd installment
                                                @elseif($purchase_line->instalments == 'instalments_2_3')
                                                    2nd & 3rd installment
                                                @elseif($purchase_line->instalments == 'instalments_2')
                                                    2nd installment
                                                @elseif($purchase_line->instalments == 'instalments_3')
                                                    3rd installment
                                                @elseif($purchase_line->instalments == 'instalments_4')
                                                    4th installment
                                                @elseif($purchase_line->instalments == 'instalments_3_4')
                                                    3rd & 4th installment
                                                @elseif($purchase_line->instalments == 'no_instalment')
                                                    No Installment
                                                @else
                                                    {{ $purchase_line->instalments }}
                                                @endif
                                            @endif
                                        </td>

                                        <td>
                                            @if ($purchase_line->product->type == 'variable')
                                                {{ @$purchase_line->variations->sub_sku }}
                                            @else
                                                {{ @$purchase_line->contract->number ?? '-' }}
                                            @endif
                                        </td>
                                        <td>
                                            {{ @$transaction->ref_method_check === null || @$transaction->ref_method_check == 'no' ? 'No' : 'Yes' }}
                                        </td>
                                        <td>
                                            {{ @$transaction->ref_standard_check === null || @$transaction->ref_standard_check == 'no' ? 'No' : 'Yes' }}
                                        </td>




                                    </tr>
                                    @php
                                        $total_before_tax += $purchase_line->quantity * $purchase_line->purchase_price;
                                    @endphp
                                @endforeach
                            </table>
                        </div>
                    </div>
                </div>
                <br>


                {{-- @if (!empty($activities))
                    <div class="row">
                        <div class="col-md-12">
                            <strong>{{ __('lang_v1.activities') }}:</strong><br>
                            @includeIf('activity_log.activities', ['activity_type' => 'purchase'])
                        </div>
                    </div>
                @endif --}}

                {{-- Barcode --}}
                <div class="row print_section">
                    <div class="col-xs-12">
                        <img class="center-block"
                            src="data:image/png;base64,{{ DNS1D::getBarcodePNG($purchase->ref_no, 'C128', 2, 30, [39, 48, 54], true) }}">
                    </div>
                </div>
            </div>
        @endcomponent
    </div>

    <form action="{{ action([\App\Http\Controllers\PurchaseController::class, 'updateStatus'], [$purchase->id]) }}"
        method="POST" id="update_purchase_status_form" enctype="multipart/form-data">
        @csrf

        <div class="row">
            <div class="col-md-12"> <input type="hidden" id="sample_id_field" name="search_nomenclature"
                    value="{{ $sample_id }}">
                <input type="hidden" id="purchase_id" name="purchase_id" value="{{ $id }}">
                @if (@$purchase->ref_standard_check == 'yes')
                    @component('components.widget', ['class' => 'box-primary', 'title' => 'Reference Standard'])
                        <!-- Table container for standards -->
                        <div class="col-sm-12" id="purchaseTableContainerStandards" style="margin-top:-20px;">
                            <table class="table dataTable" id="purchasesTableAddStandards">
                                <thead class="bg-gray" style="font-size: 12px;border-radius:4px;">
                                    <tr>
                                        <th>@lang('method.hash_sign')</th>
                                        <th>@lang('method.standard')</th>
                                        <th>@lang('method.batch')</th>
                                        <th>@lang('method.quantity')</th>
                                        <th>@lang('method.acct_unit')</th>
                                        <th>@lang('method.potency')</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="tableBodyCreateStandards">
                                    <tr>
                                        <td class="serial-numberSt" style="width: 5%;">1</td>
                                        <td style="width: 30%;">
                                            {!! Form::text('standards[1][new_standard_code]', null, [
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
                                                        $genericNamesString = $product->generic
                                                            ? $product->generic->name
                                                            : '';
                                                    @endphp
                                                    <option value="{{ $genericNamesString }}"
                                                        data-id="{{ $product->id }}">
                                                    </option>
                                                @endforeach
                                            </datalist>
                                            <div id="selected_standards_1" class="selected-standards"
                                                style="margin-top:10px;">
                                            </div>
                                        </td>
                                        <td style="width: 20%;">
                                            {!! Form::text('standards[1][new_batch_code]', null, [
                                                'class' => 'form-control batch-field',
                                                'id' => 'st_new_batch_code_1',
                                                'placeholder' => __('Batch No'),
                                                'style' => 'width:100%;font-size:12px;',
                                                'list' => 'st_batch_codes',
                                                'autocomplete' => 'off',
                                                // 'required' => 'required',
                                            ]) !!}
                                            {!! Form::hidden('standards[1][batch_id]', null, ['id' => 'st_batch_id_1']) !!}
                                        </td>
                                        <td style="width: 10%;">
                                            <input type="number" name="standards[1][st_quantity]"
                                                class="form-control quantity-field" id="st_quantity_1" min="0"
                                                placeholder="Enter Qty" autocomplete="off" value="0" required>
                                        </td>
                                        <td class="input-group" style="width: 20%;">
                                            {!! Form::select('standards[1][unit_id]', $units, $sample_unit_id, [
                                                'class' => 'form-control select2',
                                                'required' => 'required',
                                                'style' => 'width: 150%;',
                                            ]) !!}
                                        </td>
                                        <td style="width: 10%;">
                                            {!! Form::text('standards[1][potency]', null, [
                                                'class' => 'form-control potency-field',
                                                'id' => 'potency_1',
                                                'placeholder' => __('Enter Potency'),
                                                'style' => 'width:100%;font-size:12px;',
                                                'autocomplete' => 'off',
                                            ]) !!}
                                        </td>

                                        <td>
                                            <a class="btn btn-sm btn-primary addPurchaseRowCreateStandards"><i
                                                    class="fa fa-plus"></i></a>
                                        </td>

                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @endcomponent
                @endif
            </div>
        </div>

        <a href="{{ route('purchase.view_details', ['id' => $purchase->id]) }}" class="btn btn-primary float-right"
            style=" float: right;">
            <i class="fas fa-print" aria-hidden="true"></i> @lang('Print')
        </a>
        {{-- <div class="row">

            <div class="col-md-12">
                @if (@$purchase->ref_method_check == 'yes')

                    @component('components.widget', ['class' => 'box-primary', 'title' => 'Titration Method'])
                        <table class="table dataTable" id="purchasesTableAddMethods" style="width: 100%;">
                            <thead class="bg-gray" style="font-size: 12px;border-radius:4px;">
                                <tr>
                                    <th style="width:5%">@lang('method.hash_sign')</th>
                                    <th style="width:45%">@lang('method.method')</th>
                                    <th style="width:40%">@lang('method.files')</th>
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
                                        ]) !!}
                                        <datalist id="method_names" role="listbox">
                                            @foreach ($methods as $method)
                                                <option value="{{ $method->method_name }}"
                                                    data-id="{{ $method->id }}">
                                                </option>
                                            @endforeach
                                        </datalist>
                                    </td>
                                    <td style="width:40%">
                                        <div class="file-input-wrapper">
                                            <div class="file-input-button">Upload File</div>
                                            <input type="file" class="file-input" id="method_files_1"
                                                name="methods[1][method_files][]" multiple>
                                        </div>
                                        <div class="file-names" id="file-names-1">No file selected</div>
                                    </td>
                                    <td class="hidden">
                                        <input type="hidden" id="method_product_id_field_1"
                                            name="methods[1][product_id]" value="">
                                    </td>
                                    <td style="width:10%">
                                        <a class="btn btn-sm btn-primary addPurchaseRowCreateMethods"><i
                                                class="fa fa-plus"></i></a>
                                    </td>
                                </tr>
                            </tbody>

                        </table>
                        {{-- <div class="col-sm-3" style="display: none;">
                        <div class="form-group">
                            {!! Form::label('product_locations', __('business.business_locations') . ':') !!} @show_tooltip(__('lang_v1.product_location_help'))
                            {!! Form::select(
                                'product_locations[]',
                                $business_locations,
                                [$afmsl_location->id],
                                [
                                    'class' => 'form-control select2',
                                    'multiple',
                                    'id' => 'product_locations',
                                ],
                            ) !!}
                        </div>
                    </div> 
                    @endcomponent
                @endif
            </div>


            <style>
                .file-input-wrapper {
                    position: relative;
                    display: inline-block;
                    width: 100%;
                }

                .file-input-button {
                    display: inline-block;
                    cursor: pointer;
                    padding: 4px 10px;
                    border: 1px solid #ccc;
                    border-radius: 4px;
                    background-color: #e0e0e0;
                    color: #333;
                    font-size: 14px;
                }


                .file-input {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    opacity: 0;
                    cursor: pointer;
                }

                .file-names {
                    margin-top: 10px;
                    font-size: 14px;
                    color: #555;
                }
            </style>

        </div> --}}
        <div class="row justify-content-center" style="margin-bottom:10px;">
            {{-- create contract radio buttons  --}}
            <div class="col-sm-12 text-center">

                <div class="form-check form-check-inline" style="display: inline-block; margin-right: 10px;">
                    <input class="form-check-input" type="radio" name="received_status" id="received_by_afmsl"
                        value="Received by AFMSL">
                    <label class="form-check-label" for="received_by_afmsl"
                        style="display: inline-block; margin-left: 5px;">
                        Received By AFMSL
                    </label>
                </div>
                <div class="form-check form-check-inline" style="display: inline-block;">
                    <input class="form-check-input" type="radio" name="received_status" id="not_received_by_afmsl"
                        value="Not received by AFMSL">
                    <label class="form-check-label" for="not_received_by_afmsl"
                        style="display: inline-block; margin-left: 5px;">
                        Not Received
                    </label>
                </div>
                <input type="text" id="not_received_reason" name="not_received_reason"
                    style="display: none;margin-left:10px;padding:10px;border-radius:10px;border:none;background:#E5E4E2;width:250px;"
                    placeholder="Type reason for not receiving..." maxlength="100">
            </div>
        </div>
        <div class="row">
            <<div class="col-sm-12 text-center">
                <button type="button" id="myButton" class="btn btn-primary">@lang('messages.save')</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.cancel')</button>
        </div>
    </form>



    <div class="modal fade" id="checklistModal" tabindex="-1" role="dialog"
        aria-labelledby="checklistModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="checklistForm" method="POST" action="{{ route('purchases.save-checklist') }}">
                    @csrf

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tbody>
                                    @foreach ($checklist_items as $key => $item)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $item['name'] }}</td>
                                            <td>
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox"
                                                            name="checklist_items[{{ $key }}][complies]"
                                                            value="1" checked>
                                                        <input type="hidden"
                                                            name="checklist_items[{{ $key }}][name]"
                                                            value="{{ $item['name'] }}">
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{-- Disclaimer Per Batch --}}
                        <div style="margin-top:15px; border-top:1px solid #ddd; padding-top:10px;">
                            <h6 style="font-weight:bold; margin-bottom:8px;">Disclaimer</h6>

                            <input type="hidden" id="selected_line_id" value="">

                            <div style="display:flex; align-items:center; gap:8px;">

                                {{-- Batch Dropdown - chota --}}
                                <div style="flex: 0 0 180px;">
                                    <select id="disclaimer_batch_select" class="form-control form-control-sm"
                                        style="font-size:12px;">
                                        <option value="">-- Select Batch --</option>
                                        @foreach ($purchase->purchase_lines as $line)
                                            <option value="{{ $line->id }}"
                                                data-batch="{{ $line->batch->code ?? 'N/A' }}"
                                                data-product="{{ $line->product->name ?? '-' }}"
                                                data-disclaimer="{{ $line->disclaimer ?? '' }}">
                                                {{ $line->batch->code ?? 'N/A' }} — {{ $line->product->name ?? '-' }}
                                                {{ !empty($line->disclaimer) ? '✓' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Textarea - saath mein --}}
                                <div style="flex:1; display:none;" id="disclaimer_text_box">
                                    <textarea id="disclaimer_text" class="form-control form-control-sm" rows="1" placeholder="Enter disclaimer..."
                                        style="font-size:12px; resize:none; height:38px; overflow:hidden;"></textarea>
                                </div>

                                {{-- Save Button --}}
                                <div id="disclaimer_save_btn" style="display:none; flex-shrink:0;">
                                    <button type="button" id="saveDisclaimer" class="btn btn-warning btn-sm">
                                        <i class="fa fa-save"></i> Save
                                    </button>
                                    <span id="disclaimer_success"
                                        style="display:none; color:green; font-size:11px; display:block; margin-top:3px;">
                                        <i class="fa fa-check"></i> Saved!
                                    </span>
                                </div>

                            </div>
                        </div>

                    </div>
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="ref_no" value="{{ $transaction->ref_no }}">
                    <input type="hidden" name="transaction_id" value="{{ $transaction->id }}">


                    <!-- Checklist Modal Footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" id="saveChecklist" class="btn btn-primary">Save Checklist</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
<script>
    $(document).ready(function() {
        // Main form submit button handler
        $('#myButton').on('click', function(e) {
            e.preventDefault();
            let receivedByAfmsl = $('#received_by_afmsl').is(':checked');

            if (receivedByAfmsl) {
                // Show checklist modal but don't submit main form yet
                $('#checklistModal').modal('show');
            } else {
                // Submit main form directly if no checklist needed
                submitMainForm();
            }
        });

        // Checklist modal save button handler
        $('#saveChecklist').on('click', function(e) {
            e.preventDefault();
            submitChecklistForm();
        });

        // Main form submission function
        function submitMainForm() {
            // Show loading state
            $('#myButton').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

            // Submit the form via AJAX to handle the response properly
            $.ajax({
                url: $('#update_purchase_status_form').attr('action'),
                method: 'POST',
                data: $('#update_purchase_status_form').serialize(),
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.msg);
                        // Redirect or close modal as needed
                        window.location.href = "{{ route('purchase.view') }}";
                    } else {
                        toastr.error(response.msg);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Something went wrong');
                },
                complete: function() {
                    $('#myButton').prop('disabled', false).html('@lang('messages.save')');
                }
            });
        }

        // Checklist form submission function
        function submitChecklistForm() {
            let formData = {
                product_id: $('input[name="product_id"]').val(),
                ref_no: $('input[name="ref_no"]').val(),
                transaction_id: $('input[name="transaction_id"]').val(),
                checklist_items: [],
                _token: $('meta[name="csrf-token"]').attr('content')
            };

            // Collect checklist items
            $('[name^="checklist_items"]').each(function() {
                let nameParts = $(this).attr('name').match(/checklist_items\[(\d+)\]\[(\w+)\]/);
                if (nameParts) {
                    let index = nameParts[1];
                    let field = nameParts[2];
                    if (!formData.checklist_items[index]) {
                        formData.checklist_items[index] = {};
                    }
                    formData.checklist_items[index][field] = field === 'complies' ?
                        ($(this).is(':checked') ? 1 : 0) : $(this).val();
                }
            });

            formData.checklist_items = formData.checklist_items.filter(Boolean);

            // Submit checklist via AJAX
            $.ajax({
                url: "{{ route('purchases.save-checklist') }}",
                method: 'POST',
                data: formData,
                beforeSend: function() {
                    $('#saveChecklist').prop('disabled', true).html(
                        '<i class="fa fa-spinner fa-spin"></i> Saving...');
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.msg);
                        $('#checklistModal').modal('hide');
                        // Now submit the main form
                        submitMainForm();
                    } else {
                        toastr.error(response.msg);
                    }
                },
                error: function(xhr) {
                    let errorMsg = xhr.responseJSON?.message || 'Failed to save checklist';
                    toastr.error(errorMsg);
                    console.error('Error:', xhr.responseText);
                },
                complete: function() {
                    $('#saveChecklist').prop('disabled', false).text('Save Checklist');
                }
            });
        }

        // Prevent form submission on enter key in checklist modal
        $('#checklistForm').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                submitChecklistForm();
            }
        });
    });
</script>


<script>
    document.getElementById('method_files_1').addEventListener('change', function() {
        const fileInput = this;
        const fileNamesContainer = document.getElementById('file-names-1');
        const files = fileInput.files;

        if (files.length === 0) {
            fileNamesContainer.textContent = 'No file selected';
        } else {
            const fileNames = [];
            for (let i = 0; i < files.length; i++) {
                fileNames.push(files[i].name);
            }
            fileNamesContainer.textContent = fileNames.join(', ');
        }
    });
</script>
<script>
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
                var genericNames = response.generic_names;
                var pharmacopeia = response.pharmacopeia;
                var genericNameId = response.generic_name_id;
                var contractType = response.contract_type;
                var variation_id = response.variation_id;
                var batchesForSample = response.batches_for_sample;
                var currentQuantity = response.current_quantity;


                $('#product_id_field_1').val(selectedSampleId);

                $('#pv-column').html(
                    '<span style="font-size: 12px;"><strong>PV No:</strong> (<strong>' + (
                        pvNumber ? pvNumber : '-') + '</strong>)  </span>');
                $('#generic-column').html(
                    '<span style="font-size: 12px;"><strong>Generics: (' + (
                        genericNames.length > 0 ? genericNames.join(', ') : '-') +
                    ')</strong></span>'
                );
                $('#pharmacopeia-column').html(
                    '<span style="font-size: 12px;"><strong>Pharmacopeia:</strong> (<strong>' +
                    (
                        pharmacopeia ?
                        pharmacopeia : '-') + '</strong>)</span>');


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
                            <td class="serial-numberSt" style="width: 5%;">${tbodyRowCount}</td>
                            <td style="width: 30%;">

                                {!! Form::text('standards[${tbodyRowCount}][new_standard_code]', null, [
                                    'class' => 'form-control',
                                    'id' => 'new_standard_code_${tbodyRowCount}',
                                    'placeholder' => __('Standard Name '),
                                    'style' => 'width:100%;font-size:12px;',
                                    'list' => 'standard_codes',
                                    'autocomplete' => 'off',
                                ]) !!}
                                {!! Form::hidden('standards[${tbodyRowCount}][standard_id]', null, [
                                    'id' => 'standard_id_${tbodyRowCount}',
                                ]) !!}
                            </td>
                            <td style="width: 20%;">

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
                            <td style="width: 10%;">

                                <input type="number" name="standards[${tbodyRowCount}][st_quantity]" class="form-control" id="st_quantity_${tbodyRowCount}" min="0" placeholder="Enter Qty" autocomplete="off" value="0">

                </td>
                                        <td class="input-group" >
                              {!! Form::select('standards[${tbodyRowCount}][unit_id]', $units, $sample_unit_id, [
                                  'placeholder' => __('messages.please_select'),
                                  'class' => 'form-control select3',
                                  'required' => 'required',
                                  'style' => 'width: 150%;',
                              ]) !!}
                          </td>
                    <td style="width:10%;">

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
            $('.select3').select2();

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
                      <td style="width:40%;">
            <div class="file-input-wrapper">
                <div class="file-input-button">Upload File</div>
                <input type="file" class="file-input" id="method_files_${tbodyRowCount}" name="methods[${tbodyRowCount}][method_files][]" multiple>
            </div>
            <div class="file-names" id="file-names-${tbodyRowCount}"> </div>
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
        $(document).on('change', '.file-input', function() {
            const fileInput = this;
            const fileNamesContainer = fileInput
                .nextElementSibling; // Get the file-names div next to the file input
            const files = fileInput.files;

            if (files.length === 0) {
                fileNamesContainer.textContent = '';
            } else {
                const fileNames = [];
                for (let i = 0; i < files.length; i++) {
                    fileNames.push(files[i].name);
                }
                fileNamesContainer.textContent = fileNames.join(', ');
            }
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


        // $('#not_received_reason').hide();

        $('input[name="received_status"]').change(function() {
            if ($('#received_by_afmsl').is(':checked')) {
                $('#not_received_reason').hide();
            } else if ($('#not_received_by_afmsl').is(':checked')) {
                $('#not_received_reason').show();
            }
        });

        // Handle submission buttons
        $('button[type=submit]').on('click', function(e) {
            e.preventDefault();
            var form = $('#update_purchase_status_form')[0];
            if (form.checkValidity()) {
                form.submit();
            } else {
                $('<input type="submit">').hide().appendTo(form).click().remove();
            }
        });
    });
    // Disclaimer dropdown change
    $('#disclaimer_batch_select').on('change', function() {
        var lineId = $(this).val();
        var disclaimer = $(this).find(':selected').data('disclaimer');
        var batch = $(this).find(':selected').data('batch');

        if (lineId) {
            $('#selected_line_id').val(lineId);
            $('#disclaimer_text').val(disclaimer || '');
            $('#disclaimer_text').attr('placeholder', 'Enter disclaimer for batch ' + batch + '...');
            $('#disclaimer_text_box').slideDown(200);
            $('#disclaimer_save_btn').slideDown(200);

        } else {
            $('#disclaimer_text_box').slideUp(200);
            $('#disclaimer_save_btn').slideUp(200);
        }
    });

    // Save Disclaimer
    $('#saveDisclaimer').on('click', function() {
        var lineId = $('#selected_line_id').val();
        var text = $('#disclaimer_text').val().trim();

        if (!lineId) {
            toastr.warning('Please select a batch first.');
            return;
        }
        if (!text) {
            toastr.warning('Please enter disclaimer text.');
            return;
        }

        $('#saveDisclaimer').prop('disabled', true)
            .html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: '{{ route('purchases.save-disclaimer') }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                line_id: lineId,
                disclaimer: text
            },
            success: function(response) {
                if (response.success) {
                    var option = $('#disclaimer_batch_select option[value="' + lineId + '"]');
                    option.data('disclaimer', text);
                    if (option.text().indexOf('✓') === -1) {
                        option.text(option.text() + ' ✓');
                    }
                    $('#disclaimer_success').fadeIn().delay(2000).fadeOut();
                    toastr.success(response.msg);
                } else {
                    toastr.error(response.msg);
                }
            },
            error: function() {
                toastr.error('Something went wrong. Please try again.');
            },
            complete: function() {
                $('#saveDisclaimer').prop('disabled', false)
                    .html('<i class="fa fa-save"></i> Save Disclaimer');
            }
        });
    });

    // File input change — dynamic rows ke liye
    $(document).on('change', '.file-input', function() {
        var files = this.files;
        var fileNamesContainer = $(this).siblings('.file-names')[0];
        if (files.length === 0) {
            fileNamesContainer.textContent = '';
        } else {
            var fileNames = [];
            for (var i = 0; i < files.length; i++) {
                fileNames.push(files[i].name);
            }
            fileNamesContainer.textContent = fileNames.join(', ');
        }
    });
</script>
