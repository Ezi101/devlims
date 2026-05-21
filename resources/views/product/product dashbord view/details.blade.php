<div id="accordion">
    <div class="card">
        <div class="nav-tabs-custom">
            <div class="tab-content">
                <div class="tab-pane active">
                    <div class="form-group" data-toggle="collapse show" data-target="#information" aria-expanded="true"
                        aria-controls="information" style="background-color: lightgray">
                        <div class="row">
                            <div class="col-md-12">
                                <button class="btn btn-link" style="font-size:15px ;color:black">
                                    <strong>Information</strong>
                                </button>
                            </div>
                        </div>
                    </div>


                    <div id="information" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
                        <div class="card-body" style="margin-top: 15px;">
                            <div class="row">
                                <div class="col-sm-6">
                                    <span>@lang('product.brand') </span>
                                    <br>
                                    <strong>{{ @$product->transaction->brand->name ?? '-' }} </strong>
                                </div>

                                <div class="col-sm-6">
                                    <span>@lang('product.nomenclature_type')</span>
                                    <br>
                                    <strong>
                                        <a href="#" data-toggle="modal" data-target="#nomenclatureModal"
                                            class="text-decoration-none">
                                            {{ ucwords(@$product->transaction->contract_type) ?? '-' }}
                                        </a>
                                    </strong>

                                </div>
                            <div>
                                @include('product.modal-details')
                            </div>

                                <div class="clear-flex"></div>

                                <div class="col-sm-6">
                                    <span>@lang('product.product_name')</span>
                                    <br>
                                    <strong>{{ $product->name ?? '-' }} </strong>
                                </div>

                                <div class="col-sm-6">

                                    <span>@lang('product.sku')</span>
                                    <br>
                                    <strong>{{ $product->sku ?? '-' }} </strong>
                                </div>


                                <div class="clear-flex"></div>

                                <div class="col-sm-6">
                                    <span>PV Number</span>
                                    <br>
                                    <strong>{{ $product->pv_number ?? '-' }} </strong>
                                </div>

                                <div class="col-sm-6">
                                    <span>Dosage</span>
                                    <br>
                                    <strong> {{ $product->dosage->name ?? '-' }} </strong>
                                </div>

                                <div class="clear-flex"></div>

                                <div class="col-sm-6">
                                    <span>Unit</span>
                                    <br>
                                    <strong>{{ @$product->unit->actual_name ?? '-' }} </strong>
                                </div>

                                <div class="col-sm-6">
                                    <span>Category</span>
                                    <br>
                                    <strong>{{ @$product->category->name ?? '-' }}</strong>
                                </div>

                                <div class="clear-flex"></div>

                                <div class="col-sm-6">
                                    <span>Pharmacopeia</span>
                                    <br>
                                    <strong>{{ $product->pharma->name ?? ($product->types_of_sample ?? '-') }}</strong>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
