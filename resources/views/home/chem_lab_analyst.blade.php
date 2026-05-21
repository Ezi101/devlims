@extends('layouts.app')
@section('title', __('home.home'))

<script src="{{ asset('js/dataTable/jquery.js') }}"></script>
<script src="{{ asset('js/chart.js') }}"></script>
<script src="{{ asset('js/plot.js') }}"></script>

@section('content')
    <style>
        .your-model {
            width: 100%;
        }

        .info-box-icon {
            height: 42px !important;
            width: 42px !important;
            line-height: 42px !important;
        }

        .info-box-content2 {
            padding: 2px 0px 6px 10px;
            margin-left: 50px;
        }

        .info-box-content3 {
            padding: 2px 0px 0px 10px;
            margin-left: 50px;
            font-weight: 500;
            font-size: 15px;
        }

        .info-box-text2 {
            color: #8898aa;
            font-weight: 600;
            font-size: 17px;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .info-box-number {
            color: #525f7f;
            display: block;
            font-weight: 600;
            font-size: 15px;
        }
    </style>
    <!-- Content Header (Page header) -->
    <section class="content-header content-header-custom">
        @php
            $user = auth()->user();
            $rawRole = $user?->roles?->first()?->name ?? '';
            $roleName = $rawRole ? explode('#', $rawRole)[0] : 'User';
        @endphp

        <h1>
            {{ __('home.welcome_message', ['name' => $user?->first_name ?? '']) }}
            @if ($roleName)
                <small
                    style="background-color: #1b0e0849; color: #333; padding: 2px 8px; border-radius: 999px; font-size: 12px; margin-left: 8px;">
                    {{ ucwords($roleName) }}
                </small>
            @endif
        </h1>
    </section>
    <section class="content content-custom no-print">
        <br>
        @if (auth()->check() &&
                auth()->user()->hasRole('Chemical Lab Analyst' . '#' . $business_id))
            <div class="row">
                <!-- Modal Structure -->
                <div class="modal fade" id="issueDetailsModal" tabindex="-1" role="dialog"
                    aria-labelledby="issueDetailsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="@lang('messages.close')">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="all_issue_ids">@lang('lang_v1.issue_id')</label>
                                    <select style="height: 50px;width:100%;" class="form-control select2" id="all_issue_ids"
                                        placeholder="@lang('lang_v1.search_issue_id_holder')">
                                        <option value="">@lang('lang_v1.search_issue_id_holder')</option>
                                        @foreach ($all_issue_ids as $id)
                                            <option value="{{ $id }}">{{ $id }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="issueDetailsContent" class="row main-contain"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                    data-dismiss="modal">@lang('messages.close')</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @component('components.dashbord_widget', ['class' => 'box-primary'])
                <div class="row">
                    <div class="col-sm-4">
                        <div id="performTestDiv" class="info-box info-box-new-style big-tab bg-green"
                            style="height: 150px; display: flex; align-items: center; cursor: pointer;">
                            <div style="width: 30%; display: flex; justify-content: center; align-items: center;">
                                <div class="info-box-icon">
                                    <i style="height: 60px; color: white;" class="fa-solid fa-vials"></i>
                                </div>
                            </div>
                            <div style="width: 70%; display: flex; flex-direction: column; justify-content: center;">
                                <div class="info-box-content" style="margin-left:-60px; text-align: center;">
                                    <span style="color: white; font-size: 20px;">Perform Test</span><br>
                                    <span style="color: white; font-size: 16px;">Total Assigned:
                                        {{ $total_assigned_tests }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endcomponent
    </section>
    @endif
@stop

@section('javascript')
    <script src="{{ asset('js/home.js?v=' . $asset_v) }}"></script>
    <script>
        $(document).ready(function() {
            $('#performTestDiv').on('click', function() {
                $('#issueDetailsModal').modal('show');
            });

            $('#all_issue_ids').on('change', function() {
                var selectedIssueId = $(this).val();
                if (selectedIssueId) {
                    $.ajax({
                        url: '/get-test-by-issue-id',
                        method: 'GET',
                        data: {
                            issue_id: selectedIssueId
                        },
                        success: function(response) {
                            $('#issueDetailsContent').empty();

                            response.test_ids_sr.forEach(function(test_id_sr) {
                                var testHeader = `
                            <div class="col-md-12">
                                <h4 class="card-title">Test ID: ${test_id_sr}</h4>
                            </div>`;
                                $('#issueDetailsContent').append(testHeader);

                                var batchCards = response.batches[test_id_sr].map(
                                    function(batch) {
                                        return `
                                <div class="col-md-4 col-sm-6">
                                    <div class="info-box info-box-new-style  bg-green batch-item" data-test-id="${test_id_sr}" data-batch-id="${batch.id}" style="cursor: pointer;">
                                        <div class="info-box-content">
                                            <p class="batch-text">Batch: ${batch.batch_code}</p>
                                        </div>
                                    </div>
                                </div>`;
                                    }).join('');

                                $('#issueDetailsContent').append(batchCards);
                            });

                            $('.batch-item').on('click', function() {
                                var testId = $(this).data('test-id');
                                var performTestUrl = '{{ url('/performtest') }}' +
                                    '?samplegroup=' + testId;
                                window.location.href = performTestUrl;
                            });

                            if (!$('#issueDetailsModal').hasClass('show')) {
                                $('#issueDetailsModal').modal('show');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                        }
                    });
                }
            });
        });
    </script>
@endsection
