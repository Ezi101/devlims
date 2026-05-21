<style>
    #approveremarksModal .modal-dialog {
        max-width: 500px;
        margin: auto;
    }

    #approveremarksModal .modal-content {
        border-radius: 8px;
        padding: 20px;
        background-color: #f9f9f9;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    #approveremarksModal .modal-header {
        border-bottom: none;
        padding-bottom: 0;
    }

    #approveremarksModal {
        width: 100%;
    }

    .approveremarkdata label {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 10px;
        display: inline-block;
        color: #333;
    }

    .approveremarkdata textarea {
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

    .approveremarkdata textarea:focus {
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

    #approveremarksModal .btn-secondary {
        background-color: #6c757d;
        color: white;
        border: none;
    }

    #approveremarksModal .btn-primary {
        background-color: #007bff;
        color: white;
        border: none;
    }

    #approveremarksModal .btn-primary:hover {
        background-color: #0056b3;
    }
</style>


<div class="modal" id="approveremarksModal" tabindex="-1" style="overflow: scroll">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ action([\App\Http\Controllers\STRController::class, 'approve_str_approval_store']) }}" method="POST"
                id="reject_approveremarks_form">
                @csrf
                <div class="approveremarkdata">
                    <input type="hidden" name="status" value="approved">
                    <input type="hidden" name="str_no" value="{{ @$strs->str_no }}">

                    <label for="approveremarks">Remarks</label>
                    <br>
                    <textarea name="approveremarks_description" id="approveremarks" cols="30" rows="6" placeholder="Add your approval remarks..."
                        maxlength="100"></textarea>

                </div>
                <div class="modal-footer" id="modal-footer">
                    <button type="button" class="approveremarkclose btn btn-secondary">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
