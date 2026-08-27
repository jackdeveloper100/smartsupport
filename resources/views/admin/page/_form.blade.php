<form class="ajax-form" method="post" action="admin/page/save">
    @csrf
    <input type="hidden" name="id" value="{{ @$model->id }}">
    <div class="col-md-6">
        <div class="form-group">
            <label class="body" >Title <span class="star">*</span></label>
            <input type="text" class="form-control" id="title" placeholder="Title" name="title" aria-label="Name" value="{{ $model->title }}" />
        </div>
    </div>
    <div class="form-group">
        <label for="body">Body <span class="star">*</span></label>
        <textarea name="body" id="summernote">{!! $model->body !!}</textarea>
    </div>
    <br>
    <button type="submit" class="btn btn-primary">Submit</button>
    <a class="pjax" href="admin/pages"><button type="button" class="btn btn-secondary">Back</button></a>
</form>
@push('scripts')
<script type="text/javascript">
documentReady(function() {
    app.addCSS(['https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css']);
    app.loadScript('https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js', function() {
        initEditorFull($('#summernote'),'admin/page/save-image');
    });
    $('.ajax-form').validate({
        submitHandler: function(form) {
            var postData = new FormData(form);
            app.ajaxFileRequest($(form).attr("action"), postData);
        }
    })
});
</script>
@endpush