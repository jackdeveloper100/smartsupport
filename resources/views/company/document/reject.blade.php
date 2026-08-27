    
 <form class="ajax-form" method="post" action="{{ route('company/document/change_status', ['id' => $id, 'type' => 'reject']) }}"   id="ajax-form">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title" id="myModalLabel1">Reject</h5>
        <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
    </div>
 

<div class="modal-body">
    <div class="col-md-12">
         <div class="form-group">   
            <label for="reason" class="body">Reason <span class="star">*</span></label>
            <input type="text" id="reason" name="rejected_reason" class="form-control" placeholder="Please Enter a Reject Reason">
        </div>
    </div>
    
    <div class="modal-footer">
            <button type="button" class="btn" data-bs-dismiss="modal">
                <span class="d-sm-block">Close</span>
            </button>
            <button type="submit" class="btn btn-primary ms-1" >
                <span class="d-sm-block">Submit</span>
            </button>
        </div>
</div>

</form>

    <script type="text/javascript">
        documentReady(function() {
            $('.ajax-form').validate({
                submitHandler: function(form) {
                    app.ajaxForm(form);
                }
            })
        });
    </script>
