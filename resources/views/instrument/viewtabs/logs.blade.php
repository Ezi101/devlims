@extends('layouts.app')

@section('title', __('Equipments Log'))

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
        <div>

            <div class="table-responsive logs_table">

                <table class="table dataTable table-striped ajax_view hide-footer">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Action</th>
                            <th @if (request()->route()->hasParameter('module')) style="display: none;" @endif>Location</th>

                            <th>Details</th>
                            <th class="no-print" hidden>#</th>
                            {{-- <th class="no-print">Actions</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr data-module="{{ $log->module }}">
                                <td>{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                                <td>
                                    @if ($log->event === 'deleted')
                                        <span class="badge bg-red" data-toggle="tooltip"
                                            title="Deleted - {{ $log->details }}">Deleted</span>
                                    @elseif ($log->event === 'created')
                                        <span class="badge bg-green" data-toggle="tooltip"
                                            title="Created - {{ $log->details }}">Created</span>
                                    @elseif ($log->event === 'printed')
                                        <span class="badge bg-gray">Printed</span>
                                    @elseif ($log->event === 'received')
                                        <span class="badge bg-olive" title="Received - {{ $log->details }}">Received</span>
                                    @elseif ($log->event === 'updated')
                                        <span class="badge bg-orange" data-toggle="tooltip">Updated</span>
                                    @elseif ($log->event === 'responded')
                                        <span class="badge bg-yellow">Responded</span>
                                    @elseif ($log->event === 'issued')
                                        <span class="badge bg-light-blue">Issued</span>
                                    @elseif ($log->event === 'approved')
                                        <span class="badge bg-green" data-toggle="tooltip"
                                            title="Approved - {{ $log->details }}">Approved</span>
                                    @elseif ($log->event === 'rejected' || $log->event === 'rejectd')
                                        <span class="badge bg-maroon" data-toggle="tooltip"
                                            title="Rejected - {{ $log->details }}">Rejected</span>
                                    @elseif ($log->event === 'remarks')
                                        <span class="badge badge-success">Remarks</span>
                                    @elseif ($log->event === 'sampleused')
                                        <span class="badge bg-cyan">SampleUsed</span>
                                    @elseif ($log->event === 'labelPrint')
                                        <span class="badge bg-orange">LabelPrint </span>
                                    @elseif ($log->event === 'login')
                                        <span class="badge bg-black" data-toggle="tooltip"
                                            title="Login - {{ $log->details }}">Login</span>
                                    @elseif ($log->event === 'logout')
                                        <span class="badge bg-dark" data-toggle="tooltip"
                                            title="Logout - {{ $log->details }}">Logout</span>
                                    @endif
                                </td>

                                <td @if (request()->route()->hasParameter('module')) style="display: none;" @endif>
                                    {{ $log->module }}</td>

                                <td>
                                    @if ($log->event === 'deleted')
                                        Record with <span style="font-weight: bold;">{{ $log->details }}</span>
                                        was <span style="font-weight:bold;">deleted by
                                            {{ $log->user->getUserFullNameAttribute() ?? 'System' }}
                                        @elseif ($log->event === 'created')
                                            A new record was <span style="font-weight:bold;">created</span>
                                            having
                                            <span style="font-weight: bold;">{{ $log->details }}</span> by
                                            {{ $log->user->getUserFullNameAttribute() ?? 'System' }}
                                        @elseif ($log->event === 'received')
                                            A Sample having <span style="font-weight: bold;">{!! $log->details !!}</span>
                                            <span></span>
                                            by
                                            {{ $log->user->getUserFullNameAttribute() ?? 'System' }}.
                                        @elseif ($log->event === 'issued')
                                            A Sample having <span style="font-weight: bold;">{!! $log->details !!}</span>
                                            <span></span>
                                            by
                                            {{ $log->user->getUserFullNameAttribute() ?? 'System' }}.
                                        @elseif ($log->event === 'approved')
                                            An entry having <span style="font-weight: bold;">{{ $log->details }}</span> was
                                            approved
                                            by
                                            {{ $log->user->getUserFullNameAttribute() ?? 'System' }}.
                                        @elseif ($log->event === 'rejected' || $log->event === 'rejectd')
                                            An entry having <span style="font-weight: bold;">{{ $log->details }}</span> was
                                            rejected
                                            by
                                            {{ $log->user->getUserFullNameAttribute() ?? 'System' }}.
                                        @elseif ($log->event === 'remarks')
                                            <span style="font-weight: bold;">{{ $log->details }}</span> by
                                            {{ $log->user->getUserFullNameAttribute() ?? 'System' }}
                                        @elseif ($log->event === 'printed')
                                            A Document having <span style="font-weight: bold;">{{ $log->details }}</span>
                                            by
                                            {{ $log->user->getUserFullNameAttribute() ?? 'System' }}.
                                        @elseif ($log->event === 'updated')
                                            Record with <span>{!! $log->details !!}</span>
                                            by <span
                                                style="font-weight: bold;">{{ $log->user->getUserFullNameAttribute() ?? 'System' }}</span>
                                        @elseif ($log->event === 'responded')
                                            Entry with <span>{!! $log->details !!}</span>
                                            by
                                            {{ $log->user->getUserFullNameAttribute() ?? 'System' }}
                                        @elseif ($log->event === 'sampleused')
                                            Entry with <span style="font-weight: bold;">{{ $log->details }}</span>
                                            <span style="font-weight:bold;"></span> by
                                            {{ $log->user->getUserFullNameAttribute() ?? 'System' }}
                                        @elseif ($log->event === 'labelPrint')
                                            <span style="font-weight: bold;">{{ $log->details }}</span>
                                            <span style="font-weight:bold;"></span> by
                                            {{ $log->user->getUserFullNameAttribute() ?? 'System' }}
                                        @elseif ($log->event === 'login')
                                            User <span
                                                style="font-weight: bold;">{{ $log->user->getUserFullNameAttribute() ?? 'System' }}</span>
                                            logged in
                                        @elseif ($log->event === 'logout')
                                            User <span
                                                style="font-weight: bold;">{{ $log->user->getUserFullNameAttribute() ?? 'System' }}</span>
                                            logged out
                                    @endif
                                </td>
                                <td hidden>{{ $log->id }}</td>

                                {{-- <td>
                                <form action="{{ route('audit-log.destroy', $log->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-xs"
                                        onclick="return confirm('Are you sure you want to delete this log entry?')"><i
                                            class="fas fa-trash"></i></button>
                                </form>
                            </td> --}}
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </section>
@endsection
