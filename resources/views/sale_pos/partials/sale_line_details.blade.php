<table class="table @if(!empty($for_ledger)) table-slim mb-0 bg-light-gray @else bg-gray @endif" @if(!empty($for_pdf)) style="width: 100%;" @endif>
        <tr @if(empty($for_ledger)) class="bg-green" @endif>
        <th>#</th>
        <th>{{ __('sale.product') }}</th>
        <th>{{ __('sale.batch') }}</th>
        <th>{{ __('sale.qty') }}</th>
        <th>{{ __('sale.mfg_date') }}</th>
        <th>{{ __('sale.exp_date') }}</th>
    </tr>
    @foreach($sell->sell_lines as $sell_line)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>
                {{ $sell_line->product->name }}
            </td>
            <td>{{ $sell_line->batch ? $sell_line->batch->code : '--' }}</td>
            <td>
                {{ $sell_line->quantity ? $sell_line->quantity : '--' }}
            </td>
            <td>
                {{ $sell_line->batch ? $sell_line->batch->mfg_date : '--' }}
            </td>
            <td>
                {{ $sell_line->batch ? $sell_line->batch->expiry_date : '--' }}
            </td>
        </tr>
    @endforeach
</table>