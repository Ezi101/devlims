@extends('layouts.app')
@section('title', __('sale.add_contract'))

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('sale.add_contract')
        </h1>
    </section>


    <section class="content">
        {{-- radio button and sample field and supplier field --}}
        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
                {{-- <div class="col-sm-4">
                    <div class="form-group">
                        <label>Sample:*</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-search"></i></span>
                            <select name="sample_id" id="sample_id" class="form-control" style="width: 100%;">
                                <option value="">Search Sample</option>
                            </select>
                        </div>
                    </div>
                </div> --}}

                {{-- <div class="col-sm-4">
                    <div class="form-group">
                        <label>Supplier:*</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-user"></i></span>
                            <select name="contact_id" id="supplier_id" class="form-control" style="width: 100%;">
                                <option value="">Search Supplier</option>
                            </select>
                        </div>
                    </div>
                </div> --}}
                <div class="col-sm-4">
                    <div class="form-group" style="position: relative;"> <label>Sample:*</label>
                        <div class="input-group">
                            <input type="text" id="sample_search_custom" class="form-control" placeholder="Search Sample..."
                                autocomplete="off">
                            <span class="input-group-btn">
                                <button type="button" id="btn_search_sample" class="btn btn-primary">
                                    <i class="fa fa-search"></i>
                                </button>
                            </span>
                        </div>
                        <ul id="sample_results_list" class="custom-search-results"></ul>
                    </div>
                </div>

                <div class="col-sm-4">
                    <div class="form-group" style="position: relative;">
                        <label>Supplier:*</label>
                        <div class="input-group">
                            <input type="text" id="supplier_search_custom" class="form-control"
                                placeholder="Search Supplier..." autocomplete="off">
                            <span class="input-group-btn">
                                <button type="button" id="btn_search_supplier" class="btn btn-primary">
                                    <i class="fa fa-search"></i>
                                </button>
                            </span>
                        </div>
                        <ul id="supplier_results_list" class="custom-search-results"></ul>
                    </div>
                </div>

                <div class="col-sm-4" id="contractsCreateContainer">
                    <label for="contract_type">@lang('product.nomen_type'):*</label>
                    <div style="margin-top: 5px;">
                        <label class="radio-inline">
                            <input type="radio" name="contract_type" class="contract_type" value="tender"> @lang('product.tender')
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="contract_type" class="contract_type" value="supply"> @lang('product.supply')
                        </label>
                    </div>
                </div>
            </div>

            <div id="additional_fields" style="display: none; margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px;">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            {!! Form::label('te_number', __('T/E Number') . ':*') !!}
                            {!! Form::text('te_number', null, ['class' => 'form-control', 'placeholder' => 'T/E Number']) !!}
                        </div>
                    </div>
                </div>
            </div>
        @endcomponent

        {{-- supply form --}}
        <div style="display:none;" id="contractSupplyFormContainerMain">
            @component('components.widget', ['class' => 'box-solid'])
                {!! Form::open([
                    'url' => action([\App\Http\Controllers\ContractController::class, 'store']),
                    'method' => 'post',
                    'id' => 'new_contract_add_form_supply_main',
                ]) !!}

                <div class="row">
                    {{-- contract number field --}}
                    <div class="form-group c_number_div col-sm-4">
                        {!! Form::label('number', __('product.c_number') . ':*', ['class' => 'c_number_label']) !!}
                        {!! Form::text('number', null, [
                            'class' => 'form-control form-control-sm c_number',
                            'required',
                            'placeholder' => __('product.c_number'),
                        ]) !!}
                    </div>
                    {{-- offering date --}}
                    {{-- <div class="form-group supply-fields col-sm-4">
                        {!! Form::label('offering_date', __('product.offer_date') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            <input type="text" id="offering_date" name="offering_date" class="form-control form-control-sm"
                                placeholder="{{ __('product.offer_date') }}" autocomplete="off">
                        </div>
                    </div> --}}
                    {{-- fiscal year --}}
                    <div class="form-group supply-fields col-sm-4">
                        {!! Form::label('fiscal_year_id', __('product.fisc_yr') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            <select id="fiscal_year_id" name="fiscal_year_id" class="form-control select2 form-control-sm"
                                style="width: 100%;" required>
                                <option value="">@lang('messages.please_select')</option>
                                @foreach ($fiscal_years as $fiscal_year)
                                    <option value="{{ $fiscal_year->id }}">{{ $fiscal_year->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    {{-- Acceptance Letter --}}
                    {{-- <div class="form-group col-sm-4">
                        {!! Form::label('acceptance_letter_date', 'Acceptance Letter No:*') !!}
                        {!! Form::text('acceptance_letter_date', null, [
                            'class' => 'form-control form-control-sm',
                            'placeholder' => 'Enter Acceptance Letter No',
                        ]) !!}
                    {{-- </div> --}}
                    {{-- IEI Approved Date --}}
                    {{-- <div class="form-group col-sm-4">
                        {!! Form::label('iei_approved_date', 'IEI Approved Date:*') !!}
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            {!! Form::text('iei_approved_date', null, [
                                'class' => 'form-control form-control-sm datepicker',
                                'placeholder' => 'Select IEI Approved Date',
                                'readonly',
                            ]) !!}
                        </div>
                    </div> --}}
                    {{-- Bulk Sampling Date --}}
                    {{-- <div class="form-group col-sm-4">
                        {!! Form::label('bulk_sampling_date', 'Bulk Sampling Date:*') !!}
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            {!! Form::text('bulk_sampling_date', null, [
                                'class' => 'form-control form-control-sm datepicker',
                                'placeholder' => 'Select Bulk Sampling Date',
                                'readonly',
                            ]) !!}
                        </div>
                    </div> --}}
                    {{-- Desired Offered Date
                    <div class="form-group col-sm-4">
                        {!! Form::label('desired_offered_date', 'Desired Offered Date:*') !!}
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            {!! Form::text('desired_offered_date', null, [
                                'class' => 'form-control form-control-sm datepicker',
                                'placeholder' => 'Select Desired Offered Date',
                                'readonly',
                            ]) !!}
                        </div>
                    </div> --}}
                    {{-- Sampling On --}}
                    {{-- <div class="form-group col-sm-4">
                        {!! Form::label('sampling_on', 'Sampling On:*') !!}
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            {!! Form::text('sampling_on', null, [
                                'class' => 'form-control form-control-sm datepicker',
                                'placeholder' => 'Select Sampling On Date',
                                'readonly',
                            ]) !!}
                        </div>
                    </div> --}}
                </div>

                <div class="row">
                    {{-- Loc --}}
                    <div class="form-group supply-fields col-sm-4">
                        {!! Form::label('loc', 'Loc:*') !!}
                        @php
                            $locationOptions = [
                                'lahore' => 'Lahore',
                                'karachi' => 'Karachi',
                                'rawalpindi' => 'Rawalpindi',
                            ];
                        @endphp
                        {!! Form::select('loc', $locationOptions, null, [
                            'class' => 'form-control select2 form-control-sm',
                            'style' => 'width:100%;',
                            'required' => 'required',
                            'placeholder' => 'Select Location',
                        ]) !!}
                    </div>
                    {{-- Package Type --}}
                    <div class="form-group supply-fields col-sm-4">
                        {!! Form::label('package_type', __('product.package_type') . ':*') !!}
                        {!! Form::text('package_type', null, [
                            'class' => 'form-control form-control-sm',
                            'placeholder' => __('product.package_type'),
                        ]) !!}
                    </div>
                    {{-- Number of Packages --}}
                    <div class="form-group supply-fields col-sm-4">
                        {!! Form::label('num_of_package', __('product.number_of_packages') . ':*') !!}
                        {!! Form::text('num_of_package', null, [
                            'class' => 'form-control form-control-sm',
                            'placeholder' => __('product.number_of_packages'),
                        ]) !!}
                    </div>
                    {{-- contract type hidden --}}
                    <div class="form-group" hidden>
                        <input type="text" class="form-control form-control-sm" name="c_type" value="supply" readonly>
                    </div>
                    {{-- Total Instalment Select --}}
                    <div class="form-group supply-fields col-sm-4">
                        {!! Form::label('t_instalment', __('product.t_instalment') . ':*') !!}
                        @php
                            $d_types = ['1' => '1', '2' => '2', '3' => '3', '4' => '4'];
                        @endphp
                        {!! Form::select('t_instalment', $d_types, null, [
                            'class' => 'form-control select2 form-control-sm',
                            'id' => 't_instalment_select',
                            'placeholder' => __('product.please_select_no_inst'),
                            'style' => 'width:100%;',
                            'required' => 'required',
                        ]) !!}
                    </div>
                </div>

                {{-- Instalment Fields --}}

                {{-- Installment 1 --}}
                <div class="instalment-field instalment_1"
                    style="display:none; border: 1px solid #ddd; border-radius: 5px; padding: 15px; margin-bottom: 15px;">
                    <h5 style="font-weight: bold; color: #337ab7;"><i class="fa fa-list-ol"></i> 1st Installment</h5>
                    <div class="row">
                        {{-- 1. Qty --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('instalment_1', '1st Installment Qty:*') !!}
                            {!! Form::number('instalment_1', null, [
                                'class' => 'form-control form-control-sm instalment',
                                'placeholder' => '1st Installment',
                            ]) !!}
                        </div>
                        {{-- 2. DD Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst1_dd_date', 'DD Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst1_dd_date', null, [
                                    'class' => 'form-control form-control-sm datepicker dd-date-field',
                                    'placeholder' => 'DD Date',
                                    'readonly',
                                    'data-inst' => '1',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 3. Desired Offered Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst1_desired_offered_date', 'Desired Offered Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst1_desired_offered_date', null, [
                                    'class' => 'form-control form-control-sm datepicker desired-date-field',
                                    'placeholder' => 'Desired Offered Date',
                                    'readonly',
                                    'data-inst' => '1',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 4. Offering Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst1_offering_date', 'Offer Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst1_offering_date', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'Offer Date',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 5. Sampling On --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst1_sampling_on', 'Sampling On:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst1_sampling_on', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'Sampling On',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 6. Shipment Date --}}
                        {{-- <div class="form-group col-sm-3">
                            {!! Form::label('inst1_shipment_date', 'Shipment Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst1_shipment_date', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'Shipment Date',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div> --}}
                        {{-- 7. AFMSL Received Date (naya field) --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst1_afmsl_received_date', 'AFMSL Received Date:') !!}
                            {!! Form::text('inst1_afmsl_received_date', null, [
                                'class' => 'form-control form-control-sm',
                                'placeholder' => 'Auto filled on receive',
                                'readonly',
                            ]) !!}
                        </div>
                        {{-- 8. Acceptance Letter Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst1_acceptance_letter_date', 'Acceptance Letter Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst1_acceptance_letter_date', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'Acceptance Letter Date',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 9. Bulk Stamping Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst1_bulk_stamping_date', 'Bulk Stamping Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst1_bulk_stamping_date', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'Bulk Stamping Date',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 10. IEI Approved Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst1_iei_approved_date', 'IEI Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst1_iei_approved_date', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'IEI Date',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 11. I Note Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst1_i_note_date', 'I Note Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst1_i_note_date', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'I Note Date',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 12. EU Opinion Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst1_eu_opinion_date', 'EU Opinion Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst1_eu_opinion_date', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'EU Opinion Date',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 13. Case Ref Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst1_case_ref_date', 'Case Ref Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst1_case_ref_date', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'Case Ref Date',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Installment 2 --}}
                <div class="instalment-field instalment_2"
                    style="display:none; border: 1px solid #ddd; border-radius: 5px; padding: 15px; margin-bottom: 15px;">
                    <h5 style="font-weight: bold; color: #337ab7;"><i class="fa fa-list-ol"></i> 2nd Installment</h5>
                    <div class="row">
                        {{-- 1. Qty --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('instalment_2', '2nd Installment Qty:*') !!}
                            {!! Form::number('instalment_2', null, [
                                'class' => 'form-control form-control-sm instalment',
                                'placeholder' => '2nd Installment',
                            ]) !!}
                        </div>
                        {{-- 2. DD Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst2_dd_date', 'DD Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst2_dd_date', null, [
                                    'class' => 'form-control form-control-sm datepicker dd-date-field',
                                    'placeholder' => 'DD Date',
                                    'readonly',
                                    'data-inst' => '2',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 3. Desired Offered Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst2_desired_offered_date', 'Desired Offered Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst2_desired_offered_date', null, [
                                    'class' => 'form-control form-control-sm datepicker desired-date-field',
                                    'placeholder' => 'Desired Offered Date',
                                    'readonly',
                                    'data-inst' => '2',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 4. Offering Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst2_offering_date', 'Offer Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst2_offering_date', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'Offer Date',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 5. Sampling On --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst2_sampling_on', 'Sampling On:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst2_sampling_on', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'Sampling On',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 6. Shipment Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst2_shipment_date', 'Shipment Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst2_shipment_date', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'Shipment Date',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 7. AFMSL Received Date (naya field) --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst2_afmsl_received_date', 'AFMSL Received Date:') !!}
                            {!! Form::text('inst2_afmsl_received_date', null, [
                                'class' => 'form-control form-control-sm',
                                'placeholder' => 'Auto filled on receive',
                                'readonly',
                            ]) !!}
                        </div>
                        {{-- 8. Acceptance Letter Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst2_acceptance_letter_date', 'Acceptance Letter Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst2_acceptance_letter_date', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'Acceptance Letter Date',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 9. Bulk Stamping Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst2_bulk_stamping_date', 'Bulk Stamping Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst2_bulk_stamping_date', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'Bulk Stamping Date',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 10. IEI Approved Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst2_iei_approved_date', 'IEI Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst2_iei_approved_date', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'IEI Date',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 11. I Note Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst2_i_note_date', 'I Note Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst2_i_note_date', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'I Note Date',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 12. EU Opinion Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst2_eu_opinion_date', 'EU Opinion Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst2_eu_opinion_date', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'EU Opinion Date',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 13. Case Ref Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst2_case_ref_date', 'Case Ref Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst2_case_ref_date', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'Case Ref Date',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Installment 3 --}}
                <div class="instalment-field instalment_3"
                    style="display:none; border: 1px solid #ddd; border-radius: 5px; padding: 15px; margin-bottom: 15px;">
                    <h5 style="font-weight: bold; color: #337ab7;"><i class="fa fa-list-ol"></i> 3rd Installment</h5>
                    <div class="row">
                        {{-- 1. Qty --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('instalment_3', '3rd Installment Qty:*') !!}
                            {!! Form::number('instalment_3', null, [
                                'class' => 'form-control form-control-sm instalment',
                                'placeholder' => '3rd Installment',
                            ]) !!}
                        </div>
                        {{-- 2. DD Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst3_dd_date', 'DD Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst3_dd_date', null, [
                                    'class' => 'form-control form-control-sm datepicker dd-date-field',
                                    'placeholder' => 'DD Date',
                                    'readonly',
                                    'data-inst' => '3',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 3. Desired Offered Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst3_desired_offered_date', 'Desired Offered Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst3_desired_offered_date', null, [
                                    'class' => 'form-control form-control-sm datepicker desired-date-field',
                                    'placeholder' => 'Desired Offered Date',
                                    'readonly',
                                    'data-inst' => '3',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 4. Offering Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst3_offering_date', 'Offer Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst3_offering_date', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'Offer Date',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 5. Sampling On --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst3_sampling_on', 'Sampling On:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst3_sampling_on', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'Sampling On',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 6. Shipment Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst3_shipment_date', 'Shipment Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst3_shipment_date', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'Shipment Date',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 7. AFMSL Received Date (naya field) --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst3_afmsl_received_date', 'AFMSL Received Date:') !!}
                            {!! Form::text('inst3_afmsl_received_date', null, [
                                'class' => 'form-control form-control-sm',
                                'placeholder' => 'Auto filled on receive',
                                'readonly',
                            ]) !!}
                        </div>
                        {{-- 8. Acceptance Letter Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst3_acceptance_letter_date', 'Acceptance Letter Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst3_acceptance_letter_date', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'Acceptance Letter Date',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 9. Bulk Stamping Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst3_bulk_stamping_date', 'Bulk Stamping Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst3_bulk_stamping_date', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'Bulk Stamping Date',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 10. IEI Approved Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst3_iei_approved_date', 'IEI Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst3_iei_approved_date', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'IEI Date',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 12. I Note Date   --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst3_i_note_date', 'I Note Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst3_i_note_date', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'I Note Date',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>

                        {{-- 13. EU Opinion Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst3_eu_opinion_date', 'EU Opinion Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst3_eu_opinion_date', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'EU Opinion Date',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 14. Case Ref Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst3_case_ref_date', 'Case Ref Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst3_case_ref_date', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'Case Ref Date',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>


                    </div>
                </div>

                {{-- Installment 4 --}}
                <div class="instalment-field instalment_4"
                    style="display:none; border: 1px solid #ddd; border-radius: 5px; padding: 15px; margin-bottom: 15px;">
                    <h5 style="font-weight: bold; color: #337ab7;"><i class="fa fa-list-ol"></i> 4th Installment</h5>
                    <div class="row">
                        {{-- 1. Qty --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('instalment_4', '4th Installment Qty:*') !!}
                            {!! Form::number('instalment_4', null, [
                                'class' => 'form-control form-control-sm instalment',
                                'placeholder' => '4th Installment',
                            ]) !!}
                        </div>
                        {{-- 2. DD Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst4_dd_date', 'DD Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst4_dd_date', null, [
                                    'class' => 'form-control form-control-sm datepicker dd-date-field',
                                    'placeholder' => 'DD Date',
                                    'readonly',
                                    'data-inst' => '4',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 3. Desired Offered Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst4_desired_offered_date', 'Desired Offered Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst4_desired_offered_date', null, [
                                    'class' => 'form-control form-control-sm datepicker desired-date-field',
                                    'placeholder' => 'Desired Offered Date',
                                    'readonly',
                                    'data-inst' => '4',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 4. Offering Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst4_offering_date', 'Offer Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst4_offering_date', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'Offer Date',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 5. Sampling On --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst4_sampling_on', 'Sampling On:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst4_sampling_on', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'Sampling On',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 6. Shipment Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst4_shipment_date', 'Shipment Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst4_shipment_date', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'Shipment Date',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 7. AFMSL Received Date (naya field) --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst4_afmsl_received_date', 'AFMSL Received Date:') !!}
                            {!! Form::text('inst4_afmsl_received_date', null, [
                                'class' => 'form-control form-control-sm',
                                'placeholder' => 'Auto filled on receive',
                                'readonly',
                            ]) !!}
                        </div>
                        {{-- 8. Acceptance Letter Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst4_acceptance_letter_date', 'Acceptance Letter Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst4_acceptance_letter_date', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'Acceptance Letter Date',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 9. Bulk Stamping Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst4_bulk_stamping_date', 'Bulk Stamping Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst4_bulk_stamping_date', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'Bulk Stamping Date',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 10. IEI Approved Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst4_iei_approved_date', 'IEI Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst4_iei_approved_date', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'IEI Date',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>

                        {{-- 12. I Note Date   --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst4_i_note_date', 'I Note Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst4_i_note_date', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'I Note Date',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 13. EU Opinion Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst4_eu_opinion_date', 'EU Opinion Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst4_eu_opinion_date', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'EU Opinion Date',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>
                        {{-- 14. Case Ref Date --}}
                        <div class="form-group col-sm-3">
                            {!! Form::label('inst4_case_ref_date', 'Case Ref Date:') !!}
                            <div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('inst4_case_ref_date', null, [
                                    'class' => 'form-control form-control-sm datepicker',
                                    'placeholder' => 'Case Ref Date',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>


                    </div>
                </div>

                {{-- Total Quantity --}}
                <div class="row" style="margin-top: 10px;">
                    <div class="form-group supply-fields col-sm-3">
                        {!! Form::label('t_quantity', __('product.t_quantity') . ':*') !!}
                        {!! Form::text('t_quantity', null, [
                            'class' => 'form-control form-control-sm',
                            'placeholder' => __('product.t_quantity'),
                            'readonly' => true,
                            'id' => 'total_quantity',
                        ]) !!}
                    </div>
                </div>

                {{-- Description --}}
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group supply-fields">
                            {!! Form::label('description', __('product.c_note') . ':') !!}
                            {!! Form::textarea('c_description', null, [
                                'class' => 'form-control',
                                'placeholder' => 'Contract description',
                                'style' => 'resize:none;',
                                'rows' => '2',
                            ]) !!}
                        </div>
                    </div>
                </div>

                <div class="form-group" style="display:none;">
                    <input type="hidden" name="sample_id_supply" value="">
                </div>
                <div class="form-group" style="display:none;">
                    <input type="hidden" name="supplier_id_supply" value="">
                </div>

                <div class="col-sm-12 text-center">
                    <button type="submit" id="supply_contract_save_button_big"
                        class="btn btn-big btn-primary">@lang('messages.save')</button>
                </div>

                {!! Form::close() !!}
            @endcomponent
        </div>

        {{-- tender form --}}
        <div style="display: none;" id="contractTenderFormContainerMain">
            @component('components.widget', ['class' => 'box-solid'])
                <div class="row">
                    <div class="col-sm-4">
                        {!! Form::open([
                            'url' => action([\App\Http\Controllers\ContractController::class, 'store']),
                            'method' => 'post',
                            'id' => 'new_contract_add_form_tender_main',
                        ]) !!}


                        {{-- contract number field --}}
                        <div class="form-group c_number_div">
                            {!! Form::label('number', __('product.te_number') . ':*', ['class' => 'c_number_label']) !!}
                            {!! Form::text('number', null, [
                                'class' => 'form-control c_number',
                                'required',
                                'placeholder' => __('product.te_number'),
                            ]) !!}
                        </div>
                    </div>
                    {{-- 1. Acceptance Letter --}}
                    <div class="form-group col-sm-4">
                        {!! Form::label('acceptance_letter_date', 'Acceptance Letter No:*') !!}
                        {!! Form::text('acceptance_letter_date', null, [
                            'class' => 'form-control form-control-sm',
                            'placeholder' => 'Enter Acceptance Letter No',
                        ]) !!}
                    </div>

                    {{-- 2. Bulk Sampling Date --}}
                    <div class="form-group col-sm-4">
                        {!! Form::label('bulk_sampling_date', 'Bulk Sampling Date:*') !!}
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            {!! Form::text('bulk_sampling_date', null, [
                                'class' => 'form-control form-control-sm datepicker',
                                'placeholder' => 'Select Bulk Sampling Date',
                                'readonly',
                            ]) !!}
                        </div>
                    </div>

                    {{-- 3. Desired Offered Date --}}
                    <div class="form-group col-sm-4">
                        {!! Form::label('desired_offered_date', 'Desired Offered Date:*') !!}
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            {!! Form::text('desired_offered_date', null, [
                                'class' => 'form-control form-control-sm datepicker',
                                'placeholder' => 'Select Desired Offered Date',
                                'readonly',
                            ]) !!}
                        </div>
                    </div>
                    <div class="form-group col-sm-4">
                        {!! Form::label('sampling_on', 'Sampling On:*') !!}
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            {!! Form::text('sampling_on', null, [
                                'class' => 'form-control form-control-sm datepicker',
                                'placeholder' => 'Select Sampling On Date',
                                'readonly',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            {!! Form::label('tender_date', __('Date') . ':*') !!}
                            <div class="input-group"><span class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </span>
                                <input type="text" id="tender_date" name="tender_date"
                                    class="form-control form-control-sm" placeholder="{{ __('method.date') }}"
                                    autocomplete="off">
                            </div>
                        </div>
                    </div>

                    <div class="form-group  col-sm-4">
                        {!! Form::label('fiscal_year_id', __('product.fisc_yr') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>
                            <select id="fiscal_year_id" name="fiscal_year_id" class="form-control select2 form-control-sm"
                                style="width: 100%;" required>
                                <option value="">@lang('messages.please_select')</option>
                                @foreach ($fiscal_years as $fiscal_year)
                                    <option value="{{ $fiscal_year->id }}">{{ $fiscal_year->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group col-sm-4">
                        {!! Form::label('contract_quantity', 'Contract Quantity:*') !!}
                        {!! Form::number('contract_quantity', null, [
                            'class' => 'form-control',
                            'required',
                            'step' => 'any',
                            'placeholder' => 'Enter Contract Quantity',
                        ]) !!}
                    </div>

                    <div class="form-group col-sm-4">
                        {!! Form::label('received_quantity', 'Received Quantity:') !!}
                        {!! Form::number('received_quantity', 0, [
                            'class' => 'form-control',
                            'step' => 'any',
                            'placeholder' => 'Received Quantity',
                        ]) !!}
                    </div>


                    <div class="form-group" style="display: none;">
                        <input type="hidden" class="form-control form-control-sm" name="sample_id_tender" value="">
                    </div>
                    <div class="form-group" style="display: none;">
                        <input type="hidden" class="form-control form-control-sm" name="supplier_id_tender" value="">
                    </div>


                    {{-- contract type hidden --}}
                    <div class="form-group" style="display: none;">
                        <input type="hidden" class="form-control form-control-sm" name="c_type" value="tender">
                    </div>



                </div>


                <div class="col-sm-12 text-center">

                    <button type="submit" id="tender_contract_save_button_big"
                        class="btn btn-big btn-primary">@lang('messages.save')</button>
                </div>

                {!! Form::close() !!}
            @endcomponent
        </div>
    </section>
    <div class="modal fade contact_modal" tabindex="1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle">
    </div>
@endsection
@section('javascript')
    <script>
        $(document).ready(function() {

            // $('#sample_id').select2({
            //     ajax: {
            //         // url: '/contracts/get-samples', <-- Isay khatam karein
            //         url: "{{ url('contracts/get-samples') }}", // <-- Ye use karein
            //         dataType: 'json',
            //         delay: 500,
            //         data: function(params) {
            //             return {
            //                 q: params.term
            //             };
            //         },
            //         processResults: function(data) {
            //             return {
            //                 results: data.results
            //             };
            //         }
            //     },
            //     minimumInputLength: 1,
            //     placeholder: "Search Sample..."
            // });

            // // Supplier Search
            // $('#supplier_id').select2({
            //     ajax: {
            //         url: '/contracts/get-suppliers',
            //         dataType: 'json',
            //         delay: 500,
            //         data: function(params) {
            //             return {
            //                 q: params.term
            //             };
            //         },
            //         processResults: function(data) {
            //             return {
            //                 results: data.results
            //             };
            //         }
            //     },
            //     minimumInputLength: 1,
            //     placeholder: "Search Supplier..."
            // });
            $(document).on('click', '#sample_results li', function() {
                let selectedText = $(this).text().trim();
                let selectedId = $(this).data('id');

                $('#sample_search').val(selectedText);
                $('#sample_results').hide();

                if ($('#search_nomenclature').find("option[value='" + selectedId + "']").length) {
                    $('#search_nomenclature').val(selectedId).trigger('change');
                } else {
                    var newOption = new Option(selectedText, selectedId, true, true);
                    $('#search_nomenclature').append(newOption).trigger('change');
                }

                console.log("Product selected, triggering dependency fields...");
            });
            $('#sample_id').on('change', function() {
                var selectedValue = $(this).val();
                $('input[name="sample_id_tender"]').val(selectedValue);
                $('input[name="sample_id_supply"]').val(selectedValue);

            });

            // Function to append selected supplier ID to hidden input fields
            $('#supplier_id').on('change', function() {
                var selectedValue = $(this).val();
                $('input[name="supplier_id_tender"]').val(selectedValue);
                $('input[name="supplier_id_supply"]').val(selectedValue);

            });
            if ($('#sample_id').val()) {
                $('#sample_id').trigger('change');
            }

            if ($('#supplier_id').val()) {
                $('#supplier_id').trigger('change');
            }
            // Function to toggle visibility based on the selected radio button
            $('input[name="contract_type"]').on('change', function() {
                var selectedValue = $(this).val();
                if (selectedValue === 'tender') {
                    $('#contractSupplyFormContainerMain').hide();
                    $('#contractTenderFormContainerMain').show();
                } else if (selectedValue === 'supply') {
                    $('#contractSupplyFormContainerMain').show();
                    $('#contractTenderFormContainerMain').hide();
                }
            });

            // Initial visibility based on the selected radio button
            var selectedValue = $('input[name="contract_type"]:checked').val();
            if (selectedValue === 'tender') {
                $('#contractSupplyFormContainerMain').hide();
                $('#contractTenderFormContainerMain').show();
            } else if (selectedValue === 'supply') {
                $('#contractSupplyFormContainerMain').show();
                $('#contractTenderFormContainerMain').hide();
            }

            // quick add date for contract / flex
            $(document).ready(function() {
                // Tamam date fields ke liye common settings
                $('.datepicker, #offering_date, #tender_date').datepicker({
                    format: 'yyyy-mm-dd',
                    startView: 'years',
                    minViewMode: 'days',
                    autoclose: true,
                    todayHighlight: true
                });
            });

            $('#saveQuickDateRange').click(function() {
                var startDate = $('#quickStartDate').val();
                var endDate = $('#quickEndDate').val();
                var name = $('#quickName').val();
                var dateRange = name + ' ' + startDate + ' to ' + endDate;

                var newOption = $('<option>', {
                    value: dateRange,
                    text: dateRange
                });

                $('#date_range').append(newOption);

                $('#date_range').val(dateRange);

                $('#quickAddDateRangeModal').modal('hide');
            });


            //instalments fields show/hide logic 

            //$('.instalment-fields').hide();

            $('#t_instalment_select').on('change', function() {
                var selectedValue = parseInt($(this).val());
                $('.instalment-field').hide();
                // $('.instalment-fields').show();
                for (var i = 1; i <= selectedValue; i++) {
                    $('.instalment_' + i).show();
                }
            });
            $(document).on('changeDate', '.dd-date-field', function() {
                var ddDate = $(this).val();
                var instNum = $(this).data('inst');
                if (ddDate) {
                    var ddMoment = new Date(ddDate);
                    ddMoment.setDate(ddMoment.getDate() - 60);
                    var yyyy = ddMoment.getFullYear();
                    var mm = String(ddMoment.getMonth() + 1).padStart(2, '0');
                    var dd = String(ddMoment.getDate()).padStart(2, '0');
                    var desiredDate = yyyy + '-' + mm + '-' + dd;
                    $('input[name="inst' + instNum + '_desired_offered_date"]').val(desiredDate);
                }
            });

            // offering date picker 

            // $('#offering_date').datepicker({
            //     format: 'dd-mm-yyyy',
            //     autoclose: true
            // });
            // $('#tender_date').datepicker({
            //     format: 'dd-mm-yyyy',
            //     autoclose: true
            // });

            // total quantity auto fill
            $('.instalment').on('input', function() {
                var total = 0;
                $('.instalment').each(function() {
                    var value = parseFloat($(this).val());
                    if (!isNaN(value)) {
                        total += value;
                    }
                });
                $('#total_quantity').val(total);
            });
        });
        $(document).ready(function() {

            // Generic Search Function
            function performSearch(url, query, resultsListId) {
                if (query.length < 1) return;

                $.ajax({
                    url: url,
                    type: "GET",
                    data: {
                        q: query
                    },
                    success: function(response) {
                        let list = $(`#${resultsListId}`);
                        list.empty();
                        let results = response.results ? response.results : response;

                        if (results.length > 0) {
                            $.each(results, function(i, item) {
                                list.append(`<li class="list-group-item list-group-item-action" 
                            style="cursor:pointer" data-id="${item.id}">${item.text}</li>`);
                            });
                            list.show();
                        } else {
                            list.append('<li class="list-group-item disabled">No results found</li>')
                                .show();
                        }
                    }
                });
            }

            // 1. Sample Search Trigger
            $('#btn_search_sample').on('click', function() {
                let q = $('#sample_search_custom').val();
                performSearch("{{ url('contracts/get-samples') }}", q, 'sample_results_list');
            });

            // 2. Supplier Search Trigger
            $('#btn_search_supplier').on('click', function() {
                let q = $('#supplier_search_custom').val();
                performSearch("{{ url('contracts/get-suppliers') }}", q, 'supplier_results_list');
            });

            // Handle Selection for Sample
            $(document).on('click', '#sample_results_list li', function() {
                let id = $(this).data('id');
                let text = $(this).text();
                if (!id) return;

                $('#sample_search_custom').val(text);
                $('input[name="sample_id_tender"]').val(id);
                $('input[name="sample_id_supply"]').val(id);
                $('#sample_results_list').hide();
            });

            // Handle Selection for Supplier
            $(document).on('click', '#supplier_results_list li', function() {
                let id = $(this).data('id');
                let text = $(this).text();
                if (!id) return;

                $('#supplier_search_custom').val(text);
                $('input[name="supplier_id_tender"]').val(id);
                $('input[name="supplier_id_supply"]').val(id);
                $('#supplier_results_list').hide();
            });

            // Hide lists when clicking outside
            $(document).click(function(e) {
                if (!$(e.target).closest('.form-group').length) {
                    $('.custom-search-list').hide();
                }
            });

            // --- Baqi aapka purana logic (Radio toggle, Datepicker, etc.) niche rehne dein ---
            $('input[name="contract_type"]').on('change', function() {
                var selectedValue = $(this).val();
                if (selectedValue === 'tender') {
                    $('#contractSupplyFormContainerMain').hide();
                    $('#contractTenderFormContainerMain').show();
                } else {
                    $('#contractSupplyFormContainerMain').show();
                    $('#contractTenderFormContainerMain').hide();
                }
            });
        });




        // $(document).ready(function() {
        // $('#date_range').daterangepicker({
        // autoUpdateInput: false,
        // locale: {
        // cancelLabel: 'Clear'
        // },
        // ranges: {

        // '6 Months': [moment(), moment().add(6, 'months')],
        // 'Fiscal Year': [moment().startOf('year').add(6, 'months'), moment().add(1, 'years')
        // .endOf('year').subtract(6, 'months')
        // ]

        // },
        // opens: 'left' // Adjust the calendar placement as needed
        // });

        // $('#date_range').on('apply.daterangepicker', function(ev, picker) {
        // $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format(
        // 'DD-MM-YYYY'));
        // });

        // $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
        // $(this).val('');
        // });
        // });
    </script>
    <style>
        /* Dono dropdowns (Sample & Supplier) ke liye common styling */
        .custom-search-results {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            z-index: 1000;
            width: 100%;
            background: white;
            border: 1px solid #ccc;
            border-radius: 0 0 4px 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin: -1px 0 0 0;
            /* Input ke saath jura hua dikhay */
            padding: 0;
            list-style: none;
            max-height: 250px;
            /* Zyada products hon to scrollbar aa jaye */
            overflow-y: auto;
        }

        .custom-search-results li {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
            font-size: 13px;
            color: #333;
            display: flex;
            max-height: 30px;
            align-items: center;
            transition: background-color 0.2s;
        }

        .custom-search-results li:last-child {
            border-bottom: none;
        }

        .custom-search-results li:hover {
            background-color: #f4f4f4;
        }

        /* Agar aap icon add karna chahen to uske liye */
        .custom-search-results li i {
            font-size: 11px;
            margin-right: 8px;
            color: #777;
        }

        .custom-search-results li.disabled {
            background: #fafafa;
            color: #999;
            cursor: default;
        }
    </style>
@endsection
