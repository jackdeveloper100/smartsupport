    @extends('layouts.blank')
    @section('title')
    Forgot Password
    @endsection
    @section('content')
        <style>
            .star {
                color: red;
            }
        </style>
    
        <div class="row  main-log-pages">
            <div class="col-lg-5 col-12">
                <div id="auth-left">
                    <div class="auth-logo">
                        <a href="">
                            <img src="{{$general->getFileUrl(config('setting.app_logo'),'setting')}}">
                        </a>
                    </div>
    
                    <h1 class="auth-title">Forgot Password? 🔒 </h1>
                    <p class="auth-subtitle mb-2 mb-sm-3">Enter your email and we'll send you instructions to reset your password.</p>
    
                    <form class="ajax-form mb-0" action="{{ route('auth/password-forgot-process') }}" method="POST" id="ajax-form" >
                        {{ csrf_field() }}
                        <input type="hidden" name="step" id="step" value="1">
    
                        <!-- Email Block -->
                        <div class="form-group position-relative has-icon-left mb-4 email-block" id="email-block">
                            <input type="text" class="form-control form-control-xl" name="email" id="email" placeholder="Email *">
                            <div class="form-control-icon">
                                <i class="bi bi-envelope"></i>
                            </div>
                            <div class="col-12 recaptcha-block">
                                {{ view('common/recaptcha') }}
                            </div>
                        </div>
                     
                        <!-- OTP Block -->
                        <div class="form-group position-relative has-icon-left mb-4 otp-block" style="display:none;" id="otp-block">
                            <input type="text" class="form-control form-control-xl" name="otp" placeholder="OTP">
                            <div class="form-control-icon">
                                <i class="bi bi-envelope"></i>
                            </div>
                            <div class="text-center mt-5 text-lg fs-4">
                                <p class='text-gray-600'>
                                    Didn't get the code?
                                    <a href="javascript:void(0)" id="resend-otp-link" class="font-bold">Resend</a>.
                                </p>
                            </div>
                        </div>
    
                        <!-- Password Block -->
                       <div class="form-group position-relative has-icon-left mb-4 password-block" style="display:none;" id="password-block">
                           <label for="password" class="form-label">Password</label>
                                <div class="position-relative">
                                    <input type="password" id="password" class="form-control form-control-xl mb-2" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" />
                                    <div class="form-control-icon">
                                        <i class="bi bi-shield-lock"></i>
                                    </div>
                                    <span class="input-group-text cursor-pointer toggle-password"><i class="bi bi-eye"></i></span>
                                </div>

                            <!-- Password Confirm Field -->
                            <label for="password_confirm" class="form-label">Confirm Password</label>
                            <div class="position-relative">
                                <input type="password" id="password_confirm" class="form-control form-control-xl" name="password_confirm" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password_confirm" />
                                <div class="form-control-icon">
                                    <i class="bi bi-shield-lock"></i>
                                </div>
                                <span class="input-group-text cursor-pointer toggle-password"><i class="bi bi-eye"></i></span>
                            </div>
                        </div>
    
                        <!-- Submit Button -->
                        <button class="btn btn-primary btn-block btn-lg shadow-lg mt-4 mt-sm-3" type="submit">Reset Password</button>
                    </form>
    
                    <div class="text-center mt-2 mt-sm-2 text-lg fs-5">
                        <p class='text-gray-600'>
                            Remember your account? 
                            <a href="{{ route('login') }}" class="font-bold">Log in</a>.
                        </p>
                    </div>
                </div>
        </div>
    
        <div class="col-lg-7 d-none d-lg-block position-relative">
            <div class="common-home-img regi-page-img">
                 <img src="./assets/img/forgot-pwd-img.png" alt="image">
            </div>
                <div id="auth-right"> </div>
            </div>
        </div>
    @endsection
    
    @push('scripts')
    <script type="text/javascript">
        documentReady(function() {
            $('#ajax-form').validate({
                submitHandler: function(form) {
                    app.ajaxForm(form, function(response) {
                        if ($('#step').val() == '1') {
                            try {
                                grecaptcha.reset();
                            } catch (e) {}
                        }
    
                        if (response.status) {
                            if (response.message) {
                                Swal.fire("", response.message, "success").then(function() {
                                    if (response.next == 'step_2') {
                                        $('#otp-block').show();
                                        $('#email-block').hide();
                                        $('#step').val('2');
                                    } else if (response.next == 'step_3') {
                                        $('#password-block').show();
                                        $('#otp-block').hide();
                                        $('#step').val('3');
                                    } else if (response.next == 'redirect') {
                                        window.location.href = response.url;
                                    }
                                });
                            }
                        } else if (response.message) {
                            Swal.fire("Warning", response.message, "warning");
                        }
                    });
                }
            });
    
            $('#resend-otp-link').on('click', function() {
                var email = $('input[name="email"]').val(); 
                resendOtp(email);
            });
        });
    
        function resendOtp(email) {
            app.ajaxPost('{{ route('auth/resend-otp') }}', {type: 'forgot_password', code: btoa(email), token: btoa(email)});
        }
    </script>
    @endpush
