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
                                <table class="table dataTable table-striped" id="expired_batch_table">
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
                                    <tbody>
                                        @foreach ($expiredBatches as $index => $batch)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td><strong>{{ $batch->code }}</strong></td>
                                                <td>{{ $batch->product->name ?? '--' }}</td>
                                                <td>{{ $batch->mfg_date ?? '--' }}</td>
                                                <td class="text-danger">
                                                    <strong>{{ $batch->expiry_date }}</strong>
                                                </td>
                                                <td>
                                                    @php
                                                        $days = '--';
                                                        try {
                                                            $expiry = $batch->expiry_date;
                                                            if (preg_match('/^\d{4}-\d{2}-\d{2}/', $expiry)) {
                                                                $date = \Carbon\Carbon::parse($expiry);
                                                            } elseif (preg_match('/^\d{2}-\d{4}$/', $expiry)) {
                                                                $date = \Carbon\Carbon::createFromFormat(
                                                                    'm-Y',
                                                                    $expiry,
                                                                )->endOfMonth();
                                                            } elseif (preg_match('/^\d{2}-\d{2}$/', $expiry)) {
                                                                $date = \Carbon\Carbon::createFromFormat(
                                                                    'm-y',
                                                                    $expiry,
                                                                )->endOfMonth();
                                                            } else {
                                                                $date = \Carbon\Carbon::parse($expiry);
                                                            }
                                                            $days = $date->diffInDays(now());
                                                        } catch (\Exception $e) {
                                                            $days = '?';
                                                        }
                                                    @endphp
                                                    @if ($days !== '--' && $days !== '?')
                                                        <span class="badge badge-danger">{{ $days }} days ago</span>
                                                    @else
                                                        <span class="badge badge-warning">{{ $days }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
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
            $('#expired_batch_table').DataTable({
                language: {
                    search: '<i class="fa fa-search"></i>',
                    searchPlaceholder: "Search Batch"
                },
                order: [
                    [5, 'desc']
                ],
                pageLength: 25,
            });
        });
    </script>
@endsection
