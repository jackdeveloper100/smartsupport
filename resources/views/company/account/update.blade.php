@extends('company.layouts.main')
@section('title')
Profile
@endsection
@section('content')
<style>
    .star{
        color: red;
    }
    .logoIcon{
        display: flex;
        flex-direction: column;
        text-align: left;
    }
    fa-camera {
        top: 0px;
        right: -1px;
        left:75px;
    }
    .logoGap{
        gap:30px;
    }
    .camIcon{
        top: 0px;
        right: -1px;
        left: 75px;
    }
    @media only screen and (max-width:435px){
        .col-12-xs{
            width:100% !important;
        }
    }
</style>

<?php 
    $isSubUser = ($model->type == 2 && !empty($model->company_id));
    $userFullName = trim($model->first_name . ' ' . $model->last_name);
    $companyOwner = $isSubUser ? \App\Models\User::find($model->company_id) : $model;
?>

<div class="page-title">
    <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
            <h3>{{ $isSubUser ? 'Personal Profile' : 'Account Profile' }}</h3>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-sm-end ">
                <ol class="breadcrumb ">
                    <li class="breadcrumb-item"><a href="company/dashboard" class="pjax">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Profile</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="section">
    <div class="row">
        <div class="col-12">
            <div class="card p-3 mb-4">
                <div class="row gap-2 align-items-center">
                    <div class="col-md-6 col-12-xs">
                        <h4 class="mb-4">Profile</h4>
                        <div class="d-flex flex-column flex-md-row gap-sm-4 gap-0 align-items-center">
                            <div class="avatar avatar-2xl logoIcon position-relative">
                                <img src="{{ $general->getFileUrl($model->image,'profile') }}" alt="Avatar" style="width: 90px; height: 90px; object-fit: cover; border-radius: 50%;">
                                <fa-camera class="camIcon position-absolute" style="bottom:0; right:0; cursor:pointer;"><i class="fa fa-camera" onclick="app.showModalView('company/account/image')"></i></fa-camera>
                            </div>
                            <div class="flex-column ms-2">
                                <h3 class="mt-2 mb-1">{{ $isSubUser ? ($userFullName ?: $model->email) : $model->company_name }}</h3>
                                <p class="text-muted mb-0">{{ $model->email }}</p>
                            </div>
                        </div>
                    </div> 
                    
                    <div class="col-md-5 col-12-xs ms-auto">
                        @if($isSubUser)
                            <!-- Read-Only Company Details for Team Members -->
                            <div class="rounded p-3">
                                <span class="badge bg-secondary mb-2">Company</span>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ $general->getFileUrl($companyOwner->company_logo ?? $companyOwner->image,'profile') }}" alt="Company Logo" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;" class="border bg-white">
                                    <div>
                                        <h5 class="mt-2 mb-1">{{ $companyOwner->company_name ?? 'Company Account' }}</h5>
                                        <p class="text-muted mb-0">{{ $companyOwner->email ?? '' }}</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Editable Company Logo for Main Company Owner -->
                            <h4 class="mb-4">Company Logo</h4>
                            <div class="d-flex flex-column flex-md-row gap-sm-4 gap-0">
                                <div class="avatar avatar-2xl logoIcon position-relative">
                                    <img src="{{ $general->getFileUrl($model->company_logo,'profile') }}" alt="Company Logo" style="width: 90px; height: 90px; object-fit: cover; border-radius: 8px;">
                                    <fa-camera class="position-absolute" style="bottom:0; right:0; cursor:pointer;"><i class="fa fa-camera" onclick="app.showModalView('company/account/company-logo')"></i></fa-camera>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form action="company/account/save" method="post" class="ajax-form">
                        {{ csrf_field() }}
                        <div class="row">
                            @if($isSubUser)
                                <!-- Team Member Personal Fields (3 x col-md-4) -->
                                <div class="col-md-4 col-12 mb-3">
                                    <div class="form-group">
                                        <label for="first_name" class="form-label">First Name <span class="star">*</span></label>
                                        <input type="text" name="first_name" id="first_name" class="form-control" placeholder="First Name" value="{{ $model->first_name }}">
                                    </div>  
                                </div>

                                <div class="col-md-4 col-12 mb-3">
                                    <div class="form-group">
                                        <label for="last_name" class="form-label">Last Name</label>
                                        <input type="text" name="last_name" id="last_name" class="form-control" placeholder="Last Name" value="{{ $model->last_name }}">
                                    </div>  
                                </div>

                                <div class="col-md-4 col-12 mb-3">
                                    <div class="form-group">
                                        <label for="email" class="form-label">Email <span class="star">*</span></label>
                                        <input type="text" name="email" id="email" class="form-control" placeholder="Email" value="{{ $model->email }}">
                                    </div>
                                </div>
                            @else
                                <!-- Company Owner Fields -->
                                <div class="col-sm-6 col-12 mb-3">
                                    <div class="form-group">
                                        <label for="company_name" class="form-label">Company Name <span class="star">*</span></label>
                                        <input type="text" name="company_name" id="company_name" class="form-control" placeholder="Company Name" value="{{ $model->company_name }}">
                                    </div>  
                                </div>

                                <div class="col-md-6 col-12 mb-3">
                                    <div class="form-group">
                                        <label for="email" class="form-label">Email <span class="star">*</span></label>
                                        <input type="text" name="email" id="email" class="form-control" placeholder="Email" value="{{ $model->email }}">
                                    </div>
                                </div>

                                <!-- Email Reminders -->
                                <div class="col-md-6 col-12 mb-3">
                                    <div class="form-group">
                                        <label for="email_reminders" class="form-label">Email Reminders</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="email_reminders" name="email_reminders"
                                                   value="1" {{ old('email_reminders', $model->email_reminders ?? 1) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="email_reminders">
                                                Enable Email Reminders
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <!-- Self/Company Reminders -->
                                        <div class="col-md-6 col-12 email-sub-options" style="display: {{ old('email_reminders', $model->email_reminders ?? 1) ? 'block' : 'none' }};">
                                            <div class="form-group">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="is_company_email_enabled" name="is_company_email_enabled"
                                                           value="1" {{ old('is_company_email_enabled', $model->is_company_email_enabled ?? 0) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="is_company_email_enabled">
                                                        Self/Company Reminders
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Contractor Reminders -->
                                        <div class="col-md-6 col-12 email-sub-options" style="display: {{ old('email_reminders', $model->email_reminders ?? 1) ? 'block' : 'none' }};">
                                            <div class="form-group">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="is_contractor_email_enabled" name="is_contractor_email_enabled"
                                                           value="1" {{ old('is_contractor_email_enabled', $model->is_contractor_email_enabled ?? 0) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="is_contractor_email_enabled">
                                                        Contractor Reminders
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="col-12 mt-2">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script type="text/javascript">
documentReady(function () {
    $('#email_reminders').on('change', function () {
        $('.email-sub-options').toggle(this.checked);
    }).trigger('change');

    $('.ajax-form').validate({
        submitHandler: function (form) {
            app.ajaxForm(form);
        }
    });
});
</script>
@endpush
