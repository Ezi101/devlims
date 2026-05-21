<table align="center"
    style="border-spacing: {{ $barcode_details->col_distance * 1 }}in {{ $barcode_details->row_distance * 1 }}in; overflow: hidden !important;">
    @foreach ($page_products as $page_product)
        {{-- @dd($print,$page_product); --}}
        @if ($loop->index % $barcode_details->stickers_in_one_row == 0)
            <!-- create a new row -->
            <tr>
                <!-- <columns column-count="{{ $barcode_details->stickers_in_one_row }}" column-gap="{{ $barcode_details->col_distance * 1 }}"> -->
        @endif
        <td align="center" valign="center" style="border: 1px dotted lightgray; padding: 0;">
            <div
                style="width: {{ $barcode_details->width * 1 }}in; height: {{ $barcode_details->height * 1 }}in; position: relative; overflow: hidden !important; display: flex; align-items: center; justify-content: center;">

                <div
                    style="position: absolute; left: 0; top: 0; bottom: 0; width: 0.15in; display: flex; align-items: center; justify-content: center;">
                    <div
                        style="writing-mode: vertical-rl; transform: rotate(180deg); font-size: 9px; font-weight: bold; letter-spacing: 1px;">
                        {{ Str::contains(Str::lower($page_product->product->item_type), 'non-refrigerated') ? 'NRI' : (Str::contains(Str::lower($page_product->product->item_type), 'refrigerated') ? 'RI' : $page_product->product->item_type) }}
                    </div>
                </div>

                <div
                    style="width: 1.2in; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                    @if (!empty($page_product->product_name))
                        <div
                            style="font-size: 11px; font-weight: bold; line-height: 1; margin-bottom: 2px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $page_product->product_name }}
                        </div>
                    @endif

                    <img style="height: {{ $barcode_details->height * 0.35 }}in !important; width: 1.15in !important; object-fit: fill; display: block;"
                        src="data:image/png;base64,{{ DNS1D::getBarcodePNG($page_product->issue_id, 'C128', 1, 30, [0, 0, 0], false) }}">

                    <div style="font-size: 11px; font-weight: 600; margin-top: 1px;">
                        {{ $page_product->issue_id }}
                    </div>
                </div>

                <div
                    style="position: absolute; right: 2px; top: 0; bottom: 0; width: 0.2in; display: flex; align-items: center; justify-content: center;">
                    <div
                        style="writing-mode: vertical-rl; font-size: 8.5px; font-weight: bold; color: #000; font-family: sans-serif; white-space: nowrap;">
                        {{ $page_product->expiry_date ?? '' }}
                    </div>
                </div>

            </div>
        </td>

        @if ($loop->iteration % $barcode_details->stickers_in_one_row == 0)
            </tr>
        @endif
    @endforeach
</table>

<style type="text/css">
    td {
        border: 1px dotted lightgray;
    }

    @media print {
        table {
            page-break-after: always;
        }

        @page {
            size: {{ $paper_width }}in {{ $paper_height }}in;
            margin-top: {{ $margin_top }}in !important;
            margin-bottom: {{ $margin_top }}in !important;
            margin-left: {{ $margin_left }}in !important;
            margin-right: {{ $margin_left }}in !important;
        }
    }
</style>
