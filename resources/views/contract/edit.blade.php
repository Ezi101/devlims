@extends('layouts.app')
@section('title', __('sale.edit_contract_inst'))

@section('content')
    <section class="content-header">
        <h1>@lang('sale.edit_contract_inst')</h1>
    </section>

    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            <div class="card mb-3">
                <div class="card-body">
                    <h4 class="card-title">@lang('product.contract_details')</h4>

                    {!! Form::open(['route' => ['contracts.update', $contract->id], 'method' => 'PUT']) !!}
                    <input type="hidden" name="c_type" value="supply">

                    <div class="row">
                        {{-- Contract No --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('product.contract_no')</label>
                                {!! Form::text('number', $contract->number, ['class' => 'form-control']) !!}
                            </div>
                        </div>

                        {{-- Supplier --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('product.supplier')</label>
                                <div class="input-group">
                                    @if ($contract->supplier)
                                        <select name="supplier_id" class="form-control select2" id="supplier_name">
                                            @foreach ($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}"
                                                    {{ $supplier->id == $contract->user_id ? 'selected' : '' }}>
                                                    {{ $supplier->text }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        {!! Form::text('supplier_name', null, ['class' => 'form-control', 'id' => 'supplier_name', 'readonly' => true]) !!}
                                    @endif
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default bg-white btn-flat" data-toggle="modal"
                                            data-target="#supplierModal">
                                            <i class="fa fa-plus-circle text-primary fa-lg"></i>
                                        </button>
                                    </span>
                                </div>
                                {!! Form::hidden('supplier_id', $contract->user_id, ['id' => 'supplier_id']) !!}
                            </div>
                        </div>

                        {{-- Fiscal Year --}}
                        <div class="form-group col-sm-6">
                            {!! Form::label('fiscal_year_id', __('product.fisc_yr') . ':*') !!}
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                <select name="fiscal_year_id" class="form-control select2" style="width:100%;" required>
                                    <option value="">@lang('messages.please_select')</option>
                                    @foreach ($fiscal_years as $fy)
                                        <option value="{{ $fy->id }}"
                                            {{ $contract->fiscal_year_id == $fy->id ? 'selected' : '' }}>
                                            {{ $fy->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Loc --}}
                        <div class="form-group col-sm-6">
                            <label>Loc:*</label>
                            @php
                                $locationOptions = [
                                    'lahore' => 'Lahore',
                                    'karachi' => 'Karachi',
                                    'rawalpindi' => 'Rawalpindi',
                                ];
                            @endphp
                            {!! Form::select('loc', $locationOptions, $contract->loc, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%;',
                                'required' => 'required',
                                'placeholder' => 'Select Location',
                            ]) !!}
                        </div>

                        {{-- Package Type --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('product.package_type')</label>
                                {!! Form::text('package_type', $contract->packages_type, ['class' => 'form-control']) !!}
                            </div>
                        </div>

                        {{-- Number of Packages --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('product.number_of_packages')</label>
                                {!! Form::text('num_of_package', $contract->number_of_packages, ['class' => 'form-control']) !!}
                            </div>
                        </div>

                        {{-- Total Instalment --}}
                        <div class="form-group col-sm-6">
                            <label>@lang('product.t_instalment'):*</label>
                            @php $d_types = ['1'=>'1','2'=>'2','3'=>'3','4'=>'4']; @endphp
                            {!! Form::select('t_instalment', $d_types, $contract->t_installment, [
                                'class' => 'form-control select2',
                                'id' => 't_instalment_select',
                                'placeholder' => 'Select',
                                'style' => 'width:100%;',
                            ]) !!}
                        </div>

                        {{-- Total Quantity --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('product.t_quantity')</label>
                                {!! Form::text('t_quantity', $contract->t_quantity, [
                                    'class' => 'form-control',
                                    'id' => 'total_quantity',
                                    'readonly' => true,
                                ]) !!}
                            </div>
                        </div>
                    </div>

                    {{-- ===== Installment Fields ===== --}}
                    @php
                        $instDates = is_array($contract->installment_dates)
                            ? $contract->installment_dates
                            : json_decode($contract->installment_dates, true) ?? [];
                        $instOrdinals = ['1st', '2nd', '3rd', '4th'];
                        $instDbKeys = ['1st_installment', '2nd_installment', '3rd_installment', '4rt_installment'];
                    @endphp

                    @for ($i = 1; $i <= 4; $i++)
                        @php
                            $d = $instDates[$i] ?? [];
                            $visible = $contract->t_installment >= $i;
                        @endphp
                        <div class="instalment-field instalment_{{ $i }}"
                            style="{{ $visible ? '' : 'display:none;' }} border:1px solid #ddd; border-radius:5px; padding:15px; margin-bottom:15px;">
                            <h5 style="font-weight:bold; color:#337ab7;">
                                <i class="fa fa-list-ol"></i> {{ $instOrdinals[$i - 1] }} Installment
                            </h5>
                            <div class="row">
                                {{-- Qty --}}
                                <div class="form-group col-sm-3">
                                    <label>{{ $instOrdinals[$i - 1] }} Installment Qty:*</label>
                                    {!! Form::number('instalment_' . $i, $contract->{$instDbKeys[$i - 1]}, [
                                        'class' => 'form-control form-control-sm instalment',
                                        'placeholder' => $instOrdinals[$i - 1] . ' Installment',
                                    ]) !!}
                                </div>
                                {{-- DD Date --}}
                                <div class="form-group col-sm-3">
                                    <label>DD Date:</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        {!! Form::text('inst' . $i . '_dd_date', $d['dd_date'] ?? null, [
                                            'class' => 'form-control form-control-sm datepicker dd-date-field',
                                            'placeholder' => 'DD Date',
                                            'readonly',
                                            'data-inst' => $i,
                                        ]) !!}
                                    </div>
                                </div>
                                {{-- Desired Offered Date --}}
                                <div class="form-group col-sm-3">
                                    <label>Desired Offered Date:</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        {!! Form::text('inst' . $i . '_desired_offered_date', $d['desired_offered_date'] ?? null, [
                                            'class' => 'form-control form-control-sm datepicker desired-date-field',
                                            'placeholder' => 'Desired Offered Date',
                                            'readonly',
                                            'data-inst' => $i,
                                        ]) !!}
                                    </div>
                                </div>
                                {{-- Offering Date --}}
                                <div class="form-group col-sm-3">
                                    <label>Offer Date:</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        {!! Form::text('inst' . $i . '_offering_date', $d['offering_date'] ?? null, [
                                            'class' => 'form-control form-control-sm datepicker',
                                            'placeholder' => 'Offer Date',
                                            'readonly',
                                        ]) !!}
                                    </div>
                                </div>
                                {{-- Sampling On --}}
                                <div class="form-group col-sm-3">
                                    <label>Sampling On:</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        {!! Form::text('inst' . $i . '_sampling_on', $d['sampling_on'] ?? null, [
                                            'class' => 'form-control form-control-sm datepicker',
                                            'placeholder' => 'Sampling On',
                                            'readonly',
                                        ]) !!}
                                    </div>
                                </div>
                                {{-- Shipment Date --}}
                                {{-- <div class="form-group col-sm-3">
                                    <label>Shipment Date:</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        {!! Form::text('inst' . $i . '_shipment_date', $d['shipment_date'] ?? null, [
                                            'class' => 'form-control form-control-sm datepicker',
                                            'placeholder' => 'Shipment Date',
                                            'readonly',
                                        ]) !!}
                                    </div>
                                </div> --}}
                                {{-- AFMSL Received Date --}}
                                <div class="form-group col-sm-3">
                                    <label>AFMSL Received Date:</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text" class="form-control form-control-sm"
                                            value="{{ $d['afmsl_received_date'] ?? '' }}" placeholder="Auto filled on receive"
                                            readonly>
                                    </div>
                                </div>
                                {{-- Acceptance Letter Date --}}
                                <div class="form-group col-sm-3">
                                    <label>Acceptance Letter Date:</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        {!! Form::text('inst' . $i . '_acceptance_letter_date', $d['acceptance_letter_date'] ?? null, [
                                            'class' => 'form-control form-control-sm datepicker',
                                            'placeholder' => 'Acceptance Letter Date',
                                            'readonly',
                                        ]) !!}
                                    </div>
                                </div>
                                {{-- Bulk Stamping Date --}}
                                <div class="form-group col-sm-3">
                                    <label>Bulk Stamping Date:</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        {!! Form::text('inst' . $i . '_bulk_stamping_date', $d['bulk_stamping_date'] ?? null, [
                                            'class' => 'form-control form-control-sm datepicker',
                                            'placeholder' => 'Bulk Stamping Date',
                                            'readonly',
                                        ]) !!}
                                    </div>
                                </div>
                                {{-- IEI Date --}}
                                <div class="form-group col-sm-3">
                                    <label>IEI Date:</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        {!! Form::text('inst' . $i . '_iei_approved_date', $d['iei_approved_date'] ?? null, [
                                            'class' => 'form-control form-control-sm datepicker',
                                            'placeholder' => 'IEI Date',
                                            'readonly',
                                        ]) !!}
                                    </div>
                                </div>
                                {{-- I Note Date --}}
                                <div class="form-group col-sm-3">
                                    <label>I Note Date:</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        {!! Form::text('inst' . $i . '_i_note_date', $d['i_note_date'] ?? null, [
                                            'class' => 'form-control form-control-sm datepicker',
                                            'placeholder' => 'I Note Date',
                                            'readonly',
                                        ]) !!}
                                    </div>
                                </div>
                                {{-- EU Opinion Date --}}
                                <div class="form-group col-sm-3">
                                    <label>EU Opinion Date:</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        {!! Form::text('inst' . $i . '_eu_opinion_date', $d['eu_opinion_date'] ?? null, [
                                            'class' => 'form-control form-control-sm datepicker',
                                            'placeholder' => 'EU Opinion Date',
                                            'readonly',
                                        ]) !!}
                                    </div>
                                </div>
                                {{-- Case Ref Date --}}
                                <div class="form-group col-sm-3">
                                    <label>Case Ref Date:</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        {!! Form::text('inst' . $i . '_case_ref_date', $d['case_ref_date'] ?? null, [
                                            'class' => 'form-control form-control-sm datepicker',
                                            'placeholder' => 'Case Ref Date',
                                            'readonly',
                                        ]) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endfor

                    {{-- Description --}}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                {!! Form::label('c_description', __('product.c_note') . ':') !!}
                                {!! Form::textarea('c_description', $contract->description, [
                                    'class' => 'form-control',
                                    'rows' => 2,
                                    'style' => 'resize:none;',
                                ]) !!}
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary pull-right">@lang('messages.save')</button>
                    <a href="{{ route('contracts.index') }}" class="btn btn-default pull-right" style="margin-right:5px;">
                        @lang('messages.cancel')
                    </a>

                    {!! Form::close() !!}
                </div>
            </div>
        @endcomponent
    </section>

    {{-- Supplier Modal --}}
    <div class="modal fade" id="supplierModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">@lang('product.add_supplier')</h4>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <label for="new_supplier_name">@lang('product.name')</label>
                    <input type="text" id="new_supplier_name" class="form-control" placeholder="@lang('product.supplier_name')">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('messages.close')</button>
                    <button type="button" class="btn btn-success" id="saveSupplier">@lang('messages.save')</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('javascript')
    <script>
        $(document).ready(function() {

            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                startView: 'years',
                minViewMode: 'days',
                autoclose: true,
                todayHighlight: true
            });

            // Installment show/hide
            $('#t_instalment_select').on('change', function() {
                var val = parseInt($(this).val());
                $('.instalment-field').hide();
                for (var i = 1; i <= val; i++) {
                    $('.instalment_' + i).show();
                }
            });
            // Jab user DD Date change kare datepicker se
            $(document).on('changeDate', '.dd-date-field', function() {
                var ddDate = $(this).val();
                var instNum = $(this).data('inst');
                if (ddDate) {
                    var desiredField = $('input[name="inst' + instNum + '_desired_offered_date"]');
                    var d = new Date(ddDate);
                    d.setDate(d.getDate() - 60);
                    var yyyy = d.getFullYear();
                    var mm = String(d.getMonth() + 1).padStart(2, '0');
                    var dd = String(d.getDate()).padStart(2, '0');
                    desiredField.val(yyyy + '-' + mm + '-' + dd);
                }
            });
            // Page load par existing DD dates se desired date calculate karo
            function calcDesiredFromDD(instNum, ddValue) {
                if (!ddValue) return;
                var desiredField = $('input[name="inst' + instNum + '_desired_offered_date"]');
                // Sirf tab fill karo jab field empty ho
                if (desiredField.val()) return;
                var d = new Date(ddValue);
                d.setDate(d.getDate() - 60);
                var yyyy = d.getFullYear();
                var mm = String(d.getMonth() + 1).padStart(2, '0');
                var dd = String(d.getDate()).padStart(2, '0');
                desiredField.val(yyyy + '-' + mm + '-' + dd);
            }

            // Page load par check karo
            for (var i = 1; i <= 4; i++) {
                calcDesiredFromDD(i, $('input[name="inst' + i + '_dd_date"]').val());
            }

            // Total quantity auto sum
            $('.instalment').on('input', function() {
                var total = 0;
                $('.instalment').each(function() {
                    var v = parseFloat($(this).val());
                    if (!isNaN(v)) total += v;
                });
                $('#total_quantity').val(total);
            });
        });

        // Supplier modal JS
        document.addEventListener("DOMContentLoaded", function() {
            let supplierDropdown = document.getElementById("supplier_name");
            let supplierIdField = document.getElementById("supplier_id");
            let newSupplierInput = document.getElementById("new_supplier_name");
            let saveSupplierBtn = document.getElementById("saveSupplier");

            if (supplierDropdown) {
                supplierDropdown.addEventListener("change", function() {
                    supplierIdField.value = this.options[this.selectedIndex].value;
                });
            }

            saveSupplierBtn.addEventListener("click", function() {
                let name = newSupplierInput.value.trim();
                if (!name) {
                    alert("Please enter a supplier name");
                    return;
                }

                fetch("{{ route('suppliers.storeNew') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({
                            name: name,
                            business_id: "{{ auth()->user()->business_id }}"
                        })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            let opt = document.createElement("option");
                            opt.text = data.supplier_business_name;
                            opt.value = data.id;
                            supplierDropdown.appendChild(opt);
                            supplierDropdown.value = data.id;
                            supplierIdField.value = data.id;
                            $("#supplierModal").modal("hide");
                            newSupplierInput.value = "";
                        } else {
                            alert("Error adding supplier");
                        }
                    })
                    .catch(e => console.error(e));
            });
        });
    </script>
@endsection
