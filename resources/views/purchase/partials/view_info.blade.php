<div class="modal-header">
    <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">&times;</span></button>

    <div class="row" style="margin-left: 20px; margin-right:20px;">
        @component('components.widget', ['class' => 'box-primary ', 'title' => 'Received stock details'])




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
                                @if ($purchase->status == 'Received by AFMSL')
                                    {{ ucfirst(__($purchase->status)) }}
                                    {{ \Carbon\Carbon::parse($purchase->d_rcv_by_afmsl)->format('j F Y H:i') }}
                                    <!-- Date and time -->
                                @elseif ($purchase->status == 'Forwarded by 2IC')
                                    {{ ucfirst(__($purchase->status)) }}:
                                    {{ \Carbon\Carbon::parse($purchase->d_fwd_by_2ic)->format('j F Y H:i') }}
                                    <!-- Date and time -->
                                @elseif ($purchase->status == 'Forwarded by AFIMS')
                                    {{ ucfirst(__($purchase->status)) }}:
                                    {{ \Carbon\Carbon::parse($purchase->d_fwd_by_afims)->format('j F Y H:i') }}
                                    <!-- Date and time -->
                                @else
                                    {{ ucfirst(__($purchase->status)) }} <!-- Other status -->
                                @endif
                            @endif
                            <br>
                        @endif
                    </div>


                    @if (isset($transaction->checklist))
                        <button type="button" class="btn btn-xs btn-default" data-toggle="modal"
                            data-target="#complianceModal">
                            <i class="fa fa-eye"></i> Compliance Details
                        </button>

                        <div class="modal fade custom-compliance-modal" id="complianceModal" tabindex="-1" role="dialog"
                            aria-hidden="true" data-backdrop="static">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content p-4"
                                    style="background: rgba(255,255,255,0.95); border-radius: 12px;">
                                    <button type="button" class="close custom-close" aria-label="Close"
                                        onclick="$('#complianceModal').modal('hide')"
                                        style="position: absolute; top: 10px; right: 15px; font-size: 2rem;">
                                        &times;
                                    </button>
                                    <table class="table table-hover table-bordered mt-4"
                                        style="background: white; border-radius: 8px; overflow: hidden;">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Checklist Item</th>
                                                <th>Complies</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach (json_decode($transaction->checklist->checklist_items, true) as $item)
                                                <tr>
                                                    <td>{{ $item['name'] }}</td>
                                                    <td class="text-center">{{ $item['complies'] ? 'Yes' : 'No' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <script>
                        $('#complianceModal').on('hidden.bs.modal', function() {
                            if ($('.modal:visible').length) {
                                $('body').addClass('modal-open');
                            }
                        });
                    </script>

                    <style>
                        .custom-compliance-modal .modal-content {
                            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
                            padding: 20px;
                        }

                        .custom-close {
                            background: none;
                            border: none;
                            height: 5px;
                            color: #333;
                            cursor: pointer;
                        }
                    </style>
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
                                        <th>@lang('batch.exp')</th>

                                        <th class="">
                                            @if ($purchase->type == 'purchase_order')
                                                @lang('lang_v1.order_quantity')
                                            @else
                                                @lang('purchase.purchase_quantity')
                                            @endif
                                        </th>


                                        <th> @lang('product.instalments')</th>
                                        {{-- <th> Location ID</th> --}}
                                        <th> @lang('product.contract_no')</th>
                                        <th> @lang('product.method')</th>
                                        <th> @lang('product.standard')</th>

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
                                            @if ($purchase_line->product->type == 'variable')
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

                                        <td>
                                            <span class="display_currency" data-is_quantity="true"
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
        <a href="{{ route('purchase.view_details', ['id' => $purchase->id]) }}" class="btn btn-primary float-right"
            style=" float: right;">
            <i class="fas fa-print" aria-hidden="true"></i> @lang('Print')
        </a>
        <input type="hidden" id="sample_id_field" name="search_nomenclature" value="{{ $sample_id }}">
        {{-- 
        @if (auth()->user()->can('others.issue_sample_shortcut'))
            <div class="row">
                <input type="hidden" id="sample_id_field" name="search_nomenclature" value="{{ $sample_id }}">

                <div class="col-sm-12 text-center">
                    <a href="{{ action([\App\Http\Controllers\SellController::class, 'create_new'], ['id' => $purchase->id, 'type' => 'purchase']) }}"
                        class="btn btn-big btn-success"><i class="fa-solid fa-share-from-square"></i>
                        {{ __('messages.issue_sample') }}</a>
                    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.cancel')</button>

                </div>
            </div>
        @endif --}}
    </div>
</div>

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


    });
</script>
