<div class="container a4-page">

    <body class="A4">

        <header>
            <div class="row header modal-header" style="display: flex; justify-content: space-between;">
                <div class="col-md-2 mt-3" style="align-items: center;">
                    <img src="{{ asset('dummy/paklogo4.png') }}" width="100px" />
                </div>
                <div class="col-md-8 mt-3" style="text-align: center;">
                    <h4 style="font-weight: bold;">ARMED FORCES MEDICAL STORES LABORATORY</h4>
                    <h4 style="font-weight: bold;">(AFMSL) Chaklala</h4>
                    <h5 style="font-weight: bold; text-decoration: underline;">Sample Assignment Summary</h5>
                </div>
                <div class="col-md-2 mt-3" style="text-align: end;">
                    <img src="{{ asset('dummy/AFMS LOGO-01.png') }}" width="110px" />
                </div>
            </div>
        </header>
        <main>
            <table style="width:100%; border-collapse: collapse;">
                <tr>
                    <td colspan="6"><strong>Sample: </strong></td>
                    <td colspan="8"><span>{{ @$ptr->sample->name }}</span></td>

                    <td colspan="6"><strong>Generic Name:</strong></td>
                    <td colspan="8">
                        <span>
                            {{ @$ptr->genericName->name }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td colspan="6"><strong>PTR NO: </strong></td>
                    <td colspan="8"><span>{{ @$ptr->ptr_no }}</span></td>
                    <td colspan="6"><strong>Method:</strong></td>
                    <td colspan="8">
                        <span>
                            {{ @$ptr->method->method_name }}
                        </span>
                    </td>

                </tr>

            </table>
            <table class="table dataTable table-stripe ajax_view hide-footer">
                <thead class="table-header">
                    <tr>
                        <th>@lang('method.test_id')</th>
                        <th>@lang('method.test_name')</th>
                        <th>@lang('method.batches')</th>
                        {{-- <th>@lang('method.no_of_batches')</th> --}}
                        <th>@lang('method.analyst')</th>
                    </tr>
                </thead>
                <tbody id="dataTableBody">
                    @foreach ($sample_readings as $reading)
                        @php
                            $batch = ($reading->batch_id);
                            $batches = App\Batch::where('id', $batch)->get();
                        @endphp
                        <tr>
                            <td>{{ $reading->test }}</td>
                            <td>{{ $reading->task->tests->name }}
                                @if ($reading->task->subtest != null)
                                    ({{ $reading->task->subtest->name }})
                                @endif
                            </td>
                            <td>
                                @foreach ($batches as $ba)
                                    {{ $ba->code }} <br>
                                @endforeach
                            </td>
                            {{-- <td>{{ ($batch) }}</td> --}}
                            <td>
                                {{ $reading->members->user->user_full_name }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </main>

    </body>
</div>
<div class="modal-footer" id="modal-footer">
    <button type="button" class="ptrclose btn btn-secondary" data-dismiss="modal">Close</button>
    <button type="button" class="btn btn-primary" id="printButton" onclick="printModalContent()">Print</button>
</div>
