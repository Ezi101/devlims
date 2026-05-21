<title>{{ __('barcode.print_labels') }}</title>
<button class="btn btn-success" onclick="window.print()">Print</button>
<div id="sample_dashbord_preview_body">
    @php
        $loop_count = 0;
    @endphp

    {{-- Provide product details for a single product here --}}
    @php
        $product_details = [
            [
                'qty' => 1,
                'details' => $product, // Replace 'product' with the variable holding your product details
            ],
        ];
    @endphp

    @while ($product_details[0]['qty'] > 0)
        @php
            $loop_count += 1;
            $is_new_row = $barcode_details->stickers_in_one_row == 1 || $loop_count % $barcode_details->stickers_in_one_row == 1;
            $is_new_paper = ($barcode_details->is_continuous && $is_new_row) || (!$barcode_details->is_continuous && $loop_count % $barcode_details->stickers_in_one_sheet == 1);
            $is_paper_end = ($barcode_details->is_continuous && $loop_count % $barcode_details->stickers_in_one_row == 0) || (!$barcode_details->is_continuous && $loop_count % $barcode_details->stickers_in_one_sheet == 0);
        @endphp

        @if ($is_new_paper)
            {{-- Actual Paper --}}
            <div style="height: {{ !$barcode_details->is_continuous ? $barcode_details->paper_height : $barcode_details->height }}in !important; width: {{ $barcode_details->paper_width }}in !important; line-height: 16px !important;"
                class="{{ !$barcode_details->is_continuous ? 'label-border-outer' : '' }}">

                {{-- Paper Internal --}}
                <div style="{{ !$barcode_details->is_continuous ? "margin-top: $barcode_details->top_margin in !important; margin-bottom: $barcode_details->top_margin in !important; margin-left: $barcode_details->left_margin in !important; margin-right: $barcode_details->left_margin in !important;" : '' }}"
                    class="label-border-internal">
        @endif

        @php
            $first_row = (!$barcode_details->is_continuous && $loop_count % $barcode_details->stickers_in_one_sheet <= $barcode_details->stickers_in_one_row) || ($barcode_details->is_continuous && $loop_count <= $barcode_details->stickers_in_one_row);
        @endphp

        <div style="height: {{ $barcode_details->height }}in !important; line-height: {{ $barcode_details->height }}in; width: {{ $barcode_details->width }}in !important; display: inline-block; {{ !$is_new_row ? "margin-left: $barcode_details->col_distance in !important;" : '' }} {{ !$first_row ? "margin-top: $barcode_details->row_distance in !important;" : '' }}"
            class="sticker-border text-center">
            <div style="display: inline-block; vertical-align: middle; line-height: 16px !important;">
                {{-- Business Name --}}
                @if (!empty($print['business_name']))
                    <b style="display: block !important" class="text-uppercase">{{ $business_name }}</b>
                @endif

                {{-- Sample Name --}}
                @if (!empty($print['name']))
                    <span style="display: block !important">
                        {{ $product_details[0]['details']->product_actual_name }}
                    </span>
                @endif

                {{-- Variation --}}
                @if (!empty($print['variations']) && $product_details[0]['details']->is_dummy != 1)
                    <span style="display: block !important">
                        <b>{{ $product_details[0]['details']->product_variation_name }}</b>:
                        {{ $product_details[0]['details']->variation_name }}
                    </span>
                @endif

                {{-- Price --}}
                @if (!empty($print['price']))
                    <b>@lang('lang_v1.price'):</b>
                    {{ session('currency')['symbol'] ?? '' }}

                    @php
                        $price = $print['price_type'] == 'inclusive' ? $product_details[0]['details']->sell_price_inc_tax : $product_details[0]['details']->default_sell_price;
                    @endphp

                    {{ @num_format($price) }}
                @endif

                <br>
                {{-- Barcode --}}
                <img class="center-block"
                    style="max-width: 90% !important; height: {{ $barcode_details->height * 0.24 }}in !important;"
                    src="data:image/png;base64,{{ DNS1D::getBarcodePNG($product_details[0]['details']->sub_sku, $product_details[0]['details']->barcode_type, 3, 30, [39, 48, 54], true) }}">
            </div>
        </div>

        @if ($is_paper_end)
            {{-- Actual Paper --}}
</div>

{{-- Paper Internal --}}
</div>
@endif

@php
    $product_details[0]['qty'] = $product_details[0]['qty'] - 1;
@endphp
@endwhile
</div>



</div>

<script type="text/javascript"></script>

<style type="text/css">
    .text-center {
        text-align: center;
    }

    .text-uppercase {
        text-transform: uppercase;
    }

    /*Css related to printing of barcode*/
    .label-border-outer {
        border: 0.1px solid grey !important;
    }

    .label-border-internal {
        /*border: 0.1px dotted grey !important;*/
    }

    .sticker-border {
        border: 0.1px dotted grey !important;
        overflow: hidden;
        box-sizing: border-box;
    }

    #preview_box {
        padding-left: 30px !important;
    }

    @media print {
        .content-wrapper {
            border-left: none !important;
            /*fix border issue on invoice*/
        }

        .label-border-outer {
            border: none !important;
        }

        .label-border-internal {
            border: none !important;
        }

        .sticker-border {
            border: none !important;
        }

        #preview_box {
            padding-left: 0px !important;
        }

        #toast-container {
            display: none !important;
        }

        .tooltip {
            display: none !important;
        }

        .btn {
            display: none !important;
        }
    }

    @media print {
        #preview_body {
            display: block !important;
        }
    }

    @page {
        size: {{ $barcode_details->paper_width }}in @if (!$barcode_details->is_continuous && $barcode_details->paper_height != 0)
            {{ $barcode_details->paper_height }}in
        @endif
        ;

        /*width: {{ $barcode_details->paper_width }}in !important;*/
        /*height:@if ($barcode_details->paper_height != 0)
        {{ $barcode_details->paper_height }}in !important
    @else
        auto
    @endif
    ;
    */ margin-top: 0in;
    margin-bottom: 0in;
    margin-left: 0in;
    margin-right: 0in;

    @if ($barcode_details->is_continuous)
        /*page-break-inside : avoid !important;*/
    @endif
    }
</style>
