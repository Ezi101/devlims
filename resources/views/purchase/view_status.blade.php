<div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      @include('purchase.partials.view_details')
    </div>
  </div>
  
  <script type="text/javascript">
      $(document).ready(function(){
          var element = $('div.modal-xl');
          __currency_convert_recursively(element);
      });
  </script>