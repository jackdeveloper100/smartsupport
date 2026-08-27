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

<div class="page-title">
    <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
            <h3>Account Profile</h3>
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
                <div class="row gap-2">
                    <div class="col-6 col-12-xs ">
                        <h4 class="mb-4">Profile </h4>
                        <div class="d-flex flex-column flex-md-row gap-sm-4 gap-0">
                            <div class="avatar avatar-2xl logoIcon">
                                <img src="{{ $general->getFileUrl($model->image,'profile') }}" alt="Avatar">
                                <fa-camera class="camIcon"><i class="fa fa-camera" onclick="app.showModalView('company/account/image')"></i></fa-camera>
                            </div>
                            <div class="flex-column">
                                <h3 class="mt-3">{{ $model->company_name }} </h3>
                                <p class="text-small">{{ $model->email }}</p>
                            </div>
                        </div>
                    </div> 
                    <div class="col-5 col-12-xs">
                        <h4 class="mb-4">Company Logo</h4>
                        <div class="d-flex flex-column flex-md-row gap-sm-4 gap-0">
                            <div class="avatar avatar-2xl logoIcon">
                                <img src="{{ $general->getFileUrl($model->company_logo,'profile') }}" alt="Avatar">
                                <fa-camera><i class="fa fa-camera" onclick="app.showModalView('company/account/company-logo')"></i></fa-camera>
                            </div>
                        </div>
                    </div>
                    <!--<div class="d-flex align-items-center justify-content-end mt-3 mt-md-0">-->
                    <!--        <button class="btn btn-primary" id="openAffiliateModal">Apply as Affiliate</button>-->
                    <!--</div>-->
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
                            <div class="col-sm-6 col-12">
                                <div class="form-group">
                                    <label for="name" class="form-label">Company Name <span class="star">*</span></label>
                                    <input type="text" name="company_name" id="first_name" class="form-control" placeholder="Company Name" value="{{ $model->company_name }}">
                                </div>  
                            </div>

                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label for="name" class="form-label">Email <span class="star">*</span></label>
                                    <input type="text" name="email" id="email" class="form-control" placeholder="Email" value="{{ $model->email }}">
                                </div>
                            </div>

                            <!-- Email Reminders -->
                            <div class="col-md-6 col-12">
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
                            
                           


                            <!-- Referral Link -->
                            @if($model->is_affiliate)
                            <div class="col-12 mb-3">
                                <label class="form-label">Your Referral Link</label>
                                <div class="input-group">
                                    <input type="text" id="affiliate_link" class="form-control" readonly
                                           value="{{ url('/account/register?ref=' . $model->affiliate_code) }}">
                                    <button class="btn btn-secondary" type="button" id="copy_affiliate_link">Copy</button>
                                </div>
                                <small id="copySuccess" class="text-success d-none mt-1">
                                    Referral link copied!
                                </small>
                            </div>
                            @endif

                          
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
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

    // Affiliate toggle
    $('#apply_affiliate').on('change', function () {
        $('#affiliate_section').toggle(this.checked);
    });

     // Show/hide "Other" role
    $('#affiliate_role_type').on('change', function () {
        $('#affiliate_role_other_div').toggle($(this).val() === 'other');
        if ($(this).val() !== 'other') {
            $('#affiliate_role_other_div input').val(''); // clear value if not other
        }
    }).trigger('change');
    
    // Copy referral link
    $('#copy_affiliate_link').on('click', function () {
        var copyText = document.getElementById("affiliate_link");
        copyText.select();
        copyText.setSelectionRange(0, 99999); // for mobile devices
        navigator.clipboard.writeText(copyText.value).then(function() {
            document.getElementById('copySuccess').classList.remove('d-none');
            setTimeout(function() {
                document.getElementById('copySuccess').classList.add('d-none');
            }, 2000);
        });
    });    
 
    
  // Open affiliate modal on button click
 $('#openAffiliateModal').on('click', function() {
    app.showModalView('company/account/affiliate');

    // Wait until modal is added to DOM
    $('#common-modal .modal-dialog').addClass('modal-lg');
});

      // Email reminders toggle
    $('#email_reminders').on('change', function () {
        $('.email-sub-options').toggle(this.checked);
    }).trigger('change');

    // Affiliate toggle
    $('#apply_affiliate').on('change', function () {
        $('#affiliate_section').toggle(this.checked);
    });


    // Ajax form submit
    $('.ajax-form').validate({
        submitHandler: function (form) {
            app.ajaxForm(form);
        }
    });
});

</script>
@endpush
