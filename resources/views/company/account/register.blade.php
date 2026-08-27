@extends('company.layouts.blank')

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
    </style>
   
    <div class="row main-log-pages">
        <div class="col-lg-5 col-12">
            <div id="auth-left">
                <div class="auth-logo">
                    <a href="company/auth/login">
                        <img src="{{$general->getFileUrl(config('setting.app_logo'),'setting')}}" alt="Logo">
                    </a>
                </div>
                <h1 class="auth-title">Welcome to {{ Config::get('setting.app_name') }}</h1>
                <p class="auth-subtitle mb-2 mb-sm-5">Create your account.</p>

                <form class="ajax-form mb-3" action="{{route('company/account/register-process')}}" method="POST" id="ajax-form" onsubmit="event.preventDefault();app.ajaxFileForm(this);grecaptcha.reset();">
                    @csrf
                    
                    <input type="hidden" name="type" value="2">
                    
                    <!-- Company Name  Field -->
                    <div class="form-group position-relative has-icon-left mb-3 mb-sm-4">
                        <input type="text" class="form-control form-control-xl" placeholder="Enter Your Company Name *" name="company_name">
                        <div class="form-control-icon">
                            <i class="bi bi-building"></i>
                        </div>
                    </div>


                    <!-- Email Field -->
                    <div class="form-group position-relative has-icon-left mb-3 mb-sm-4">
                        <input type="text" class="form-control form-control-xl" placeholder="Enter Your Email *" name="email">
                        <div class="form-control-icon">
                            <i class="bi bi-envelope"></i>
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="form-group position-relative has-icon-left mb-3 mb-sm-4">
                        <input type="password" class="form-control form-control-xl" placeholder="Password *" name="password">
                        <div class="form-control-icon">
                            <i class="bi bi-shield-lock"></i>
                        </div>
                        <span class="input-group-text cursor-pointer toggle-password"><i class="bi bi-eye"></i></span>
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="form-group position-relative has-icon-left mb-3 mb-sm-4">
                        <input type="password" class="form-control form-control-xl" placeholder="Confirm Password *" name="password_confirm">
                        <div class="form-control-icon">
                            <i class="bi bi-shield-lock"></i>
                        </div>
                        <span class="input-group-text cursor-pointer toggle-password"><i class="bi bi-eye"></i></span>
                    </div>
                        <p>
                            The Password Must be a Minimum Of 8 Characters and a Maximum Of 32 Characters, 
                            Containing at Least One Uppercase Letter, One Lowercase Letter,
                            and One Special Character (e.g., !, @, #, $).
                        </p>
                    <!-- reCAPTCHA -->
                   <div class="col-12 recaptcha-block">
                                {{ view('common/recaptcha') }}
                        </div>
                    <!-- Terms & Conditions -->
                    <div class="form-check form-check-lg d-flex align-items-end">
                        <input class="form-check-input me-2" type="checkbox" id="remember-me" name="terms" value="1" checked>
                        <label class="form-check-label text-gray-600" for="flexCheckDefault">
                            You must agree to the privacy policy and terms.
                        </label>
                    </div>

                    <!-- Sign Up Button -->
                    <button class="btn btn-primary btn-block btn-lg shadow-lg mt-5">Sign Up</button>
                </form>

                <!-- Login Link -->
                <div class="text-center mt-1 mt-sm-2 text-lg fs-5">
                    <p class='text-gray-600'>Already have an account? <a href="company/auth/login" class="font-bold">Log in</a>.</p>
                </div>
            </div>
        </div>

        <!-- Right Section (Hidden on Small Devices) -->
        <div class="col-lg-7 d-none d-lg-block position-relative">
        <div class="common-home-img regi-page-img">
      <img src="./assets/img/sign-up.png" alt="image">
    </div>
            <div id="auth-right">
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!--<script>-->
    <!--    documentReady(function() {-->
    <!--        $('#ajax-form').validate({-->
    <!--            submitHandler: function(form) {-->
    <!--                app.ajaxForm(form, function(response) {-->
    <!--                    grecaptcha.reset();-->
    <!--                    if (response.status) {-->
    <!--                        window.location.href = response.url;-->
    <!--                    } else if (response.message) {-->
    <!--                        Swal.fire("Warning", response.message, "warning");-->
    <!--                    }-->
    <!--                });-->
    <!--            }-->
    <!--        });-->
    <!--    })-->
    <!--</script>-->
@endpush
