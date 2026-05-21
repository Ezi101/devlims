@extends('layouts.app')

@section('title', __('Utilization Details'))

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('devices.device')
            <small>@lang('lang_v1.manage_equipment')</small>
        </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        @isset($id)
            @include('instrument.partials.device_nav', ['id' => $id])
        @endisset

        <div class="table-responsive">
            <table class="table text-center dataTable table-striped ajax_view hide-footer">
                <thead>
                    <tr class="text-center">
                        <th style="display: none;">#</th>
                        <th>Utilization Time</th>
                        <th>Apparatus Status</th>
                        <th>Batch No.</th>
                        <th>@lang('product.product')</th>
                        <th>Chemical</th>
                        <th>Standard</th>
                        <th>Performed By</th>
                        <th>Lab</th>
                        <th class="no-print">@lang('lang_v1.actions')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($utilizations as $index => $utilization)
                        <tr>
                            <td style="display: none;">{{ $loop->iteration }}</td>
                            <td>
                                {{ $utilization->utilization_start_time->format('H:i') }} -
                                {{ $utilization->utilization_end_time->format('H:i') }}
                            </td>
                            <td>
                                {{ optional($utilization->device)->name ?? 'Apparatus' }}
                                ({{ $utilization->apparatus_status === 'not_okay' ? 'Not OK' : 'OK' }})
                            </td>
                            <td>{{ $utilization->sample_number }}</td>
                            <td>{{ optional($utilization->product)->name ?? 'N/A' }}</td>
                            <td>
                                @isset($utilization->chem_id)
                                    {{ optional($utilization->chemical->product)->name }}
                                    @isset($utilization->chem_qty)
                                        <b>({{ $utilization->chem_qty }})</b>
                                    @endisset
                                @else
                                    ---
                                @endisset
                            </td>
                            <td>
                                @isset($utilization->standard_id)
                                    {{ optional($utilization->standard->product)->name }}
                                    @isset($utilization->standard_qty)
                                        <b>({{ $utilization->standard_qty }})</b>
                                    @endisset
                                @else
                                    ---
                                @endisset
                            </td>
                            <td>{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</td>
                            <td>{{ str_replace('#15', '', optional($utilization->device)->lab) }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-primary btn-xs dropdown-toggle" type="button"
                                        id="actionMenu{{ $utilization->id }}" data-toggle="dropdown">
                                        Actions <span class="caret"></span>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a class="dropdown-item" href="{{ route('utilizations.show', $utilization) }}">
                                            <i class="fas fa-eye"></i> View
                                        </a>

                                        @can('Devices.Utilizations.delete')
                                            <form action="{{ route('utilizations.destroy', $utilization) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item delete-utilization">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        @endcan
                                        <a class="dropdown-item"
                                            href="{{ route('logs.index', ['module' => 'utilization']) }}">
                                            <i class="fa-solid fa-clock-rotate-left"></i> Logs
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted">No utilizations found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
