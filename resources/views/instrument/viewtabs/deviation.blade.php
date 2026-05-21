@extends('layouts.app')

@section('title', __('Deviation Details'))

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('devices.device')
            <small>@lang('lang_v1.manage_equipment')</small>
        </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        @include('instrument.partials.device_nav', ['id' => $id])

        <div class="table-responsive">
            <table class="table text-center dataTable table-striped ajax_view hide-footer">
                <thead>
                    <tr>
                        <th>@lang('method.date')</th>
                        <th>@lang('method.deviation_no')</th>
                        <th>@lang('Test')</th>
                        <th>@lang('method.batch')</th>
                        <th>@lang('method.user')</th>
                        <th>@lang('messages.type')</th>
                        <th>@lang('devices.device')</th>
                        <th>@lang('method.response')</th>
                        <th class="no-print">@lang('lang_v1.actions')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($deviations as $deviation)
                        @php
                            $testName = optional(optional($deviation->test)->tests)->name;
                            $batchCode = optional($deviation->batch)->code;
                            $userName =
                                optional($deviation->user)->first_name . ' ' . optional($deviation->user)->last_name;
                            $deviceName = optional($deviation->device)->name ?? __('messages.no_data_found');
                            $deviationDate = \Carbon\Carbon::parse($deviation->deviation_date)->format('d-m-Y');
                            $responseSnippet = Str::limit($deviation->response, 30);
                        @endphp

                        <tr>
                            <td>{{ $deviationDate }}</td>
                            <td>{{ $deviation->id }}</td>
                            <td>{{ $testName }}</td>
                            <td>{{ $batchCode }}</td>
                            <td>{{ $userName }}</td>
                            <td>{{ $deviation->type }}</td>
                            <td>{{ $deviceName }}</td>
                            <td>{{ $responseSnippet }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-primary btn-xs dropdown-toggle" type="button"
                                        data-toggle="dropdown">
                                        @lang('lang_v1.actions') <span class="caret"></span>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ route('deviations.show', $deviation->id) }}">
                                            <i class="fas fa-eye"></i> @lang('messages.view')
                                        </a>

                                        @can('Deviations.delete')
                                            <form method="POST" action="{{ route('deviations.destroy', $deviation->id) }}"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item delete-deviation">
                                                    <i class="fas fa-trash"></i> @lang('messages.delete')
                                                </button>
                                            </form>
                                        @endcan

                                        <a class="dropdown-item"
                                            href="{{ route('logs.index', ['module' => 'deviation']) }}">
                                            <i class="fa-solid fa-clock-rotate-left"></i> @lang('messages.logs')
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
