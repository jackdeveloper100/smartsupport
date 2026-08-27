<style>
     .star{
        color: red;
    }
</style>

<form class="ajax-form" method="post" action="admin/email-template/save" id="ajax-form">
    @csrf
    <input type="hidden" name="id" value="{{ @$model->id }}">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="body" >Title <span class="star">*</span></label>
                <input type="text" class="form-control" id="title" placeholder="Title" name="title" aria-label="Name" value="{{ $model->title }}" />
            </div>
        </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="body" >Subject <span class="star">*</span></label>
                    <input type="text" class="form-control" id="subject" placeholder="subject" name="subject" aria-label="subject" value="{{ $model->subject }}" />
                </div>
            </div>
     </div>
    <div class="form-group">
        <label for="body">Body <span class="star">*</span></label>
        <textarea name="body"  id="summernote">{!! $model->body !!}</textarea>
    </div>
    <div class="mb-3">
            <label class="body mt-2" ><b> Parameters </b></label>
            <div>
                {{$model->params}}
                <p>These parameters are dynamic. Use <code>&#123;&#123; Parameters &#125;&#125;</code>  to utilize them.</p>
            </div>
        </div>
    <br>
    <button type="submit" class="btn btn-primary">Submit</button>
    <a class="pjax" href="admin/email-template"><button type="button" class="btn btn-secondary">Back</button></a>
</form>

@push('scripts')
<script type="text/javascript">
documentReady(function() {
    app.addCSS(['https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css']);
    app.loadScript('https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js', function() {
        initEditorFull($('#summernote'),'admin/email-template/save-image');
    });
    // $('.ajax-form').validate({
    //     submitHandler: function(form) {
    //         var postData = new FormData(form);
    //         app.ajaxFileRequest($(form).attr("action"), postData);
    //     }
    // });
    
    jQuery.validator.addMethod("alphaOnly", function(value, element) {
            return this.optional(element) || /^[a-zA-Z\s]+$/.test(value);
        }, "Please enter only alphabetic characters");
        $('#ajax-form').validate({
           

            submitHandler: function(form) {
                app.ajaxFileForm(form);
            },
            
          
        });
});
</script>

@endpush