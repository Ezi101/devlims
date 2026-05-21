   <!-- Add Signature Modal -->
   <div class="modal fade" id="addSignatureModal" tabindex="-1" role="dialog" aria-labelledby="addSignatureModalLabel"
       aria-hidden="true">
       <div class="modal-dialog" role="document">
           <div class="modal-content">
               <div class="modal-header">
                   <h3 class="modal-title" id="addSignatureModalLabel">@lang('lang_v1.add_signature')</h3>

               </div>
               <div class="modal-body">
                   <form method="POST" action="{{ route('signatures.store') }}">
                       @csrf


                       <div class="form-group">
                           <label for="user_id">@lang('method.user')</label>
                           <select name="user_id" id="user_id" class="form-control select2" style="width: 100%;"
                               required>
                               <option value="">@lang('messages.please_select')</option>
                               @foreach ($users as $user)
                                   <option value="{{ $user->id }}" data-employee="{{ $user->id }}"
                                       data-designation="{{ $user->getRoleNameAttribute() }}">
                                       {{ $user->userFullName }}
                                   </option>
                               @endforeach

                           </select>

                       </div>

                       <div class="form-group" hidden>
                           <label for="name">@lang('method.name')</label>
                           <input type="text" name="name" id="name" class="form-control">
                       </div>

                       <div class="form-group" hidden>
                           <label for="employee_id">@lang('method.emp_id')</label>
                           <input type="text" name="employee_id" id="employee_id" class="form-control" readonly>
                       </div>

                       <div class="form-group">
                           <label for="designation">@lang('method.role')</label>
                           <input type="text" name="designation" id="designation" class="form-control" readonly>
                       </div>

                       <div class="modal-footer">
                           <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                           <button type="button" class="btn btn-default"
                               data-dismiss="modal">@lang('messages.close')</button>
                       </div>
                   </form>
               </div>
           </div>
       </div>
   </div>
