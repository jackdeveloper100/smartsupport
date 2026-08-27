@extends('company.layouts.blank')
@section('title')
Login To Your Account
@endsection
@section('content')
<!-- /.login-box -->
<style>
    .star{
        color: red;
    }
    .authentication-inner {
    max-width: 460px;
    margin: auto;
   }
</style>

<div class="row main-log-pages">
  <div class="col-lg-5 col-12">
    <div id="auth-left">
      <div class="auth-logo">
        <a href="company/login"><img src="{{$general->getFileUrl(config('setting.app_logo'),'setting')}}" alt="Logo"></a>
      </div>
      <h1 class="auth-title">Welcome to {{ Config::get('setting.app_name') }}</h1>

      <p class="auth-subtitle mb-2 mb-sm-4">Please Log-in to Your Account</p>
        <form id="ajax-form" class="mb-3" action="{{ route('company/auth/login-process') }}" method="POST">
              {{ csrf_field() }}

                  <div class="form-group position-relative has-icon-left mb-4">
                      <input type="email" class="form-control form-control-xl" id="email" name="email" placeholder="Enter your email *" autofocus />
                      <div class="form-control-icon">
                            <i class="bi bi-person"></i>
                        </div>
                        <label id="email-error" class="error text-danger inner-error-msg" for="email"></label>
                  </div>

                  <div class="form-group position-relative has-icon-left mb-4 custom-input-box">
                      <input type="password" id="password" class="form-control form-control-xl" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" />      
                        <div class="form-control-icon">
                           <i class="bi bi-shield-lock"></i>
                        </div>
                        <span class="input-group-text cursor-pointer toggle-password"><i class="bi bi-eye"></i></span>
                        <label id="password-error" class="error text-danger inner-error-msg" for="password"></label>
                  </div>

              <!-- Error Message div: Hidden by default -->
              <div class="error-msg bottom-error-msg" style="display:none;">
                  <div class="error text-danger" id="error-message"></div>
              </div>
              
              
                <!-- Email Verification Message -->
               <div class="verify-msg bottom-large-msg" style="display: none;">
                    <div class="error text-dark" id="verify-message"></div>
                </div>

               @if(session('error'))
                    <div class="error text-danger pb-1 session-error">
                        {{ session('error') }}
                    </div>
                @endif
                
              @if(session('success'))
                    <div class="success text-success pb-1 session-error">
                        {{ session('success') }}
                    </div>
                @endif
                
              <div class="form-check form-check-lg d-flex align-items-end pt-1">
                    <input class="form-check-input me-2" type="checkbox" id="remember-me" name="remember" value="1" checked >
                    <label class="form-check-label text-gray-600" for="flexCheckDefault">
                        Remember Me
                    </label>
                </div>
                  <button id="top-center" class="btn btn-primary btn-block btn-lg shadow-lg mt-4 mt-sm-3" type="submit" >Log in</button>
          </form>
          <div class="text-center mt-2 mt-sm-2 text-lg fs-5">
          <p class="text-gray-600 mb-2 mb-sm-2">Don't have an account? <a href="company/account/register" class="font-bold">Sign
                        up</a>.</p>
                <p class="mb-0"><a class="font-bold" href="company/auth/password-forgot">Forgot password?</a>.</p>
          </div>
          
           </div>
      </div>
  <div class="col-lg-7 d-none d-lg-block position-relative">
  <div class="common-home-img">
      <img src="./assets/img/login-img.png" alt="image">
    </div>
        <div id="auth-right">

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>

    // Set CSRF token in AJAX headers globally
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        xhrFields: {
            withCredentials: true
        }
    });


    documentReady(function() {
    $('#email, #password').on('input', function () {
        $('#error-message').html('');
        $('.error-msg').hide();

        $('#verify-message').html('');
        $('.verify-msg').hide();
        
        $('.session-error').remove(); 
    });

    $('#ajax-form').validate({
        rules: {
            email: {
                required: true,
                email: true
            },
            password: {
                required: true,
                minlength: 6,
                maxlength: 32
            }
        },
        messages: {
            email: {
                required: "Please Enter Your Email.",
                email: "Please enter a valid email address."
            },
            password: {
                required: "Please Enter Your Password",
                minlength: "Please enter at least 6 characters.",
                maxlength: "Please enter no more than 32 characters."
            }
        },

        invalidHandler: function () {
            $('#error-message').html('');
            $('.error-msg').hide();
            $('#verify-message').html('');
            $('.verify-msg').hide();
        },

        submitHandler: function (form) {
            $('#email-error, #password-error').html('');
            $('#error-message, #verify-message').html('');
            $('.error-msg, .verify-msg').hide();

            $.ajax({
                type: 'POST',
                url: $(form).attr('action'),
                data: $(form).serialize(),
                success: function (response) {
                    if (response.status === 1) {
                        localStorage.setItem('success_message', response.message);
                        window.location.href = 'company/dashboard';
                    } else if (response.status === 2) {
                        $('#verify-message').html(response.message);
                        $('.verify-msg').show();
                    } else {
                        $('#error-message').html(response.message);
                        $('.error-msg').show();
                    }
                },
                error: function () {
                    $('#error-message').html('An error occurred. Please try again.');
                    $('.error-msg').show();
                }
            });

            return false; // Prevent actual form submission
        }
    });
});

</script>
@endpush
