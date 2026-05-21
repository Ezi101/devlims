@php
    $product = is_array($products) ? $products[0] : $products->first();
    $lab_names = ['Physical Lab Manager', 'Chemical Lab Manager', 'Micro Lab Manager', 'Retention Location'];
    $row_count = 0;

    $first_batch = is_array($batch) ? (is_array($batch) ? $batch[0] : $batch->first()) : null;
    $global_base_id = (int) ($first_batch['issue_id'] ?? 0) - 1;

    $id_increment_counter = 0;
@endphp

{{-- @foreach ($batch as $b)
    @php
        $current_batch = $b['batch'];
        $po_ref = $b['ref_no'] ?? 'N/A';
    @endphp

    @foreach ($lab_names as $lab_name)
        @php
            if ($lab_name == 'Retention Location') {
                $unique_issue_id = $po_ref; // Yahan PO number (PO2026/6389) aa jayega
            } else {
                $unique_issue_id = str_pad($global_base_id + $id_increment_counter, 8, '0', STR_PAD_LEFT);
            }
        @endphp
        <tr>
            <td style="width: 25%;">
                <strong>Batch:</strong> {{ $current_batch->code ?? 'N/A' }}
                <input type="hidden" name="products[{{ $row_count }}][product_id]"
                    value="{{ $product->product_id ?? '' }}">
                <input type="hidden" name="products[{{ $row_count }}][variation_id]"
                    value="{{ $product->variation_id ?? '' }}">
            </td>

            <td style="width: 45%;">
                <span class="label label-info" style="font-size: 12px;">{{ $lab_name }}</span>
                <input type="hidden" name="products[{{ $row_count }}][lab_name]" value="{{ $lab_name }}">
            </td>

            <td style="width: 15%;">
                <input type="number" class="form-control" name="products[{{ $row_count }}][quantity]"
                    value="1">
            </td>

            <td style="display: none">
                <input type="hidden" name="products[{{ $row_count }}][issue_id]" value="{{ $unique_issue_id }}">
                <input type="hidden" name="products[{{ $row_count }}][batch]"
                    value="{{ $current_batch->code ?? '' }}">
                <input type="hidden" name="products[{{ $row_count }}][ref_no]" value="{{ $po_ref }}">
                <input type="hidden" name="products[{{ $row_count }}][exp_date]"
                    value="{{ $b['expiry_date'] ?? '' }}">
            </td>
        </tr>
        @php
            $row_count++;
            $id_increment_counter++;
        @endphp
    @endforeach
@endforeach --}}
@foreach ($batch as $b)
    @php
        $current_batch = $b['batch'];
        $po_ref = $b['ref_no'] ?? 'N/A';
        $lab_name = $b['lab_name']; // ← Actual lab name jo transaction se aaya

        if ($lab_name == 'Retention Location') {
            $unique_issue_id = $po_ref;
        } else {
            $unique_issue_id = str_pad($global_base_id + $id_increment_counter, 8, '0', STR_PAD_LEFT);
        }
    @endphp
    <tr>
        <td style="width: 25%;">
            <strong>Batch:</strong> {{ $current_batch->code ?? 'N/A' }}
            <input type="hidden" name="products[{{ $row_count }}][product_id]"
                value="{{ $product->product_id ?? '' }}">
            <input type="hidden" name="products[{{ $row_count }}][variation_id]"
                value="{{ $product->variation_id ?? '' }}">
        </td>
        <td style="width: 45%;">
            <span class="label label-info" style="font-size: 12px;">{{ $lab_name }}</span>
            <input type="hidden" name="products[{{ $row_count }}][lab_name]" value="{{ $lab_name }}">
        </td>
        <td style="width: 15%;">
            <input type="number" class="form-control" name="products[{{ $row_count }}][quantity]" value="1">
        </td>
        <td style="display: none">
            <input type="hidden" name="products[{{ $row_count }}][issue_id]" value="{{ $unique_issue_id }}">
            <input type="hidden" name="products[{{ $row_count }}][batch]"
                value="{{ $current_batch->code ?? '' }}">
            <input type="hidden" name="products[{{ $row_count }}][ref_no]" value="{{ $po_ref }}">
            <input type="hidden" name="products[{{ $row_count }}][exp_date]"
                value="{{ $b['expiry_date'] ?? '' }}">
        </td>
    </tr>
    @php
        $row_count++;
        $id_increment_counter++;
    @endphp
@endforeach
