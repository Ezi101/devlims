<div id="accordion">
    <div class="card">
        <div class="nav-tabs-custom">
            <div class="tab-content">
                <div class="tab-pane active">
                    <div class="card-body" style="margin-top: 15px;">
                        <div class="card-body">
                            @component('components.widget', ['class' => 'box-primary'])
                                <div class="row">
                                    <div class="col-sm-12">
                                        <table class="table dataTable ajax_view hide-footer inventory-reports-table"
                                            id="inventory-reports-table">
                                            <thead>
                                                <tr>
                                                    <th>AFMSL Quantity</th>
                                                    <th>Retention Quantity</th>
                                                    <th>AFIMS Quantity</th>
                                                    <th>User Quantity</th>
                                                    <th>Total Quantity</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $afmsl_qty = \App\VariationLocationDetails::where(
                                                                'location_id',
                                                                5,
                                                            )
                                                                ->where('product_id', $product->id)
                                                                ->sum('qty_available');
                                                        @endphp
                                                        {{ number_format($afmsl_qty, 2) }}
                                                    </td>
                                                    <td>
                                                        @php
                                                            $retention_qty = \App\VariationLocationDetails::where(
                                                                'location_id',
                                                                6,
                                                            )
                                                                ->where('product_id', $product->id)
                                                                ->sum('qty_available');
                                                        @endphp
                                                        {{ number_format($retention_qty, 2) }}
                                                    </td>
                                                    <td>
                                                        @php
                                                            $afims_qty = \App\VariationLocationDetails::where(
                                                                'location_id',
                                                                8,
                                                            )
                                                                ->where('product_id', $product->id)
                                                                ->sum('qty_available');
                                                        @endphp
                                                        {{ number_format($afmis_qty, 2) }}
                                                    </td>
                                                    <td>
                                                        @php
                                                            $user_qty = \App\VariationLocationDetails::where(
                                                                'location_id',
                                                                9,
                                                            )
                                                                ->where('product_id', $product->id)
                                                                ->sum('qty_available');
                                                        @endphp
                                                        {{ number_format($user_qty, 2) }}
                                                    </td>
                                                    <td>
                                                        @php
                                                            $total_qty =
                                                                $afmsl_qty + $retention_qty + $afims_qty + $user_qty;
                                                        @endphp
                                                        {{ number_format($total_qty, 2) }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endcomponent

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- <div class="card"> --}}
    {{-- <div class="nav-tabs-custom">
            <div class="tab-content">
                <div class="tab-pane active"> --}}
    {{-- <div class="card-header" style="background-color: lightgray" data-toggle="collapse"
                        data-target="#system_information" aria-expanded="false" aria-controls="system_information">
                        <h5 class="mb-0">
                            <button class="btn btn-link collapsed" style="font-size:15px; color:black">
                                <strong>Statistical Information</strong>
                            </button>
                        </h5>
                    </div> --}}
    {{-- <div id="system_information" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                        <div class="card-body" style="margin-top: 15px;">
                            <div class="row" style="margin-top:20px"> --}}
    {{-- <div class="col-md-12"> --}}
    @component('components.widget', ['class' => 'box-primary', 'title' => 'Batch Details'])
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>@lang('method.hash_sign')</th>
                    <th>@lang('batch.number')</th>
                    <th>@lang('method.date')</th>
                    <th>@lang('method.qty')</th>
                    <th>@lang('method.contract_no')</th>
                    <th>@lang('method.installments')</th>
                </tr>
            </thead>
            <tbody>

                @foreach ($purchase_linedata as $purchase_line)
                    <tr>
                        <td> {{ $loop->iteration }} </td>

                        <td> {{ $purchase_line->batch->code ?? 'N/A' }} </td>
                        <td> {{ $purchase_line->created_at ?? 'N/A' }} </td>
                        <td> {{ $purchase_line->quantity ?? 'N/A' }} </td>

                        <td> {{ $purchase_line->contract->number ?? 'N/A' }} </td>

                        <td>
                            @if ($purchase_line->instalments == 'instalments_1')
                                1st Installment
                            @elseif($purchase_line->instalments == 'instalments_2')
                                2nd Installment
                            @elseif($purchase_line->instalments == 'instalments_1_2')
                                1st & 2nd Installment
                            @elseif($purchase_line->instalments == 'instalments_1_2_3')
                                1st,2nd & 3rd Installment
                            @elseif($purchase_line->instalments == 'instalments_2_3')
                                2nd & 3rd Installment
                            @elseif($purchase_line->instalments == 'instalments_3')
                                3rd Installment
                            @elseif($purchase_line->instalments == 'instalments_4')
                                4th Installment
                            @elseif($purchase_line->instalments == 'instalments_3_4')
                                3rd & 4th Installment
                            @elseif($purchase_line->instalments == 'no_instalments')
                                No Installment
                            @else
                                {{ $purchase_line->instalments ?? 'N/A' }}
                            @endif
                        </td>

                    </tr>
                @endforeach



            </tbody>

        </table>
    @endcomponent

    {{-- </div> --}}
    {{-- </div>
                        </div>
                    </div> --}}
    {{-- </div>
            </div>
        </div> --}}
    {{-- </div> --}}


</div>
