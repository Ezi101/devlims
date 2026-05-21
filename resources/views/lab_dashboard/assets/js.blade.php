<script src="{{ asset('js/apexcharts.js') }}"></script>
<script src="{{ asset('js/chart.js') }}"></script>
<script>
    $(document).ready(function() {
        // $(".logo").fadeOut();
        var width = 1500;
        var position = 0;
        var interval = 0;

        function animate_ambassidors() {
            if (width > 0) {
                $(".slider").animate({
                    scrollLeft: width
                }, 50000);
                width = 0;
            } else {

                $(".slider").animate({
                    scrollLeft: width
                }, 12000);
                width = 1500;
            };
        }

        setInterval(function() {
            animate_ambassidors();
        }, 50000);

        function animate_fade() {
            var index_in = Math.floor(Math.random() * 7);
            $(".image").eq(index_in).fadeOut(5000);
            $(".image").eq(index_in).fadeIn(5000);
        }


        setInterval(function() {
            animate_fade();
        }, 3000);

        animate_ambassidors();
        animate_fade();
    });

    //Get Data for  Dashboard
    $(document).ready(function() {
        sendAjaxRequest();
    });

    function sendAjaxRequest() {
        $.ajax({
            type: "get",
            url: "{{ route('lab.dashboard') }}",
            data: {
                "_token": "{{ csrf_token() }}",
            },
            success: function(response) {
                if (response.success) {
                    $('#sample_table_body').empty()
                    for (let index = 0; index < response.sample.length; index++) {
                        let contract_type;
                        if (response.sample[index].contract_type) {
                            if (response.sample[index].contract_type === 'lp') {
                                if (response.sample[index].source_customer) {
                                    contract_type = response.sample[index].contract_type + ' ' + '(' + (
                                        response.sample[index].source_customer.name) + ')';
                                } else {
                                    contract_type = response.sample[index].contract_type;
                                }
                            } else {
                                contract_type = response.sample[index].contract_type;
                            }
                        } else {
                            contract_type = '--';
                        }
                        let status;
                        if (response.sample[index].status) {
                            status = response.sample[index].status;
                        } else {
                            status = '--';
                        }
                        const batch = response.sample[index].batches.length;
                        $('#sample_table_body').append(
                            `<tr>
                                <td style="width: 40%">` + response.sample[index].product.name + `</td>    
                                <td style="width: 20%">` + batch + `</td>       
                                <td style="width: 20%">` + capitalizeFirstLetter(contract_type) + `</td>       
                               <td style="width: 20%">` +
                            (status === 'lp' ?
                                status + (response.sample[index].sourceCustomer.name) :
                                status) +
                            `</td>          
                            </tr>`
                        );
                    }

                    function capitalizeFirstLetter(string) {
                        return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
                    }
                    $('.announcement').empty();
                    response.announcement.forEach(({
                        date,
                        announcement
                    }, index) => {
                        let formattedDate = new Date(date).toLocaleString();
                        let capitalizedAnnouncement = announcement.charAt(0).toUpperCase() +
                            announcement.slice(1);

                        $('.announcement').append(
                            `<span style="font-size: 30px;color:white;">${capitalizedAnnouncement}</span>&nbsp;<span style="font-size: 15px;color:white;">${formattedDate}</span>&nbsp;<span style="font-size: 30px;color:white;"> |</span>`
                        );

                        if (index < response.announcement.length - 1) {
                            $('.announcement').append(
                                '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
                        }
                    });
                    $('#task_table_body').empty();

                    let groupedTasks = response.task.reduce((acc, task) => {
                        let testMethodName = task.testmethod.name;
                        let sampleName = task.samples.name;
                        let batch = task.batch_id;

                        if (!acc[testMethodName]) {
                            acc[testMethodName] = {};
                        }

                        if (!acc[testMethodName][sampleName]) {
                            acc[testMethodName][sampleName] = new Set();
                        }

                        acc[testMethodName][sampleName].add(batch);
                        return acc;
                    }, {});

                    for (let testMethodName in groupedTasks) {
                        let sampleBatchInfo = groupedTasks[testMethodName];
                        let uniqueSamples = [];
                        let totalBatches = 0;

                        for (let sampleName in sampleBatchInfo) {
                            let batchCount = sampleBatchInfo[sampleName].size;
                            uniqueSamples.push(`${sampleName} [${batchCount}]`);
                            totalBatches += batchCount;
                        }

                        uniqueSamples = uniqueSamples.join(', ');

                        let newRow = $(
                            `<tr>
                                <td style="width:30%">${testMethodName}</td>
                                <td style="width:55%"><marquee>${uniqueSamples}</marquee></td>
                                <td style="width:15%">${totalBatches}</td>
                            </tr>`
                        );

                        $('#task_table_body').prepend(newRow);
                    }

                    $('.total').empty();
                    $('.total').html(response.total);
                    $('.completed').empty();
                    $('.completed').html(response.completed);
                    $('.not_started').empty();
                    $('.not_started').html(response.not_started);
                    $('.in_progress').empty();
                    $('.in_progress').html(response.in_progress);
                    $('.on_hold').empty();
                    $('.on_hold').html(response.on_hold);
                    $('.cancelled').empty();
                    $('.cancelled').html(response.cancelled);
                    //Today
                    $('.totalToday').empty();
                    $('.totalToday').html(response.totalToday);
                    $('.completedToday').empty();
                    $('.completedToday').html(response.completedToday);
                    $('.not_startedToday').empty();
                    $('.not_startedToday').html(response.not_startedToday);
                    $('.in_progressToday').empty();
                    $('.in_progressToday').html(response.in_progressToday);
                    $('.on_holdToday').empty();
                    $('.on_holdToday').html(response.on_holdToday);
                    $('.cancelledToday').empty();
                    $('.cancelledToday').html(response.cancelledToday);
                }
                var updateSeries = [response.total, response.completed, response.not_started, response
                    .in_progress, response.on_hold, response.cancelled
                ];
                packageChart.updateSeries(updateSeries);

                // Updating chart data
                const dataArrayInProgress = Object.values(response.data.in_progress);
                const dataArrayNotStarted = Object.values(response.data.not_started);
                const dataArrayCompleted = Object.values(response.data.completed);

                myChart.data.datasets[0].data = dataArrayInProgress;
                myChart.data.datasets[1].data = dataArrayNotStarted;
                myChart.data.datasets[2].data = dataArrayCompleted;

                myChart.update();
            }
        });
    }

    setInterval(sendAjaxRequest, 30000);

    const tables = [
        document.getElementById('sample_table_body'),
        document.getElementById('task_table_body')
    ];

    let scrollAmounts = [0, 0];
    const scrollSpeed = 0.5; // Adjust this value to control the scroll speed
    let scrollingDirections = [true, true];
    let holding = [false, false]; // To track if it's holding at the end or top
    const holdTime = 5000; // 5 seconds hold time

    function autoScroll() {
        tables.forEach((tbody, index) => {
            if (!holding[index]) {
                if (scrollingDirections[index]) {
                    scrollAmounts[index] += scrollSpeed;
                    if (tbody.scrollTop + tbody.clientHeight >= tbody.scrollHeight) {
                        scrollingDirections[index] = false;
                        holding[index] = true;
                        setTimeout(() => {
                            holding[index] = false;
                        }, holdTime);
                    }
                } else {
                    scrollAmounts[index] -= scrollSpeed;
                    if (tbody.scrollTop <= 0) {
                        scrollingDirections[index] = true;
                        holding[index] = true;
                        setTimeout(() => {
                            holding[index] = false;
                        }, holdTime);
                    }
                }
                tbody.scrollTop = scrollAmounts[index];
            }
        });
        requestAnimationFrame(autoScroll);
    }

    requestAnimationFrame(autoScroll);
    autoScroll();

    //Auto scroll Card 
    let currentSlide = 0;
    const slides = document.querySelectorAll('.cardSlide');
    const totalSlides = slides.length;

    function showSlide(index) {
        const slider = document.querySelector('.card-wrapper-slide');
        slider.style.transform = `translateX(-${index * 100}%)`;
    }

    function nextSlide() {
        currentSlide = (currentSlide + 1) % totalSlides;
        showSlide(currentSlide);
    }

    setInterval(nextSlide, 10000);

    //PIE Chart
    var options = {
        series: [13, 44, 55, 45, 65, 75],
        chart: {
            width: '100%',
            height: 350,
            type: 'pie',
        },
        labels: ['Total', 'Completed', 'Queued', 'In Progress', 'On Hold', 'Unsatisfactory'],
        colors: ['#11CDEF', '#2DCE89', '#F5365C', '#FF851B', '#5c4033', '#A9A9A9'],
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: '100%',
                    height: 300
                },
                pie: {
                    customScale: 1.1
                },
                legend: {
                    position: 'bottom'
                }
            }
        }, {
            breakpoint: 768,
            options: {
                chart: {
                    width: '100%',
                    height: 300
                },
                pie: {
                    customScale: 1
                },
                legend: {
                    position: 'center'
                }
            }
        }, {
            breakpoint: 1024,
            options: {
                chart: {
                    width: '100%',
                    height: 300
                },
                pie: {
                    customScale: 1
                },
                legend: {
                    position: 'right'
                }
            }
        }],
        legend: {
            position: 'bottom'
        },
        title: {
            text: 'FY SUMMARY',
            align: 'center',
            style: {
                fontSize: '15px',
                fontWeight: 'bold'
            }
        }
    };


    // Create chart
    var packageChart = new ApexCharts(document.querySelector("#chart"), options);
    packageChart.render();

    // Column Chart
    const ctx = document.getElementById('column_chart').getContext('2d');
    const myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['July', 'August', 'September', 'October', 'November', 'December', 'January', 'February',
                'March', 'April', 'May', 'June'
            ],
            datasets: [{
                    label: 'In Progress',
                    data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                    backgroundColor: '#FF851B',
                    borderColor: '#FF851B',
                    borderWidth: 1
                },
                {
                    label: 'Queued',
                    data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                    backgroundColor: '#F5365C',
                    borderColor: '#F5365C',
                    borderWidth: 1
                },
                {
                    label: 'Completed',
                    data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                    backgroundColor: '#2DCE89',
                    borderColor: '#2DCE89',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'FY SUMMARY',
                    font: {
                        size: 15 // Set the font size to 15px
                    }
                }
            }
        }
    });

    document.getElementById('column_chart').parentElement.style.height = '310px';
</script>
