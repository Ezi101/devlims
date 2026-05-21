<div class="card-body">
    <div class="row">
        <div class="col-md-12">
            <!-- Custom Tabs -->
            <div class="nav-tabs-custom">
                <div class="tab-content">
                    <div class="tab-pane active" id="ab">

                        <table class="table table-bordered table-striped ajax_view hide-footer test_table"
                            id="task-test-table">
                            <thead>
                                <tr>
                                    <th style="width: 50%">@lang('method.test_name')</th>
                                    <th style="width: 50%">@lang('method.test_id')</th>
                                    <th style="width: 50%">@lang('method.performed_by')</th>

                                    <th style="width: 50%">@lang('sale.status')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($method as $m)
                                    <tr>

                                    </td>
                                    <td style="width: 50%">
                                        <small>{{ @$m->testGroup->name }}</small>
                                </td>
                                
                                        <td style="width: 50%">
                                            {{-- <a
                                                href="{{ action([\App\Http\Controllers\TestController::class, 'show'], ['test' => $m->test]) }}"> --}}
                                                <small>{{ @$m->test }}</small>
                                            {{-- </a> --}}

                                            <td style="width: 50%">
                                                @php
                                                if (@$m->task->members) {
                                                    foreach ($m->task->members as $u) {
                                                        $assign_to = $u->surname . ' ' . $u->first_name . ' ' . $u->last_name;
                                                    }
                                                } else {
                                                    $assign_to = '---';
                                                }
                                            @endphp        {{$assign_to}}                                    </td>
                                            
                                            
                                        <td style="width: 50%">
                                            @if ($m->status == 'completed')
                                                @php
                                                    $status = __('project::lang.completed');
                                                    $bg = 'bg-green'; // Completed: Green
                                                @endphp
                                            @elseif ($m->status == 'cancelled')
                                                @php
                                                    $status = __('project::lang.cancelled');
                                                    $bg = 'bg-red'; // Cancelled: Red
                                                @endphp
                                            @elseif ($m->status == 'on_hold')
                                                @php
                                                    $status = __('project::lang.on_hold');
                                                    $bg = 'bg-yellow'; // On hold: Yellow
                                                @endphp
                                            @elseif ($m->status == 'in_progress')
                                                @php
                                                    $status = __('project::lang.in_progress');
                                                    $bg = 'bg-blue'; // In progress: Blue
                                                @endphp
                                            @elseif ($m->status == 'not_started')
                                                @php
                                                    $status = __('project::lang.not_started');
                                                    $bg = 'bg-gray'; // Not started: Gray
                                                @endphp
                                            @elseif ($m->status == 'rejected')
                                                @php
                                                    $status = __('project::lang.rejected');
                                                    $bg = 'bg-red'; // Rejected: Red
                                                @endphp
                                            @elseif ($m->status == 'approved')
                                                @php
                                                    $status = __('project::lang.approved');
                                                    $bg = 'bg-green'; // Approved: Green
                                                @endphp
                                            @endif

                                            <span class="label {{ @$bg }}">{{ @$status }}</span>
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

</div>
