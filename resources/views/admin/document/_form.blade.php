<link rel="stylesheet" href="theme/extensions/filepond/filepond.css">
<form class="ajax-form" method="post" action="{{ route('admin/document/save', ['id'=>$userId]) }}" enctype="multipart/form-data" id="ajax-form">
    @csrf
    <input type="hidden" name="id" value="{{ @$model->id }}">
    <div class="row">
 
        <div class="col-md-6">
            <div class="form-group">
                <label class="body" for="title">Title <span class="star">*</span></label>
                <input type="text" class="form-control" id="title" placeholder="Enter Title" name="title" value="{{ @$model->name }}"  required/>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="body" for="approved_status">Document Type <span class="star">*</span></label>
                <select class="form-control" id="type" name="type" required>
                    <option value="0" {{ (@$model->type == '0') ? 'selected' : '' }}>Type 1</option>
                    <option value="1" {{ (@$model->type == '1') ? 'selected' : '' }}>Type 2</option>
                    <option value="2" {{ (@$model->type == '2') ? 'selected' : '' }}>Type 3</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="body" for="approved_status">Expiration Date <span class="star">*</span></label>
                <input type="date" class="form-control" id="expired_at" name="expired_at"  value="{{ @$model->expired_at }}" required>
                 
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="body" for="approved_status">Description <span class="star">*</span></label>
                <textarea  class="form-control" id="description" name="description" >{{ @$model->description }}</textarea>
                 
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="body" for="status">Status <span class="star">*</span></label>
                <select class="form-control" id="status" name="status" required>
                    <option value="0" {{ (@$model->status == '0') ? 'selected' : '' }}>Inactive</option>
                    <option value="1" {{ (@$model->status == '1') ? 'selected' : '' }}>Active</option>
                    <option value="2" {{ (@$model->status == '2') ? 'selected' : '' }}>Expiring Soon</option>
                    <option value="3" {{ (@$model->status == '3') ? 'selected' : '' }}>Expired</option>
                    <option value="4" {{ (@$model->status == '4') ? 'selected' : '' }}>Missing</option>
                </select>
            </div>
        </div>
     

        <div class="col-md-6">
            <div class="form-group">
                <label class="body" for="approved_status">Approved Status <span class="star">*</span></label>
                <select class="form-control" id="approved_status" name="approved_status" required>
                    <option value="0" {{ (@$model->approve_status == '0') ? 'selected' : '' }}>Pending</option>
                    <option value="1" {{ (@$model->approve_status == '1') ? 'selected' : '' }}>Approved</option>
                    <option value="2" {{ (@$model->approve_status == '2') ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
        </div>

           <!-- Rejected Reason Field (Initially Hidden) -->
           <div class="col-md-6" id="rejected_reason_div" style="display:{{(@$model->approve_status == '2')?'block':'none' }};">
            <div class="form-group">
                <label class="body" for="rejected_reason">Rejected Reason</label>
                <input type="text" class="form-control" id="rejected_reason" name="rejected_reason" value="{{@$model->reject_reason}}" placeholder="Enter the reason for rejection" />
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label class="body" for="file">File <span class="star">*</span></label>
               
                            <!-- File uploader with validation -->
                            <input type="file" name="file" id="file" class="with-validation-filepond"  
                                data-max-file-size="10MB" data-max-files="1">
            </div>
        </div>
    <!-- Submit Button -->
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </div>
    </div>
</form>


@push('scripts')
<script src="theme/extensions/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js"></script>
<script src="theme/extensions/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.min.js"></script>
<script src="theme/extensions/filepond-plugin-image-crop/filepond-plugin-image-crop.min.js"></script>
<script src="theme/extensions/filepond-plugin-image-exif-orientation/filepond-plugin-image-exif-orientation.min.js"></script>
<script src="theme/extensions/filepond-plugin-image-filter/filepond-plugin-image-filter.min.js"></script>
<script src="theme/extensions/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js"></script>
<script src="theme/extensions/filepond-plugin-image-resize/filepond-plugin-image-resize.min.js"></script>
<script src="theme/extensions/filepond/filepond.js"></script>
<script src="theme/static/js/pages/filepond.js"></script>
<script type="text/javascript">
    documentReady(function() {
        FilePond.create(document.querySelector(".with-validation-filepond"), {
  credits: null,
  allowImagePreview: true,
  allowMultiple: false,
  allowFileEncode: false,
  required: false,
  acceptedFileTypes: ["image/png","image/jpg",'application/pdf'],
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
                app.ajaxFileForm(form);
            },
              errorPlacement: function(error, element) {
                error.insertAfter(element.closest('.mb-3'));
            },
        });

        $('#approved_status').change(function() {
            if ($(this).val() == '2') {
                $('#rejected_reason_div').show(); 
            } else {
                $('#rejected_reason_div').hide();  
            }
        });

        if ($('#approved_status').val() == 'rejected') {
            $('#rejected_reason_div').show();
        }
    });
    
</script>
@endpush
