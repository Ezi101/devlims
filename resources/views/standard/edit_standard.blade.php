@extends('layouts.app')

@section('content')
    @component('components.widget', ['class' => 'box-primary', 'title' => __('reagent.standard_edit_stock_log_report')])
        <form id="editForm">
            <!-- Hidden fields for transaction ID and CSRF -->
            <input type="hidden" id="stock_id" name="id" value="{{ $transaction->id ?? '' }}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" id="recevied_by_afmsl_modal_hidden" name="recevied_by_afmsl" value="">

            <!-- Product Details -->
    <div class="row">
    <!-- 1 -->
    <div class="col-sm-3">
        <div class="form-group">
            <label>Standard Name</label>
            <select name="search_nomenclature" id="search_nomenclature" class="form-control select2"
                placeholder="{{ __('lang_v1.search_TmStandard_placeholder') }}">
                <option value="">{{ __('lang_v1.search_TmStandard_placeholder') }}</option>
                @foreach ($standards as $standard)
                    <option value="{{ $standard->name }}" {{ $transaction->product_id == $standard->id ? 'selected' : '' }}>
                        {{ ucfirst($standard->name) }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- 2 -->
    <div class="col-sm-3">
        <div class="form-group">
            {!! Form::label('brand_id', __('product.brand') . ':') !!}
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa-solid fa-industry"></i>
                </span>
                {!! Form::select('brand_id', $brands, $transaction->brand_id ?? null, [
                    'placeholder' => __('messages.please_select'),
                    'class' => 'form-control select2',
                    'id' => 'manufacturer_select_field',
                ]) !!}
            </div>
        </div>
    </div>

    <!-- 3 -->
    <div class="col-sm-3" id="DpCreateContainer">
        <div class="form-group">
            {!! Form::label('delivery_person', __('Delivered By') . ':') !!}
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa-solid fa-dolly"></i>
                </span>
                <select name="delivery_person_id" id="delivery_person_id" class="form-control select2" style="width:100%;">
                    <option value="">{{ __('messages.please_select') }}</option>
                    @foreach ($deliveryPersons as $person)
                        <option value="{{ $person->id }}"
                            data-image="{{ asset('uploads/' . $person->picture) }}"
                            {{ $transaction->delivery_person_id == $person->id ? 'selected' : '' }}>
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

    <!-- 4 -->


        <div class="col-sm-3">
    <div class="form-group">
        {!! Form::label('supplier_id', __('purchase.supplier') . ':*') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-user"></i>
            </span>
            {!! Form::select('contact_id', $suppliers, $transaction->contact_id ?? null, [
                'class' => 'form-control select2',
                'placeholder' => __('messages.please_select'),
                'required',
                'id' => 'supplier_id',
            ]) !!}
        </div>
    </div>
</div>
   
</div>

 

<div class="row mt-3">
    <!-- 5 -->
    <div class="col-sm-3">
        <div class="form-group">
            {!! Form::label('transability', __('Traceability') . ':') !!}
            {!! Form::text('transability', $transaction->transability ?? null, [
                'class' => 'form-control',
                'placeholder' => __('Enter Transability'),
                'required' => true,
            ]) !!}
        </div>
    </div>

    <!-- 6 -->
    <div class="col-sm-3">
        <div class="form-group">
            {!! Form::label('location', __('Location') . ':') !!}
            {!! Form::text('location', $transaction->location ?? null, [
                'class' => 'form-control',
                'placeholder' => __('Enter location'),
                'required' => true,
            ]) !!}
        </div>
    </div>

     <div class="col-sm-3" id="standardType">
        <div class="form-group">
            <label>{{ __('Standard Type') }}:</label>
            <div class="radio">
                <label>
                    <input type="radio" name="standard_type" value="primary" {{ $transaction->standard_type == 'primary' ? 'checked' : '' }}>
                    {{ __('Primary') }}
                </label>
            </div>
            <div class="radio">
                <label>
                    <input type="radio" name="standard_type" value="secondary" {{ $transaction->standard_type == 'secondary' ? 'checked' : '' }}>
                    {{ __('Secondary') }}
                </label>
            </div>
            <div class="radio">
                <label>
                    <input type="radio" name="standard_type" value="working" {{ $transaction->standard_type == 'working' ? 'checked' : '' }}>
                    {{ __('Working') }}
                </label>
            </div>
            <div id="standardCodeDisplay" style="margin-top: 10px; font-weight: bold;"></div>
        </div>
    </div>

    <!-- 7 -->
    <div class="col-sm-3">
        <div class="form-group">
            <label>Storage Condition</label>
            <select name="storage_condition" class="form-control">
                <option value="">Select Storage Condition</option>
                @foreach($conditions as $key => $condition)
                    <option value="{{ $key }}" 
                        {{ (isset($transaction) && trim(strtolower($transaction->item_type)) == trim(strtolower($key))) ? 'selected' : '' }}>
                        {{ $condition }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>


</div>


            <!-- Batch Details Table -->
            <h4>Batch Details</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Batch Code</th>
                        <th>Manufacturing Date</th>
                        <th>Expiry Date</th>
                        <th>Quantity</th>
                        <th>Potency</th>
                        <th>Acct Unit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchase_lines as $index => $line)
                        <tr>
                            <td>
                                <input type="text" name="purchase_lines[{{ $index }}][batch_code]" class="form-control" value="{{ $line->batch_code }}">
                            </td>
                            <td>
                                <input type="text" name="purchase_lines[{{ $index }}][mfg_date]" class="form-control datepicker" value="{{ $line->mfg_date }}">
                            </td>
                            <td>
                                <input type="text" name="purchase_lines[{{ $index }}][exp_date]" class="form-control datepicker" value="{{ $line->expiry_date }}">
                            </td>
                            <td>
                                <input type="number" name="purchase_lines[{{ $index }}][quantity]" class="form-control" value="{{ $line->quantity }}">
                            </td>
                            <td>
                                <input type="number" name="purchase_lines[{{ $index }}][potency]" class="form-control" value="{{ $line->potency }}">
                            </td>
                             <td>
                             <div class="form-group">
                                    {!! Form::select('unit_id', $units, $transaction->unit_id ?? null, [
                                      
                                        'class' => 'form-control select2',
                                        'style' => 'width: 100%;',
                                    ]) !!}
                                </div>

                                </td>
                            <!-- Hidden field to carry purchase_line_id -->
                            <input type="hidden" name="purchase_lines[{{ $index }}][purchase_line_id]" value="{{ $line->purchase_line_id }}">
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </form>

        <div class="mt-3">
            <button type="submit" form="editForm" id="update" class="btn btn-info">Update</button>
            <button type="button" id="receiveds_by_afmsl_btn" class="btn btn-success">Received by AFMSL</button>
        </div>
    @endcomponent

    <!-- Include jQuery and datepicker library if not already loaded -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.datepicker').datepicker({
                format: "MM yyyy", // format like "September 2023"
                minViewMode: 1,    // allow selection of month and year only
                autoclose: true,
                todayHighlight: true
            });
        });

        // Handle form submit for normal update
        $('#editForm').on('submit', function(e) {
            e.preventDefault();
            $('#recevied_by_afmsl_modal_hidden').val('');
            let id = $('#stock_id').val();
            let formData = $(this).serialize();
            $.ajax({
                url: `/stock/update/${id}`,
                type: 'Post',
                data: formData,
                success: function(response) {
                  
                    // console.log(response);
                    
                    
                    toastr.success("Stock updated successfully!", "Success");
                    
                },
                error: function() {
                    swal("Error!", "Error updating stock", "error");
                }
            });
        });

        // Handle Received by AFMSL update
        $('#receiveds_by_afmsl_btn').on('click', function() {
            $('#recevied_by_afmsl_modal_hidden').val('1');
            let id = $('#stock_id').val();
            let formData = $('#editForm').serialize();
            $.ajax({
                url: `/stock/update/${id}`,
                type: 'PUT',
                data: formData,
                success: function(response) {
                    toastr.success("Stock updated successfully!", "Success");
                    setTimeout(function() {
                        window.location.href = '{{ route('stock.index') }}';
                    }, 2000);
                },
                error: function() {
                    swal("Error!", "Error updating stock", "error");
                }
            });
        });
    </script>

    <script>
    $(document).ready(function () {
    // Load all suppliers into the supplier select
    $.ajax({
        url: '/get-all-suppliers', // Define this route
        method: 'GET',
        success: function (response) {
            $('#supplier_id').empty().append('<option value="">' + "{{ __('messages.please_select') }}" + '</option>');
            $.each(response, function (id, name) {
                $('#supplier_id').append('<option value="' + id + '">' + name + '</option>');
            });
        }
    });
});

    </script>
@endsection
