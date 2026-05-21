
    <div class="modal-dialog modal-lg" role="document">
        <form action="{{ url('update-associated-test') }}" method="post">
            @csrf
        <div class="modal-content">
            <div class="modal-header ">
                <h5 class="modal-title" id="testModalLabel">Edit Associated Test</h5>
            </div>
            <div class="modal-body">
                <input type="hidden" id="test_id" name="test_id" value="{{ $testList->id }}">
                <div class="form-group row">
                    <label for="test_name" class="col-sm-12 col-form-label">Name</label>
                    <div class="col-sm-12">
                        <input type="text" id="test_name" name="name" value="{{ $testList->name }}" class="form-control">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="group" class="col-sm-12 col-form-label">Short Description:</label>
                    <div class="col-sm-12">
                        <input type="text" class="form-control" name="description" value="{{ $testList->description }}">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Update</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </form>
</div>
