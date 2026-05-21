@php $row_index = 0; @endphp

@forelse ($batch as $single_batch)
    @php
        $current_batch = is_array($single_batch) ? (object) $single_batch['batch'] : $single_batch->batch;
        $current_issue_id = is_array($single_batch) ? $single_batch['issue_id'] : $single_batch->issue_id;
    @endphp

    @foreach ($products as $product)
        <tr>
            <td style="width: 20%;">
                {{ $current_batch->code }}
                @if ($product->variation_name != 'DUMMY')
                    <br><b>{{ $product->variation_name }}</b>
                @endif

                <input type="hidden" name="products[{{ $row_index }}][product_id]" value="{{ $product->product_id }}">
                <input type="hidden" name="products[{{ $row_index }}][variation_id]"
                    value="{{ $product->variation_id }}">
            </td>

            <td style="width:10%;">
                <input type="number" class="form-control" min="1" name="products[{{ $row_index }}][quantity]"
                    value="1" style="width:100%;">
            </td>

            <td style="display: none">
                <input type="hidden" name="products[{{ $row_index }}][exp_date]"
                    value="{{ $current_batch->expiry_date }}">
                <input type="hidden" name="products[{{ $row_index }}][mfg_date]"
                    value="{{ $current_batch->mfg_date }}">
                <input type="hidden" name="products[{{ $row_index }}][issue_id]" value="{{ $current_issue_id }}">
                <input type="hidden" name="products[{{ $row_index }}][batch]" value="{{ $current_batch->code }}">
            </td>
        </tr>
        @php $row_index++; @endphp
    @endforeach
@empty
    <tr>
        <td colspan="6">No batches found.</td>
    </tr>
@endforelse
