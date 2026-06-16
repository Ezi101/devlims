@extends('layouts.app')
@section('title', 'Expired Batches')

@section('content')
    <section class="content-header">
        <h1>Expired Batches
            <small>Manage expired batch logs</small>
        </h1>
    </section>

    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
                <div class="col-md-12">
                    <div class="nav-tabs-custom">
                        <div class="tab-content">
                            <div class="tab-pane active">
                                <table class="table table-striped ajax_view" id="expired_batch_table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Batch Code</th>
                                            <th>Sample / Product</th>
                                            <th>Mfg Date</th>
                                            <th>Expiry Date</th>
                                            <th>Days Expired</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endcomponent
    </section>
@endsection

@section('javascript')
    <script>
        "use strict"
        $(document).ready(function() {
            var i = 1;
            $('#expired_batch_table').DataTable({
                language: {
                    search: '<i class="fa fa-search"></i>',
                    searchPlaceholder: "Search Batch"
                },
                processing: true,
                serverSide: true,
                destroy: true,
                order: [
                    [4, 'desc']
                ],
                pageLength: 25,
                ajax: {
                    url: "{{ route('batch.expired') }}",
                },
                columns: [{
                        render: function() {
                            return i++;
                        }
                    },
                    {
                        data: 'code',
                        name: 'code'
                    },
                    {
                        data: 'product_name',
                        name: 'product_name'
                    },
                    {
                        data: 'mfg_date',
                        name: 'mfg_date'
                    },
                    {
                        data: 'expiry_date',
                        name: 'expiry_date'
                    },
                    {
                        data: 'days_expired',
                        name: 'days_expired'
                    },
                ],
            });
        });
    </script>
@endsection
