@extends('layouts.app')
@section('title', __('product.demand_req_edit'))

@section('content')
    <section class="content-header">
        <h1>@lang('product.demand_req')
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @include('layouts.partials.error')

        <form action="{{ route('demand.update', ['id' => $transaction->id]) }}" method="POST" id="edit_demand_form">
            @csrf
            @method('POST')

            @if ($transaction->product_type === 'standard')
                @component('components.widget', ['class' => 'box-primary', 'title' => __('product.demand_req_st')])
                    <table class="table table-bordered table-striped dataTable" id="purchasesTableAddStandards">
                        <thead class="bg-gray" style="font-size: 12px;border-radius:4px;">
                            <tr>
                                <th>@lang('method.standard')</th>
                                <th>@lang('method.potency')</th>
                                <th>@lang('method.batch')</th>
                                <th>@lang('method.quantity')</th>
                                <th>@lang('method.acct_unit')</th>
                                <th>@lang('method.demand_by')</th>
                            </tr>
                        </thead>
                        <tbody id="tableBodyCreateStandards">
                            <tr>
                                <td>
                                    <div class="form-group">
                                        <div class="input-group">


                                            <input type="text" name="standards[1][standard_name]"
                                                id="standard_select_field_1" class="form-control"
                                                value="{{ $transaction->product->name ?? '-' }}" readonly>
                                            <input type="hidden" name="standards[1][standard_id]" id="standard_id_field_1"
                                                class="form-control" value="{{ $transaction->product->id ?? '-' }}">
                                        </div>
                                    </div>
                                </td>
                                <td>

                                    <div class="input-group">
                                        <input type="number" name="standards[1][potency]" class="form-control"
                                            id="st_potency_1" value="{{ $transaction->potency }}">
                                    </div>
                                </td>

                                <td>
                                    <div class="input-group">

                                        <input type="text" name="standards[1][batch_no]" class="form-control" id="batch_1"
                                            value="{{ $transaction->batch_no }}" readonly>
                                    </div>


                                <td>
                                    <div class="input-group">
                                        <input type="number" name="standards[1][st_quantity]" class="form-control"
                                            id="st_quantity_1" min="0" placeholder="Enter Qty" autocomplete="off"
                                            value="{{ $purchase_lines->where('product_type', 'standard')->first()->quantity ?? '' }}">
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <input type="text" name="standards[1][unit_name]" id="unit_name_1"
                                            class="form-control" value="{{ @$transaction->product->unit->actual_name ?? '-' }}"
                                            readonly>
                                        <input type="hidden" name="standards[1][unit_name]" id="unit_id_1" class="form-control"
                                            value="{{ @$transaction->product->unit->id }}">
                                    </div>
                                </td>

                                <td>
                                    <div class="input-group">
                                        <input type="text" name="standards[1][demand_by]" id="user" class="form-control"
                                            value="{{ $demandByUser->first_name }} {{ $demandByUser->last_name }}" readonly>
                                    </div>
                                </td>

                            </tr>
                        </tbody>
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
                        <input type="hidden" id="st_location_id_1" name="standards[1][location_id]"
                            value="{{ $default_location }}">
                        <input type="hidden" id="product_type_1" name="standards[1][product_type]" value="standard">



                    </table>
                @endcomponent
            @endif

            @if ($transaction->product_type === 'reagent')

                @component('components.widget', ['class' => 'box-primary', 'title' => __('product.demand_req_chem')])
                    <table class="table table-bordered table-striped dataTable" id="purchasesTableAddMethods"
                        style="width: 100%;">
                        <thead class="bg-gray" style="font-size: 12px;border-radius:4px;">
                            <tr>
                                <th>@lang('method.chemical')</th>
                                <th>@lang('method.quantity')</th>
                                <th> @lang('method.demand_by')</th>
                            </tr>
                        </thead>
                        <tbody id="tableBodyCreateChemicals">
                            <tr>
                                <td>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input type="text" name="chemicals[1][chemical_id]" id="chemical_select_field_1"
                                                class="form-control" value="{{ $transaction->product->name ?? '-' }}" readonly>
                                            <input type="hidden" id="chemical_id_h_field_1" name="chemicals[1][chemical_id]"
                                                value="{{ $transaction->product->id }}">

                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input type="number" name="chemicals[1][chem_qty]" class="form-control"
                                                id="chem_qty_1"
                                                value="{{ $purchase_lines->where('product_type', 'reagent')->first()->quantity ?? '' }}">
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="input-group">
                                        <input type="text" name="chemicals[1][demand_by]" id="user" class="form-control"
                                            value="{{ $demandByUser->first_name }} {{ $demandByUser->last_name }}" readonly>
                                    </div>
                                </td>

                            </tr>
                        </tbody>
                    </table>



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
                    <input type="hidden" id="chem_location_id_1" name="chemicals[1][location_id]"
                        value="{{ $default_location }}">
                    <input type="hidden" id="product_type_1" name="chemicals[1][product_type]" value="reagent">
                @endcomponent
            @endif

            <div class="row mt-3">
                <div class="col-sm-12 text-center">
                    {{-- <button type="submit" class="btn btn-primary"
                        {{ $transaction->status === 'approved' ? 'disabled' : '' }}> Update</button> --}}
                    <button type="submit" class="btn btn-primary"
                        {{ $transaction->status === 'approved' ? 'disabled' : '' }}> Approve / Forward</button>
                </div>
            </div>
        </form>
    </section>
@endsection
