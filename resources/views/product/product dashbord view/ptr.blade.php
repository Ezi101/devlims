{{-- <div id="accordion">
    <div class="card">
        <div class="nav-tabs-custom">
            <div class="tab-content">
                <div class="tab-pane active">
                    <div class="card-body" style="margin-top: 15px;">
                        <div class="card-body">
                            @component('components.widget', ['class' => 'box-primary', ])
                            <div class="row">
                                    <div class="col-sm-12">
                                        <table class="table dataTable ajax_view hide-footer ptr-reports-table" id="ptr-reports-table">
                                            <thead>
                                                <tr>
                                                    <th>Date & Time</th>
                                                    <th>PTR NO</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($ptr as $p)
                                                    <tr>
                                                        <td>{{ $p->reported_datetime }}</td>
                                                        <td>{{ $p->ptr_no }}</td>
                                                        <td>
                                                            @if ($p->status == 'approved')
                                                                @php
                                                                    $status = __('Approved');
                                                                    $bg = 'bg-green';
                                                                @endphp
                                                            @elseif ($p->status == 'rejected')
                                                                @php
                                                                    $status = __('Rejected');
                                                                    $bg = 'bg-red';
                                                                @endphp
                                                            @elseif ($p->status == 'pending')
                                                                @php
                                                                    $status = __('Pending');
                                                                    $bg = 'bg-info';
                                                                @endphp
                                                            @endif
                            
                                                            <span
                                                                class="label {{ @$bg }}">{{ @$status }}</span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endcomponent
                            {{-- </div> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>





<div class="card-body">
    <div class="row">
        <div class="col-md-12">
            <!-- Custom Tabs -->
            <div class="nav-tabs-custom">
                <div class="tab-content">
                    <div class="tab-pane active" id="ab">

                        {{-- <table class="table table-bordered table-striped ajax_view hide-footer test_table" --}}
                            {{-- id="test_table"> --}}
                            <thead>
                                <tr>
                                    {{-- <th style="width: 50%">@lang('method.test_id')</th>
                                    <th style="width: 50%">@lang('method.test_name')</th>
                                    <th style="width: 50%">@lang('sale.status')</th> --}}
                                </tr>
                             </thead> 
                            {{-- <tbody>  --}}
                                {{-- @foreach ($method as $m)
                                    <tr>
                                        <td style="width: 50%">
                                                <small>{{ @$m->test }}</small>
                                        </td>
                                        <td style="width: 50%">
                                            <small>{{ @$m->testGroup->name }}</small>
                                    </td>
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
                                @endforeach --}}
                            {{-- </tbody> --}}
                        {{-- </table> --}}

                    {{-- </div>
                </div>
            </div>
        </div>
    </div>

</div> --}}
 
