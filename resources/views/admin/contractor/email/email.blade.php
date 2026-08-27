<style>
   @media (max-width: 576px) {

             .h2-center,
             td.h2-center 
             h2, 
             h2 span
             h3 {
                font-size: 18px !important;
            } 
    }
</style>
    <div class="modal-header ">
            <h5 class="modal-title" id="myModalLabel1">Reminder Mail</h5>
            <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
    </div>
    <form class="ajax-form" method="post" action="{{ route('admin/contractor/send-remindermail-save') }}"  enctype="multipart/form-data" id="ajax-form">
    @csrf
    <div class="modal-body" >
        <input type="hidden" name="id" value="{{ @$ids }}">
            <div class="col-md-12">
                <div class="form-group">
                    <label class="body" for="title">Subject <span class="star">*</span></label>
                    <input type="text" class="form-control" id="subject" placeholder="Enter Subject" name="subject" value="" >
                </div>
            </div>

        <div class="col-md-12">
            <div class="form-group">
                <label class="form-label">Message <span class="star">*</span></label>
                <div class="input-group input-group-merge">
                    <textarea class="form-control h-50" rows="3" name="message" placeholder=""></textarea>
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