<div class="row">
    <div class="col-sm-12">
        @forelse($locations as $key => $value)
            <div class="box box-solid">
                <div class="box-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <table
                                class="table table-condensed table-bordered text-center table-striped add_opening_stock_table">
                                <thead>
                                    <tr class="bg-green">
                                        {{-- <th>@lang('Id')</th> --}}
                                        <th>@lang('Batch No')</th>
                                        <th>@lang('Expiry date')</th>
                                        <th>@lang('Quantity')</th>
                                        {{-- <th>@lang('note')</th> --}}
                                        <th>&nbsp;</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $subtotal = 0;
                                    @endphp
                                    @foreach ($product->variations as $variation)
                                        @if (empty($purchases[$key][$variation->id]))
                                            @php
                                                $purchases[$key][$variation->id][] = ['quantity' => 0, 'purchase_price' => $variation->default_purchase_price, 'purchase_line_id' => null, 'lot_number' => null, 'transaction_date' => null, 'purchase_line_note' => null, 'secondary_unit_quantity' => 0];
                                            @endphp
                                        @endif

                                        @foreach ($purchases[$key][$variation->id] as $sub_key => $var)
                                            @php

                                                $purchase_line_id = $var['purchase_line_id'];

                                                $qty = $var['quantity'];

                                                $purcahse_price = $var['purchase_price'];

                                                $row_total = $qty * $purcahse_price;

                                                $subtotal += $row_total;
                                                $lot_number = $var['lot_number'];
                                                $transaction_date = $var['transaction_date'];
                                                $purchase_line_note = $var['purchase_line_note'];
                                            @endphp

                                            <tr>
                                                {{-- <td>
                                                #
                                                </td> --}}
                                                <td>
                                                    {{-- <div class="input-group"> --}}
                                                        {!! Form::select('batch_no',$batch_no,['class' => 'form-control'],
                                                        ) !!}
                                                    {{-- </div> --}}
                                                </td>
                                                <td>
                                                    <div class="input-group date">
                                                        {!! Form::text('expiry_date',$transaction_date,['class' => 'form-control input-sm os_date', 'readonly'],
                                                        ) !!}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        {!! Form::text(
                                                            'quantity',@format_quantity($qty),['class' => 'form-control input-sm input_number purchase_quantity input_quantity', 'required'],
                                                        ) !!}
                                                        <span class="input-group-addon">
                                                            {{ $product->unit->short_name }}
                                                        </span>
                                                    </div>
                                                    
                                                </td>
                                            
                                                
                                                {{-- <td>
                                                    {!! Form::textarea(
                                                        'stocks[' . $key . '][' . $variation->id . '][' . $sub_key . '][purchase_line_note]',
                                                        $purchase_line_note,
                                                        ['class' => 'form-control input-sm', 'rows' => 3],
                                                    ) !!}
                                                </td> --}}
                                                <td>
                                                    @if ($loop->index == 0)
                                                        <button type="button"
                                                            class="btn btn-primary btn-xs add_stock_row"
                                                            data-sub-key="{{ count($purchases[$key][$variation->id]) }}"
                                                            data-row-html='<tr>
					{{-- <td>#</td> --}}
                        <td>
                           <input class="form-control" name="batch_no" type="text" > 
                           {{-- {!! Form::text('batch_no',$transaction_date,['class' => ''],
                            ) !!} --}}
					</td>
					
	<td>
		<div class="input-group date">
			<input class="form-control input-sm os_date" name="expiry_date" type="text" readonly>
		</div>
	</td>
    <td>
        <div class="input-group">
              <input class="form-control input-sm input_number purchase_quantity" required="" name="quantity" type="text" value="0">
              <span class="input-group-addon">
                {{ $product->unit->short_name }}
              </span>
            </div>
        </td>
	{{-- <td>
		<textarea rows="3" class="form-control input-sm" name="stocks[{{ $key }}][{{ $variation->id }}][__subkey__][purchase_line_note]"></textarea>
	</td> --}}
	<td>&nbsp;</td></tr>'><i
                                                                class="fa fa-plus"></i></button>
                                                    @else
                                                        &nbsp;
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                               
                            </table>

                        </div>
                    </div>
                </div>
            </div> <!--box end-->
        @empty
            <h3>@lang('lang_v1.product_not_assigned_to_any_location')</h3>
        @endforelse
    </div>
</div>
