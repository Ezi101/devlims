@extends('layouts.app')
@section('title', 'Global ID Update History')
@section('content')
    <section class="content-header">
        <h1>Global ID Update History</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">ID Replacement History</h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered table-striped" id="replacement_history_table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Old Product</th>
                                    <th>Old ID</th>
                                    <th>New Product</th>
                                    <th>New ID</th>
                                    <th>Details (Affected Tables)</th>
                                    <th>Updated By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($logs as $log)
                                    <tr>
                                        <td>{{ $log->created_at->format('d-M-Y H:i') }}</td>
                                        <td>{{ $log->old_product_name }}</td>
                                        <td><span class="label label-danger">{{ $log->old_product_id }}</span></td>
                                        <td>{{ $log->new_product_name }}</td>
                                        <td><span class="label label-success">{{ $log->new_product_id }}</span></td>
                                        <td><small>{{ $log->update_details }}</small></td>
                                        <td>{{ $log->user->first_name ?? 'N/A' }} {{ $log->user->last_name ?? '' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No history found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        {{-- Pagination Fix for Bootstrap --}}
                        <div class="pull-right">
                            {!! $logs->links() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
