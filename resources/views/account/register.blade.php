@extends('layouts.blank')

@section('title')
    Register Your Account
@endsection

@section('content')
<style>
    .star {
        color: red;
    }
    ::placeholder {
        color: red;
    }
    .registration-form {
        display: none;
    }
    .choices__inner{
        font-size : 1.155rem;
    }
</style>

<div class="row main-log-pages">
    <div class="col-lg-5 col-12">
        <div id="auth-left">
            <div class="auth-logo mb-3">
                <a href="#">
                    <img src="{{$general->getFileUrl(config('setting.app_logo'),'setting')}}" alt="Logo">
                </a>
            </div>
            <h1 class="auth-title">Welcome to {{ Config::get('setting.app_name') }}</h1>
            <p class="auth-subtitle mb-4">Create your account.</p>

            <!-- Radio Buttons -->
            <div class="mb-4">
                <label class="me-3"><input type="radio" class="form-check-input" name="register_type" value="user" checked> Register as Contractor</label>
                <label><input type="radio" name="register_type" class="form-check-input" value="company"> Register as Company</label>
            </div>

            <!-- USER REGISTRATION FORM -->
            <form class="ajax-form registration-form user-form" action="{{ route('account/register-process') }}" method="POST" id="user-form" onsubmit="event.preventDefault(); app.ajaxFileForm(this); grecaptcha.reset();">
                @csrf
                <div class="form-group position-relative has-icon-left mb-3">
                    <input type="text" class="form-control form-control-xl" placeholder="Enter Your First Name *" name="first_name">
                    <div class="form-control-icon"><i class="bi bi-person"></i></div>
                </div>

                <div class="form-group position-relative has-icon-left mb-3">
                    <input type="text" class="form-control form-control-xl" placeholder="Enter Your Last Name" name="last_name">
                    <div class="form-control-icon"><i class="bi bi-person"></i></div>
                </div>

                <div class="form-group position-relative has-icon-left mb-3">
                    <input type="text" class="form-control form-control-xl" placeholder="Enter Your Business Name " name="business_name">
                    <div class="form-control-icon"><i class="bi bi-building"></i></div>
                </div>

                <div class="form-group position-relative has-icon-left mb-3">
                    <select class="choices form-select form-control-xl" name="company_name">
                        <option value="" >Select Company *</option>
                        @foreach($model as $company)
                            <option value="{{ $company->id }}">{{ $company->company_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group position-relative has-icon-left mb-3">
                    <input type="text" class="form-control form-control-xl" placeholder="Enter Your Email *" name="email">
                    <div class="form-control-icon"><i class="bi bi-envelope"></i></div>
                </div>

                <div class="form-group position-relative has-icon-left mb-3">
                    <input type="password" class="form-control form-control-xl" placeholder="Password *" name="password">
                    <div class="form-control-icon"><i class="bi bi-shield-lock"></i></div>
                    <span class="input-group-text cursor-pointer toggle-password"><i class="bi bi-eye"></i></span>
                </div>

                <div class="form-group position-relative has-icon-left mb-3">
                    <input type="password" class="form-control form-control-xl" placeholder="Confirm Password *" name="password_confirm">
                    <div class="form-control-icon"><i class="bi bi-shield-lock"></i></div>
                    <span class="input-group-text cursor-pointer toggle-password"><i class="bi bi-eye"></i></span>
                </div>

                <p class="text-muted small">
                   The Password Must be a Minimum Of 8 Characters and a Maximum Of 32 Characters, Containing at Least One Uppercase Letter, One Lowercase Letter, and One Special Character (e.g., !, @, #, $).
                </p>

                <div class="col-12 recaptcha-block mb-3">
                    {{ view('common/recaptcha') }}
                </div>

                <div class="form-check form-check-lg d-flex align-items-end mb-3">
                    <input class="form-check-input me-2" type="checkbox" name="terms" value="1" checked>
                    <label class="form-check-label text-gray-600">You must agree to the privacy policy and terms.</label>
                </div>

                <button class="btn btn-primary btn-block btn-lg shadow-lg mt-3">Sign Up</button>
            </form>

            <!-- COMPANY REGISTRATION FORM -->
            <form class="ajax-form registration-form company-form" action="{{ route('company/account/register-process') }}" method="POST" id="company-form" onsubmit="event.preventDefault(); app.ajaxFileForm(this); grecaptcha.reset();">
                @csrf
                <input type="hidden" name="type" value="2">

                <div class="form-group position-relative has-icon-left mb-3">
                    <input type="text" class="form-control form-control-xl" placeholder="Enter Your Company Name *" name="company_name">
                    <div class="form-control-icon"><i class="bi bi-building"></i></div>
                </div>

                <div class="form-group position-relative has-icon-left mb-3">
                    <input type="text" class="form-control form-control-xl" placeholder="Enter Your Email *" name="email">
                    <div class="form-control-icon"><i class="bi bi-envelope"></i></div>
                </div>

                <div class="form-group position-relative has-icon-left mb-3">
                    <input type="password" class="form-control form-control-xl" placeholder="Password *" name="password">
                    <div class="form-control-icon"><i class="bi bi-shield-lock"></i></div>
                    <span class="input-group-text cursor-pointer toggle-password"><i class="bi bi-eye"></i></span>
                </div>

                <div class="form-group position-relative has-icon-left mb-3">
                    <input type="password" class="form-control form-control-xl" placeholder="Confirm Password *" name="password_confirm">
                    <div class="form-control-icon"><i class="bi bi-shield-lock"></i></div>
                    <span class="input-group-text cursor-pointer toggle-password"><i class="bi bi-eye"></i></span>
                </div>

                <p class="text-muted small">
                    The Password Must be a Minimum Of 8 Characters and a Maximum Of 32 Characters, Containing at Least One Uppercase Letter, One Lowercase Letter, and One Special Character (e.g., !, @, #, $).
                </p>

                <div class="col-12 recaptcha-block mb-3">
                    {{ view('common/recaptcha') }}
                </div>

                <div class="form-check form-check-lg d-flex align-items-end mb-3">
                    <input class="form-check-input me-2" type="checkbox" name="terms" value="1" checked>
                    <label class="form-check-label text-gray-600">You must agree to the privacy policy and terms.</label>
                </div>

                <button class="btn btn-primary btn-block btn-lg shadow-lg mt-3">Sign Up</button>
            </form>

            <!-- Login Link -->
            <div class="text-center mt-4 fs-6">
                <p class='text-gray-600'>Already have an account? <a href="login" class="font-bold">Log in</a>.</p>
            </div>
        </div>
    </div>

    <!-- Image Right Side -->
    <div class="col-lg-7 d-none d-lg-block position-relative">
        <div class="common-home-img regi-page-img">
            <img src="./assets/img/sign-up.png" alt="image">
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        function toggleForms() {
            const selected = document.querySelector('input[name="register_type"]:checked').value;
            document.querySelector('.user-form').style.display = selected === 'user' ? 'block' : 'none';
            document.querySelector('.company-form').style.display = selected === 'company' ? 'block' : 'none';
        }

        // Initial display
        toggleForms();

        // Event listener for radio change
        document.querySelectorAll('input[name="register_type"]').forEach(radio => {
            radio.addEventListener('change', toggleForms);
        });
    });
</script>
@endpush
