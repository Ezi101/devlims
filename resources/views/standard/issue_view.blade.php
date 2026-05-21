@extends('layouts.app')
@section('title', __('product.demand_req'))

@section('content')
@if ($transaction->product_type === 'standard')
<section class="content-header">
    <h1>@lang('product.demand_log')
        <i class="fa fa-keyboard-o hover-q text-muted" aria-hidden="true" data-container="body"
           data-toggle="popover" data-placement="bottom"
           data-content="@include('purchase.partials.keyboard_shortcuts_details')" data-html="true"
           data-trigger="hover" data-original-title="" title=""></i>
    </h1>
</section>
@endif

<!-- Main content -->
<section class="content">
    @include('layouts.partials.error')

    <form action="{{ route('demand.update_and_approve', ['id' => $transaction->id]) }}" method="POST"
          id="edit_demand_form">
        @csrf
        @method('POST')

        @if ($transaction->product_type === 'standard')
            @component('components.widget', ['class' => 'box-primary'])
                <div class="row">
                    <div class="col-sm-6">
                        {!! Form::label('standards[1][new_batch_code]', __('product.standard') . ':') !!}
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-search"></i>
                                </span>
                                <select name="standards[1][product_id]" id="search_nomenclature" class="form-control select2" disabled>
                                    <option value="">{{ __('lang_v1.search_standard_placeholder') }}</option>
                                    @foreach ($generics as $generic)
                                        <option value="{{ $generic->generic_id }}"
                                                {{ $generic->generic_id == $transaction->product->generic_name ? 'selected' : '' }}>
                                            {{ $generic->generic_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="input-group">
                            {!! Form::label('batch_quantity', __('purchase.batch_quantity') . ':', ['class' => 'input-group-text']) !!}
                            <input type="number" name="standards[1][st_quantity]" class="form-control"
                                   id="st_quantity_1" min="0" placeholder="Enter Qty" autocomplete="off"
                                   value="{{ $purchase_lines->where('product_type', 'standard')->first()->quantity ?? '' }}">
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="input-group">
                            {!! Form::label('batch', __('purchase.potency') . ':', ['class' => 'input-group-text']) !!}
                            <input type="number" name="standards[1][potency]" class="form-control"
                                   id="st_quantity_1" min="0" placeholder="Enter Potency" autocomplete="off" step="0.1"
                                   value="{{ $transaction->potency }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-3">
                        <div class="input-group">
                            {!! Form::label('Demand By', __('purchase.demand_by') . ':', ['class' => 'input-group-text']) !!}
                            <input type="text" name="standards[1][demand_by]" id="user" class="form-control"
                                   value="{{ $transaction->demand_by }}" readonly>
                        </div>
                    </div>


                    <div class="col-sm-3">
                        <div class="input-group">
                            {!! Form::label('batch', __('purchase.batch_no') . ':', ['class' => 'input-group-text']) !!}
                            <select name="standards[1][batch_no]" id="batch" class="form-control">
                                <option value="">{{ __('lang_v1.select_batch_placeholder') }}</option>
                                <option value="{{ $transaction->batch_no }}" selected>{{ $transaction->batch_no }}</option >
                            </select>
                            <div id="batch-info" class="mt-2" style="display: none;">
                                <small class="text-muted">
                                    <span id="mfg_date">(MFG: ),</span>
                                    <span id="expiry_date">(EXP: )</span>
                                </small>
                            </div>
                        </div>
                    </div>
                    

                <div class="row mt-3">
                    <div class="col-sm-6">
                        <div class="hidden">
                            <input type="hidden" id="product_id_field_1" name="standards[1][product_id]" value="{{ $purchase_lines->where('product_type', 'standard')->first()->product_id ?? '' }}">
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="hidden">
                            <input type="hidden" id="variation_id_field_standard" name="standards[1][variation_id]" value="{{ $purchase_lines->where('product_type', 'standard')->first()->variation_id ?? '' }}">
                        </div>
                    </div>
                </div>
            </div>

            @endcomponent
        @endif

        @if ($transaction->product_type === 'reagent')
            <section class="content-header">
                <h1>@lang('product.demand_chemical')
                    <i class="fa fa-keyboard-o hover-q text-muted" aria-hidden="true" data-container="body"
                       data-toggle="popover" data-placement="bottom"
                       data-content="@include('purchase.partials.keyboard_shortcuts_details')" data-html="true"
                       data-trigger="hover" data-original-title="" title=""></i>
                </h1>
            </section>
            <section class="content">
                @component('components.widget', ['class' => 'box-primary'])
                    <div class="row">
                        {{-- Reagents --}}
                        <div class="col-sm-6">
                            {!! Form::label('chemicals[1][new_batch_code]', __('product.chemical') . ':') !!}
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-search"></i>
                                    </span>
                                    <select name="chemicals[1][product_id]" id="search_nomenclature"
                                            class="form-control select2 " disabled>
                                        <option value="">{{ __('lang_v1.search_chemical_placeholder') }}</option>
                                        @foreach ($samples as $sample)
                                            <option value="{{ $sample->id }}"
                                                    {{ $sample->id == $transaction->product_id && $transaction->product_type == 'reagent' ? 'selected' : '' }}>
                                                {{ $sample->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="input-group">
                                {!! Form::label('batch_quantity', __('purchase.batch_quantity') . ':', ['class' => 'input-group-text']) !!}
                                <input type="number" name="chemicals[1][st_quantity]" class="form-control"
                                       id="st_quantity_1" min="0" placeholder="Enter Qty" autocomplete="off"
                                       value="{{ $purchase_lines->where('product_type', 'reagent')->first()->quantity ?? '' }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="input-group">
                                {!! Form::label('Demand By', __('purchase.demand_by') . ':', ['class' => 'input-group-text']) !!}
                                <input type="text" name="chemicals[1][demand_by]" id="user" class="form-control"
                                       value="{{ $transaction->demand_by }}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="hidden">
                                <input type="hidden" name="chemicals[1][variation_id]"
                                       value="{{ $purchase_lines->where('product_type', 'reagent')->first()->variation_id ?? '' }}">
                            </div>
                            <div class="hidden">
                                <input type="hidden" id="product_id_field_2" name="chemicals[1][product_id]" value="{{ $purchase_lines->where('product_type', 'reagent')->first()->product_id ?? '' }}">
                            </div>
                            @if ($business_locations)
                                @php
                                    $default_location = current(array_keys($business_locations->toArray()));
                                @endphp
                            @else
                                @php
                                    $default_location = '0';
                                @endphp
                            @endif
                            <div class="hidden">
                                <input type="hidden" name="chemicals[1][location_id]" value="{{ $default_location }}">
                            </div>
                        </div>
                    </div>
                @endcomponent
            </section>
        @endif

      
    </form>
</section>
@endsection

@section('javascript')
<script>
    $(document).ready(function () {
        $('.select2').select2();

        $('#search_nomenclature').on('change', function () {
            var selectedSampleId = $(this).val();

            // Make the AJAX request
            $.ajax({
                url: '/get-generic-info',
                method: 'GET',
                data: {
                    sample_id: selectedSampleId
                },
                success: function (response) {
                    var pvNumber = response.pv_number;
                    var genericName = response.generic_name;
                    var contractType = response.contract_type;
                    var variation_id = response.variation_id;
                    var currentQuantity = response.current_quantity;
                    var sampleId = response.sample_id;

                    $('#product_id_field_2').val(sampleId);
                    $('#variation_id_field_2').val(variation_id);

                    // Further actions based on response
                },
                error: function (xhr, status, error) {
                    console.error(error);
                }
            });
        });

        $('#search_nomenclature').change(function () {
            var selectedGenericId= $(this).val();

            // Make the AJAX request
            $.ajax({
                url: '/get-generic-info',
                method: 'GET',
                data: {
                    generic_id: selectedGenericId
                },
                success: function (response) {
                    var pvNumber = response.pv_number;
                    var genericName = response.generic_name;
                    var contractType = response.contract_type;
                    var variation_id = response.variation_id;
                    var batchesForSample = response.batches_for_sample;
                    var currentQuantity = response.current_quantity;
                    var sampleId = response.sample_id;

                    $('#supplier_id').prop('disabled', false);
                    $('#manufacturer_select_field').prop('disabled', false);

                    $('#product_id_field_1').val(sampleId);
                    $('#variation_id_field_1').val(variation_id);
                    $('#sample_id_contract_tender').val(sampleId);
                    $('#sample_id_contract_supply').val(sampleId);

                    // Further actions based on response
                },
                error: function (xhr, status, error) {
                    console.error(error);
                }
            });
        });

        $('#search_nomenclature').on('change', function () {
        var selectedGenericId = $(this).val();

        // Make the AJAX request to fetch batches for the selected generic ID
        $.ajax({
            url: '{{ route('get.batches') }}',
            method: 'GET',
            data: {
                generic_id: selectedGenericId
            },
            success: function (response) {
                var batchDropdown = $('#batch');
                batchDropdown.empty();
                batchDropdown.append('<option value="">{{ __('lang_v1.select_batch_placeholder') }}</option>');

                $.each(response.batches, function (index, batch) {
                    batchDropdown.append('<option value="' + batch.batch_no + '" data-mfg="' + batch.mfg_date + '" data-expiry="' + batch.expiry_date + '" data-potency="' + batch.potency + '">' + batch.batch_no + '</option>');
                });

                // Auto-select the first batch if available
                if (response.batches.length > 0) {
                    var firstBatch = response.batches[0];
                    $('#batch-info').html('<small class="text-muted">MFG: (' + firstBatch.mfg_date + '), EXP: (' + firstBatch.expiry_date + '), Potency: (' + firstBatch.potency + ')</small>').show();
                } else {
                    $('#batch-info').html('').hide();
                }
            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    });
    });

    // Handle change in batch dropdown to update batch info display
    $('#batch').change(function () {
        var selectedOption = $(this).find('option:selected');
        var mfgDate = selectedOption.data('mfg');
        var expiryDate = selectedOption.data('expiry');
        var potency = selectedOption.data('potency');

        if (mfgDate && expiryDate && potency !== undefined) {
            $('#batch-info').html('<small class="text-muted">MFG: (' + mfgDate + '), EXP: (' + expiryDate + '), Potency: (' + potency + ')</small>').show();
        } else {
            $('#batch-info').html('').hide();
        }
    });


    document.getElementById('edit_demand_form').addEventListener('submit', function () {
        document.querySelector('button[type="submit"]').disabled = true;
    });


    function rejectDemand(transactionId) {
   
    $.ajax({
        url: '/demand/' + transactionId + '/reject', 
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            transactionId: transactionId
        },
        success: function (response) {
            alert('Demand rejected successfully.');
            location.reload();
        },
        error: function (xhr, status, error) {
      
            alert('Error rejecting demand: ' + error);
        }
    });
}

</script>


<script>
    $(document).ready(function() {
        $('#search_nomenclature').change(function() {
            var productId = $(this).val();

            if (productId) {
                $.ajax({
                    url: '{{ route("get.batches") }}',
                    type: 'GET',
                    data: { product_id: productId },
                    success: function(response) {
                        if (response.batches.length > 0) {
                            var batchOptions = '<option value="">{{ __('lang_v1.select_batch_placeholder') }}</option>';
                            response.batches.forEach(function(batch) {
                                batchOptions += `<option value="${batch.batch_no}" data-mfg="${batch.mfg_date}" data-expiry="${batch.expiry_date}">${batch.batch_no}</option>`;
                            });
                            $('#batch').html(batchOptions).change();
                        } else {
                            $('#batch').html('<option value="">{{ __('lang_v1.select_batch_placeholder') }}</option>');
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                    }
                });
            } else {
                $('#batch').html('<option value="">{{ __('lang_v1.select_batch_placeholder') }}</option>');
            }
        });

        $('#batch').change(function() {
            var selectedOption = $(this).find('option:selected');
            var mfgDate = selectedOption.data('mfg');
            var expiryDate = selectedOption.data('expiry');

            if (mfgDate && expiryDate) {
                $('#batch-info').show();
                $('#mfg_date').text(`MFG: ${mfgDate}`);
                $('#expiry_date').text(`EXP: ${expiryDate}`);
            } else {
                $('#batch-info').hide();
            }
        });
    });
</script>
@endsection
