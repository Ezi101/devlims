@extends('layouts.app')
@section('title', 'Inventory Report')

@section('content')
    <section class="content-header">
        <h1>Inventory Report</h1>
    </section>

    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            <form id="inventory-form">
                <div class="row">
                    <!-- Dropdown -->
                    <div class="col-md-8">
                        <div class="form-group">
                            <select name="sample_id" id="sample_id" class="form-control select2" style="margin-top: 5px;" required>
                                <option value="">Please Select</option>
                                @foreach ($samples as $sample)
                                    <option value="{{ $sample->id }}">{{ $sample->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Help Box -->
                    <div class="col-md-4">
                        <div class="help-box custom-bg-infobox">
                            <p>Select a sample from the dropdown to view inventory details.</p>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Inventory Table -->
            <div class="row" id="inventory-details" style="margin-top: 10px; display: none;">
                <div class="col-12">
                    <div class="table-responsive">

                        <table class="table table-bordered dataTable table-striped" id="inventory-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Batch</th>
                                    <th>Total</th>
                                    <th>Issued</th>
                                    {{-- <th>Balance</th> --}}
                                    <th>AFMSL</th>
                                    <th>Retention</th>
                                    <th>AFIMS</th>
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Table rows initialized empty -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Total Quantity Card -->
            <div class="row" style="display: none;margin-top:30px;" id="info-div">
                @component('components.widget', ['class' => 'box-secondary custom-info-div', 'title' => 'Current Stock'])
                    <!-- Card 1: AFMSL -->
                    <div class="col-md-3 col-sm-6">
                        <div class="info-card custom-card">
                            <h5>AFMSL</h5>
                            <p><strong><span id="current-afmsl"></span></strong></p>
                        </div>
                    </div>
                    <!-- Card 2: Retention -->
                    <div class="col-md-3 col-sm-6">
                        <div class="info-card custom-card">
                            <h5>Retention</h5>
                            <p><strong><span id="current-retention"></span></strong></p>
                        </div>
                    </div>
                    <!-- Card 3: AFIMS -->
                    <div class="col-md-3 col-sm-6">
                        <div class="info-card custom-card">
                            <h5>AFIMS</h5>
                            <p><strong><span id="current-afims"></span></strong></p>
                        </div>
                    </div>
                    <!-- Card 4: User -->
                    <div class="col-md-3 col-sm-6">
                        <div class="info-card custom-card">
                            <h5>User</h5>
                            <p><strong><span id="current-user"></span></strong></p>
                        </div>
                    </div>
                @endcomponent
            </div>
            <style>
                .custom-card {
                    border-radius: 8px;
                    padding: 15px;
                    background-color: #f8f9fa;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                    text-align: center;
                    margin-bottom: 15px;
                    margin-top: -30px;
                    transition: transform 0.2s ease-in-out;
                }



                .custom-card p {
                    font-size: 16px;
                    color: #555;
                }

                .custom-card:hover {
                    /* transform: scale(1.05); */
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
                }

                .custom-info-div {
                    background-color: #c4a1735d;
                    /* You can replace this with any color */
                    /* padding: 20px; */
                    border-radius: 8px;
                }
            </style>
        @endcomponent
    </section>

    <style>
        .info-card {
            border-radius: 10px;
            padding: 15px;
            background-color: #f9f9f9;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .info-card h5 {
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }

        .info-card p {
            font-size: 16px;
            color: #555;
        }

        .buttons-print::before {
            content: "\f02f";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            margin-right: 5px;
            color: grey;
        }
    </style>
    <style>
        .buttons-csv::before,
        .buttons-excel::before {
            content: "\f1c3";
        }

        .buttons-print::before {
            content: "\f02f";
        }

        .buttons-pdf::before {
            content: "\f1c1";
        }

        .buttons-colvis::before {
            content: "\f065";
        }

        .buttons-csv::before,
        .buttons-excel::before,
        .buttons-print::before,
        .buttons-pdf::before,
        .buttons-colvis::before {
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            margin-right: 5px;
            color: grey;
        }

        .buttons-csv,
        .buttons-excel,
        .buttons-print,
        .buttons-pdf,
        .buttons-colvis {
            font-size: 12px;
            padding: 5px 8px;
        }

        .table>tbody>tr>td,
        .table>tbody>tr>th,
        .table>tfoot>tr>td,
        .table>tfoot>tr>th,
        .table>thead>tr>td,
        .table>thead>tr>th {
            padding: 4px;
            line-height: 1.32857143;
            border-top: 1px solid #ddd;
        }

        @media print {

            .page-break {
                page-break-before: always;
            }

            @page {
                margin-top: 20px;
                margin-bottom: 30px;
            }

        }
    </style>
@endsection

@section('javascript')
    <script>
        $(document).ready(function() {
            var table = $('#inventory-table').DataTable({
                dom: 'Bfrtip',
                paging: false,
                searching: false,
                ordering: false,
                info: false,
                buttons: [{
                    extend: 'print',
                    text: 'Print',
                    className: 'buttons-print',
                    exportOptions: {
                        columns: ':not(.no-print)' // Exclude no-print columns
                    },
                    customize: function(win) {
                        $(win.document.body).find('h1').remove();
                        var defaultTitle = $('title').text();
                        var reportTitle = defaultTitle.split(' - ')[0] + ' Report';
                        const sampleName = $('#sample_id option:selected').text();

                        var header = `
                    <header style="padding: 10px;">
                        <div class="row header" style="display: flex; justify-content: space-between; align-items: center;">
                            <div class="col-md-2">
                                <img src="{{ asset('dummy/paklogo4.png') }}" width="100px" />
                            </div>
                            <div class="col-md-8" style="text-align: center;">
                                <h4 style="font-weight: bold;">ARMED FORCES MEDICAL STORES LABORATORY</h4>
                                <hr style="margin: 5px 0;">
                                <h5 style="font-weight: bold;">Inventory Report</h5>                                    
                                <h6 style="font-weight: bold;">${sampleName}</h6>
                            </div>
                            <div class="col-md-2" style="text-align: end;">
                                <img src="{{ asset('dummy/AFMS LOGO-01.png') }}" width="110px" />
                            </div>
                        </div>
                    </header>
                `;
                        $(win.document.body).prepend(header);

                        $.get('/get-footer', function(footerContent) {
                            $(win.document.body).append(footerContent);
                        });

                        $(win.document.body).find('table').addClass('print-table');
                    }
                }]
            });

            $('#sample_id').change(function() {
                const sample_id = $(this).val();
                const sampleName = $('#sample_id option:selected').text();
                $('#dynamic-sample-heading').remove();

                if (sample_id) {
                    $.ajax({
                        url: "{{ route('products.getInventoryDetails') }}",
                        type: "GET",
                        data: {
                            sample_id
                        },
                        beforeSend: function() {
                            $('#inventory-details').hide();
                            table.clear().draw(); // Clear the table
                            $('.info-card span').text('-'); // Clear info card
                        },
                        success: function(response) {
                            if (response) {
                                $('#info-div').show();
                                $('#inventory-table').before(
                                    `<h4 id="dynamic-sample-heading">${sampleName}</h4>`
                                );

                                // Update info card
                                $('#current-afmsl').text(response.quantities.afmsl_qty || 0);
                                $('#current-retention').text(response.quantities
                                    .retention_qty || 0);
                                $('#current-afims').text(response.quantities.afmis_qty || 0);
                                $('#current-user').text(response.quantities.user_qty || 0);

                                let serialNo = 1;

                                // Combine issued quantities by batch
                                let issuedQuantitiesByBatch = {};
                                response.issued_quantities.forEach((iq) => {
                                    if (!issuedQuantitiesByBatch[iq.batch]) {
                                        issuedQuantitiesByBatch[iq.batch] = {
                                            batch: iq.batch,
                                            issued_quantity: 0
                                        };
                                    }
                                    issuedQuantitiesByBatch[iq.batch].issued_quantity +=
                                        iq.issued_quantity;
                                });

                                let totalReceived = 0;
                                let totalIssued = 0;
                                let totalBalance = 0;
                                let totalAFMSL = 0;
                                let totalRetention = 0;

                                // Populate table rows
                                response.batches.forEach((batch) => {
                                    const receivedQty = batch.quantity_received || 0;
                                    const issuedQty = issuedQuantitiesByBatch[batch
                                        .batch]?.issued_quantity || 0;

                                    const balance = receivedQty - issuedQty;
                                    const retentionQty = response.retention_quantities
                                        .find((rq) => rq.batch === batch.batch)
                                        ?.quantity || 0;
                                    const afmslQty = balance - retentionQty;

                                    totalReceived += receivedQty;
                                    totalIssued += issuedQty;
                                    totalBalance += balance;
                                    totalAFMSL += afmslQty;
                                    totalRetention += retentionQty;

                                    // Add row to table
                                    table.row.add([
                                        serialNo++,
                                        batch.batch,
                                        receivedQty,
                                        issuedQty,
                                        // balance,
                                        afmslQty,
                                        retentionQty,
                                        response.quantities.afmis_qty || 0,
                                        response.quantities.user_qty || 0,
                                    ]).draw(false);
                                });

                                // Add totals row
                                table.row.add([
                                    '',
                                    '<strong>Total</strong>',
                                    `<strong>${totalReceived}</strong>`,
                                    `<strong>${totalIssued}</strong>`,
                                    // `<strong>${totalBalance}</strong>`,
                                    `<strong>${totalAFMSL}</strong>`,
                                    `<strong>${totalRetention}</strong>`,
                                    '',
                                    '',
                                ]).draw(false);

                                $('#inventory-details').show();
                            }
                        },
                        error: function(xhr) {
                            swal("Error", xhr.responseJSON.error || "An error occurred.",
                                "error");
                        },
                    });
                }
            });
        });
    </script>
@endsection
