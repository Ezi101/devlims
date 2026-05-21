<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sample Test Report</title>

    <link rel="stylesheet" href="{{ asset('css/app.css?v=' . $asset_v) }}">
    <link rel="stylesheet" href="{{ asset('css/vendor.css') }}">

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        h4 {
            border-bottom: 1px solid;
            font-weight: bold;
            text-align: center;
        }

        h5 {
            text-align: center;
            margin-top: -5px;
            /* font-weight: bold; */
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            /* border: 1px solid; */
            padding: 4px;
        }

        .table>tbody>tr>td,
        .table>tbody>tr>th,
        .table>tfoot>tr>td,
        .table>tfoot>tr>th,
        .table>thead>tr>td,
        .table>thead>tr>th {
            padding: 4px;
            line-height: 1.42857143;
            vertical-align: top;
            border-top: 1px solid #ddd;
        }


        @page {
            size: A4;
            margin: 50px;

            /* counter-increment: page; */
        }

        @media print {
            .main {
                margin-top: 70px;
                margin-bottom: 50px;
            }


            .header,
            .footer {
                position: fixed;
                left: 0;
                right: 0;
                color: #333;
            }


            .header {
                text-align: center;
                padding: 10px;
                page-break-before: always;
                height: 50px;
            }

            .footer {
                padding: 10px;
                font-size: 10px;
                text-align: center;
                page-break-before: always;
                height: 20px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
                /* Adjust as needed */
            }

            table,
            th,
            td {
                border: 1px solid #ddd;
            }

            th,
            td {
                padding: 8px;
                text-align: left;
            }

        }
    </style>

</head>

<body class="A4">

    <header>
        <div class="header row " style=" display: flex;  justify-content: space-between; align-items: center;">

            <div class="col-md-2 mt-3">
                <img src="{{ asset('dummy/paklogo4.png') }}" width="100px" />
            </div>


            <div class="col-md-8 mt-3">
                <h4 style="font-weight: bold;">ARMED FORCES MEDICAL STORES LABORATORY</h4>
                <h5 style="font-weight: bold;">SAMPLE TEST REPORT</h5>
            </div>


            <div class="col-md-2 mt-3">
                <div style="text-align: end">
                    <img src="{{ asset('dummy/AFMS LOGO-01.png') }}" width="100px" />
                </div>
            </div>

        </div>
    </header>

    <main class="main ">
        <div class="container content">
            <div class="row">
                <div class="tab-content">
                    <div class="row" style="margin-top:10px ;display: flex;  justify-content: space-between; ">
                        <div class="col-sm-2">
                        </div>

                        <div class="col-sm-12" style="display: flex;  justify-content: space-between;">
                            <div class="col-sm-8">
                                <table style="line-height: 0.828571;">
                                    <tr>
                                        <td><strong>STR No:</strong></td>
                                        <td>{{ $strs->str_no }}</td>

                                        <td><strong>Sample ID:</strong></td>
                                        <td>{{ @$strs->product->sku }} </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Sample Name:</strong></td>
                                        <td>{{ @$strs->product->name }}</td>

                                        <td><strong>Batch No:</strong></td>
                                        <td>{{ @$strs->batch->code }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Supplier</strong></td>
                                        <td>{{ @$strs->transaction->contact->name }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-2" style="width: 30%">
                                <div class="">
                                    <img class="mt-5"
                                        src="data:image/png;base64,{{ DNS2D::getBarcodePNG(route('sample-testing-reports.show', ['sample_testing_report' => $strs->str_no]), 'QRCODE', 3, 3, [39, 48, 54]) }}"
                                        style="width: 60px;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane active" id="">
                        <table class="table-sm table table-bordered " style="background: white; margin-top: 10px;">
                            <tr>
                                <td><strong>Contract No</strong></td>
                                <td>{{ @$strs->contract->number }}</td>

                                <td><strong>DOM</strong></td>
                                <td>{{ @$strs->transaction->transaction_date }} </td>
                            </tr>
                            <tr>
                                <td><strong>Contracted Quantity</strong></td>
                                <td>{{ @$strs->transaction->contract->t_quantity }} </td>

                                <td><strong>DOE</strong></td>
                                <td> .....</td>
                            </tr>
                            <tr>
                                <td><strong>Seal Intact</strong></td>
                                <td>....</td>
                                <td><strong>Delivered By</strong></td>
                                <td>.....</td>

                            </tr>
                            <tr>
                                <td><strong>Nature Of Sample</strong></td>
                                <td>.....</td>

                                <td><strong>OEM / MFr</strong></td>
                                <td>{{ $strs->product->brand->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Received By</strong></td>
                                <td>{{ @$strs->transaction->sales_person->username }}</td>

                                <td><strong>Pharmacopeia</strong></td>
                                <td>{{ @$strs->product->types_of_sample }}</td>

                            </tr>
                            <tr>
                                <td><strong>Reported Date And time</strong></td>
                                <td>{{ $strs->reported_datetime }}</td>

                                <td><strong>Sample Tested Earlier</strong></td>
                                <td>....</td>

                            </tr>
                            <tr>

                                <td><strong>Analysis Started On</strong></td>
                                <td>....</td>

                                <td><strong>Analysis Completed On</strong></td>
                                <td>....</td>
                            </tr>
                        </table>
                    </div>

                    <table class="table table221 table-condensed table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 20%; text-align: center">Test</th>
                                <th style="width: 40%; text-align: center;">Specification</th>
                                <th style="width: 15%; text-align: center;">Results</th>
                                <th style="width: 10%; text-align: center;">Comply</th>
                                <th style="width: 15%; text-align: center;">Analyst</th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach ($strss as $s)
                                <tr>
                                    <td style=" text-align: center;width: 20%;">{{ $s->test_name }}</td>
                                    <td style=" text-align: center;width: 40%">{{ $s->test_specifications }}</td>
                                    <td style=" text-align: center;width: 15%">{{ $s->test_result }}</td>
                                    <td style=" text-align: center;width: 10%">{{ $s->test_comply }}</td>
                                    <td style=" text-align: center;width: 15%">{{ $s->test_analyst_id }}</td>
                                </tr>
                                <tr>
                                    <td style=" text-align: center;width: 20%;">{{ $s->test_name }}</td>
                                    <td style=" text-align: center;width: 40%">{{ $s->test_specifications }}</td>
                                    <td style=" text-align: center;width: 15%">{{ $s->test_result }}</td>
                                    <td style=" text-align: center;width: 10%">{{ $s->test_comply }}</td>
                                    <td style=" text-align: center;width: 15%">{{ $s->test_analyst_id }}</td>
                                </tr>
                                <tr>
                                    <td style=" text-align: center;width: 20%;">{{ $s->test_name }}</td>
                                    <td style=" text-align: center;width: 40%">{{ $s->test_specifications }}</td>
                                    <td style=" text-align: center;width: 15%">{{ $s->test_result }}</td>
                                    <td style=" text-align: center;width: 10%">{{ $s->test_comply }}</td>
                                    <td style=" text-align: center;width: 15%">{{ $s->test_analyst_id }}</td>
                                </tr>
                                <tr>
                                    <td style=" text-align: center;width: 20%;">{{ $s->test_name }}</td>
                                    <td style=" text-align: center;width: 40%">{{ $s->test_specifications }}</td>
                                    <td style=" text-align: center;width: 15%">{{ $s->test_result }}</td>
                                    <td style=" text-align: center;width: 10%">{{ $s->test_comply }}</td>
                                    <td style=" text-align: center;width: 15%">{{ $s->test_analyst_id }}</td>
                                </tr>
                                <tr>
                                    <td style=" text-align: center;width: 20%;">{{ $s->test_name }}</td>
                                    <td style=" text-align: center;width: 40%">{{ $s->test_specifications }}</td>
                                    <td style=" text-align: center;width: 15%">{{ $s->test_result }}</td>
                                    <td style=" text-align: center;width: 10%">{{ $s->test_comply }}</td>
                                    <td style=" text-align: center;width: 15%">{{ $s->test_analyst_id }}</td>
                                </tr>
                                <tr>
                                    <td style=" text-align: center;width: 20%;">{{ $s->test_name }}</td>
                                    <td style=" text-align: center;width: 40%">{{ $s->test_specifications }}</td>
                                    <td style=" text-align: center;width: 15%">{{ $s->test_result }}</td>
                                    <td style=" text-align: center;width: 10%">{{ $s->test_comply }}</td>
                                    <td style=" text-align: center;width: 15%">{{ $s->test_analyst_id }}</td>
                                </tr>
                                <tr>
                                    <td style=" text-align: center;width: 20%;">{{ $s->test_name }}</td>
                                    <td style=" text-align: center;width: 40%">{{ $s->test_specifications }}</td>
                                    <td style=" text-align: center;width: 15%">{{ $s->test_result }}</td>
                                    <td style=" text-align: center;width: 10%">{{ $s->test_comply }}</td>
                                    <td style=" text-align: center;width: 15%">{{ $s->test_analyst_id }}</td>
                                </tr>
                                <tr>
                                    <td style=" text-align: center;width: 20%;">{{ $s->test_name }}</td>
                                    <td style=" text-align: center;width: 40%">{{ $s->test_specifications }}</td>
                                    <td style=" text-align: center;width: 15%">{{ $s->test_result }}</td>
                                    <td style=" text-align: center;width: 10%">{{ $s->test_comply }}</td>
                                    <td style=" text-align: center;width: 15%">{{ $s->test_analyst_id }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>


                </div>
            </div>
        </div>

    </main>

    <footer class="footer">
        <div>
            <div class="row" style="border-top: 1px solid;border-bottom: 1px solid;">
                <div class="col-sm-12">
                    <div class="col-sm-3" style="border-right: 1px solid">
                        <span><strong>Name</strong> </span><br>
                        <span><strong>Details</strong> </span><br>
                        <span><strong>Role</strong> </span>
                    </div>
                    <div class="col-sm-3" style="border-right: 1px solid">
                        <span><strong>Name</strong> </span><br>
                        <span><strong>Details</strong> </span><br>
                        <span><strong>Role</strong> </span>
                    </div>
                    <div class="col-sm-3" style="border-right: 1px solid">
                        <span><strong>Name</strong> </span><br>
                        <span><strong>Details</strong> </span><br>
                        <span><strong>Role</strong> </span>
                    </div>
                    <div class="col-sm-3">
                        <span><strong>Name</strong> </span><br>
                        <span><strong>Details</strong> </span><br>
                        <span><strong>Role</strong> </span>
                    </div>
                </div>
            </div>
            <p>Electronically verified report. No signature required.</p>

        </div>

    </footer>
</body>

</html>
