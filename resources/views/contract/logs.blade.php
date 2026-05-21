@extends('layouts.app')
@section('title', __('Contract Log'))

@section('content')
    <section class="content">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">@lang('Contract Logs')</h3>
            </div>
            <div class="box-body">
                <table class="table table-bordered table-striped" id="contract_log_table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>@lang('messages.date')</th>
                            <th>@lang('business.user')</th>
                            <th>@lang('brand.status')</th>
                            <th>@lang('lang_v1.description')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </section>
@endsection

@section('javascript')
    <script type="text/javascript">
        // $(document).ready(function() {
        //     var contract_log_table = $('#contract_log_table').DataTable({
        //         processing: true,
        //         serverSide: true,
        //         aaSorting: [
        //             [0, 'desc']
        //         ], // Latest logs pehle dikhane ke liye
        //         ajax: "{{ action([\App\Http\Controllers\ContractController::class, 'contractLogs']) }}",
        //         columns: [{
        //                 data: 'created_at',
        //                 name: 'created_at'
        //             },
        //             {
        //                 data: 'causer_id',
        //                 name: 'user.first_name'
        //             }, // Searchable by user name
        //             {
        //                 data: 'description',
        //                 name: 'description'
        //             },
        //             {
        //                 data: 'properties',
        //                 name: 'properties'
        //             }
        //         ],
        //         dom: 'Bfrtip', // Buttons display karne ke liye
        //         buttons: [
        //             'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
        //         ]
        //     });
        // });
        $(document).ready(function() {
            var contract_log_table = $('#contract_log_table').DataTable({
                processing: true,
                serverSide: true,
                aaSorting: [
                    [0, 'desc']
                ],
                ajax: "{{ action([\App\Http\Controllers\ContractController::class, 'contractLogs']) }}",
                columns: [{
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'causer_id',
                        name: 'user.first_name'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'properties',
                        name: 'properties',
                        render: function(data, type, row) {
                            // Ye line HTML tags ko decode karke as a UI element dikhayegi
                            return $('<div>').html(data).text();
                        }
                    }
                ],
                // 'l' add karne se 'Show entries' dropdown wapas aa jayega
                // dom: 'lBfrtip',
                dom: '<"row" <"col-sm-4" l><"col-sm-4 text-center" B><"col-sm-4" f>>rtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
                ],
                // Aap entries ki tadad bhi specify kar sakte hain
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ]
            });
        });
    </script>
@endsection
<style>
    /* Buttons ko center mein align karne ke liye */
    .dt-buttons {
        display: inline-block;
        float: none !important;
        text-align: center;
        margin-top: 5px;
    }

    /* Search bar (Filter) ko right side par chipkane ke liye */
    .dataTables_filter {
        float: right;
        text-align: right;
    }

    /* "Show entries" (Length) ko left side par rakhne ke liye */
    .dataTables_length {
        float: left;
        text-align: left;
    }

    #contract_log_table td {
        vertical-align: top !important;
        /* Text hamesha cell ke top se shuru ho */
        line-height: 1.6;
        /* Lines ke darmiyan thora gap */
    }

    /* Responsive mobile fix: Agar screen choti ho to sab center ho jaye */
    @media (max-width: 768px) {

        .dataTables_length,
        .dt-buttons,
        .dataTables_filter {
            float: none !important;
            text-align: center;
            margin-bottom: 10px;
        }
    }
</style>
