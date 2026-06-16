@extends('layouts.app')
@section('title', __('lang_v1.str'))



@section('content')

    <section class="content">
        {!! Form::open([
            'url' => action([\App\Http\Controllers\STRController::class, 'store']),
            'method' => 'post',
            'id' => 'section_add_form',
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
                        <input type="text" name="w_batch_id" value="{{ @$wbatch->id }}" class="hidden">
                        <input type="text" name="contract_id" value="{{ @$contract_no_for_str }}" class="hidden">
                        <input type="text" name="supplier_id" value="{{ @$transaction->contact_id }}" class="hidden">
                        <input type="text" name="r_stock_id" value="{{ @$transaction->id }}" class="hidden">

                        <tr>
                            <td colspan="2"><strong>Sample Name</strong></td>
                            <td colspan="2">
                                <input type="text" class="hidden" name="sample_name"
                                    value ="{{ $product->name ?? '-' }}">{{ $product->name ?? '-' }}
                            </td>

                            <td colspan="2"><strong>Batch No</strong></td>
                            <td colspan="2">
                                <input type="text" class="hidden" name="batch_no" value="{{ $batch->code ?? '-' }}">
                                {{ $batch->code ?? '-' }}
                                @if (!empty($wbatch->code))
                                    <small>(W Batch-{{ $wbatch->code }})</small>
                                @endif
                            </td>


                        </tr>
                        <tr>
                            <td colspan="2"><strong>Contract No</strong></td>
                            <td colspan="2"><input type="text" class="hidden" name="contract_no"
                                    value ="{{ @$transaction->contract->id }}">{{ @$transaction->contract->number ?? 'N/A' }}
                            </td>

                            <td colspan="2"><strong>DOM</strong></td>
                            <td colspan="2"><input type="text" class="hidden" name="date_of_manufacture"
                                    value ="{{ $batch->mfg_date ?? '-' }}">{{ $batch->mfg_date ?? '-' }} @if (!empty($wbatch->mfg_date))
                                    <small>(WDOM-{{ $wbatch->mfg_date }})</small>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><strong>Supplier</strong></td>
                            <td colspan="2"><input type="text" class="hidden" name="supplier_name"
                                    value ="{{ $transaction->contact->id ?? '-' }}">{{ $transaction->contact->supplier_business_name ?? '-' }}
                            </td>

                            <td colspan="2"><strong>DOE</strong></td>
                            <td colspan="2"> <input type="text" class="hidden" name="date_of_expiry"
                                    value ="{{ $batch->expiry_date ?? '-' }}">{{ $batch->expiry_date ?? '-' }}@if (!empty($wbatch->expiry_date))
                                    <small>(WDOE-{{ $wbatch->expiry_date }})</small>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><strong>Contracted Qty</strong></td>
                            <td colspan="2"><input type="text" class="hidden" name="contracted_Qty"
                                    value ="{{ @$transaction->contract->t_quantity ?? 0 }}">{{ @$transaction->contract->t_quantity ?? 'N/A' }}
                            </td>

                            <td colspan="2"><strong>Delivered By</strong></td>
                            <td colspan="2"><input type="text" class="hidden" name="delivery_person"
                                    value ="{{ @$transaction->delivryperson->id }}">{{ @$transaction->delivryperson->name }}
                            </td>

                        </tr>
                        <tr>
                            <td colspan="2"><strong>Nature Of Sample</strong></td>
                            <td colspan="2">
                                <input type="text" class="hidden" name="nature_of_sample"
                                    value="{{ @$transaction->contract->id }}">

                                @php
                                    $contractType = @$transaction->contract->type;
                                    $sourceName = @$transaction->source_name;
                                    $installments = @$purchaseLine->instalments;
                                    $installmentText = '';

                                    switch ($installments) {
                                        case 'instalments_1':
                                            $installmentText = '(1st Installment)';
                                            break;
                                        case 'instalments_1_2':
                                            $installmentText = '(1st & 2nd Installment)';
                                            break;
                                        case 'instalments_1_2_3':
                                            $installmentText = '(1st, 2nd & 3rd Installment)';
                                            break;
                                        case 'instalments_2':
                                            $installmentText = '(2nd Installment)';
                                            break;
                                        case 'instalments_2_3':
                                            $installmentText = '(2nd & 3rd Installment)';
                                            break;
                                        case 'instalments_3':
                                            $installmentText = '(3rd Installment)';
                                            break;
                                        case 'instalments_3_4':
                                            $installmentText = '(3rd & 4th Installment)';
                                            break;
                                        case 'instalments_4':
                                            $installmentText = '(4th Installment)';
                                            break;
                                        case 'no_instalments':
                                            $installmentText = '(No Installment)';
                                            break;
                                    }

                                    if ($contractType === 'supply') {
                                        $displayText = 'Supply ' . $installmentText;
                                    } elseif ($contractType === 'tender') {
                                        $displayText = 'Tender';
                                    } elseif (!empty($sourceName)) {
                                        $displayText = $sourceName;
                                    } elseif (!empty($contractType)) {
                                        $displayText = ucwords($contractType);
                                    } else {
                                        $displayText = 'N/A';
                                    }
                                @endphp

                                <span>{{ $displayText }}</span>
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

                            {{-- <td colspan="2"><strong>Sample Specs</strong></td>
                            <td colspan="2"><input type="text" class="hidden" name="sample_specs"
                                    value ="{{ $product->category->id ?? '-' }}">{{ $product->category->name ?? '-' }}
                            </td> --}}
                            <td colspan="2"><strong>Generics:</strong></td>
                            <td colspan="2">
                                {{ @$product->genericNames->pluck('name')->join(', ') }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><strong>Pharmacopeia</strong></td>
                            <td colspan="2"><input type="text" class="hidden" name="pharmacopeia"
                                    value ="{{ $product->pharma->name ?? '-' }}">{{ $product->pharma->name ?? '-' }}</td>

                            <td colspan="2"><strong>Sample ID</strong></td>
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
                            <th class="text-center" style="width: 10%">Test</th>
                            <th class="text-center" style="width: 30%">Specifications</th>
                            <th class="text-center" style="width: 15%">Reference Tests</th>
                            <th class="text-center" style="width: 15%">Results</th>
                            <th class="text-center" style="width: 10%">Comply</th>
                            <th class="text-center" style="width: 10%">Log Book</th>
                            <th class="text-center" style="width: 15%">Analyst</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ass_test as $test)
                            @php

                                // $refernce_tests = App\TestBatch::where('batch_id', $batch->id)
                                //     ->where('test_id', @$test->test_id)
                                //     ->where('sample_id', @$product->id)
                                //     ->whereNotNull('results')
                                //     ->get();

                                $refernce_tests = App\TestBatch::where('batch_id', $batch->id)
                                    ->where('test_id', @$test->test_id)
                                    ->where('sample_id', @$product->id)
                                    ->whereNotNull('results')
                                    ->whereHas('test.samplereading', function ($query) {
                                        $query->where('status', 'approved');
                                    })
                                    ->get();

                                // $baseQuery = App\TestBatch::where('test_id', @$test->test_id)
                                //     ->where('sample_id', @$product->id)
                                //     ->whereNotNull('results')
                                //     ->whereHas('test.samplereading', function ($q) {
                                //         $q->where('status', 'approved');
                                //     });

                                // $refernce_tests = (clone $baseQuery)->where('batch_id', $batch->id)->get();

                                // if ($refernce_tests->isEmpty()) {
                                //     $refernce_tests = $baseQuery->get();
                                // }

                            @endphp


                            <tr id="tr_id" data-id="{{ $test->id }}">
                                <input type="text" class="hidden" name="t_id[]" value="{{ @$test->id }}">
                                <td class="text-start" style="width: 10%"><input type="text" class="hidden"
                                        name="t_name[]" value="{{ @$test->test->name }}">{{ @$test->test->name }}</td>
                                <td class="text-center" style="width: 30%"><input type="text" class="hidden"
                                        name="t_specifications[]"
                                        value="{{ @$test->test_specifications }}">{{ @$test->test_specifications }}</td>

                                <td class="text-center" style="width: 15%">
                                    <select class="form-control strdataid" data-id="{{ @$loop->iteration }}"
                                        name="r_t_id[]">
                                        {{-- <option value="" selected>Please Select</option> --}}
                                        @foreach ($refernce_tests as $item)
                                            <option value="{{ @$item->id }}">{{ @$item->test }}</option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="text-center" style="width: 15%"><input readonly type="text"
                                        id="t_result{{ @$loop->iteration }}" class="form-control"
                                        name="t_result[{{ @$loop->iteration }}]" value="">
                                </td>
                                <td class="text-center" style="width: 10%"><input readonly
                                        id="t_comply{{ @$loop->iteration }}" type="text" class="form-control"
                                        name="t_comply[{{ @$loop->iteration }}]" value="">
                                </td>
                                <td class="text-center" style="width: 10%"><input readonly
                                        id="t_logbok{{ @$loop->iteration }}" type="text" class="form-control">
                                </td>
                                <td class="text-center" style="width: 15%"><input readonly type="text"
                                        id="t_analyst{{ @$loop->iteration }}" class="form-control"
                                        name="t_analyst[{{ @$loop->iteration }}]" value="">
                                </td>
                            </tr>
                            @if ($sub_test)
                                @if ($test->test_id == $sub_test->test_id)
                                    @php
                                        $sub_tests = App\PTR::where('business_id', $sub_test->business_id)
                                            ->where('sample_id', $sub_test->sample_id)
                                            ->where('ptr_no', $sub_test->ptr_no)
                                            ->where('Ptr_status', 'active')
                                            // ->groupBy('test_id')
                                            ->whereNotNull('sub_test_id')
                                            ->get();

                                        // dd($sub_tests);

                                    @endphp
                                    @foreach ($sub_tests as $sub_t)
                                        @php
                                            // $refernce_test = App\TestBatch::where('batch_id', $batch->id)
                                            //     ->where('test_id', @$sub_t->test_id)
                                            //     ->where('sample_id', @$product->id)
                                            //     ->get();
                                            $refernce_tests = App\TestBatch::where('batch_id', $batch->id)
                                                ->where('test_id', @$test->test_id)
                                                ->where('sample_id', @$product->id)
                                                ->whereNotNull('results')
                                                ->whereHas('test.samplereading', function ($query) {
                                                    $query->where('status', 'approved');
                                                })
                                                ->get();
                                        @endphp

                                        <tr id="tr_id" data-id="{{ $sub_t->id }}">
                                            <input type="text" class="hidden" name="t_id[]"
                                                value="{{ @$sub_t->id }}">
                                            <td class="text-start" style="width: 10%"><input type="text" class="hidden"
                                                    name="t_name[]"
                                                    value="{{ @$sub_t->test->name }}">{{ @$sub_t->test->name }}
                                                @if ($sub_t->subtests)
                                                    ({{ $sub_t->subtests->name }})
                                                @endif
                                            </td>
                                            <td class="text-center" style="width: 30%"><input type="text" class="hidden"
                                                    name="t_specifications[]"
                                                    value="{{ @$sub_t->test_specifications }}">{{ @$sub_t->test_specifications }}
                                            </td>

                                            <td class="text-center" style="width: 20%">
                                                <select class="form-control strdataid" data-id="{{ $sub_t->id }}"
                                                    name="r_t_id[]">
                                                    {{-- <option value="" selected>Please Select</option> --}}
                                                    @foreach ($refernce_test as $item)
                                                        <option value="{{ @$item->id }}">{{ @$item->test }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="text-center" style="width: 15%"><input readonly type="text"
                                                    id="t_result{{ $sub_t->id }}" class="form-control"
                                                    name="t_result[{{ @$loop->iteration }}]" value="">
                                            </td>
                                            <td class="text-center" style="width: 10%"><input readonly
                                                    id="t_comply{{ @$sub_t->id }}" type="text" class="form-control"
                                                    name="t_comply[{{ @$loop->iteration }}]" value="">
                                            </td>
                                            <td class="text-center" style="width: 15%"><input readonly type="text"
                                                    id="t_analyst{{ @$sub_t->id }}" class="form-control"
                                                    name="t_analyst[{{ @$loop->iteration }}]" value="">
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            @endif
                        @endforeach
                    </tbody>
                </table>

            </div>

            <div class="" style="text-align: center">
                <button type="submit" class="btn btn-lg btn-success" id="remarksButton">Forward</button>

                <!-- Modal -->
                <div class="modal" id="observationModal" tabindex="-1">
                    {{-- style="overflow: scroll; max-width: 600px; margin: auto;"> --}}
                    <div class="modal-dialog">
                        <div class="modal-content" style="border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
                            <form id="observationForm" method="POST" action="{{ route('str.update.observation') }}">
                                @csrf
                                <div style="padding: 20px;">
                                    {{-- <input type="hidden" name="status" value="rejected"> --}}
                                    <input type="hidden" name="str_no" value="{{ @$strs->str_no }}">
                                    <input type="hidden" name="request_type" id="request_type" value="">

                                    <div class="remarkdata" style="margin-bottom: 15px;">
                                        {{-- <label for="observation" style="font-weight: bold; margin-bottom: 5px;">
                                            <h2><b>Remarks</b></h2>
                                        </label> --}}
                                        <label for="observation" style="font-weight: bold; margin-bottom: 5px;">
                                            <h2><b>Opinion and Interpretation</b></h2>
                                        </label>
                                        <textarea name="observation" id="observation" cols="30" rows="6"
                                            placeholder="Add your opinion and interpretation..."
                                            style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px; resize: none;" required></textarea>
                                        {{-- Amendment Checkbox --}}
                                        <div class="form-group mt-3" style="text-align: left;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="amendmentCheckbox"
                                                    name="has_amendment">
                                                <label class="form-check-label" for="amendmentCheckbox"
                                                    style="font-size: 15px; font-weight: 600; color: #333;">
                                                    Add Amendment to Report
                                                </label>
                                            </div>
                                        </div>

                                        {{-- Amendment Textarea - hidden by default --}}
                                        <div id="amendmentSection" style="display: none;">
                                            <label style="font-weight: bold; margin-bottom: 5px;">Amendment Details</label>
                                            <textarea name="amendment" id="amendment" cols="30" rows="4" placeholder="Enter amendment details..."
                                                style="width: 100%; padding: 10px; border: 1px solid #ced4da; 
                                                    border-radius: 5px; resize: none;"></textarea>
                                        </div>
                                    </div>

                                    <div class="modal-footer" id="modal-footer" style="justify-content: space-between;">
                                        {{-- <button type="button" class="remarkclose btn btn-secondary" style="border-radius: 5px;">Close</button> --}}

                                        <button type="submit" id="saveRemark" class="btn btn-primary"
                                            name="submit_with_observation" style="border-radius: 5px;">Submit with
                                            observation</button>
                                        <button type="button" class="btn btn-warning" id="createSTR"
                                            name="submit_without_observation" style="border-radius: 5px;">
                                            Submit without observation
                                        </button>

                                    </div>
                                </div>
                            </form>

                        </div>

                    </div>
                </div>
            </div>



        @endcomponent
    </section>

    {!! Form::close() !!}
@endsection

@section('javascript')

    <script>
        $(document).ready(function() {
            function firstUpper(string) {
                return string.charAt(0).toUpperCase() + string.slice(1);
            }

            function fetchDataForSelect(selectElement) {
                var str_row_id = $(selectElement).data('id');
                var str_id = $(selectElement).val();

                // Send AJAX request
                $.ajax({
                    type: 'get',
                    url: '{{ route('str.data') }}',
                    data: {
                        'str_test_id': str_id
                    },
                    success: function(response) {
                        console.log(response);

                        if (response) {
                            var analyst = response.analyst;

                            if (response.results) {
                                $(`#t_result${str_row_id}`).val(response.results);
                            } else {
                                $(`#t_result${str_row_id}`).val("N/A");
                            }

                            if (response.comply) {
                                $(`#t_comply${str_row_id}`).val(firstUpper(response.comply));
                            } else {
                                $(`#t_comply${str_row_id}`).val("N/A");
                            }

                            if (response.log_book) {
                                $(`#t_logbok${str_row_id}`).val(firstUpper(response.log_book));
                            } else {
                                $(`#t_logbok${str_row_id}`).val("-");
                            }

                            if (response.analyst) {
                                $(`#t_analyst${str_row_id}`).val(firstUpper(
                                    (analyst.surname || '') + " " +
                                    (analyst.first_name || '') + " " +
                                    (analyst.last_name || '')
                                ));
                            } else {
                                $(`#t_analyst${str_row_id}`).val("N/A");
                            }
                        } else {
                            console.log("No response data received.");
                        }
                    }
                });
            }

            $('.strdataid').each(function() {
                fetchDataForSelect(this);
            });

            // On select change, fetch data for the selected reference test
            $('.strdataid').change(function() {
                fetchDataForSelect(this);
            });
            // Amendment checkbox toggle
            $('#amendmentCheckbox').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#amendmentSection').slideDown(300);
                } else {
                    $('#amendmentSection').slideUp(300);
                    $('#amendment').val('');
                }
            });
        });
    </script>
    <script>
        document.getElementById('remarksButton').addEventListener('click', function() {
            $('#observationModal').modal('show'); // Show the modal
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#createSTR').on('click', function(event) {
                event.preventDefault();

                let $btn = $(this);
                $btn.prop('disabled', true).text('Submitting the request...');

                var formData = $('#section_add_form').serializeArray();

                formData.push({
                    name: '_token',
                    value: "{{ csrf_token() }}"
                });
                formData.push({
                    name: 'request_type',
                    value: 'ajax'
                });

                $.ajax({
                    url: "{{ action([\App\Http\Controllers\STRController::class, 'store']) }}",
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            toastr.success('STR Created Successfully');
                            window.location.href =
                                "{{ route('sample-testing-reports.index') }}";
                        } else {
                            toastr.error(response.msg || 'Something went wrong!');
                            $btn.prop('disabled', false).text(
                                'Submit without observation'); // Re-enable if failed
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        $btn.prop('disabled', false).text(
                            'Submit without observation'); // Re-enable if error
                    }
                });
            });

            // Handle "Submit with observation"
            $('#observationForm').on('submit', function(event) {
                let $btn = $('#saveRemark');
                $btn.prop('disabled', true).text(
                    'Submitting the request...'); // Disable to prevent double click
            });
        });
    </script>


@endsection
