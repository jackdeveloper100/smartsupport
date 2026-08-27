<style>
     .star{
        color: red;
    }
</style>
<form class="ajax-form" method="post" action="{{ route('admin/user/save') }}" enctype="multipart/form-data" id="ajax-form">
    @csrf
    <input type="hidden" name="id" value="{{ @$model->id }}">
    <input type="hidden" name="pass" value="{{ @$model->password }}">
    <input type="hidden" name="type" value="0">
    
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="basic-icon-default-fullname">First Name <span class="star">*</span></label>
                        <input type="text" class="form-control" id="first_name" placeholder="First Name" name="first_name" aria-label="first_name" value="{{ @$model->first_name }}" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="basic-icon-default-fullname">Last Name <span class="star">*</span></label>
                        <input type="text" class="form-control" id="last_name" placeholder="Last Name" name="last_name" aria-label="last_name" value="{{ @$model->last_name }}" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="basic-icon-default-fullname">Email <span class="star">*</span></label>
                        <input type="email" class="form-control" id="email" placeholder="Email" name="email" aria-label="Name" value="{{ @$model->email }}" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group ">
                        <label class="form-label" for="basic-icon-default-password">Password <span class="star">*</span></label>
                        <div class="position-relative">
                            <input type="password" class="form-control" id="password" placeholder="Password" name="password" autocomplete="new-password" />
                            <span class="input-group-text cursor-pointer toggle-password"><i class="bi bi-eye"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="basic-icon-default-fullname">Status</label>
                            
                            <select class="form-select" name="status" aria-label="Status">
                                <option value="1" <?php
                                                    if (@$model->status == 1) {
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
        <div class="col-md-6"><br>
        <?php if (!empty(@$model->image)) { ?>
        
            <img src="{{ $general->getFileUrl(@$model->image,'profile') }}" style="width:100px;height:100px" class="img-fluid" id="image"><br>
            <?php } else { ?>
            <img src="{{ $general->getNoFile() }}" class="img-fluid" style="width:100px;height:100px" id="image"><br>
        <?php  } ?>
        <div class="form-group">
            <label class="form-label">Image <span class="star">*</span></label>
                <input type="file" class="form-control " accept="image/*" name="image" onchange="previewImage(this,'#image')"><br> 
        </div>
    
        </div>
<div class="col-md-12"> 
    <div class="form-group">
        <label class="form-label" for="basic-icon-default-fullname">Permission <span class="star">*</span></label>
    </div>
    <div class="row">
        <?php foreach ((new \App\Services\PermissionService())->getPermissionListData() as $permissionList) { ?>
            <div class="col-md-3 col-sm-6 checkbox-block mb-3">
                <label class="form-check d-flex gap-2">
                    <input onchange="$(this).closest('.checkbox-block').find('.checkbox-child').prop('checked',this.checked);" 
                        class="form-check-input checkbox-parent" 
                        type="checkbox" 
                        name="permission[]" 
                        value="{{ $permissionList['key'] }}" 
                        <?php echo in_array($permissionList['key'], explode(',', @$model->permission)) ? 'checked' : ''; ?> 
                    />
                    <h4 class="fs-5" style="margin-bottom: 0%;"> {{ $permissionList['title'] }}</h4>
                </label>
                <div class="checkbox-items form-check">
                    <?php if (isset($permissionList['list']) && $permissionList['list']) { ?>
                        <?php foreach ($permissionList['list'] as $permission) { ?>
                            <label>
                                <input 
                                    class="form-check-input checkbox-child" 
                                    type="checkbox" 
                                    name="permission[]" 
                                    value="{{ $permission['key'] }}" 
                                    data-parent="{{ $permissionList['key'] }}"
                                    <?php echo in_array($permission['key'], explode(',', @$model->permission)) ? 'checked' : ''; ?> 
                                />
                                {{ $permission['title'] }}
                            </label><br />
                            
                            <?php if (isset($permission['list']) && $permission['list']) { ?>
                                <?php foreach ($permission['list'] as $subPermission) { ?>
                                    <label style="margin-left: 25px;">
                                        <input 
                                            class="form-check-input checkbox-child" 
                                            type="checkbox" 
                                            name="permission[]" 
                                            value="{{ $subPermission['key'] }}" 
                                            data-parent="{{ $permission['key'] }}"
                                            <?php echo in_array($subPermission['key'], explode(',', @$model->permission)) ? 'checked' : ''; ?> 
                                        />
                                        {{ $subPermission['title'] }}
                                    </label><br />
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
    </div>  
</div>

</div>
    <button type="submit" class="btn btn-primary">Submit</button>
    <a href="admin/users" class="btn btn-secondary" style="color: white">Back</a>
</form>

@push('scripts')
<script type="text/javascript">
    documentReady(function() {
        // Add a custom method to validate alphabetic characters
        jQuery.validator.addMethod("alphaOnly", function(value, element) {
            return this.optional(element) || /^[a-zA-Z\s]+$/.test(value);
        }, "Please enter only alphabetic characters");
        $('#ajax-form').validate({
            submitHandler: function(form) {
                app.ajaxFileForm(form);
            },
        });

        // function previewImage(file) {
        //     // Implement code to display preview of selected image
        // }
        
   $('input[type=checkbox]').change(function () {
    const checkbox = $(this);
    const isChecked = checkbox.is(':checked');
    const value = checkbox.val();
    const parentKey = checkbox.data('parent');

    // If this checkbox has children, toggle them too
    const childCheckboxes = $('input[type=checkbox][data-parent="' + value + '"]');
    childCheckboxes.prop('checked', isChecked);

    // If this checkbox has a parent, ensure parent(s) get selected
    if (isChecked && parentKey) {
        // Recursively check all parents up the chain
        let currentKey = parentKey;
        while (currentKey) {
            const parentCheckbox = $('input[type=checkbox][value="' + currentKey + '"]');
            parentCheckbox.prop('checked', true);
            currentKey = parentCheckbox.data('parent'); // climb up the tree
        }
    }

    // If parent is unchecked, also uncheck all children recursively
    if (!isChecked) {
        uncheckChildren(value);
    }
});

    // Recursively uncheck all children
    function uncheckChildren(key) {
        const children = $('input[type=checkbox][data-parent="' + key + '"]');
        children.prop('checked', false);
        children.each(function () {
            const childKey = $(this).val();
            uncheckChildren(childKey); // Recursively go deeper
        });
    }

    });
</script>


@endpush