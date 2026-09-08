<style>
    .star {
        color: red;
    }
</style>

<?php
    $general = new \App\Helpers\General();
    $isEdit = !empty($model->id);
?>

<form class="ajax-form" method="post" action="{{ route('company/team-member/save') }}" enctype="multipart/form-data" id="ajax-form">
    @csrf
    <input type="hidden" name="id" value="{{ @$model->id }}">
    <input type="hidden" name="pass" value="{{ @$model->password }}">
    <input type="hidden" name="type" value="2">
    
    <div class="row">
        <!-- User Details Section -->
        <div class="col-md-12">
            <div class="row">
                @if(!$isEdit)
                {{-- Create Mode: First Name, Last Name, Email in a single row with col-md-4 --}}
                <div class="col-md-4 col-12 mb-3">
                    <div class="form-group">
                        <label class="form-label" for="first_name">First Name <span class="star">*</span></label>
                        <input type="text" class="form-control" id="first_name" placeholder="First Name" name="first_name" value="{{ @$model->first_name }}" />
                    </div>
                </div>
                <div class="col-md-4 col-12 mb-3">
                    <div class="form-group">
                        <label class="form-label" for="last_name">Last Name </label>
                        <input type="text" class="form-control" id="last_name" placeholder="Last Name" name="last_name" value="{{ @$model->last_name }}" />
                    </div>
                </div>
                <div class="col-md-4 col-12 mb-3">
                    <div class="form-group">
                        <label class="form-label" for="email">Email <span class="star">*</span></label>
                        <input type="text" class="form-control" id="email" placeholder="Email" name="email" value="{{ @$model->email }}" />
                    </div>
                </div>
                @else
                {{-- Update Mode: First Name, Last Name, Email, Status with col-md-6 --}}
                <div class="col-md-6 col-12 mb-3">
                    <div class="form-group">
                        <label class="form-label" for="first_name">First Name <span class="star">*</span></label>
                        <input type="text" class="form-control" id="first_name" placeholder="First Name" name="first_name" value="{{ @$model->first_name }}" />
                    </div>
                </div>
                <div class="col-md-6 col-12 mb-3">
                    <div class="form-group">
                        <label class="form-label" for="last_name">Last Name </label>
                        <input type="text" class="form-control" id="last_name" placeholder="Last Name" name="last_name" value="{{ @$model->last_name }}" />
                    </div>
                </div>
                <div class="col-md-6 col-12 mb-3">
                    <div class="form-group">
                        <label class="form-label" for="email">Email <span class="star">*</span></label>
                        <input type="text" class="form-control" id="email" placeholder="Email" name="email" value="{{ @$model->email }}" />
                    </div>
                </div>
                <div class="col-md-6 col-12 mb-3">
                    <div class="form-group">
                        <label class="form-label" for="status">Status <span class="star">*</span></label>
                        <select class="form-select" name="status" id="status" aria-label="Status">
                            <option value="1" <?php if (!isset($model->status) || @$model->status == 1) { echo 'selected'; } ?>>Active</option>
                            <option value="0" <?php if (isset($model->status) && @$model->status == 0) { echo 'selected'; } ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                @endif
            </div>
        </div>

        @if($isEdit)
        <!-- Profile Image Section (Update Mode Only) -->
        <div class="col-md-6 col-12 mb-4">
            <?php if (!empty($model->image)) { ?>
                <div class="img-preview mb-2">
                    <img src="{{ $general->getFileUrl($model->image,'profile') }}" style="width:90px; height:90px; object-fit:cover; border-radius:8px;" class="img-fluid" id="image">
                </div>
            <?php } else { ?>
                <div class="img-preview mb-2">
                    <img src="{{ $general->getNoFile() }}" class="img-fluid" style="width:90px; height:90px; object-fit:cover; border-radius:8px;" id="image">
                </div>
            <?php } ?>
            <div class="form-group">
                <label class="form-label">Profile Image </label>
                <input type="file" class="form-control" accept="image/*" name="image" onchange="previewImage(this,'#image')">
            </div>
        </div>
        @endif

<?php
    $sessionUser = auth()->user();
    $companyOwnerId = $sessionUser->getCompanyOwnerId();
    $isCompanyOwner = ($sessionUser->id == $companyOwnerId);
    $isSelfEdit = (isset($model->id) && $model->id == $sessionUser->id);
    $disablePermissions = ($isSelfEdit && !$isCompanyOwner);
    $disabledAttr = $disablePermissions ? 'disabled' : '';
?>

        <!-- Permission Section (Matching Admin Form Layout without card boxes) -->
        <div class="col-md-12 mt-2"> 
            <div class="form-group mb-3">
                <label class="form-label fw-bold fs-5" for="permission">
                    Permission <span class="star">*</span>
                    @if($disablePermissions)
                    <span class="badge bg-light-info text-info fs-7 ms-2"><i class="bi bi-lock-fill me-1"></i> Read Only (You cannot edit your own permissions)</span>
                    @endif
                </label>
            </div>
            <div class="row">
                <?php foreach ((new \App\Services\CompanyPermissionService())->getPermissionListData() as $permissionList) { 
                    $userPermissions = explode(',', @$model->permission);
                    $isParentChecked = in_array($permissionList['key'], $userPermissions);
                ?>
                    <div class="col-md-3 col-sm-6 checkbox-block mb-3">
                        <label class="form-check d-flex gap-2 align-items-center mb-2">
                            <input 
                                class="form-check-input checkbox-parent" 
                                type="checkbox" 
                                name="permission[]" 
                                value="{{ $permissionList['key'] }}" 
                                <?php echo $isParentChecked ? 'checked' : ''; ?> 
                                {{ $disabledAttr }}
                            />
                            <h4 class="fs-5 mb-0 fw-bold">{{ $permissionList['title'] }}</h4>
                        </label>
                        <div class="checkbox-items form-check">
                            <?php if (isset($permissionList['list']) && $permissionList['list']) { ?>
                                <?php foreach ($permissionList['list'] as $permission) { 
                                    $isChildChecked = in_array($permission['key'], $userPermissions);
                                ?>
                                    <label class="d-block mb-1">
                                        <input 
                                            class="form-check-input checkbox-child" 
                                            type="checkbox" 
                                            name="permission[]" 
                                            value="{{ $permission['key'] }}" 
                                            data-parent="{{ $permissionList['key'] }}"
                                            <?php echo $isChildChecked ? 'checked' : ''; ?>
                                            {{ $disabledAttr }}
                                        />
                                        <span class="fs-7 ms-1">{{ $permission['title'] }}</span>
                                    </label>
                                <?php } ?>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary me-2">Submit</button>
        <a href="company/team-members" class="btn btn-secondary pjax" style="color: white;">Back</a>
    </div>
</form>

@push('scripts')
<script type="text/javascript">
    documentReady(function() {
        $('#ajax-form').validate({
            submitHandler: function(form) {
                app.ajaxFileForm(form);
            }
        });

        // Exact admin-side checkbox sync behavior
        $('input[type=checkbox]').change(function () {
            const checkbox = $(this);
            const isChecked = checkbox.is(':checked');
            const value = checkbox.val();
            const parentKey = checkbox.data('parent');

            // If this checkbox has children, toggle them too
            const childCheckboxes = $('input[type=checkbox][data-parent="' + value + '"]');
            childCheckboxes.prop('checked', isChecked);

            // If this checkbox has a parent, ensure parent(s) get selected when child is checked
            if (isChecked && parentKey) {
                let currentKey = parentKey;
                while (currentKey) {
                    const parentCheckbox = $('input[type=checkbox][value="' + currentKey + '"]');
                    parentCheckbox.prop('checked', true);
                    currentKey = parentCheckbox.data('parent');
                }
            }

            // If parent is unchecked, also uncheck all children recursively
            if (!isChecked) {
                uncheckChildren(value);
            }
        });

        function uncheckChildren(key) {
            const children = $('input[type=checkbox][data-parent="' + key + '"]');
            children.prop('checked', false);
            children.each(function () {
                const childKey = $(this).val();
                uncheckChildren(childKey);
            });
        }

        // On page load, auto-check parent if any of its children are checked
        $('.checkbox-child:checked').each(function() {
            const parentKey = $(this).data('parent');
            if (parentKey) {
                $('input[type=checkbox][value="' + parentKey + '"]').prop('checked', true);
            }
        });
    });
</script>
@endpush
