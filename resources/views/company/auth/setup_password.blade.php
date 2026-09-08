@extends('company.layouts.blank')

@section('title')
Set Up Password
@endsection

@section('content')
    <style>
        .star {
            color: red;
        }
    </style>

    <div class="row main-log-pages">
        <div class="col-lg-5 col-12">
            <div id="auth-left">
                <div class="auth-logo">
                    <a href="{{ route('login') }}">
                        <img src="{{ $general->getFileUrl(config('setting.app_logo'), 'setting') }}" alt="logo">
                    </a>
                </div>

                <h1 class="auth-title">Set Up Password 🔒</h1>
                <p class="auth-subtitle mb-2 mb-sm-3">Welcome <strong>{{ $user->first_name }}</strong>! Create a password for your account.</p>

                <form class="ajax-form mb-0" action="{{ route('company/auth/setup-password-process') }}" method="POST" id="ajax-form">
                    {{ csrf_field() }}
                    <input type="hidden" name="token" value="{{ $token }}">

                    <!-- Password Block -->
                    <div class="form-group position-relative has-icon-left mb-4" id="password-block">
                        <label for="password" class="form-label">Password <span class="star">*</span></label>
                        <div class="position-relative mb-3">
                            <input type="password" id="password" class="form-control form-control-xl" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" />
                            <div class="form-control-icon">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                            <span class="input-group-text cursor-pointer toggle-password"><i class="bi bi-eye"></i></span>
                        </div>

                        <!-- Password Confirm Field -->
                        <label for="password_confirm" class="form-label">Confirm Password <span class="star">*</span></label>
                        <div class="position-relative">
                            <input type="password" id="password_confirm" class="form-control form-control-xl" name="password_confirm" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password_confirm" />
                            <div class="form-control-icon">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                            <span class="input-group-text cursor-pointer toggle-password"><i class="bi bi-eye"></i></span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button class="btn btn-primary btn-block btn-lg shadow-lg mt-4 mt-sm-3" type="submit">Set Up Password</button>
                </form>

                <div class="text-center mt-2 mt-sm-3 text-lg fs-5">
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
            errorPlacement: function (error, element) {}, // Prevent inline error DOM elements to preserve UI layout
            submitHandler: function(form) {
                app.ajaxForm(form, function(response) {
                    if (response.status) {
                        if (response.message) {
                            Swal.fire("Success", response.message, "success").then(function() {
                                if (response.next == 'redirect') {
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
    });
</script>
@endpush
