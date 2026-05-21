<!-- Modal -->
@foreach ($sampleTest as $index => $t)
    <div class="modal animated zoomInUp custo-zoomInUp" id="feature_modal{{ $t->test_id }}" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body"
                    style="box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.4); padding: 20px; background-color: #f4f4f4; border-radius: 8px; margin-bottom: 10px;">
                    <div class="form-group">
                        <table class="table table-bordered table-striped dataTable sampleTable" id="sampleTable">
                            @if (count($users) > 0)
                            <thead>
                                <tr>
                                    <th>Members</th>
                                    <th>No of Batch</th>
                                </tr>
                            </thead>
                            <tbody>
                                    @foreach ($users as $key => $u)
                                        <tr class="tr{{ $t->test_id }}" data-user='{{ $u->id }}'>
                                            <td>
                                                <input style="border: 1px solid rgba(150, 142, 142, 0.555)" readonly
                                                    type="text" class="form-control styled-input" list="user-list"
                                                    required value="{{ $u->full_name }}" />
                                                <input type="hidden" class="form-control"
                                                    name="member[{{ $t->test_id }}][]" value="{{ $u->id }}">
                                            </td>
                                            <td>
                                                <input style="border: 1px solid rgba(150, 142, 142, 0.555);"
                                                    type="number"
                                                    class="batch-input form-control batch styled-input modelBatch{{ $u->id }} totlBatch"
                                                    id="batch{{ $t->test_id }}" name="batch[{{ $t->test_id }}][]"
                                                    value="" data-id="{{ $t->test_id }}"
                                                    data-test_name="{{ $t->testmethod->name }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="2" class="empty-state">
                                            <div class="empty-state-content">
                                                <i class="fas fa-users-slash empty-state-icon"></i>
                                                <h4 class="empty-state-title">No Analysts Available</h4>
                                                <p class="empty-state-message">
                                                    There are currently no analysts assigned to this lab type.<br>
                                                    Please contact your administrator to add analysts.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                                @endif
                        </table>

                        <style>
                            .empty-state {
                                padding: 30px;
                                text-align: center;
                                background-color: #fdfdfd;
                            }

                            .empty-state-content {
                                max-width: 400px;
                                margin: 0 auto;
                            }

                            .empty-state-icon {
                                font-size: 48px;
                                color: #6c757d;
                                margin-bottom: 20px;
                                opacity: 0.7;
                            }

                            .empty-state-title {
                                color: #495057;
                                font-size: 18px;
                                font-weight: 600;
                                margin-bottom: 10px;
                            }

                            .empty-state-message {
                                color: #6c757d;
                                font-size: 14px;
                                line-height: 1.5;
                                margin-bottom: 0;
                            }
                        </style>
                    </div>
                </div>

                <div class="modal-footer">
                    {{-- <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button> --}}
                    <button type="button" class="btn btn-primary" data-dismiss="modal">@lang('messages.save')</button>
                </div>
            </div>
        </div>
    </div>
@endforeach
<style>
    .styled-input {
        border-radius: 8px;
        background-color: #f9f9f9;
        border: 1px solid #ddd;
        padding: 10px;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
        transition: box-shadow 0.3s ease, border-color 0.3s ease;
    }

    .styled-input:focus {
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        border-color: #007bff;
    }

    .form-control {
        font-size: 14px;
    }

    /* Adjust table styles for better alignment */
    #sampleTable {
        width: 100%;
        border-radius: 8px;
        overflow: hidden;
    }

    #sampleTable thead th {
        background-color: #f1f1f1;
        text-align: center;
        padding: 10px;
        border: none;
    }

    #sampleTable tbody tr {
        border-bottom: 1px solid #eaeaea;
    }

    #sampleTable tbody tr:last-child {
        border-bottom: none;
    }
</style>

@if ($sub_Test && $sub_Test->test_id !== null && $sub_Test->sub_test_id !== null)
    @php
        $sub_test = App\SampleAndTests::with('testmethod')
            ->where('business_id', auth()->user()->business->id)
            ->where('sample_id', $sub_Test->sample_id)
            ->whereIn('lab', $roleNames)
            ->whereNotNull('sub_test_id') // Correct method to check for not null
            ->get();
    @endphp
    @foreach ($sub_test as $t)
        <div class="modal animated zoomInUp custo-zoomInUp" id="feature_modal{{ $t->test_id }}{{ $t->sub_test_id }}"
            tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body"
                        style="box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.4); padding: 20px; background-color: #f4f4f4; border-radius: 8px; margin-bottom: 10px;">
                        <div class="form-group">

                            <table class="table table-bordered table-striped dataTable" id="sampleTable">
                                <thead>
                                    <tr>
                                        <th>Members</th>
                                        <th>No of Batch</th>

                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach ($users as $key => $u)
                                        <tr class="tr{{ $t->test_id }}" data-user='{{ $u->id }}'>
                                            <td>
                                                <input readonly type="text" class="form-control styled-input"
                                                    list="user-list" required value="{{ $u->full_name }}" />
                                                <input type="hidden" class="form-control"
                                                    name="sub_test_member[{{ $t->sub_test_id }}][]"
                                                    value="{{ $u->id }}">
                                            </td>
                                            <td>
                                                <input type="number"
                                                    class="form-control batchsub batch-input styled-input modelBatch{{ $u->id }} totlBatch"
                                                    id="batch_sub{{ $t->sub_test_id }}"
                                                    name="sub_test_batch[{{ $t->sub_test_id }}][]" value=""
                                                    data-id="{{ $t->sub_test_id }}"
                                                    data-test_name="{{ $t->testmethod->name }}">
                                            </td>
                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">@lang('messages.save')</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif
{{-- @endforeach --}}
{{-- <script>
    var outUser = 0;
    var test_ids = 0;

    function outusers() {

        if (outUser) {
            $(".tr" + test_ids).each(function() {
                var userData = $(this).data('user');
                var test_id = $(this).data('test');
                if (userData == outUser) {
                    $(this).remove();
                }
            });
        }

    }
</script> --}}
