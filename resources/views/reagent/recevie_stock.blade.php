@extends('layouts.app')
@section('title', __('reagent.reagent'))

@section('content')

    @php
        $custom_labels = json_decode(session('business.custom_labels'), true);
    @endphp

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('reagent.recv_reagent')

        </h1>
    </section>

    <!-- Main content -->
    <section class="content">

         <!-- Page level currency setting -->
         <input type="hidden" id="p_code" value="{{ $currency_details->code }}">
         <input type="hidden" id="p_symbol" value="{{ $currency_details->symbol }}">
         <input type="hidden" id="p_thousand" value="{{ $currency_details->thousand_separator }}">
         <input type="hidden" id="p_decimal" value="{{ $currency_details->decimal_separator }}">

        @include('layouts.partials.error')

        {!! Form::open([
            'url' => action([\App\Http\Controllers\ReagentController::class, 'store_chemical']),
            'method' => 'post',
            'id' => 'add_purchase_form',
            'files' => true,
        ]) !!}
        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
                {{-- sample field --}}
                <div class="col-sm-6">
                    {!! Form::label('', __('product.chemical') . ':') !!}

                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-search"></i>
                            </span>
                            <select name="search_nomenclature" id="search_nomenclature" class="form-control select2"
                                placeholder="{{ __('lang_v1.search_chemical_placeholder') }}">
                                <option value="">{{ __('lang_v1.search_chemical_placeholder') }}</option>
                                @foreach ($samples as $sample)
                                    <option value="{{ $sample->id }}">
                                        {{ $sample->name }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="input-group-btn">
                                <button id="quickAddButton" tabindex="-1" type="button"
                                    class="btn btn-default bg-white btn-flat btn-modal"
                                    data-href="{{ action([\App\Http\Controllers\ReagentController::class, 'quickAdd']) }}"
                                    data-container=".quick_add_product">
                                    <i class="fa fa-plus-circle text-primary fa-lg"></i>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
                {{-- manufacturer field --}}
                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('brand_id', __('product.brand') . ':') !!}
                        <div class="input-group">
                            {{-- <span class="input-group-addon">
                                <i class="fa-solid fa-industry"></i>
                            </span> --}}
                            {!! Form::select(
                                'brand_id',
                                $brands,
                                !empty($duplicate_product->brand_id) ? $duplicate_product->brand_id : null,
                                [
                                    'placeholder' => __('messages.please_select'),
                                    'class' => 'form-control select2',
                                    'id' => 'manufacturer_select_field',
                                    'disabled' => 'disabled',
                                ],
                            ) !!}
                            <span class="input-group-btn">
                                <button type="button" @if (!auth()->user()->can('brand.create')) disabled @endif
                                    class="btn btn-default bg-white btn-flat btn-modal"
                                    data-href="{{ action([\App\Http\Controllers\BrandController::class, 'create'], ['quick_add' => true]) }}"
                                    title="@lang('brand.add_brand')" data-container=".view_modal">
                                    <i class="fa fa-plus-circle text-primary fa-lg"></i>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
                {{-- supplier field --}}
                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('supplier_id', __('purchase.supplier') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-user"></i>
                            </span>
                            {!! Form::select('contact_id', [], null, [
                                'class' => 'form-control',
                                'placeholder' => __('messages.please_select'),
                                'required',
                                'id' => 'supplier_id',
                            ]) !!}
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default bg-white btn-flat add_new_supplier"
                                    data-name="">
                                    <i class="fa fa-plus-circle text-primary fa-lg"></i>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="after_sample_row_fields" style="display:none;">
                <div class="row">
                    {{-- receiving date field --}}

                    <div class="col-sm-3" style="display: none;">
                        <div class="form-group">
                            {!! Form::label('transaction_date', __('purchase.purchase_date') . ':*') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </span>
                                {!! Form::text('transaction_date', @format_datetime('now'), ['class' => 'form-control', 'readonly', 'required']) !!}
                            </div>
                        </div>
                    </div>


                    {{-- reference no hidden --}}
                    <div class="col-sm-3" hidden>
                        <div class="form-group">
                            {!! Form::label('ref_no', __('purchase.ref_no') . ':') !!}
                            @show_tooltip(__('lang_v1.leave_empty_to_autogenerate'))
                            {!! Form::text('ref_no', null, ['class' => 'form-control']) !!}
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="input-group">
                            {!! Form::label('batch_no', __('Batch No') . ':') !!}
                            <input type="text" name="batch_no" class="form-control" id="batch_no"
                                placeholder="Enter Batch No" autocomplete="off" required>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            {!! Form::label('mfg_date', __('Opening Date') . ':*') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </span>
                                {!! Form::text('mfg_date', null, [
                                    'class' => 'form-control month-year-picker',
                                    'placeholder' => __('Select Month & Year'),
                                    'required',
                                    'autocomplete' => 'off',
                                ]) !!}
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-3">
                        <div class="form-group">
                            {!! Form::label('expiry_date', __('Exp Date') . ':*') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </span>
                                {!! Form::text('expiry_date', null, [
                                    'class' => 'form-control month-year-picker',
                                    'placeholder' => __('Select Month & Year'),
                                    'required',
                                    'autocomplete' => 'off',
                                ]) !!}
                            </div>
                        </div>
                    </div>

                   <div class="col-sm-3">
                        <div class="form-group"> <!-- Changed from input-group to form-group for proper Bootstrap styling -->
                            {!! Form::label('batch_quantity', __('purchase.batch_quantity') . ':') !!}
                                          <input    type="number" name="batch_quantity" class="form-control"   id="batch_quantity"   min="0" 
                                                    step="0.001" 
                                                    placeholder="Enter Quantity" 
                                                    autocomplete="off" 
                                                    value="1.00"
                                                    onkeypress="if (event.key === '-' || event.key === 'e') event.preventDefault();">
                        </div>
                    </div>

                </div>
                <div class="row">
                    {{-- business location field hidden for now --}}
                    @if ($business_locations)
                        @php
                            $default_location = current(array_keys($business_locations->toArray()));
                            $search_disable = false;
                        @endphp
                    @else
                        @php
                            $default_location = '0';
                            $search_disable = true;
                        @endphp
                    @endif
                    <div class="col-sm-3">
                        <div class="form-group">
                            {!! Form::label('unit', __('purchase.unit') . ':*') !!}
                            {!! Form::select('unit', $units->pluck('actual_name', 'id'), null, [
                                'class' => 'form-control select2',
                                'placeholder' => __('messages.please_select'),
                                'required' => true,
                                'style' => 'width:100%;',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-sm-3" hidden>
                        <div class="form-group">
                            {!! Form::label('location_id', __('purchase.business_location') . ':*') !!}
                            {!! Form::select(
                                'location_id',
                                $business_locations,
                                $default_location,
                                ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required'],
                                $bl_attributes,
                            ) !!}
                        </div>
                    </div>
                    {{-- status field recieve by  --}}
                    @php
                        $orderStatuses = [
                            'Received by AFMSL' => 'Received by AFMSL',
                            'Forward by AFIMS' => 'Forward by AFIMS',
                            'draft' => 'Draft',
                        ];
                        $statusOptions = [];

                        foreach ($orderStatuses as $key => $orderStatus) {
                            if (strtolower($orderStatus) == 'received by afmsl') {
                                if (Auth::user()->can('purchase.receive_status')) {
                                    $statusOptions[$key] = ucfirst($orderStatus);
                                }
                            }
                            if (strtolower($orderStatus) == 'forward by afims') {
                                if (Auth::user()->can('purchase.pending_status')) {
                                    $statusOptions[$key] = ucfirst($orderStatus);
                                }
                            }
                            if (strtolower($orderStatus) == 'draft') {
                                if (Auth::user()->can('purchase.ordered_status')) {
                                    $statusOptions[$key] = ucfirst($orderStatus);
                                }
                            }
                        }
                    @endphp
                    {{-- delivery person field --}}
                    <div class="col-sm-3">
                        <div class="form-group">
                            {!! Form::label('delivery_person', __('Delivered By') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa-solid fa-dolly"></i>
                                </span>
                                <select class="form-control select2" style="width:100%;">
                                    <option value="">{{ __('messages.please_select') }}</option>
                                    @foreach ($deliveryPersons as $person)
                                        <option value="{{ $person->id }}"
                                            data-image="{{ asset('uploads/' . $person->picture) }}">
                                            {{ $person->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default bg-white btn-flat btn-modal"
                                        id="openQuickAddDpModal" title="@lang('Delivered By')">
                                        <i class="fa fa-plus-circle text-primary fa-lg"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>


                    <div class="col-sm-3" id="storageCondition">
                        <div class="form-group">
                            {!! Form::label('storage_condition', __('Storage Condition') . ':') !!}
                            {!! Form::select(
                                'storage_condition',
                                [
                                    'Refrigerated' => 'Refrigerated Item',
                                    'non-Refrigerated' => 'Non-Refrigerated Item',
                                ],
                                null,
                                [
                                    'class' => 'form-control select2',
                                    'placeholder' => __('Select Storage Condition'),
                                    'required' => true,
                                ],
                            ) !!}
                        </div>
                    </div>


                </div>
            </div>
        @endcomponent



        <div class="row">
            {!! Form::hidden('recevied_by_afmsl', 0, ['id' => 'recevied_by_afmsl_hidden']) !!}

            <div class="col-sm-12 text-center">
                {{-- @can('purchase.save_draft')
                     <button type="button" id="save-button-big" class="btn btn-md btn-primary">@lang('lang_v1.save_draft')</button>
                 @endcan --}}



              
                    <button type="button" id="recevied_by_afmsl" class="btn btn-md btn-primary">@lang('lang_v1.save')</button>
              
            </div>
        </div>
        <input type="hidden" name="product_type" value="reagent">
        <input type="hidden" name="variation_id" id="variation_id_field" value="">

        {!! Form::close() !!}



    </section>
    <div class="modal fade quick_add_product" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>
    <div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        @include('contact.create', ['quick_add' => true])
    </div>

    @include('purchase.partials.import_purchase_products_modal')
    <div class="modal fade" id="quickAddDpModal" tabindex="-1" role="dialog" aria-labelledby="quickAddModalLabel"
        aria-hidden="true">

        @include('delivery_persons.quickAddDp')
    </div>
    <!-- /.content -->
    <style>
        #createContractButton {
            transition: background-color 0.3s;
        }

        #createContractButton.active {
            background-color: #28B97B;
            color: white;
        }
    </style>
@endsection

@section('javascript')
    <script src="{{ asset('js/purchase.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>


    <script type="text/javascript">
        $(document).ready(function() {
            $('.month-year-picker').datepicker({
                format: 'MM yyyy', // Important: 'MM' for full month, 'yyyy' for 4-digit year
                startView: 'years', // Opens year selection first
                minViewMode: 'months', // Restricts to month selection
                autoclose: true,
                todayHighlight: true,
            }).on('changeDate', function(e) {
                // Force format after selection (optional)
                $(this).val($(this).datepicker('getFormattedDate'));
            });
        });





        $(document).ready(function() {



            $('#openQuickAddDpModal').click(function() {
                $('#quickAddDpModal').modal('show');
            });
            $('#dp_qaf_save_button').on('click', function() {
                var form = $('#delivery_person_quick_add_form');
                var url = form.attr('action');
                var method = form.attr('method');
                var formData = new FormData(form[0]); // Use FormData to handle file uploads

                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {

                            var newOption = $('<option>', {
                                value: response.delivery_person_id,
                                text: response.delivery_person_name
                            });
                            $('#delivery_person_id').prepend(newOption).val(response
                                .delivery_person_id).trigger('change');

                            $('#quickAddDpModal').modal('hide');
                            form[0].reset();

                            toastr.success('Delivery person added successfully!');
                        } else {
                            toastr.error('Failed to add delivery person: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        toastr.error('Something went wrong.');
                    }
                });
            });

            $('#search_nomenclature').on('change', function() {
                var selectedSampleId = $(this).val();

                // Make the AJAX request
                $.ajax({
                    url: '/get-generic-info',
                    method: 'GET',
                    data: {
                        sample_id: selectedSampleId
                    },
                    success: function(response) {

                        var contractType = response.contract_type;
                        var variation_id = response.variation_id;
                        var currentQuantity = response.current_quantity;
                        var sampleId = response.sample_id;

                        $('.after_sample_row_fields').show();
                        $('#supplier_id').prop('disabled', false);
                        $('#manufacturer_select_field').prop('disabled', false);
                        $('#variation_id_field').val(variation_id);
                        $('#sample_id_contract_tender').val(sampleId);
                        $('#sample_id_contract_supply').val(sampleId);




                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching sample info:', error);
                    }
                });

            });
        });


        $('#recevied_by_afmsl').on('click', function() {
            $('#recevied_by_afmsl_hidden').val(1); // Set the hidden input value to 1
            if ($('#add_purchase_form')[0].checkValidity()) {
                $(this).prop('type', 'submit');
                $('#add_purchase_form').submit();
            } else {
                $('#add_purchase_form')[0].reportValidity();
            }



        });
    </script>



@endsection
