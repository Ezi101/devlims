<div class="modal fade" id="editModal{{ $index }}" tabindex="-1" role="dialog"
    aria-labelledby="editModalLabel{{ $index }}" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="editModalLabel{{ $index }}">@lang('lang_v1.edit_calibration')</h3>
            </div>
            <div class="modal-body">
                <form action="{{ route('calibrator.update', ['id' => $calibrator->id]) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group" style="width: 100%;margin-top:10px;" style="width: 100%;">
                        <label for="calibrator_name">Name:</label>
                        <input type="text" class="form-control" style="width: 100%;"
                            style="width: 100%;"id="calibrator_name" name="calibrator_name"
                            value="{{ $calibrator->calibrator_name }}" required>
                    </div>

                    <div class="form-group" style="width: 100%;margin-top:10px;">
                        <label for="calibrator_cnic">CNIC:</label>
                        <input type="text" class="form-control" style="width: 100%;" id="calibrator_cnic"
                            name="calibrator_cnic" value="{{ $calibrator->calibrator_cnic }}" required>
                    </div>

                    <div class="form-group" style="width: 100%;margin-top:10px;" style="width: 100%;">
                        <label for="calibrator_mobile">Mobile:</label>
                        <input type="text" class="form-control" style="width: 100%;" id="calibrator_mobile"
                            name="calibrator_mobile" value="{{ $calibrator->calibrator_mobile }}" required>
                    </div>

                    <div class="form-group" style="width: 100%;margin-top:10px;" style="width: 100%;">
                        <label for="calibration_date">Calibration
                            Date:</label>
                        <input class="form-control datepicker" style="width: 100%;" id="calibration_date"
                            name="calibration_date" value="{{ $calibrator->calibration_date }}" required>
                    </div>

                    <div class="form-group" style="width: 100%;margin-top:10px;" style="width: 100%;">
                        <label for="calibration_frequency">Calibration
                            Frequency:</label>
                        <select class="form-control select2" style="width: 100%;" id="calibration_frequency"
                            name="calibration_frequency" required>
                            <option value="1" {{ $calibrator->calibration_frequency == '1' ? 'selected' : '' }}>
                                1 Month</option>
                            <option value="3" {{ $calibrator->calibration_frequency == '3' ? 'selected' : '' }}>
                                3 Months</option>
                            <option value="6" {{ $calibrator->calibration_frequency == '6' ? 'selected' : '' }}>
                                6 Months</option>
                            <option value="12" {{ $calibrator->calibration_frequency == '12' ? 'selected' : '' }}>
                                12 Months</option>
                        </select>
                    </div>

                    <div class="form-group" style="width: 100%;margin-top:10px;" style="width: 100%;">
                        <label for="calibration_type">Calibration
                            Type:</label>
                        <select class="form-control select2" style="width: 100%;" id="calibration_type"
                            name="calibration_type" required>
                            <option value="annual" {{ $calibrator->calibration_type == 'annual' ? 'selected' : '' }}>
                                Annual</option>
                            <option value="non-annual"
                                {{ $calibrator->calibration_type == 'non-annual' ? 'selected' : '' }}>
                                Non-Annual</option>
                        </select>
                    </div>

                    <div class="form-group" style="width: 100%;margin-top:10px;" style="width: 100%;">
                        <label for="guaranteed_date">Due
                            Date:</label>
                        <input class="form-control datepicker" id="guaranteed_date" style="width: 100%;"
                            style="width: 100%;" name="guaranteed_date" value="{{ $calibrator->guaranteed_date }}"
                            required>
                    </div>

                    <div class="form-group" style="width: 100%; margin-top: 10px;">
                        <label>Is Repaired:</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="is_repaired" id="is_repaired_yes"
                                value="1" {{ $calibrator->is_repaired == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_repaired_yes">Yes</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="is_repaired" id="is_repaired_no"
                                value="0" {{ $calibrator->is_repaired == 0 ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_repaired_no">No</label>
                        </div>
                    </div>


                    <div class="form-group" style="width: 100%;margin-top:10px;" style="width: 100%;">
                        <label for="remarks">Remarks:</label>
                        <textarea class="form-control" rows="4" id="remarks" name="remarks" style="width: 100%;resize:none;"
                            required>{{ $calibrator->remarks }}</textarea>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
