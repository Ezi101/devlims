<div id="accordion">
    <div class="card">
        <div class="nav-tabs-custom">
            <div class="tab-content">
                <div class="tab-pane active">
                    <div class="card-body" style="margin-top: 15px;">
                        <div class="card-body">
                            @component('components.widget', ['class' => 'box-primary'])
                                <table class="table dataTable ajax_view hide-footer remkars_table" id="remkars_table">
                                    <tbody>
                                        @php
                                            $remarks = \App\STRRemarks::with('remarkTo', 'remarkBy')
                                                ->whereIn('str_no', $str->pluck('str_no'))
                                                ->get();
                                        @endphp

                                        @foreach ($str as $s)
                                            @foreach ($remarks->where('str_no', $s->str_no) as $index => $remark)
                                                <div class="card">
                                                    <tr>
                                                        <td style="width:80%">
                                                            <span><b>From</b>:
                                                                {{ optional($remark->remarkBy)->first_name }}</span><br>
                                                            <span><b>To</b>:
                                                                {{ optional($remark->remarkTo)->first_name }}</span>
                                                        </td>
                                                        <td style="width:20%;">
                                                            <span><b>Date</b>:
                                                                {{ $remark->created_at->format('d-m-Y') }}</span><br>
                                                            <span><b>Time</b>:
                                                                {{ $remark->created_at->format('H:m:i') }}</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <div class="description">
                                                                <span
                                                                    class="shown-content">{{ substr($remark->remark, 0, 50) }}</span>
                                                                <span class="more-content"
                                                                    style="display: none;">{{ substr($remark->remark, 50) }}</span>
                                                                <a href="#" onclick="toggleDescription(this)"
                                                                    class="toggle-description">Show More</a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </div>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            @endcomponent
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
