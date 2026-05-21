

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

    .remarkdata textarea {
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

    .remarkdata textarea:focus {
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
</style>
<div class="modal" id="remarksModal" tabindex="-1" style="overflow: scroll">
    <div class="modal-dialog">

        <div class="modal-content">

            <div class="remarkdata">
                <input type="hidden" name="task_id" id="task_id">
                <label for="">Remarks</label>
                <br>
                <textarea name="remarks" id="remarks" style="width: 100%" id="" cols="30" rows="6"></textarea>
            </div>
            <div class="modal-footer text-center" id="modal-footer">
                <button type="button" class="remarkclose btn btn-secondary">Close</button>

                <button type="button" id="save_remarks" class=" btn btn-primary">@lang('messages.save')</button>
            </div>
        </div>
    </div>
</div>

