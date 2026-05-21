<script>
    "use strict"

    getRecords(null);

    //Machine Filter
    $('#sample').on('change', function() {
        let sample = $('#sample').val();
        let from_date = $('#from_date').val();
        let to_date = $('#to_date').val();

        getRecords(sample, from_date, to_date);
    });

    //From Date Filter
    $('#from_date').on('change', function() {
        let sample = $('#sample').val();
        let from_date = $('#from_date').val();
        let to_date = $('#to_date').val();

        getRecords(sample, from_date, to_date);
    })

    //To Date Filter
    $('#to_date').on('change', function() {
        let sample = $('#sample').val();
        let from_date = $('#from_date').val();
        let to_date = $('#to_date').val();

        getRecords(sample, from_date, to_date);
    })

    //loading data table
    function getRecords(sample, from_date, to_date) {
        var i = 1;
        const table = $('#batch_table').DataTable({
            language: {
                search: '<i class="fa fa-search"></i>',
                searchPlaceholder: "Search Batch"
            },
            processing: true,
            serverSide: false,
            destroy: true,
            search: true,
            ajax: {
                url: "{{ route('batch.loadTable') }}",
                data: {
                    'sample': sample,
                    'from_date': from_date,
                    'to_date': to_date
                },
            },
            columns: [{
                    "render": function() {
                        return i++;
                    }
                },
                {
                    data: 'batch',
                    name: 'batch'
                },
                {
                    data: 'sample',
                    name: 'sample'
                },
                {
                    data: 'created_date',
                    name: 'created_date'
                },
            ],

        })
    }
</script>
