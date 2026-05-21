<div class="modal fade" id="nomenclatureModal">
    <div class="modal-dialog modal-fullscreen" role="document">

        <div class="modal-body">
            @component('components.widget', ['class' => 'box-primary', 'title' => 'Received Stock Details'])
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div class="row mt-3">
                    <div class="col-sm-12">
                        <!-- Left Column -->
                        <div class="col-sm-6">
                            <div class="invoice-col border p-3 mb-3 rounded">
                                <h5 class="mb-3"><b>Product Details</b></h5>

                                <p><b>@lang('product.sku'):</b> {{ $product->sku ?? '--' }}</p>

                                <p><b>@lang('product.brand'):</b>
                                    {{ optional(optional($product->transaction)->brand)->name ?? '--' }}
                                </p>

                                <p><b>@lang('product.unit'):</b>
                                    {{ optional($product->unit)->short_name ?? '--' }}
                                </p>

                                <p><b>@lang('product.barcode_type'):</b>
                                    {{ $product->barcode_type ?? '--' }}
                                </p>

                                @php
                                    $custom_labels = json_decode(session('business.custom_labels'), true) ?? [];
                                @endphp

                                @for ($i = 1; $i <= 20; $i++)
                                    @php
                                        $db_field = 'product_custom_field' . $i;
                                        $label = 'custom_field_' . $i;
                                    @endphp

                                    @if (!empty($product->$db_field))
                                        <p>
                                            <b>{{ $custom_labels['product'][$label] ?? '' }}:</b>
                                            {{ $product->$db_field ?? '' }}
                                        </p>
                                    @endif
                                @endfor

                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-sm-6">
                            <div class="row">
                                <div class="col-sm-12 border p-3 rounded">
                                    <h5 class="mb-3"><b>Additional Information</b></h5>

                                    <p><b>@lang('product.category'):</b>
                                        {{ optional($product->category)->name ?? '--' }}
                                    </p>

                                    <p><b>@lang('product.sub_category'):</b>
                                        {{ optional($product->sub_category)->name ?? '--' }}
                                    </p>

                                    <p><b>@lang('product.product_type'):</b>
                                        @lang('lang_v1.' . ($product->type ?? ''))
                                    </p>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <br><br>

                <!-- Table Section -->
                <div class="row mt-3">
                    <div class="col-sm-12">
                        <table class="table table-bordered table-striped table-hover table-sm text-muted"
                            style="width:100%; font-weight:300; font-size:14px;">
                            <thead>
                                <tr>
                                    <th>Sample</th>
                                    <th>Generic</th>
                                    <th>Contracts</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr>
                                    <td>{{ $product->name ?? '--' }}</td>

                                    <td>
                                        {{ optional($product->genericNames)->pluck('name')->join(', ') ?? '--' }}
                                    </td>

                                    <td>
                                        {{ optional(optional(optional($product->transaction)->contract))->number ?? '--' }}
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
<style>
    .modal-dialog {
        max-width: 100% !important;
        width: 80% !important;
        margin: 0 auto;
        position: relative;
        top: 10%;
    }


    .modal-content {
        width: 100%;
        height: auto;
        border-radius: 10px;
    }

    .modal-body {
        overflow: visible;
        max-height: none;
        height: auto;
    }


    .modal-body .row>.col-sm-12 {
        width: 100%;
        flex: 1;
    }


    .modal-header .close {
        font-size: 2rem;
        color: #333;
    }



    .modal-body .col-sm-6 {
        padding-right: 15px;
        padding-left: 15px;
    }

    .modal-body table {
        width: 100%;
        table-layout: fixed;
    }

    .modal-body table th,
    .modal-body table td {
        text-align: left;
        padding: 8px;
        word-wrap: break-word;

    }

    @media (max-width: 768px) {
        .modal-dialog {
            max-width: 100%;
            margin: 0;
        }

        .modal-body {
            padding: 10px;
        }

        .modal-body .row>.col-sm-4,
        .modal-body .row>.col-sm-6 {
            width: 100%;
            flex: 1;
        }
    }
</style>
