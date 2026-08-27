@extends('company.layouts.front')

@section('title', 'W-9 Form')

@section('content')
<style>
 
 input:focus,
select:focus {
    outline: none !important;
    box-shadow: none !important;
}
.popover-header{
    background:#435ebe;
    color:#FFFFFF;
}
.popover{
    border:1px solid #435EBE;
}
.bs-popover-end > .popover-arrow:after, .bs-popover-auto[data-popper-placement^="right"] > .popover-arrow:after {
    border-right-color:#435EBE;
}
   .btn.btn-light{
    width:23px;
    height:25px;
    border-radius: 50%;
    display:inline-flex;
    border:2px solid transparent;
    background-color:#F2F7FF;
    align-items: center;
    justify-content: center;
}
   .btn.btn-light:hover{
       background:#F7F8F9;
       border:2px solid #F2F7FF;
   }
   .btn.btn-light:active{
    color:#fff !important;
    background-color:#435ebe!important;
    border-color:none!important;
}
.btn-light i {
    padding-top:2px;
    font-size: 12px;
}

</style>
<div class="d-flex justify-content-center align-items-center min-vh-100 position-relative">

<div class="container my-4"> 
@if(session('success') || session('error'))
    <div class="position-fixed top-0 end-0 p-3" id="session-alert" style="z-index: 1050; max-width: 350px;">
            <div class="card-body p-3">
                @if(session('success'))
                    <div class="alert alert-success mb-0" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger mb-0" role="alert">
                        {{ session('error') }}
                    </div>
                @endif
            </div>
    </div>
@endif
@if ($step == 0)
    <div class="container col-md-8 card  p-4">
        <div class="row g-4 mb-5 ">
            <div class="text-center mb-3">
                @if(!empty($company?->company_logo))
                        <div class="avatar avatar-2xl logoIcon">
                            <img src="{{ $general->getFileUrl($company->company_logo, 'profile') }}" alt="Company Logo">
                          </div>
                @else
                        <div class="avatar avatar-2xl logoIcon">
                            <img src="{{ asset('upload/no_image.jpg') }}" alt="Company Logo">
                          </div>
                @endif
            </div>
    
            <h5 class="card-title m-0 text-center">Create a Form W-9</h5>
    
            <div class="col-md-6">
                <div class="card h-100 border">
                    <div class="card-header border-0 pb-0">
                        <h5 class="card-title mb-0"> 
                        Welcome,  {{ $userName }} – {{ $w9Request->vendor_company_name }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <p>Please complete your W-9 for <strong>{{ $company->company_name }}</strong>.</p>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                            <strong>Step 1 – Tax Details</strong> </li>
                            <li class="list-group-item">
                                 <strong>Step 2 – Address</strong> 
                                </li>
                            <li class="list-group-item">
                            <strong>Step 3 – Verify & Sign</strong>    
                                </li>
                        </ul>
                    </div>
                </div>
            </div>
    
            <div class="col-md-6">
                <div class="card h-100 border">
                    <div class="card-header pb-0 border-0">
                        <h4 class="card-title">Security Information</h4>
                    </div>
                    <div class="card-body">
                        <p><strong>{{ $company->company_name }} </strong> has selected getW9.tax to manage their W-9s.</p>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex px-0 justify-content-between">
                              <strong>Your information is encrypted using industry standard AES 256CBC</strong> 
                                <span class="ms-1 sidebar-link act-btns"><i class="fa fa-lock fa-2x"></i></span>
                            </li>
                            <li class="list-group-item d-flex px-0 justify-content-between">
                              <strong>The website is protected with SSL Certificate (HTTPS)</strong>  
                                <span class="ms-1 sidebar-link act-btns"><i class="fa fa-lock fa-2x"></i></span>
                            </li>
                            <li class="list-group-item d-flex px-0 justify-content-between">
                                <strong> Our cloud security features are updated regularly to meet evolvingthreats</strong>
                                <span class="ms-1 sidebar-link act-btns"><i class="fa fa-lock fa-2x"></i></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    
        <div class="">
            <form action="{{ url('/w9-form/'.$w9Request->token) }}" method="POST">
                @csrf
                <input type="hidden" name="step" value="0">
                <!--<p class="mb-4">-->
                <!--    Hi {{ $company->company_name   }}, you are currently logged into your getW9 account.-->
                <!--    Please select below which company you'd like to send the Form W-9 for.-->
                <!--    You can also <a href="javascript:void(0)" class="primary-link">clicking here</a>,-->
                <!--    and come back to this page to resume submitting this W-9.-->
                <!--</p>            -->
               

 <div class="mb-3">
     <div class="d-flex align-items-center justify-content-between mb-2">
                        <label for="company_id" class="form-label mb-0">
                           Your Company </label>
                             <button type="button" class="btn btn-light btn-xs" data-bs-toggle="popover" data-bs-placement="right" title="My company" data-bs-html="true"
                            data-bs-content="Your company has been automatically selected for this W-9 form submission">
                            <i class="fa fa-info"></i>
                        </button>
                        </div>
                        <input type="hidden" name="company_id" value="{{ $company->id }}">
                        <input type="text" id="company_name" name="company_name" class="form-control" value="{{ $company->company_name }}" readonly>
                </div>

                <div class="text-center mt-3">
                    <button type="submit" class="btn btn-primary w-25">Start</button>
                </div>
            </form>
        </div>
    </div>
@endif

@if ($step == 1)
    <div class="row justify-content-center">
        <div id="step-1" class="col-md-5 step-container card  p-4">

            <div class="text-center mb-3">
                 @if(!empty($company?->company_logo))
                  <div class="avatar avatar-2xl logoIcon">
                            <img src="{{ $general->getFileUrl($company->company_logo, 'profile') }}" alt="Company Logo">
                          </div>
                @else
                      <div class="avatar avatar-2xl logoIcon">
                            <img src="{{ asset('upload/no_image.jpg') }}" alt="Company Logo">
                          </div>
                @endif
            </div>
          <h5 class="card-title mt-0 mb-4 text-center">Step 1 - Tax Details</h5>
       
            <form action="{{ url('/w9-form/'.$w9Request->token) }}" method="POST" id ="step-1-form">
                @csrf
                <input type="hidden" name="step" value="1">
                     <div class="d-flex align-items-center justify-content-between mb-2">
               
                <label class="form-label">
            Legal Entity Name</label>
                      <button type="button" class="btn btn-light btn-xs" data-bs-toggle="popover" data-bs-placement="right" title="Legal Entity Name" data-bs-html="true"
                    data-bs-content="Name (as shown on your income tax return). Name is required on this line; do not leave this line blank. 
                    Note, if you are a sole proprietor or a single-member LLC, then enter your personal name, otherwise enter the legal name of your company.">
                    <i class="fa fa-info"></i>
                </button>

                     </div>
                <input type="text" name="entity_name" class="form-control mb-2" required value="{{ $w9Request->vendor_company_name }}">

                <small class="text-danger mb-3 d-block" style="font-weight:600;">
                    Note: If you are a sole proprietor or a single-member LLC, enter your legal name as shown on your personal tax return.
                </small>
                       <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-label">
                 Business Name (If different than above)</label>
                <button type="button" class="btn btn-light btn-xs" data-bs-toggle="popover" data-bs-placement="right" title="Business Name" data-bs-html="true"
                    data-bs-content="Business name/disregarded entity name, if different from above.">
                    <i class="fa fa-info"></i>
                </button>
                
               
                    </div>
                <input type="text" name="business_name" class="form-control mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                          <label class="form-label">
                    Entity Type <span class="text-danger">*</span></label>
                 <button type="button" class="btn btn-light btn-xs" data-bs-toggle="popover" data-bs-placement="right" title="Entity Type" data-bs-html="true"
                    data-bs-content="Check appropriate box for federal tax classification of the person whose name is entered on line 1. Select only one of the following eight options.">
                    <i class="fa fa-info"></i>
                </button>
                
              
                    </div>
                <select name="entity_type" id="entity_type" class="form-select mb-3" required>
                    <option value="">Select an Entity</option>
                    <option value="Individual/Sole Proprietor">Individual / Sole Proprietor</option>
                    <option value="Single-Member LLC">Single-Member LLC</option>
                    <option value="Multi-Member LLC">Multi-Member LLC</option>
                    <option value="C Corporation">C Corporation</option>
                    <option value="S Corporation">S Corporation</option>
                    <option value="Partnership">Partnership</option>
                    <option value="Trust/Estate">Trust / Estate</option>
                    <option value="other">Other</option>
                </select>
                
                
                 <label class="form-label" id=
                 "tax_classicfication">
                    Tax Classification <span class="text-danger">*</span></label>
                <select name="entity_specification" id="multiple_entity" class="form-select mb-3">
                    <option value="C Corporation">C Corporation</option>
                    <option value="S Corporation">S Corporation</option>
                    <option value="Partnership">Partnership</option>
                </select>

                <input type="text" id="other_entity_div" name="other_entity_name" class="form-control mb-3" placeholder="Please specify (if Other selected)">
                 <div class="d-flex align-items-center justify-content-between mb-2">
               
                <label class="form-label">
          Tax Identification Number - SSN <span class="text-danger">*</span></label>
           <button type="button" class="btn btn-light btn-xs" data-bs-toggle="popover" data-bs-placement="right" title="Social Security Number" data-bs-html="true"
                    data-bs-content="Enter your TIN in the appropriate box. The TIN provided must match the name given on line 1 to avoid backup withholding. For individuals, this is generally your social security number (SSN). If you do not have a TIN the phrase "Applied For" will be written in the space for the TIN per the Form W-9 general instructions. "Applied For" means that you have already applied for a TIN or that you intend to apply for one soon.">
                    <i class="fa fa-info"></i>
                </button>
                     </div>
                <input type="text" name="tax_id_number" id="tax_id_number" class="form-control mb-3">

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="no_tax_id" name="no_tax_id" value="1">
                    <label class="form-check-label" for="no_tax_id">I do not have a Taxpayer Identification Number</label>
                </div>

                <div id="tax_warning" style="display:none; border:1px solid #FE8024;border-radius:4px; padding: 10px; margin-bottom: 10px; background-color: #fff3e0; color: #FE8024;">
                    <p style="margin:0; font-size: 0.9rem;">
                        Please note, if you don't provide us with a correct tax ID number, we are required to follow backup withholding rules, 
                        and withhold 24% as taxes from any payments we issue to you.
                        You may apply for an EIN at <a href="https://www.irs.gov/ein" target="_blank" style="color: #FE8024;">www.irs.gov/ein</a> 
                        or SSN at <a href="https://www.ssa.gov" target="_blank" style="color: #FE8024;">www.ssa.gov</a>.
                        Also, by selecting this option we will print “Applied for” as your TIN.
                    </p>
                    <button id="understand_btn" type="button" class="btn btn-warning mt-2">I Understand</button>
                </div>
  <div class="d-flex align-items-center justify-content-between mb-2">
       <label class="form-label">
                     List account number(s) (Optional)</label>
                <button type="button" class="btn btn-light btn-xs" 
                    data-bs-toggle="popover" data-bs-placement="right" title="Account Numbers" data-bs-html="true"
                    data-bs-content="List account number(s)">
                    <i class="fa fa-info"></i>
                </button>

                     </div>
                <input type="text" name="list_account_number" class="form-control mb-3">

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="no_foreign_beneficiaries" name="no_foreign_beneficiaries" value="1">
                    <label class="form-check-label" for="no_foreign_beneficiaries">
                        I do not have any foreign partners, owners, or beneficiaries
                    </label>
                </div>

                <div class="text-center">
                      <button type="submit" class="btn btn-primary"> Save & Proceed</button>
                </div>
            </form>
        </div>
        
    </div>
@endif

@if ($step == 2)
    <div class="row justify-content-center">
        <div id="step-2" class="col-md-5 step-container card p-4 ">
            <div class="text-center mb-3">
                @if(!empty($company?->company_logo))
                   <div class="avatar avatar-2xl logoIcon">
                            <img src="{{ $general->getFileUrl($company->company_logo, 'profile') }}" alt="Company Logo">
                          </div>
                @else
                    <div class="avatar avatar-2xl logoIcon">
                            <img src="{{ asset('upload/no_image.jpg') }}" alt="Company Logo">
                          </div>
                @endif 
            </div>

            <h5 class="card-title text-center mb-4">Step 2 - Address</h5>
            
            <form action="{{ url('/w9-form/'.$w9Request->token) }}" method="POST" id="step-2 form">
                @csrf
                <input type="hidden" name="step" value="2">

                <div class="mb-3">
        
                    <label for="address_lookup" class="form-label">
                        Lookup address  <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="address_lookup" name="address_lookup" class="form-control" placeholder="Enter a location" required>
                </div>

                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                    
                    <label for="address" class="form-label">
                        Address  <span class="text-danger">*</span>
                    </label>
                    <button type="button" class="btn btn-light btn-xs" data-bs-toggle="popover" data-bs-placement="right" title="Address" data-bs-html="true"
                        data-bs-content="Address (number, street, and apt. or suite no.)">
                        <i class="fa fa-info"></i>
                    </button>
                    
                    </div>
                    <input type="text" id="address" name="address" class="form-control" required>
                </div>

                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                  
                    <label for="city" class="form-label">
                         City <span class="text-danger">*</span>
                    </label>
                      <button type="button" class="btn btn-light btn-xs" data-bs-toggle="popover" data-bs-placement="right" title="City" data-bs-html="true"
                        data-bs-content="City of business location">
                        <i class="fa fa-info"></i>
                    </button>
                    </div>
                    <input type="text" id="city" name="city" class="form-control" required>
                </div>

                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <label for="state" class="form-label">
                          State <span class="text-danger">*</span>
                    </label>
                    <button type="button" class="btn btn-light btn-xs" data-bs-toggle="popover" data-bs-placement="right" title="State" data-bs-html="true"
                        data-bs-content="State">
                        <i class="fa fa-info"></i>
                    </button>
                    
                    </div>
                    <select id="state" name="state" class="form-control" required>
                        @foreach($getUSStates as $state => $name)
                        <option value="{{ $name }}" {{ $name == 'Alabama' ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                            <!--<option value="{{ $name }}">{{ $name }}</option>-->
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                   
                    <label for="zip" class="form-label">
                Zip Code <span class="text-danger">*</span>
                    </label>
                     <button type="button" class="btn btn-light btn-xs" data-bs-toggle="popover" data-bs-placement="right" title="Zip code" data-bs-html="true"
                        data-bs-content="Zip code of business location">
                        <i class="fa fa-info"></i>
                    </button>
                    </div>
                    <input type="text" id="zip_code" name="zip_code" class="form-control" required>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary"> Save & Continue</button>
                </div>
            </form>
        </div>
    </div>
@endif

   @if ($step == 3)
        <div class="row justify-content-center">
        <!--<div id="step-3" class="col-md-6 step-container card p-4">-->
        <div id="step-3" class="col-md-5 step-container card p-4 ">
            <div class="text-center mb-3">
                @if(!empty($company?->company_logo))
                <div class="avatar avatar-2xl logoIcon">
                            <img src="{{ $general->getFileUrl($company->company_logo, 'profile') }}" alt="Company Logo">
                          </div>
                @else
                       <div class="avatar avatar-2xl logoIcon">
                            <img src="{{ asset('upload/no_image.jpg') }}" alt="Company Logo">
                          </div>
                @endif 
            </div>
    
            <h5 class="card-title text-center mb-4">Verify &amp; Sign</h5>
            <p>Please review your details below, and sign at the bottom. If you need to make any
                changes, please go back, and make changes as needed.</p>
            <div class="card border p-4 mb-3">
                <h5 class="card-title mb-3">Tax Details</h5>
                <p><strong>Legal Entity Name:</strong> {{ $w9Request->vendor_company_name ?? '-' }}</p>
                <p><strong>Business Name (If different than above):</strong> {{ $decrypted['business_name'] ?? '-' }}</p>
                @if(!empty($decrypted['entity_type']))
                    <p><strong>Entity Type:</strong> {{ $decrypted['entity_type'] ?? '-' }}</p>
                @else
                    <p><strong>Entity Type:</strong> {{ $decrypted['other_entity_name'] ?? '-' }}</p>
                @endif
                <p><strong>List account number(s):</strong> {{ $decrypted['list_account_number'] ?? '-' }}</p>
                @if($w9Request->no_foreign_beneficiaries == 1)
                <p>I do not have any foreign partners, owners, or beneficiaries</p>
                @endif
            </div>
            
            <div class="card  border p-4 mb-3">
                <h5 class="card-title mb-3">Address Details</h5>
                <p><strong>Address:</strong> {{ $decrypted['address'] ?? '-' }}</p>
                <p><strong>City:</strong> {{ $decrypted['city'] ?? '-' }}</p>
                <p><strong>State:</strong> {{ $decrypted['state'] ?? '-' }}</p>
                <p><strong>Zip:</strong> {{ $decrypted['zip_code'] ?? '-' }}</p>
            </div>
                
            <div class="">
                <form action="{{ url('/w9-form/'.$w9Request->token) }}" method="POST"  id="step-3 form">
                    @csrf
                    <input type="hidden" name="step" value="3">
                    <h5 class="card-title">Signer Signature <span class="text-danger">*</span></h5>
                    <input type="hidden" name="signature" id="signature">
                    <canvas id="signature-pad" style="border:1px solid #ccc;width:100%;height:200px;border-radius: 10px;"></canvas>
                    <span id="signature-error-placement"></span>
                    <div class="mt-2 mb-3">
                        <button type="button" id="clear-signature" class="btn btn-danger">Clear Signature</button>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                         <label class="form-label">Signer's Name <span class="text-danger">*</span></label>
                    <button type="button" class="btn btn-light btn-xs" data-bs-toggle="popover" data-bs-placement="right" title="Name" data-bs-html="true"
                        data-bs-content="Signing Person Name">
                        <i class="fa fa-info"></i>
                    </button>
                    </div>
                    <input type="text" name="signer_name" class="form-control mb-3"  value="{{ $w9Request->vendor_company_name }}" required>
                       <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-label">Signer's Email <span class="text-danger">*</span></label>
                    <button type="button" class="btn btn-light btn-xs" data-bs-toggle="popover" data-bs-placement="right" title="Email" data-bs-html="true"
                        data-bs-content="Signing Person's Email">
                        <i class="fa fa-info"></i>
                    </button>
                    </div>
                    <input type="email" name="signer_email" class="form-control mb-3" value="{{ $w9Request->vendor_email }}" required>
                       <div class="d-flex align-items-center justify-content-between mb-2">
                    <label class="form-label">Signer's Phone Number<span class="text-danger">*</span></label>
                    <button type="button" class="btn btn-light btn-xs" data-bs-toggle="popover" data-bs-placement="right" title="Phone" data-bs-html="true"
                        data-bs-content="Signing Person Phone">
                        <i class="fa fa-info"></i>
                    </button>
                    </div>
                    <input type="tel" name="signer_phone" class="form-control mb-3" value="{{ $w9Request->vendor_phone }}" required>
    
                    <div class="d-flex justify-content-center mt-4">
                        <button type="submit" class="btn btn-primary">Submit Form W-9</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
    
@if ($step == 4)
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card  p-4">
                <div class="text-center mb-3">
                    @if(!empty($company?->company_logo))
                           <div class="avatar avatar-2xl logoIcon">
                            <img src="{{ $general->getFileUrl($company->company_logo, 'profile') }}" alt="Company Logo">
                          </div>
                    @else
                           <div class="avatar avatar-2xl logoIcon">
                            <img src="{{ asset('upload/no_image.jpg') }}" alt="Company Logo">
                          </div>
                    @endif       
                </div>
    
                <h5 class="card-title mb-4 text-center">W9 Form Filed Successfully</h5>
    
                <div class="card border p-4">
                    <p class="mb-0">
                        Your Form W-9 has been securely submitted to {{ $company->company_name }}. Thank you for using it.
                    </p>
                </div>
    
                <div class="text-center mb-3">
                    <a href="{{ route('fw9/pdf', $w9Request->token) }}" target="_blank" class="btn btn-primary">
                        Click here to save a PDF of your Form W-9
                    </a>
                </div>
    
                <!--<div>-->
                <!--    <a href="{{ route('account/register') }}" class="primary">-->
                <!--       <p class="p-line"> Don't have an account with getW9? Click here to sign up, and save this Form to your online portal, and also request & manage W-9s from your vendors.-->
                <!--       </p>-->
                <!--    </a>-->
                <!--</div>-->
            </div>
        </div>
    </div>
@endif
<div class="d-flex justify-content-center">
  <footer class="footer bg-footer-theme position-absolute bottom-0">
                            <div>
                                <div class="footer-container d-flex align-items-center justify-content-between py-2 flex-md-row flex-column">
                                    <div>
                                        ©{{date('Y')}}, made by <a href="login" target="_self" class="fw-semibold">{{ config('setting.app_name') }}</a>
                                    </div>
                                </div>
                            </div>
                        </footer>
                        </div>
</div>
</div>


@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/additional-methods.min.js"></script>



<script>
$(document).ready(function () {
    $("#multiple_entity").hide();
    $("#tax_classicfication").hide();

    if ($("#step-1-form").length) {
        $("#step-1-form").validate({
            ignore: [],
            rules: {
                entity_name: { required: true },
                entity_type: { required: true },
                other_entity_name: { 
                    required: function() { return $("#entity_type").val() === "other"; },
                     pattern: /^[A-Za-z0-9 ]+$/,
                },
                tax_id_number: { 
                    required: function() { return !$("#no_tax_id").is(":checked"); },
                    pattern: /^[0-9]{9}$/
                }
            },
            messages: {
                entity_name: "Please enter legal entity name",
                entity_type: "Please select entity type",
                other_entity_name: "Please specify a valid entity type (letters and numbers only)",
                tax_id_number: {
                    required: "A correct TIN is required. If you do not have one and have, or will be applying for one, then, please mark the box below",
                    pattern: "Please enter the EIN without spaces or dashes"
                }                
            },
            errorPlacement: function(error, element) {
                error.addClass("invalid-feedback");
                element.after(error);
            },
            highlight: function(element) { $(element).addClass("is-invalid"); },
            unhighlight: function(element) { $(element).removeClass("is-invalid"); }
        });

        $("#entity_type").on('change', function() {
            if ($(this).val() === "other") {
                $("#other_entity_div").show();
            } else { 
                $("#other_entity_div").hide(); 
                $("#other_entity_div input").val('');
            }
            
            if($(this).val() === "Multi-Member LLC"){
                $('#multiple_entity').show();
                $('#tax_classicfication').show();
                
            }else { 
                $("#multiple_entity").hide(); 
                $("#tax_classicfication").hide(); 
                $("#multiple_entity input").val('');
            }
        });

        $("#no_tax_id").on('change', function() {
            if (this.checked) {
                $("#tax_id_number").hide().val('');
                $("#tax_warning").show();
            } else {
                $("#tax_id_number").show();
                $("#tax_warning").hide();
            }
        });

        $("#understand_btn").on('click', function() { 
            $("#tax_warning").hide(); 
        });
    }


    if ($("#step-2 form").length) {

        let addressSelected = false; 

        if (typeof google !== 'undefined') {
            const input = document.getElementById("address_lookup");
            const autocomplete = new google.maps.places.Autocomplete(input, {
                types: ["address"],
                componentRestrictions: { country: "us" }
            });

            autocomplete.addListener("place_changed", function () {
                const place = autocomplete.getPlace();
                addressSelected = true;

                let street = '', city = '', state = '', zip = '';

                place.address_components.forEach(function(component) {
                    const types = component.types;
                    if (types.includes("street_number")) { street = component.long_name + ' ' + street; }
                    if (types.includes("route")) { street += component.long_name; }
                    if (types.includes("locality")) { city = component.long_name; }
                    if (types.includes("administrative_area_level_1")) { state = component.short_name; }
                    if (types.includes("postal_code")) { zip = component.long_name; }
                });

                $("#address").val(street);
                $("#city").val(city);
                $("#state").val(state);
                $("#zip_code").val(zip);

                $("#address, #city, #state, #zip_code").each(function() {
                    $(this).removeClass("is-invalid");
                    $(this).next(".invalid-feedback").remove();
                });
            });
        }

       $("#step-2 form").validate({
            rules: {
                address_lookup: { required: true },
                address: { required: function() { return !addressSelected; } },
                city: { required: function() { return !addressSelected; } },
                state: { required: function() { return !addressSelected; } },
                zip_code: { 
                    required: function() { return !addressSelected; },
                    digits: true,
                    maxlength: 5
                }
            },
            messages: {
                address_lookup: "Please enter a location",
                address: "Please enter the address",
                city: "Please enter the city",
                state: "Please select a state",
                zip_code: {
                    required: "Please enter the zip code",
                    digits: "Zip code must contain only numbers",
                    maxlength: "Zip code must not exceed 5 digits"
                }
            },
            errorPlacement: function (error, element) {
                error.addClass("invalid-feedback");
                element.closest(".mb-3, .mb-4").append(error); // FIX
            },
            highlight: function (element) {
                $(element).addClass("is-invalid");
            },
            unhighlight: function (element) {
                $(element).removeClass("is-invalid");
            }
        });

    }

    if ($("#step-3 form").length) {
        $("#step-3 form").validate({
            rules: {
                signature: { required: true }, 
                signer_name: { required: true }, 
                signer_email: { required: true, email: true },
                signer_phone: { required: true } 
            },
            messages: {
                signature: "Signature is required"
            },
            errorPlacement: function (error, element) {
                error.addClass("invalid-feedback");
                if (element.attr("name") === "signature") {
                     element.closest("form").find("canvas").after(error);
                } else {
                     element.closest(".mb-3").append(error);
                }
            },
            highlight: function (element) { $(element).addClass("is-invalid"); },
            unhighlight: function (element) { $(element).removeClass("is-invalid"); },
            
    
            submitHandler: function (form) {
                if (!signaturePad.isEmpty()) {
                    document.getElementById('signature').value = signaturePad.toDataURL('image/png');
                    
                    form.submit();
                } else {
                        $("#signature-error-placement")
                        .text("Please provide a signature.") 
                        .css("color", "red"); 
                    return false; 
                }
            }
        });
    }
});
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const entityType = document.getElementById('entity_type');
        const otherEntityDiv = document.getElementById('other_entity_div');

        if (entityType.value === 'other') {
            otherEntityDiv.style.display = 'block';
        } else {
            otherEntityDiv.style.display = 'none';
        }

        entityType.addEventListener('change', function() {
            if (this.value === 'other') {
                otherEntityDiv.style.display = 'block'; 
            } else {
                otherEntityDiv.style.display = 'none'; 
                otherEntityDiv.querySelector('input').value = ''; 
            }
        });
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const taxIdInput = document.getElementById('tax_id_number');
    const noTaxIdCheckbox = document.getElementById('no_tax_id');
    const taxWarning = document.getElementById('tax_warning');
    const understandBtn = document.getElementById('understand_btn');

    function toggleTaxIdInput() {
        if (noTaxIdCheckbox.checked) {
            taxIdInput.value = '';          
            taxIdInput.disabled = true;     
            taxIdInput.style.display = 'none'; 

            taxWarning.style.display = 'block'; 
        } else {
            taxIdInput.disabled = false;
            taxIdInput.style.display = 'block'; 
            taxWarning.style.display = 'none';  
        }
    }

    toggleTaxIdInput();

    noTaxIdCheckbox.addEventListener('change', toggleTaxIdInput);

    understandBtn.addEventListener('click', function() {
        taxWarning.style.display = 'none';
    });
});
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCjLjAx9FgWSihHDKzNF3FuM_JVOxLzoag&callback=initMap&libraries=places" async defer></script>
<script>
function initMap() {
    const input = document.getElementById("address_lookup");

    const autocomplete = new google.maps.places.Autocomplete(input, {
        types: ["address"],
        componentRestrictions: { country: "us" } 
    });

    autocomplete.addListener("place_changed", function () {
        const place = autocomplete.getPlace();

        let street = '';
        let city = '';
        let state = '';
        let zip = '';

        place.address_components.forEach(function(component) {
            const types = component.types;

            if (types.includes("street_number")) {
                street = component.long_name + ' ' + street;
            }
            if (types.includes("route")) {
                street += component.long_name;
            }
            if (types.includes("locality")) {
                city = component.long_name;
            }
            if (types.includes("administrative_area_level_1")) {
                state = component.short_name; 
            }
            if (types.includes("postal_code")) {
                zip = component.long_name;
            }
        });

        document.getElementById("address").value = street;
        document.getElementById("city").value = city;
        document.getElementById("state").value = state;
        document.getElementById("zip").value = zip;
    });
}

google.maps.event.addDomListener(window, "load", initMap);
</script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.0/dist/signature_pad.umd.min.js"></script>
<script>
    const canvas = document.getElementById('signature-pad');
    const signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgba(255, 255, 255, 0)',
        penColor: 'black',
    });

    function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext("2d").scale(ratio, ratio);
        signaturePad.clear(); 
    }
    window.addEventListener("resize", resizeCanvas);
    resizeCanvas();

    document.getElementById('clear-signature').addEventListener('click', function () {
        signaturePad.clear();
    });

    function saveSignature() {
    if (!signaturePad.isEmpty()) {
        document.getElementById('signature').value = signaturePad.toDataURL('image/png');
        return true;
    }
}
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const alertDiv = document.getElementById('session-alert');
        if(alertDiv) {
            setTimeout(() => {
                alertDiv.style.transition = "opacity 0.5s ease";
                alertDiv.style.opacity = "0";
                setTimeout(() => {
                    alertDiv.remove();
                }, 500);
            }, 2500); 
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
   var popoverButtons = document.querySelectorAll('.btn-light');

    popoverButtons.forEach(function (btn) {

        // Create popover with Right placement
        var popover = new bootstrap.Popover(btn, {
            trigger: 'manual',
            placement: 'right'
        });

        btn.addEventListener('click', function (e) {
            e.preventDefault();

            // Toggle popover
            var instance = bootstrap.Popover.getInstance(btn);

            if (btn.classList.contains('showing-popover')) {
                instance.hide();
                btn.classList.remove('showing-popover');
            } else {
                // Hide all other popovers
                popoverButtons.forEach(function (btn2) {
                    var pop2 = bootstrap.Popover.getInstance(btn2);
                    if (pop2) {
                        pop2.hide();
                        btn2.classList.remove('showing-popover');
                    }
                });

                instance.show();
                btn.classList.add('showing-popover');
            }
        });
    });

    // Hide popover on outside click
    document.addEventListener('click', function (e) {
        popoverButtons.forEach(function (btn) {
            if (!btn.contains(e.target)) {
                var pop = bootstrap.Popover.getInstance(btn);
                if (pop) {
                    pop.hide();
                    btn.classList.remove('showing-popover');
                }
            }
        });
    });

    // Hide popover on scroll
    window.addEventListener('scroll', function () {
        popoverButtons.forEach(function (btn) {
            var pop = bootstrap.Popover.getInstance(btn);
            if (pop) {
                pop.hide();
                btn.classList.remove('showing-popover');
            }
        });
    });

    
});
</script>

@if($step < 4)
<script>
    function checkStatus() {
        $.get("{{ route('company/w9/status', ['token' => $w9Request->token]) }}", function(data) {
            if (data.submitted) {
                window.location.href = "{{ route('w9_form', ['token' => $w9Request->token, 'step' => 4]) }}";
            }
        });
    }
    setInterval(checkStatus, 2000); 
</script>
@endif
@endpush