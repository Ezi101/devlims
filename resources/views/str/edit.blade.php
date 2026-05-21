@extends('layouts.app')
@section('title', __('lang_v1.str'))



@section('content')

    <section class="content">
        {!! Form::open([
            'url' => action(
                [\App\Http\Controllers\STRController::class, 'update'],
                ['sample_testing_report' => $strs[0]->str_no],
            ),
            'method' => 'PUT',
        ]) !!}

        <!-- Content Header (Page header) -->
        @csrf



        <div class="row" style="text-align: center">
            <div class="col-md-3 col-sm-3 mt-3">
                <div>
                    {{-- <img src="{{ asset('public/no/' . @$company->logo) }}" width="150px" /> --}}
                </div>
            </div>
            <div class="col-md-6 col-sm-6 mt-3">
                <h4 style="font-weight: bold;text-decoration:underline;">
                    ARMED FORCES MEDICAL STORES LABORATORY
                </h4>
                <h5 style="font-weight:bold;">
                    SAMPLE TEST REPORT
                </h5>
            </div>


            <div class="col-md-3 col-sm-3">
                <div style="text-align:center ;">
                    {{-- <img src="{{ asset('public/project_logo/' . @$print->project->p_logo) }}" style=" width: 100px; margin-top:5px"> --}}
                </div>
                <div class="visible-print" style="text-align: center">
                    {{-- {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(90)->format('svg')->generate(Request::url()) !!}
                    <center>
                        @isset(@$print->status) @if (@$print->status->name == 'Blocked')
                            <h5 style="color: red; font-weight: bold">
                            @else
                                <h5 style="font-weight: bold">
                        @endif
                        </h5> @endisset
                    </center> --}}
                </div>
            </div>


        </div>

        @component('components.widget', ['class' => 'box-primary'])

            <!-- Main content -->

            <div class="tab-content">
                <div class="tab-pane active" id="">
                    <table class=" table table-bordered " style="background: white;">
                        <input type="text" name="sample_id" value="{{ $product->id }}" class="hidden">
                        <input type="text" name="batch_id" value="{{ @$batch->id }}" class="hidden">
                        <input type="text" name="contract_id" value="{{ @$transaction->contract_no }}" class="hidden">
                        <input type="text" name="supplier_id" value="{{ @$transaction->contact_id }}" class="hidden">
                        <input type="text" name="r_stock_id" value="{{ @$transaction->id }}" class="hidden">

                        <tr>
                            <td colspan="2"><strong>Sample Name</strong></td>
                            <td colspan="2">
                                <input type="text" class="hidden" name="sample_name"
                                    value ="{{ $product->name ?? '-' }}">{{ $product->name ?? '-' }}
                            </td>

                            <td colspan="2"><strong>Batch</strong></td>
                            <td colspan="2">
                                <input type="text" class="hidden" name="batch_no"
                                    value ="{{ $batch->code ?? '-' }}">{{ $batch->code ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><strong>Contract No</strong></td>
                            <td colspan="2"><input type="text" class="hidden" name="contract_no"
                                    value ="{{ $transaction->contract->id ?? '-' }}">{{ $transaction->contract->number ?? '-' }}
                            </td>

                            <td colspan="2"><strong>DOM</strong></td>
                            <td colspan="2"><input type="text" class="hidden" name="date_of_manufacture"
                                    value ="{{ $batch->mfg_date ?? '-' }}">{{ $batch->mfg_date ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><strong>Supplier</strong></td>
                            <td colspan="2"><input type="text" class="hidden" name="supplier_name"
                                    value ="{{ $transaction->contact->id ?? '-' }}">{{ $transaction->contact->supplier_business_name ?? '-' }}
                            </td>

                            <td colspan="2"><strong>DOE</strong></td>
                            <td colspan="2"> <input type="text" class="hidden" name="date_of_expiry"
                                    value ="{{ $batch->expiry_date ?? '-' }}">{{ $batch->expiry_date ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><strong>Contracted Quantity</strong></td>
                            <td colspan="2"><input type="text" class="hidden" name="contracted_Qty"
                                    value ="{{ @$transaction->purchaseLines->first()->quantity }}">{{ @$transaction->purchaseLines->first()->quantity }}
                            </td>

                            <td colspan="2"><strong>Delivered By</strong></td>
                            <td colspan="2"><input type="text" class="hidden" name="delivery_person"
                                    value ="{{ @$transaction->delivryperson->id }}">{{ @$transaction->delivryperson->name }}
                            </td>

                        </tr>
                        <tr>
                            <td colspan="2"><strong>Nature Of Sample</strong></td>

                            <td colspan="2"><input type="text" class="hidden" name="nature_of_sample"
                                    value ="{{ @$transaction->contract->id }}">{{ ucfirst(@$transaction->contract->type) }}
                            </td>
                            <td colspan="2"><strong>OEM / MFR</strong></td>
                            <td colspan="2"> <input type="text" class="hidden" name="oem_mfr"
                                    value ="{{ @$transaction->brand->id }}">{{ ucfirst(@$transaction->brand->name) }}</td>
                        </tr>
                        <tr>
                            <td colspan="2"><strong>Received By</strong></td>
                            <td colspan="2"><input type="text" class="hidden" name="recevied_by"
                                    value ="{{ @$transaction->sales_person->username ?? '-' }}">{{ ucwords(@$transaction->sales_person->userFullName) ?? '-' }}
                            </td>

                            <td colspan="2"><strong>Sample Specs</strong></td>
                            <td colspan="2"><input type="text" class="hidden" name="sample_specs"
                                    value ="{{ $product->category->id ?? '-' }}">{{ $product->category->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td colspan="2"><strong>Pharmacopeia</strong></td>
                            <td colspan="2"><input type="text" class="hidden" name="pharmacopeia"
                                    value ="{{ $product->pharma->name ?? '-' }}">{{ $product->pharma->name ?? '-' }}</td>

                            <td colspan="2"><strong>Sample ID Related To AFMSL after Testing</strong></td>
                            <td colspan="2"><input type="text" class="hidden" name="sample_unique_id"
                                    value ="{{ @$product->sku }}">{{ @$product->sku }}</td>
                        </tr>
                        {{-- <tr>
                            <td colspan="2"><strong>Reported Date And Time</strong></td>
                            <td colspan="2"><input type="text" class="hidden" name="reported_date_time"
                                    value ="">-</td>

                            <td colspan="2"><strong>Seal Intact</strong></td>
                            <td colspan="2"><input type="text" class="hidden" name="seal_intact" value ="">-
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><strong>Sample Tested Earlier</strong></td>
                            <td colspan="2"><input type="text" class="hidden" name="sample_tested_earlier"
                                    value ="">-</td>

                            <td colspan="2"><strong>Analysis Started On</strong></td>
                            <td colspan="2"><input type="text" class="hidden" name="analysis_started_on"
                                    value ="">-</td>
                        </tr>
                        <tr>
                            <td colspan="2"><strong>Analysis Completed On</strong></td>
                            <td colspan="2"><input type="text" class="hidden" name="analysis_completed_on"
                                    value ="">-</td>


                        </tr> --}}
                    </table>
                </div>
                <table class="table table-condensed table-bordered" style="background: white;">
                    <thead>
                        <tr>
                            <th class="text-center">Test</th>
                            <th class="text-center">Specifications</th>
                            <th class="text-center">Reference Tests</th>
                            <th class="text-center">Results</th>
                            <th class="text-center">Comply</th>
                            <th class="text-center">Analyst</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (count($strs) > 1)
                        @foreach ($strs as $test)
                                <tr>
                                    <input type="hidden" name="t_id[{{ $loop->iteration }}]" value="{{ $test->test_id }}">
                                    <input type="hidden" name="t_str_no[{{ $loop->iteration }}]"
                                        value="{{ $test->str_no }}">
                                    <td class="text-center" style="width: 10%">
                                        <input type="hidden" name="t_name[{{ $loop->iteration }}]"
                                            value="{{ $test->test_name }}">
                                        {{ $test->test_name }}
                                    </td>
                                    <td class="text-center" style="width: 50%">
                                        <input type="hidden" name="t_specifications[{{ $loop->iteration }}]"
                                            value="{{ $test->test_specifications }}">
                                        {{ $test->test_specifications }}
                                    </td>

                                    @php
                                        // Fetch reference tests related to the product
                                        $reference_tests = \App\SampleReading::where('product_id', $product->id)
                                            ->groupBy('test')
                                            ->get();
                                    @endphp

                                    <td class="text-center" style="width: 50%">
                                        <select class="form-control" name="r_t_id[{{ $loop->iteration }}]">
                                            <option value="">Please Select</option>
                                            @foreach ($reference_tests as $item)
                                                <option value="{{ $item->test }}"
                                                    {{ $test->refernce_test_id == $item->test ? 'selected' : '' }}>
                                                    {{ $item->test }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td class="text-center" style="width: 15%">
                                        <input type="text" class="form-control" name="t_result[{{ $loop->iteration }}]"
                                            value="{{ $test->test_result }}">
                                    </td>
                                    <td class="text-center" style="width: 10%">
                                        <input type="text" class="form-control" name="t_comply[{{ $loop->iteration }}]"
                                            value="{{ $test->test_comply }}">
                                    </td>
                                    <td class="text-center" style="width: 15%">
                                        <input type="text" class="form-control" name="t_analyst[{{ $loop->iteration }}]"
                                            value="{{ $test->test_analyst_id }}">
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            @foreach ($strs as $data)
                                <input type="hidden" name="str_no" value="{{ $data->id }}">
                                @php
                                    $tests = json_decode($data->test_id);
                                    $reference_tests = json_decode($data->refernce_test_id);
                                @endphp

                                @foreach ($tests as $key3 => $test_id)
                                    @php
                                        $ptress = \App\PTR::with(['test', 'subtests'])->find($test_id);
                                        $refer_tests = \App\TestBatch::find($reference_tests[$key3]);
                                        $available_tests = \App\TestBatch::where('test_id', $ptress->test_id)
                                            ->where('batch_id', $data->batch_no)
                                            ->where('sample_id', $data->sample_id)
                                            ->select('id', 'test')
                                            ->get();
                                    @endphp

                                    <tr data-id="{{ $key3 }}">
                                        <td class="text-center" style="width: 20%; font-weight: bold;">
                                            {{ $ptress->test->name }} @if ($ptress->subtests)
                                                ({{ $ptress->subtests->name }})
                                            @endif
                                        </td>
                                        <td class="text-center" style="width: 30%">
                                            {{ $ptress->test_specifications }}
                                        </td>
                                        <td class="text-center" style="width: 15%">
                                            <select class="form-control" name="r_t_id[]" data-id="{{ $key3 }}"
                                                id="strdataid">
                                                <option value="">Select refer test...</option>
                                                @foreach ($available_tests as $ref_test)
                                                    <option value="{{ $ref_test->id }}"
                                                        {{ $ref_test->id == $refer_tests->id ? 'selected' : '' }}>
                                                        {{ $ref_test->test }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="text-center" style="width: 15%">
                                            <input readonly type="text" id="t_result{{ $key3 }}"
                                                class="form-control" name="t_result[{{ $key3 }}]"
                                                value="{{ $refer_tests->results }}">
                                        </td>
                                        <td class="text-center" style="width: 10%">
                                            <input readonly id="t_comply{{ $key3 }}" type="text"
                                                class="form-control" name="t_comply[{{ $key3 }}]"
                                                value="{{ $refer_tests->comply }}">
                                        </td>
                                        <td class="text-center" style="width: 15%">
                                            <input readonly type="text" id="t_analyst{{ $key3 }}"
                                                class="form-control" name="t_analyst[{{ $key3 }}]"
                                                value="{{ $refer_tests->analyst->surname }} {{ $refer_tests->analyst->first_name }} {{ $refer_tests->analyst->last_name }}">
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        @endif
                    </tbody>
                </table>

            </div>

            <div class="" style="text-align: center">
                <button type="submit" class="btn btn-primary btn-lg">@lang('messages.update')</button>
                {{-- <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button> --}}
            </div>
        @endcomponent
    </section>

    {!! Form::close() !!}
@endsection

@section('javascript')
    <script src="{{ asset('modules/project/js/test.js?v=' . @$asset_v) }}"></script>

    <script>
        $(document).on('change', '#strdataid', function() {
            var str_row_id = $(this).data('id');
            var str_id = $(this).val();

            function firstUpper(string) {
                return string.charAt(0).toUpperCase() + string.slice(1);
            }

            $.ajax({
                type: 'get',
                url: '{{ route('str.data') }}',
                data: {
                    'str_test_id': str_id
                },
                success: function(response) {

                    console.log(response)

                    console.log(response.analyst);
                    var analyst = response.analyst;
                    // Set results
                    $(`#t_result${str_row_id}`).val(response.results ? response.results : "N/A");

                    // Set comply
                    $(`#t_comply${str_row_id}`).val(response.comply ? firstUpper(response.comply) :
                        "N/A");
                    if (response.analyst) {
                        $(`#t_analyst${str_row_id}`).val(firstUpper(
                            (analyst.surname || '') + " " +
                            (analyst.first_name || '') + " " +
                            (analyst.last_name || '')
                        ));

                    } else {
                        $(`#t_analyst${str_row_id}`).val("N/A");
                    }
                }
            })


            // alert(str_id);
        })
    </script>

@endsection
