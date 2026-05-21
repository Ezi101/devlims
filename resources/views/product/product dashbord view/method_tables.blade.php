<div class="card-body">
    <div class="row">
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <div class="tab-content">
                    <div class="tab-pane active" id="ab">
                        <table class="table table-bordered table-striped ajax_view hide-footer method_table" id="method_table">
                            <thead>
                                <tr>
                                <th style="width: 30%">Date</th>
                                    <th style="width: 30%">Method ID</th>
                                    <th style="width: 70%">Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($methods as $m)
                                <tr>
                                <td style="width: 30%">{{ $m->created_at   }}</td>
                                    <td style="width: 30%">{{ $m->method_no   }}</td>
                                    <td style="width: 70%">{{ $m->method_name }}</td>
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
