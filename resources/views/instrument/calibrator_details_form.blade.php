<div class="row mt-4 justify-content-center">
    <div class="col-md-12">
        <div class="card">

            <div class="card-header">
                <h3 class="card-title ">@lang('lang_v1.calibrator_details')</h3>
            </div>

            <div class="card-body mt-10">
                <form action="{{ route('calibration.store') }}" method="POST" id="calibrationForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="calibrator_name">Name:</label>
                                <input type="text" name="calibrator_name" id="calibrator_name" class="form-control"
                                    required placeholder="Your Name">
                            </div>
                            <div class="form-group">
                                <label for="calibrator_mobile">Mobile
                                    Number:</label>
                                <input type="text" name="calibrator_mobile" id="calibrator_mobile"
                                    class="form-control" placeholder="03xxxxxxxxx" required>

                            </div>
                            <div class="form-group">
                                <label for="calibrator_cnic">CNIC:</label>
                                <input type="text" name="calibrator_cnic" id="calibrator_cnic" class="form-control"
                                    required placeholder="CNIC without dashes">
                            </div>
                            <div class="form-group">
                                <label for="calibration_date">Calibration Date:</label>
                                <input name="calibration_date" id="calibration_date" class="form-control datepicker"
                                    placeholder="Please Select" required>
                            </div>
                            <div class="form-group">
                                <label>Is Repaired:</label><br>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_repaired"
                                        id="is_repaired_yes" value="1" checked>
                                    <label class="form-check-label" for="is_repaired_yes">Yes</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_repaired"
                                        id="is_repaired_no" value="0">
                                    <label class="form-check-label" for="is_repaired_no">No</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group" id="typeGroup">
                                <label for="calibration_type">Calibration Type:</label>
                                <select name="calibration_type" id="calibration_type" class="form-control select2"
                                    style="width: 100%;">
                                    <option value="">@lang('messages.please_select')</option>
                                    <option value="annual">Annual</option>
                                    <option value="non-annual">Non-Annual</option>
                                </select>
                            </div>
                            <div class="form-group" id="guaranteeGroup">
                                <label for="guaranteed_date">Due Date:</label>
                                <input name="guaranteed_date" id="guaranteed_date" class="form-control datepicker"
                                    required>
                            </div>
                            <div class="form-group" id="frequencyGroup">
                                <label for="calibration_frequency">Calibration
                                    Frequency:</label>
                                <select name="calibration_frequency" id="calibration_frequency"
                                    class="form-control select2" style="width: 100%;">
                                    <option value="">@lang('messages.please_select')</option>
                                    <option value="1">Every month</option>
                                    <option value="3">Every 3 months</option>
                                    <option value="6">Every 6 months</option>
                                    <option value="12">Every year</option>
                                </select>
                            </div>
                            <div class="form-group" id="remarksGroup" style="display: block;">
                                <label for="remarks">Remarks:</label>
                                <textarea name="remarks" id="remarks" class="form-control" rows="5" style="resize: none;" required></textarea>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="device_id" value="{{ $selectedDevice->id }}">
                    <div>
                        <a href="{{ route('instrument.callibration') }}" class="btn btn-secondary pull-right">Cancel</a>
                        <button type="submit" class="btn btn-primary pull-right">Save </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
