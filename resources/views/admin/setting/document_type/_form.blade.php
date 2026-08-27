<form class="ajax-form" method="post" action="{{ route('admin/setting/document_type/save')   }}" enctype="multipart/form-data" id="ajax-form">
    @csrf
    <input type="hidden" name="id" value="{{ @$model->id }}">
    <div class="row">
 
        <div class="col-md-6">
            <div class="form-group">
                <label class="body" for="title">Document Name <span class="star">*</span></label>
                <input type="text" class="form-control" id="title" placeholder="Document Name" name="name" value="{{ @$model->name }}"  />
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="body" for="approved_status">Document Type <span class="star">*</span></label>
                <select class="form-select" id="type" name="document_type" required>
                    <option value="1" {{ (@$model->type == '1') ? 'selected' : '' }}>Required</option>
                    <option value="0" {{ (@$model->type == '0') ? 'selected' : '' }}>Optional</option>
                </select>
            </div>
        </div>
       

    <!-- Submit Button -->
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Submit</button>
                <a href="admin/setting/document_type" class="btn btn-secondary" style="color: white">Back</a>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script type"text/javascript">
    documentReady(function() {
            $('.ajax-form').validate({
                submitHandler: function(form) {
                    app.ajaxForm(form);
                }
            })
        });

</script>
@endpush