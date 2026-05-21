@extends('layouts.app')

@section('title', 'Edit Fiscal Year')

@section('content')
    <section class="content-header">
        <h1>Edit Fiscal Year</h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Fiscal Year Details</h3>
                    </div>

                    {!! Form::model($fiscal_year, ['route' => ['fiscal-years.update', $fiscal_year->id], 'method' => 'put']) !!}
                    <div class="box-body">
                        <div class="form-group">
                            {!! Form::label('name', 'Fiscal Year Name:*') !!}
                            {!! Form::text('name', null, [
                                'class' => 'form-control',
                                'required',
                                'placeholder' => 'e.g., FY 2023-2024',
                            ]) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::label('start_date', 'Start Date:*') !!}
                            <div class="input-group date">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                {!! Form::text('start_date', $fiscal_year->start_date->format('Y-m-d'), [
                                    'class' => 'form-control datepicker',
                                    'required',
                                    'autocomplete' => 'off',
                                ]) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('end_date', 'End Date:*') !!}
                            <div class="input-group date">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                {!! Form::text('end_date', $fiscal_year->end_date->format('Y-m-d'), [
                                    'class' => 'form-control datepicker',
                                    'required',
                                    'autocomplete' => 'off',
                                ]) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    {!! Form::checkbox('is_active', '1', $fiscal_year->is_active) !!}
                                    Set as active fiscal year
                                </label>
                            </div>
                            <p class="help-block">Only one fiscal year can be active at a time.</p>
                        </div>
                    </div>

                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Update Fiscal Year
                        </button>
                        <a href="{{ route('fiscal-years.index') }}" class="btn btn-default">
                            <i class="fa fa-times"></i> Cancel
                        </a>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </section>
@endsection

@section('javascript')
    <script>
        $(document).ready(function() {
            // Initialize datepicker
            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true
            });
        });
    </script>
@endsection
