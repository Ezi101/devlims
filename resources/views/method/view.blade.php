    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Test Detail information</title>
        {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous"> --}}
        <link rel="stylesheet" href="{{ asset('css/sampletestCSS.css') }}">
        {{-- <link rel="preconnect" href="https://fonts.googleapis.com"> --}}
        {{-- <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin> --}}
        {{-- <link href="https://fonts.googleapis.com/css2?family=Dancing+Script&display=swap" rel="stylesheet"> --}}
    </head>

    <body>

        <div class="container header">
            <div class="row mt-5">
                <div class="col-md-2">
                    <img src="{{ asset('img/paklogo4.png') }}" class="w-100" alt="" srcset="">
                </div>
                <div class="col-md-8">
                    <h4 style="font-weight: bold;">ARMED FORCES MEDICAL STORES LABORATORY</h4>
                    <hr>
                    <h5 style="font-weight: bold;">Test Detail information</h5>
                </div>
                <div class="col-md-2">
                    <img src="{{ asset('img/AFMS LOGO-01.png') }}" class="w-100" alt="" srcset="">

                </div>
            </div>
        </div>

        <div class="container-fluid container">
            <div class="row">
                <div class="col-sm-4">
                    <div class="col-sm-5">
                        <strong>Sys. Gen. #:</strong>
                    </div>
                    <div class="col-sm-7">
                        <span>{{ $method[0]->samples->sku }}</span>
                    </div>
                </div>

                <div class="col-sm-4">
                    <div class="col-sm-5">
                        <strong>Section:</strong>
                    </div>
                    <div class="col-sm-7">
                        <span>....</span>
                    </div>
                </div>
                <div class="col-sm-4">
                </div>
            </div>
            <div class="clearfix" style="margin-top: 1%"></div>
            <div class="row">
                <div class="col-sm-4">
                    <div class="col-sm-5">
                        <strong>Sample: </strong>
                    </div>
                    <div class="col-sm-7">
                        <span>{{ $method[0]->samples->name }}</span>
                    </div>
                </div>

                <div class="col-sm-4">
                    <div class="col-sm-5">
                        <strong>Batch #</strong>
                    </div>
                    <div class="col-sm-7">
                        <span>{{ $method[0]->samples->brand->name }}</span>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="col-sm-5">
                        <strong>Sub No:</strong>
                    </div>
                    <div class="col-sm-7">
                        <span>{{ $method[0]->samples->id }}</span>
                    </div>
                </div>
            </div>
            <div class="clearfix" style="margin-top: 1%"></div>
            <table class="table table-responsive mt-4 table-bordered">
                <tr>
                    <th>
                        <strong>Test</strong>

                    </th>
                    <td>
                        <span>{{ $method[0]->test }}</span>
                    </td>
                    <th>
                        <strong>Test Procedure</strong>
                    </th>
                    <td>
                        <span>03</span>

                    </td>

                </tr>
                <tr>
                    <th>
                        <strong>Analyst</strong>

                    </th>
                    <td>
                        <span>kashif test</span>

                    </td>
                    <th>
                        <strong>Method ID</strong>

                    </th>
                    <td>
                        <span>DSX4785</span>

                    </td>
                </tr>
                <tr>
                    <th>

                        <strong>Formula</strong>
                    </th>
                    <td>
                        <span>{{ $method[0]->formulas->formula }}</span>

                    </td>
                    <th>
                        <strong>Limit</strong>
                    </th>
                    <td>
                        <span>grater then equle to 80</span>
                    </td>
                </tr>
                <tr>

                    <th>
                        <strong>Status</strong>
                    </th>
                    <td>

                        <span>Rejected</span>
                    </td>
                </tr>
            </table>
            @foreach ($method as $item)
                {{-- <span>{{ $item->groups->name }}</span> --}}
                <div class="group_data9 col-sm-12">
                    <div class="clearfix" style="margin-top: 1%"></div>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="col-sm-6">
                                <strong>Test Data:</strong>
                            </div>
                            <div class="col-sm-6">
                                <span>{{ $item->groups->name }}</span>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            {{ $item->groups->description }}
                        </div>
                        <div class="col-sm-4">
                            @foreach ($item as $reading)
                                {{-- <p>{{ $reading->group_reading }}</p> --}}
                            @endforeach
                        </div>
                    </div>
                    @if ($item->value != null)
                        <div class="row" style="background-color: lightpink">
                            <div class="col-sm-4">
                                <div class="col-sm-6">
                                    <strong></strong>
                                </div>
                                <div class="col-sm-6">
                                    <span>

                                        <div>
                                            {{ $item->value }}
                                        </div>

                                    </span>
                                </div>

                            </div>
                        </div>
                    @else
                </div>

        </div>
        <div class="container-fluid container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="col-md-12 col-sm-10"style="margin-top: 10px;">
                        @php
                            $leads = json_decode($item->group_reading, true);
                        @endphp
                        <div class="tab-content">
                            <div class="tab-pane active">
                                <table class="table table-bordered table-responsive">
                                    <thead>
                                        <tr>
                                            <th>Reading#</th>
                                            <th>Reading Value</th>
                                            <th>Formula With Values</th>
                                            <th>Multi Calculation Result</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (is_array($leads))
                                            @foreach ($leads as $lead)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $lead }}</td>

                                                    @php

                                                        $keys = ['name', 'value'];
                                                        $sss = [];

                                                        foreach ($values as $val) {
                                                            array_push(
                                                                $sss,
                                                                array_combine($keys, [
                                                                    $val->groups->name,
                                                                    $val->value ?? $lead,
                                                                ]),
                                                            );
                                                        }

                                                        $variableMap = [];
                                                        foreach ($sss as $item) {
                                                            $variableMap[$item['name']] = $item['value'];
                                                        }

                                                        $updatedFormula = preg_replace_callback(
                                                            '/[A-Za-z]+|\*|\/|\+|\-|\d+/',
                                                            function ($match) use ($variableMap, $lead) {
                                                                $token = $match[0];

                                                                if (isset($variableMap[$token])) {
                                                                    // dd($variableMap[$token]);
                                                                    return $variableMap[$token];
                                                                } else {
                                                                    // $token = $lead;
                                                                    return $token; // If not found, keep the token as is
                                                                }
                                                            },
                                                            $formula,
                                                        );

                                                        $result = 0;
                                                        eval('$result = (' . $updatedFormula . ');');
                                                        // Check if $result is numeric (i.e., a valid numeric value)
                                                        if (is_numeric($result)) {
                                                            // Store the result in a variable
                                                            $finalResult = $result;
                                                        } else {
                                                            $finalResult = null; // Formula could not be evaluated.
                                                        }
                                                    @endphp

                                                    <td>{{ $updatedFormula }}</td>
                                                    <td>{{ $finalResult }}</td>
                                                </tr>
                                            @endforeach
                                        @else
                                            {{-- Handle the case where decoding failed --}}
                                            <p>Invalid JSON data</p>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>
        @endforeach
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">

                    <footer class="footer">
                        <table class="table table-responsive table-bordered" style="border: 1px solid black">
                            <tr>

                                <th>
                                    <span>Name: </span><label for=""
                                        style="font-weight: lighter;">{{ Auth::user()->user_full_name }}</label> <br>
                                    <span>Details: </span> <br>
                                    <span>Role: </span><label>{{ Auth::user()->role_name }}</label> <br>
                                </th>
                                <th>
                                    <span>Name: </span> <br>
                                    <span>Details: </span> <br>
                                    <span>Role: </span> <br>
                                </th>
                                <th>
                                    <span>Name: </span> <br>
                                    <span>Details: </span><br>
                                    <span>Role: </span> <br>
                                </th>
                                <th>
                                    <span>Name: </span> <br>
                                    <span>Details: </span><br>
                                    <span>Role: </span> <br>
                                </th>
                            </tr>

                        </table>
                        {{-- <h5 class="sig">{{ Auth::user()->user_full_name }}</h5>
                        <p>Electronically verified report.</p>  --}}
                    </footer>
                </div>
            </div>
        </div>
