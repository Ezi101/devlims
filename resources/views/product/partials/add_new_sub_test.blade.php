<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            <h4 class="modal-title">
                @lang('Add Sub Test')
            </h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <div class="col-md-12">
                        <input type="text" class="form-control" name="name" id="sub_test_name" placeholder="Enter Name..">
                    </div>
                </div>
            </div>
            <br>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary btn-sm ladda-button saveSubTest" data-style="expand-right" data-dismiss="modal">
                    <span class="ladda-label">@lang('messages.save')</span>
                </button>
    
                 <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">
                    @lang('messages.close')
                </button>
            </div>
        </div>
    </div>
  </div>