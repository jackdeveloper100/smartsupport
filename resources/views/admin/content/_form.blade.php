    <style>
        
    .star{
        color: red;
    }
    </style>
    

<div class="card-body">
    @if ($model->type =='text')
    <div class="mb-3">
        <label class="form-label" for="basic-default-content">Content <span class="star">*</span></label>
        <input type="hidden" name="text" value="content">
        <textarea class="form-control" name="content" id="content-ckeditor">{{$model->content}}</textarea>
    </div>
    @endif
    @if($model->type =='html')
    <div class="mb-3">
        <label class="form-label" for="basic-default-content">Content</label>
        <textarea class="form-control" name="content" id="content-ckeditor">{{$model->content}}</textarea>
    </div>

    @endif
    @if ($model->type == 'image')
    @if (!empty($model->content))
    <label for="instrctions" class="cls-instrctr">{{$model->instructions}}</label>
    <img src="{{ $general->getFileUrl($model->content,'content') }}" class="img-fluid" id="image-preview"><br>
    @endif
    <div class="mb-3">
        <label class="form-label" for="basic-default-company">Image <span class="star">*</span></label>
        <input type="file" class="form-control custom-file-input" accept="image/*" name="image" onchange="previewImage(this.files[0])">
    </div>
    @endif

    @if ($model->type == 'video')
    @if (!empty($model->content))
    <video controls style="width: 100%;">
        <source src="{{ $general->getFileUrl($model->content,'content') }}" type="video/mp4">
    </video>
    @endif
    <div class="mb-3">
        <label class="form-label" for="basic-default-video">Video</label>
        <input type="file" class="form-control custom-file-input" accept="video/*" name="video">
    </div>
    @endif
</div>
<script type="text/javascript">
    documentReady(function() {
        <?php if ($model->type == 'html') { ?>
        
        // app.loadScript('https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js', function() {
        //     initCKEditorMin(document.querySelector('#content-ckeditor')).then(function(editor) {
        //         ckeditorObj = editor;
        //     });
        // });
        
            ClassicEditor
                .create(document.querySelector('#content-ckeditor'))
                .then(function(editor) {
                    ckeditorObj = editor;
                })
                .catch(error => {
                    console.error(error);
                });
        <?php } ?>
    });
</script>