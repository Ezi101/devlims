<div class="box box-solid" id="accordion">
    <div class="box-header no-border" style="cursor: pointer;" data-toggle="collapse" data-parent="#accordion"
        href="#collapseFilter">
        <h3 class="box-title">
            <i class="fa-solid fa-filter"></i>
            Filters
        </h3>
    </div>
    <div id="collapseFilter" class="panel-collapse collapse">
        <div class="box-body">
            <div class="row">
                <div class="filter-wrapper">

                    <div class="col-sm-3">
                        {!! Form::label('Search Sample', __('lang_v1.search_sample')) !!}
                        {!! Form::select('searchSample', $samples, request('sample_id'), [
                            'placeholder' => __('lang_v1.search_sample'),
                            'class' => 'form-control sample select2',
                            'id' => 'searchSample',
                            'style' => 'width:100%;',
                        ]) !!}
                    </div>

                    <!-- Add Test Name Filter -->
                    <div class="col-sm-3">
                        {!! Form::label('Search Test', __('lang_v1.search_test')) !!}
                        {!! Form::select('searchTest', $testsforfilter ?? [], null, [
                            'placeholder' => __('lang_v1.search_test'),
                            'class' => 'form-control test select2',
                            'id' => 'searchTest',
                            'style' => 'width:100%;',
                        ]) !!}
                    </div>

                    @php
                        // Define the statuses
                        $status = [
                            'not_started' => __('project::lang.not_started'),
                            'in_progress' => __('project::lang.in_progress'),
                            'approved' => __('project::lang.approved'),
                            'rejected' => __('project::lang.rejected'),
                        ];

                        // Add "completed" status only for the specified route
                        if (request()->routeIs('tests.completed')) {
                            $status['completed'] = __('project::lang.completed');
                        }

                        // Define a mapping between URL segments and statuses
                        $statusMapping = [
                            'queued' => 'not_started', // Map 'queued' to 'not_started'
                            'inprogress' => 'in_progress', // Map 'inprogress' to 'in_progress'
                            'approved' => 'approved', // Map 'approved' to 'approved'
                            'rejected' => 'rejected', // Map 'rejected' to 'rejected'
                            'completed' => 'completed', // Map 'completed' to 'completed'
                        ];

                        // Get the current URL path
                        $urlPath = request()->path();

                        // Extract the 'status' from the URL path using the mapping
                        $statusFromUrl = null;
                        foreach ($statusMapping as $urlSegment => $mappedStatus) {
                            if (strpos($urlPath, $urlSegment) !== false) {
                                $statusFromUrl = $mappedStatus; // Use the mapped status if the URL contains the segment
                                break;
                            }
                        }
                    @endphp

                    <div class="col-sm-3 status-hide" style="display: none;">
                        {!! Form::label('Search Status', __('lang_v1.search_by_status')) !!}
                        {!! Form::select(
                            'searchStatus',
                            // If a status is found in the URL, show only that status
                            $statusFromUrl ? [$statusFromUrl => $status[$statusFromUrl]] : $status,
                            $statusFromUrl, // Set the selected status from the URL (if any)
                            [
                                'class' => 'form-control status select2',
                                'id' => 'searchStatus',
                                'style' => 'width:100%;',
                            ],
                        ) !!}
                    </div>

                    <div class="col-sm-3 batch-hide" style="display:none;">
                        {!! Form::label('Search Batch', __('lang_v1.search_batch')) !!}
                        {!! Form::select('batchSearch', [], null, [
                            'placeholder' => __('lang_v1.search_batch'),
                            'class' => 'form-control batch select2',
                            'id' => 'batchSearch',
                            'style' => 'width:100%;',
                        ]) !!}
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="">@lang('lang_v1.enter_no_of_days')</label>
                            <input type="text" class="form-control" id="sampleDayWiseSearch"
                                placeholder="Search by number of days">
                        </div>
                    </div>
                    <br>
                    <div class="col-md-4">
                        <label>&nbsp;</label>
                        <button id="filter_btn" class="btn  btn-default" style="margin-top:25px;"> <i class="fas fa-filter"></i> Apply
                            Filter</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
