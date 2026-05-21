@extends('layouts.app')
@section('title', __('lang_v1.sample_tests'))

@section('content')
    <!-- Content Header -->
    <section class="content-header">
        <h1>@lang('lang_v1.sample_tests')
            <small>@lang('lang_v1.manage_select_sample_for_test')</small>
        </h1>
        <a href="{{ route('tests.completed') }}" class="btn btn-default pull-right" style="margin-top:-30px; ">
            <i class="fas fa-link"></i> @lang('method.list_tests')
        </a>

    </section>

    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">

                        <table id="samplesTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>@lang('product.sample')</th>
                                    <th>@lang('product.generic')</th>
                                    <th>@lang('method.count')</th>
                                    @can('others.approve_multiple_tests')
                                        <th>@lang('lang_v1.action')</th>
                                    @endcan

                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($samples as $sample)
                                    <tr>
                                        <td>
                                            <a href="{{ route('tests.completed', ['sample_id' => $sample->id]) }}"
                                                class="sample-link">
                                                {{ $sample->name }}
                                            </a>
                                        </td>
                                        <td> {{ @$sample->genericNames->pluck('name')->join(', ') }} </td>

                                        @php
                                           

                                            $business_id = session('user.business_id');

                                            // Get all users who have roles containing 'Lab Manager'
                                            $labManagerIds = \App\User::where('business_id', $business_id)
                                                ->whereHas('roles', function ($q) {
                                                    $q->where('name', 'LIKE', '%Lab Manager%');
                                                })
                                                ->pluck('id')
                                                ->toArray();
                                            // Get task IDs approved by any Lab Manager
                                            $approvedTaskIds = \App\TestApproved::whereIn('approved_by', $labManagerIds)
                                                ->pluck('test_id')
                                                ->toArray();
                                            // Get only completed sample readings that are NOT in approved list
                                            $completedUnapprovedCount = $sample->sampleReadings
                                                ->where('status', 'completed')
                                                ->filter(fn($r) => in_array($r->task_id, $approvedTaskIds))
                                                ->count();
                                        @endphp

                                        <td>{{ $completedUnapprovedCount }}</td>
                                        @can('others.approve_multiple_tests')
                                            <td>
                                                <button class="btn btn-success btn-xs approve-sample-button"
                                                    data-sample-id="{{ $sample->id }}">
                                                    <i class="fas fa-check"></i> @lang('lang_v1.approve_all_tests')
                                                </button>
                                            </td>
                                        @endcan
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endcomponent


    </section>


@endsection
@section('javascript')
    <script>
        $(document).ready(function() {
            $('#samplesTable').DataTable({
                responsive: true,
                paging: true, // Enables pagination
                searching: true, // Enables the search box
                ordering: true, // Enables column ordering

            });
        });
    </script>
    <script>
        $(document).on('click', '.approve-sample-button', function() {
            var sample_id = $(this).data('sample-id');

            swal({
                icon: 'warning',
                title: 'Confirm Approval',
                text: 'Are you sure you want to approve all tests for this sample?',
                buttons: {
                    cancel: 'Cancel',
                    confirm: 'Yes', // Change "OK" to "Yes"
                },
                dangerMode: true,
            }).then((willApprove) => {
                if (willApprove) {
                    $.ajax({
                        type: 'post',
                        url: '{{ route('test.approvalOfTestsSampleWise') }}',
                        data: {
                            sample_id: sample_id,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            swal({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                buttons: false,
                                timer: 2000,
                            }).then(function() {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            swal({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON ? xhr.responseJSON.message :
                                    'Error approving tests',
                                buttons: false,
                                timer: 4000,
                            });
                        }
                    });
                }
            });
        });
    </script>
@endsection
