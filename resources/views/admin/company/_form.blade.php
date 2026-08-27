<style>
    .star{
        color: red;
    }
</style>
<form class="ajax-form" method="post" action="{{ route('admin/company/save') }}" enctype="multipart/form-data" id="ajax-form">
    @csrf
    <input type="hidden" name="id" value="{{ @$model->id }}">
    <input type="hidden" name="pass" value="{{ @$model->password }}">
    <input type="hidden" name="type" value="2">
    <div class="row">
        <div class="col-md-6">
            <div class="row">
                <div class="col-lg-6 col-md-12 col-sm-6 col-12">
                    <div class="form-group">
                        <label class="form-label" for="basic-icon-default-fullname">Company Name <span class="star">*</span></label>
                        <input type="text" class="form-control" id="basic-icon-default-company_name" placeholder="Company Name" name="company_name" aria-label="company_name" value="{{ @$model->company_name }}" />
                    </div>
                </div>
                
                <!--<div class="col-lg-6 col-md-12 col-sm-6 col-12">-->
                    <!--    <div class="form-group">-->
                <!--        <label class="form-label" for="basic-icon-default-fullname">Last Name </label>-->
                <!--        <input type="text" class="form-control" id="basic-icon-default-last-name" placeholder="Last Name" name="last_name" aria-label="Name" value="{{ @$model->last_name }}" />-->
                <!--    </div>-->
                <!--</div>-->
                
                <div class="col-lg-6 col-md-12 col-sm-6 col-12">
                    <div class="form-group">
                        <label class="form-label" for="basic-icon-default-fullname">Email <span class="star">*</span></label>
                        <input type="text" class="form-control" id="basic-icon-default-fullname" placeholder="Email" name="email" aria-label="Name" value="{{ @$model->email }}" />
                    </div>
                </div>
                <div class="col-lg-6 col-md-12 col-sm-6 col-12">
                    <div class="form-group">
                        <label class="form-label" for="basic-icon-default-password">Password <span class="star">*</span></label>
                        <div class="position-relative">
                            <input type="password" class="form-control" id="basic-icon-default-password" placeholder="Password" name="password" autocomplete="new-password" />
                            <span class="input-group-text cursor-pointer toggle-password"><i class="bi bi-eye"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12 col-sm-6 col-12">
                    <div class="form-group">
                        <label class="form-label" for="basic-icon-default-fullname">Company Status <span class="star">*</span></label>
                        <div class="input-group input-group-merge">

                            <select class="form-select" name="status" aria-label="Status">
                                <option value="1" <?php if (@$model->status == 1) {
                                                        echo 'selected';
                                                    } ?>>Active</option>
                                <option value="0" <?php if (isset($model->status) && @$model->status == 0) {
                                    echo 'selected';
                                } ?>>Inactive</option>
                            </select>

                        </div>
                    </div>
                </div>
               <div class="col-lg-6 col-md-12 col-sm-6 col-12">
                    <label class="form-check d-flex gap-2 align-items-center">
                        <input 
                            class="form-check-input" 
                            type="checkbox" 
                            name="unlimited_conractors" 
                            value="1"{{ @$model->unlimited_conractors ? 'checked' : ''}}>
                        Enjoy Unlimited Contractors For Free
                    </label>
                </div>
                <div class="col-lg-6 col-md-12 col-sm-6 col-12">
                    <div class="form-group">
                        <label class="form-label" for="basic-icon-default-fullname">Contractor Status <span class="star">*</span></label>
                        <div class="input-group input-group-merge">

                            <select class="form-select" name="contractor_status" aria-label="contractor_status">
                                <option value="1" <?php if (@$model->contractor_status == 1) {
                                                        echo 'selected';
                                                    } ?>>Active</option>
                                <option value="0" <?php if (isset($model->contractor_status) && @$model->contractor_status == 0) {
                                    echo 'selected';
                                } ?>>Inactive</option>
                            </select>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mt-4 ">
            <?php if (!empty($model->image)) { ?>
                <div class="img-preview mb-4">
                <img src="{{ $general->getFileUrl($model->image,'profile') }}" style="width:100px;height:100px" class="img-fluid" id="image">
                </div>
            <?php } else { ?>
                 <div class="img-preview mb-4">
                <img src="{{ $general->getNoFile() }}" class="img-fluid mt-2" style="width:100px;height:100px" id="image">
                </div>
            <?php  } ?>
            <div class="form-group">
                <label clas="form-label">Image </label>
                <div class="mb-3">
                    <input type="file" class="form-control" accept="image/*" name="image" onchange="previewImage(this,'#image')">
                </div>
            </div>
        </div>

    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
  <a href="admin/company" class="btn btn-dark" style="color: white;">Back</a>
</form>

@push('scripts')
<script type="text/javascript">
    documentReady(function() {
        $('#ajax-form').validate({
            submitHandler: function(form) {
                app.ajaxFileForm(form);
            }
        })
    });
</script>

@endpush