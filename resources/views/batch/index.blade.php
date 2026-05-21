@extends('layouts.app')
@section('title', __('batch.batch_report'))

@section('content')
    <!-- Content Header (Page header) -->

    <section class="content-header">
        <h1>@lang('batch.batch_report')
            <small>@lang('batch.m_batch_report')</small>
        </h1>

    </section>
    <!-- Main content -->
    <section class="content">
        @component('components.filters', ['class' => 'box-primary','title' => __('Filters')])
        <div class="form-group">
            <div class="row">
                <div class="col-md-4">
                    <label for="sample" class="form-label">Sample</label>
                    <select name="sample" id="sample" class="form-control select2">>
                        <option value="" selected disabled>Select Option</option>
                        <option value="">All</option>
                        @foreach ($sample as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} <small>{{ $s->pv_number }}</small></option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="from_date" class="form-label">From Date</label>
                    <input type="date" class="form-control" name="from_date" id="from_date">
                </div>
                <div class="col-md-4">
                    <label for="to_date" class="form-label">To Date</label>
                    <input type="date" class="form-control" name="to_date" id="to_date">
                </div>
            </div>
        </div>
        @endcomponent
        @component('components.widget', ['class' => 'box-primary'])
            {{-- index table  --}}
            <div class="row" id="printSection">
                <div class="col-md-12">
                    <div class="nav-tabs-custom">
                        <div class="tab-content">
                            <div class="tab-pane active">

                                <table class="table dataTable table-striped ajax_view hide-footer" id="batch_table">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Batch</th>
                                            <th scope="col">Sample</th>
                                            <th scope="col">Created Date</th>
                                        </tr>
                                    </thead>
                                
                                    <tbody>
                                    </tbody>
                                </table>
                                
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal fade batches_edit" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
                </div>
            </div>
        @endcomponent
    </section>
@endsection

@section('javascript')
    @include('batch.script');
@endsection
