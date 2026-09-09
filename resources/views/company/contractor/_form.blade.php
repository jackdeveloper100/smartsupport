<style>
    .star{
        color: red;
    }
    #comment{
        resize : none;
    }
</style>

<form class="ajax-form" method="post" action="{{ route('company/contractor/save') }}" enctype="multipart/form-data" id="ajax-form">
    @csrf
    <input type="hidden" name="id" value="{{ @$model->id }}">
    <input type="hidden" name="pass" value="{{ @$model->password }}">
    <input type="hidden" name="type" value="1">
    <input type="hidden" name="company_approved_status" value="{{ isset($model->company_approved_status) ? $model->company_approved_status : 1 }}">
    <input type="hidden" name="company_name" value="{{ auth()->user()->getCompanyOwnerId() }}">
    
    <div class="row">
        <div class="col-md-6">
            <div class="row">
                <div class="col-lg-6 col-md-12 col-sm-6 col-12">
                    <div class="form-group">
                        <label class="form-label" for="basic-icon-default-fullname">First Name <span class="star">*</span></label>
                        <input type="text" class="form-control" id="basic-icon-default-first-name" placeholder="First Name" name="first_name" aria-label="first_name" value="{{ @$model->first_name }}" />
                    </div>
                </div>
                <div class="col-lg-6 col-md-12 col-sm-6 col-12">
                    <div class="form-group">
                        <label class="form-label" for="basic-icon-default-fullname">Last Name </label>
                        <input type="text" class="form-control" id="basic-icon-default-last-name" placeholder="Last Name" name="last_name" aria-label="Name" value="{{ @$model->last_name }}" />
                    </div>
                </div>
                
                <div class="col-lg-6 col-md-12 col-sm-6 col-12">
                    <div class="form-group">
                        <label class="form-label" for="basic-icon-default-fullname">Business Name </label>   
                        <input type="text" class="form-control" id="basic-icon-default-business-name" placeholder="Business Name" name="business_name" aria-label="Name" value="{{ @$model->business_name }}" />
                    </div>
                </div>
                
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
                        <label class="form-label" for="basic-icon-default-fullname">Status <span class="star">*</span></label>
                        <div class="input-group input-group-merge">

                            <select class="form-select" name="status" aria-label="Status">
                               <option value="1" <?php if (!isset($model->status) || @$model->status == 1) {
                                        echo 'selected';
                                    } ?>>Active</option>
                                <option value="0" <?php if (isset($model->status) && @$model->status == 0) {
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
        
        <!--<div class="col-md-6">-->
        <!--    <div class="form-group">-->
        <!--        <label class="form-label" for="basic-icon-default-fullname">Country</label>-->
        <!--        <div class="input-group input-group-merge">-->

        <!--            <input type="text" class="form-control" id="basic-icon-default-fullname" placeholder="Country" name="country" aria-label="Country" value="{{ @$model->country }}" />-->

        <!--        </div>-->
        <!--    </div>-->
        <!--</div>-->
        
        <div  class="col-md-6">
            <div class="form-group">
                <label class="form-label">Comment</label>
                <div class="input-group input-group-merge">
                    <textarea class="form-control h-50 " rows="3" name="comment" placeholder="" id="comment">{{@$model->comment}}</textarea>
                </div>
            </div>
        </div>  
        

    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
  <a href="company/contractors" class="btn btn-secondary" style="color: white;">Back</a>
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