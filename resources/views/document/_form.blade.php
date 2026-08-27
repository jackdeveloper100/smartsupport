<link rel="stylesheet" href="theme/extensions/filepond/filepond.css">

<?php $today = \Carbon\Carbon::now()->toDateString(); ?>
<form class="ajax-form" method="post" action="{{ route('contractor/document/save') }}" onsubmit="event.preventDefault();app.ajaxFileForm(this)"  enctype="multipart/form-data" id="ajax-form">
    @csrf
    <div class="modal-body" >
        <input type="hidden" name="id" value="{{ @$model->id }}">
        <input type="hidden" name="type" value="{{ @$type }}">
            <div class="col-md-12">
                <div class="form-group">
                    <label class="body" for="title">Document Type <span class="star">*</span></label>
                    <input type="text" class="form-control" id="title" placeholder="Enter Title" name="title" value="{{$name}}"  readonly=""/>
                </div>
            </div>

            @if($type != 2)
            <div class="col-md-12">
                <div class="form-group">    
                    <label class="body" for="approved_status">Expiration Date </label>
                    <input type="date" class="form-control" id="expired_at" name="expired_at"  value="{{ @$model->expired_at }}" min="{{ $today }}">
                </div>
            @endif
        
        <div class="col-md-12">
            <div class="form-group">
                <label class="form-label">Description </label>
                <div class="input-group input-group-merge">
                    <textarea class="form-control h-50" rows="3" name="description" placeholder="">{{@$model->description}}</textarea>
                </div>
            </div>

        <div class="col-md-12">
            <div class="form-group">
                <label class="body" for="file">File <span class="star">*</span></label>
                    <input type="file" name="filepond" id="file" class="with-validation-filepond"  
                        data-max-file-size="10MB" data-max-files="1">
            </div>
        </div>
        
        <div style="text-align:center">

    <!-- Submit Button -->
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                <span class="d-sm-block">Close</span>
            </button>
            <button type="submit" class="btn btn-primary ms-1" >
                <span class="d-sm-block">Submit</span>
            </button>
        </div>
</form>



<script type="text/javascript">
    documentReady(function() {
        FilePond.create(document.querySelector(".with-validation-filepond"), {
        credits: null,
        allowImagePreview: true,
        allowMultiple: false,
        allowFileEncode: false,
        required: false,
        acceptedFileTypes: ["image/png","image/jpg","image/jpeg",'application/pdf'],
        fileValidateTypeDetectType: (source, type) =>
            new Promise((resolve, reject) => {
            resolve(type)
            }),
        storeAsFile: true,
        })

        jQuery.validator.addMethod("alphaOnly", function(value, element) {
            return this.optional(element) || /^[a-zA-Z\s]+$/.test(value);
        }, "Please enter only alphabetic characters");
        
         $('#ajax-form').validate({
            rules: {
                title: {
                    required: true,
                },
                keyword: {
                    required: true,
                },

            },
            messages: {
                title: {
                    required: "Please enter the title",
                },
                keyword: {
                    required: "Please enter the keyword"
                },  
            },
            submitHandler: function(form) {
                event.preventDefault();
                app.ajaxFileForm(form);
            },  
            errorPlacement: function(error, element) {  
                error.insertAfter(element.closest('.mb-3'));
            },
        });
    });    
</script>

<script src="theme/extensions/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js"></script>
<script src="theme/extensions/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.min.js"></script>
<script src="theme/extensions/filepond-plugin-image-crop/filepond-plugin-image-crop.min.js"></script>
<script src="theme/extensions/filepond-plugin-image-exif-orientation/filepond-plugin-image-exif-orientation.min.js"></script>
<script src="theme/extensions/filepond-plugin-image-filter/filepond-plugin-image-filter.min.js"></script>
<script src="theme/extensions/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js"></script>
<script src="theme/extensions/filepond-plugin-image-resize/filepond-plugin-image-resize.min.js"></script>
<script src="theme/extensions/filepond/filepond.js"></script>
<script src="theme/static/js/pages/filepond.js"></script>


