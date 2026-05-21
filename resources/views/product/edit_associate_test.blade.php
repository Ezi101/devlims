<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document"> <!-- Use modal-lg for larger width -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Edit Associate Test</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('associated_test.edit') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="edit_test_id" id="edit_test_id">
                    <table class="table table-condensed">
                        <thead>
                            <tr>
                                <th>{{ __('method.test_name') }}</th>
                                <th>{{ __('method.sub_test') }}</th>
                                <th>{{ __('lang_v1.t_spec') }}</th>
                                <th>{{ __('method.test_labs') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="form-group">
                                        <select name="test" id="edit_test" class="form-control select2" required>
                                            <option value="" disabled selected>
                                                {{ __('messages.please_select') }}</option>
                                            @foreach ($test_group as $id => $name)
                                                <option value="{{ $id }}" data-name="{{ $name }}">
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <select name="sub_test" id="edit_sub_test_id" class="form-control select2">
                                            <option value="" disabled selected>
                                                {{ __('messages.please_select') }}</option>
                                            @foreach ($subTest as $item)
                                                <option value="{{ $item->id }}">
                                                    {{ $item->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group">
                                        {!! Form::textarea('test_specifications', null, [
                                            'class' => 'form-control',
                                            'id' => 'edit_test_specification',
                                            'style' => 'height: 35px;',
                                        ]) !!}
                                    </div>
                                </td>
                                <td style="display: none;">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input type="hidden" name="test_group" value="29"
                                                class="form-control groupall" required>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group">
                                        {!! Form::select('lab', $lab_roles, null, [
                                            'placeholder' => __('messages.please_select'),
                                            'class' => 'form-control select2',
                                            'id' => 'edit_lab',
                                        ]) !!}
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
        </div>
        </form>
    </div>
</div>
<style>
    /* Remove any fixed width settings */
    .select2-container {
        /* Remove or comment out fixed width */
        /* width: 300px; */
    }

    /* Instead, set width to 100% to make it responsive */
    .select2-container {
        width: 100% !important;
    }
</style>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            dropdownParent: $('#exampleModal'),
        });
    });
</script>
