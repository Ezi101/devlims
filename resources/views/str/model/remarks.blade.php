<style>
    #remarksModal .modal-dialog {
        max-width: 500px;
        margin: auto;
    }

    #remarksModal .modal-content {
        border-radius: 8px;
        padding: 20px;
        background-color: #f9f9f9;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    #remarksModal .modal-header {
        border-bottom: none;
        padding-bottom: 0;
    }

    #remarksModal {
        width: 100%;
    }

    .remarkdata label {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 10px;
        display: inline-block;
        color: #333;
    }

    .remarkdata textarea,
    .remarkdata select {
        width: 100%;
        border-radius: 6px;
        border: 1px solid #ced4da;
        padding: 10px;
        font-size: 16px;
        font-family: 'Arial', sans-serif;
        outline: none;
        resize: none;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .remarkdata textarea:focus,
    .remarkdata select:focus {
        border-color: #80bdff;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.25);
    }

    #modal-footer {
        padding-top: 15px;
        border-top: none;
        text-align: right;
    }

    #modal-footer .btn {
        padding: 10px 20px;
        font-size: 16px;
        border-radius: 4px;
        margin-left: 10px;
    }

    #remarksModal .btn-secondary {
        background-color: #6c757d;
        color: white;
        border: none;
    }

    #remarksModal .btn-primary {
        background-color: #007bff;
        color: white;
        border: none;
    }

    #remarksModal .btn-primary:hover {
        background-color: #0056b3;
    }

    .modal-header h5 {
        font-size: 22px;
        font-weight: 700;
        color: #333;
    }
</style>

<div class="modal" id="remarksModal" tabindex="-1" style="overflow: scroll">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ action([\App\Http\Controllers\STRController::class, 'str_approval_store']) }}" method="POST"
                id="reject_remarks_form">
                @csrf

                <div class="modal-header">
                    <h5>Reject STR</h5>
                </div>

                <div class="remarkdata">
                    <input type="hidden" name="status" value="rejected">
                    <input type="hidden" name="str_no" value="{{ @$strs->str_no }}">

                    <!-- Dropdown Field -->
                    <label for="type">Type</label>
                    <select name="type" id="type" class="select2" required>
                        <option value="observation" selected>Observation</option>
                        <option value="failed">Failed</option>
                        <option value="duplicate">Duplicate</option>
                    </select>

                    <!-- Remarks Field -->
                    <label for="remarks" style="margin-top: 15px;">Remarks</label>
                    <textarea name="remarks" id="remarks" cols="30" rows="6" placeholder="Add your remarks..." maxlength="100"></textarea>
                </div>
                <div class="modal-footer" id="modal-footer">
                    <button type="button" class="remarkclose btn btn-secondary">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Observation + Amendment Modal --}}
<div class="modal fade" id="observationModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 8px; padding: 20px; background-color: #f9f9f9;">
            <div class="modal-header" style="border-bottom: none;">
                <h5 style="font-size: 22px; font-weight: 700; color: #333;">Add Observation</h5>
            </div>
            <div class="modal-body">
                {{-- Observation --}}
                <div class="form-group">
                    <label style="font-size: 18px; font-weight: 600; color: #333;">Observation</label>
                    <textarea class="form-control" id="observation" rows="4" placeholder="Enter observation..."
                        style="border-radius: 6px; font-size: 16px; resize: none;"></textarea>
                </div>

                {{-- Amendment Checkbox --}}
                <div class="form-group mt-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="amendmentCheckbox">
                        <label class="form-check-label" for="amendmentCheckbox"
                            style="font-size: 16px; font-weight: 600; color: #333;">
                            Add Amendment to Report
                        </label>
                    </div>
                </div>

                {{-- Amendment Textarea - hidden by default --}}
                <div class="form-group" id="amendmentSection" style="display: none;">
                    <label style="font-size: 18px; font-weight: 600; color: #333;">Amendment Details</label>
                    <textarea class="form-control" id="amendment" rows="3" placeholder="Enter amendment details..."
                        style="border-radius: 6px; font-size: 16px; resize: none;"></textarea>
                </div>
            </div>
            <div class="modal-footer" style="border-top: none; text-align: right;">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"
                    style="background-color: #6c757d; color: white; border: none; padding: 10px 20px; font-size: 16px;">
                    Close
                </button>
                <button type="button" class="btn btn-primary" id="saveRemark"
                    style="background-color: #007bff; color: white; border: none; padding: 10px 20px; font-size: 16px;">
                    Save
                </button>
            </div>
        </div>
    </div>
</div>
