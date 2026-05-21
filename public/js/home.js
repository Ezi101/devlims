$(document).ready(function () {

    if ($('#dashboard_date_filter').length == 1) {
        dateRangeSettings.startDate = moment();
        dateRangeSettings.endDate = moment();
        $('#dashboard_date_filter').daterangepicker(dateRangeSettings, function (start, end) {
            $('#dashboard_date_filter span').html(
                start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format)
            );
            update_statistics(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
            if ($('#quotation_table').length && $('#dashboard_location').length) {
                quotation_datatable.ajax.reload();
            }
        });

        update_statistics(moment().format('YYYY-MM-DD'), moment().format('YYYY-MM-DD'));
    }

    $('#dashboard_location').change(function (e) {

        var start = $('#dashboard_date_filter')
            .data('daterangepicker')
            .startDate.format('YYYY-MM-DD');

        var end = $('#dashboard_date_filter')
            .data('daterangepicker')
            .endDate.format('YYYY-MM-DD');

        update_statistics(start, end);
    });

    //atock alert datatables
    var stock_alert_table = $('#stock_alert_table').DataTable({
        processing: true,
        serverSide: true,
        ordering: false,
        searching: false,
        scrollY: "75vh",
        scrollX: true,
        scrollCollapse: true,
        fixedHeader: false,
        dom: 'Btirp',
        ajax: {
            "url": '/home/sample-stock-alert',
            "data": function (d) {
                if ($('#stock_alert_location').length > 0) {
                    d.location_id = $('#stock_alert_location').val();
                }
            }
        },
        fnDrawCallback: function (oSettings) {
            __currency_convert_recursively($('#stock_alert_table'));
        },
    });

    $('#stock_alert_location').change(function () {
        stock_alert_table.ajax.reload();
    });
    //payment dues datatables
    purchase_payment_dues_table = $('#purchase_payment_dues_table').DataTable({
        processing: true,
        serverSide: true,
        ordering: false,
        searching: false,
        scrollY: "75vh",
        scrollX: true,
        scrollCollapse: true,
        fixedHeader: false,
        dom: 'Btirp',
        ajax: {
            "url": '/home/receive-stock-dues',
            "data": function (d) {
                if ($('#purchase_payment_dues_location').length > 0) {
                    d.location_id = $('#purchase_payment_dues_location').val();
                }
            }
        },
        fnDrawCallback: function (oSettings) {
            __currency_convert_recursively($('#purchase_payment_dues_table'));
        },
    });

    $('#purchase_payment_dues_location').change(function () {
        purchase_payment_dues_table.ajax.reload();
    });

    //Sales dues datatables
    sales_payment_dues_table = $('#sales_payment_dues_table').DataTable({
        processing: true,
        serverSide: true,
        ordering: false,
        searching: false,
        scrollY: "75vh",
        scrollX: true,
        scrollCollapse: true,
        fixedHeader: false,
        dom: 'Btirp',
        ajax: {
            "url": '/home/sales-payment-dues',
            "data": function (d) {
                if ($('#sales_payment_dues_location').length > 0) {
                    d.location_id = $('#sales_payment_dues_location').val();
                }
            }
        },
        fnDrawCallback: function (oSettings) {
            __currency_convert_recursively($('#sales_payment_dues_table'));
        },
    });

    $('#sales_payment_dues_location').change(function () {
        sales_payment_dues_table.ajax.reload();
    });

    //Stock expiry report table
    stock_expiry_alert_table = $('#stock_expiry_alert_table').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        scrollY: "75vh",
        scrollX: true,
        scrollCollapse: true,
        fixedHeader: false,
        dom: 'Btirp',
        ajax: {
            url: '/reports/stock-expiry',
            data: function (d) {
                d.exp_date_filter = $('#stock_expiry_alert_days').val();
            },
        },
        order: [[3, 'asc']],
        columns: [
            { data: 'product', name: 'p.name' },
            { data: 'location', name: 'l.name' },
            { data: 'stock_left', name: 'stock_left' },
            { data: 'exp_date', name: 'exp_date' },
        ],
        fnDrawCallback: function (oSettings) {
            __show_date_diff_for_human($('#stock_expiry_alert_table'));
            __currency_convert_recursively($('#stock_expiry_alert_table'));
        },
    });

    if ($('#quotation_table').length) {
        quotation_datatable = $('#quotation_table').DataTable({
            processing: true,
            serverSide: true,
            aaSorting: [[0, 'desc']],
            "ajax": {
                "url": '/issue-stock/draft-dt?is_quotation=1',
                "data": function (d) {
                    if ($('#dashboard_location').length > 0) {
                        d.location_id = $('#dashboard_location').val();
                    }
                }
            },
            columnDefs: [{
                "targets": 4,
                "orderable": false,
                "searchable": false
            }],
            columns: [
                { data: 'transaction_date', name: 'transaction_date' },
                { data: 'invoice_no', name: 'invoice_no' },
                { data: 'name', name: 'contacts.name' },
                { data: 'business_location', name: 'bl.name' },
                { data: 'action', name: 'action' }
            ]
        });
    }
    // Sample report in analyst dashboard
    sampleTestReport();
    function sampleTestReport() {
        $.ajax({
            url: '/get_sample_test_data',
            type: 'GET',
            success: function (response) {
                updateChart(response)

            },
            error: function (xhr, status, error) {
                alert('Error: ' + error);
            }
        });

    }
    //Sample Date Wise Data Get
    if ($('#sample_report_date_filter').length == 1) {
        dateRangeSettings.startDate = moment();
        dateRangeSettings.endDate = moment();
        $('#sample_report_date_filter').daterangepicker(dateRangeSettings, function (start, end) {
            $('#sample_report_date_filter span').html(
                start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format)
            );
            sample_date_get_data(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
            if ($('#quotation_table').length && $('#dashboard_location').length) {
                quotation_datatable.ajax.reload();
            }
        });

        sample_date_get_data(moment().format('YYYY-MM-DD'), moment().format('YYYY-MM-DD'));

        var start = $('#sample_report_date_filter')
            .data('daterangepicker')
            .startDate.format('YYYY-MM-DD');

        var end = $('#sample_report_date_filter')
            .data('daterangepicker')
            .endDate.format('YYYY-MM-DD');

        sample_date_get_data(start, end);
    }
    btn = false
    function sample_date_get_data(start, end) {
        var data = { start: start, end: end };
        $.ajax({
            method: 'get',
            url: '/home/get-sample-date-get-data',
            dataType: 'json',
            data: data,
            success: function (dateData) {
                $("#sample_report_date_filter").on('click', function () {
                    btn = true
                })
                if (btn == true) {
                    updateChart(dateData)

                }
            },
        });

    }//End Sample Date Wise Data

});

if ($('#test_report_date_filter').length == 1) {
    dateRangeSettings.startDate = moment();
    dateRangeSettings.endDate = moment();
    $('#test_report_date_filter').daterangepicker(dateRangeSettings, function (start, end) {
        $('#test_report_date_filter span').html(
            start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format)
        );
        test_date_get_data(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
        if ($('#quotation_table').length && $('#dashboard_location').length) {
            quotation_datatable.ajax.reload();
        }
    });

    test_date_get_data(moment().format('YYYY-MM-DD'), moment().format('YYYY-MM-DD'));

    var start = $('#test_report_date_filter')
        .data('daterangepicker')
        .startDate.format('YYYY-MM-DD');

    var end = $('#test_report_date_filter')
        .data('daterangepicker')
        .endDate.format('YYYY-MM-DD');

    test_date_get_data(start, end);
}
test_date_get_data()
function test_date_get_data(start, end) {
    var data = { start: start, end: end };
    $.ajax({
        url: '/test_date_get_data',
        type: 'get',
        data: data,
        success: function (response) {
            $("#test_report_date_filter").on('click', function () {
                btn = true
            })
            if (btn == true) {
                response.msg.forEach(function (row) {
                    if (row.status == 'approved') {
                        approvedCount++;
                    }
                    if (row.status == 'pending') {
                        pendingCount++;
                    }
                    if (row.status == 'rejectd') {
                        rejectdCount++;
                    }
                    assignCount++;
                });
                // Function to generate an array of dates for a specific month
                function getDatesInMonth(year, month) {
                    const startDate = new Date(year, month, 1);
                    const endDate = new Date(year, month + 1, 0);
                    const dates = [];
                    for (let date = startDate; date <= endDate; date.setDate(date.getDate() + 1)) {
                        dates.push(date.toLocaleDateString());
                    }
                    return dates;
                }

                // Get current year and month
                const currentDate = new Date();
                const currentYear = currentDate.getFullYear();
                const currentMonth = currentDate.getMonth();

                // Get dates for the current month
                const dates = getDatesInMonth(currentYear, currentMonth);

                const values = [assignCount, approvedCount, pendingCount, rejectdCount];

                // Define colors for each category
                const colors = [
                    'rgba(54, 162, 235, 0.2)', // Approved
                    'rgba(255, 206, 86, 0.2)', // Pending
                    'rgba(255, 99, 132, 0.2)' // Rejected
                    // Add more colors if you have more categories
                ];

                // Creating the chart
                var ctx = document.getElementById('myChart').getContext('2d');
                var myChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: dates, // Use dates instead of categories
                        datasets: [{
                            label: 'Tasks',
                            data: values,
                            backgroundColor: colors,
                            borderColor: colors.map(color => color.replace('0.2',
                                '1')), // Set border colors
                            borderWidth: 0.5
                        }]
                    },
                    options: {
                        scales: {
                            datasets: [{
                                barPercentage: 0.3,
                                categoryPercentage: 0.7
                            }]
                        }
                    }
                });

            }
        }
    })

}//End test Date Wise Data
function updateChart(data) {
    var SreceivedCount = 0;
    var SfinalCount = 0;
    var SpendingCount = 0;
    var SassignCount = 0;
    var SorderedCount = 0;

    if (data.type == "success") {

        data.msg.forEach(function (row) {
            if (row.status == 'received') {
                SreceivedCount++;
            }
            if (row.status == 'final') {
                SfinalCount++;
            }
            if (row.status == 'pending') {
                SpendingCount++;
            }
            if (row.status == 'ordered') {
                SorderedCount++;
            }
            SassignCount++;

        });

        var options = {
            series: [SreceivedCount, SfinalCount, SpendingCount, SassignCount, SorderedCount],
            chart: {

                width: 420,
                height: 430,
                type: 'pie',
            },
            labels: ['Received', 'Final', 'Pending', 'Total Sample', 'Ordered'],
            responsive: [{
                breakpoint: 380,
                options: {
                    chart: {
                        width: '100%'
                    },
                    pie: {
                        customScale: 1.1
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }],

            legend: {
                position: 'bottom'
            },

        };
        var SampleReportchart = new ApexCharts(document.querySelector("#SampleReportchart"), options);
        SampleReportchart.render();
    }
}



function update_statistics(start, end) {
    var location_id = '';
    if ($('#dashboard_location').length > 0) {
        location_id = $('#dashboard_location').val();
    }

    var data = { start: start, end: end, location_id: location_id };
    //get purchase details
    var loader = '<i class="fas fa-sync fa-spin fa-fw margin-bottom"></i>';
    $('.total_purchase').html(loader);
    $('.purchase_due').html(loader);
    $('.total_sell').html(loader);
    $('.invoice_due').html(loader);
    $('.total_expense').html(loader);
    $('.total_purchase_return').html(loader);
    $('.total_sell_return').html(loader);
    $('.net').html(loader);
    $.ajax({
        method: 'get',
        url: '/home/get-totals',
        dataType: 'json',
        data: data,
        success: function (data) {
            $('.total_purchase').html(__currency_trans_from_en(data.total_purchase, true));
            $('.purchase_due').html(__currency_trans_from_en(data.purchase_due, true));

            //issue-stock details
            $('.total_sell').html(__currency_trans_from_en(data.total_sell, true));
            $('.invoice_due').html(__currency_trans_from_en(data.invoice_due, true));
            //expense details
            $('.total_expense').html(__currency_trans_from_en(data.total_expense, true));
            var total_purchase_return = data.total_purchase_return - data.total_purchase_return_paid;
            $('.total_purchase_return').html(__currency_trans_from_en(total_purchase_return, true));
            var total_sell_return_due = data.total_sell_return - data.total_sell_return_paid;
            $('.total_sell_return').html(__currency_trans_from_en(total_sell_return_due, true));
            $('.total_sr').html(__currency_trans_from_en(data.total_sell_return, true));
            $('.total_srp').html(__currency_trans_from_en(data.total_sell_return_paid, true));
            $('.total_pr').html(__currency_trans_from_en(data.total_purchase_return, true));
            $('.total_prp').html(__currency_trans_from_en(data.total_purchase_return_paid, true));
            $('.net').html(__currency_trans_from_en(data.net, true));
        },
    });
}
