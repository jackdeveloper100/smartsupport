@extends('layouts.blank')
@section('title')
Login With Otp
@endsection
@section('content')
<style>
    .star{
        color: red;
    } 
</style>

<div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner py-4">
            <!-- Forgot Password -->
            <div class="card">
                <div class="card-body">
                    <!-- Logo -->
                    <div class="app-brand justify-content-center mb-4 mt-2">
                        <a href="home" class="app-brand-link gap-2">
                            <span class="app-brand-logo demo">
                                <img src="{{$general->getFileUrl(config('setting.app_logo'))}}" class="brand-image img-circle elevation-3 preview-app-logo text-center" style="height: 200%;">
                            </span>
                            <!--<span class="app-brand-text demo text-body fw-bold">{{ Config::get('setting.app_name') }}</span>-->
                        </a>
                    </div>
                    <!-- /Logo -->
                    <h4 class="mb-1 ">Login With Otp</h4>
                    <p class="mb-6" >Enter your email and we'll send you instructions to reset your password</p>
                    {{ view('common/message_alert') }}
                    <form class="ajax-form" action="{{ route('auth/login-otp-process') }}" method="POST">
                        {{ csrf_field() }}
                        <input type="hidden" name="step" id="step" value="1">
                        <div class="mb-6 email-block">
                            <label for="email" class="form-label">Email <span class="star">*</span></label>
                            <input type="email" required class="form-control" id="email" name="email" placeholder="Enter your email *" autofocus value="{{ old('email') }}" />
                            <div class="col-12 recaptcha-block">
                                {{view('common/recaptcha')}}
                            </div>
                        </div>
                        <div class="mb-6 otp-block" style="display: none;">
                            <label class="form-label">OTP <span class="star">*</span></label>
                            <input type="text" class="form-control" name="otp" placeholder="Enter your otp" autofocus value="" />
                        </div>
                        
                        <button type="submit" class="btn btn-primary d-grid w-100" >Send OTP</button>
                    </form>
                    <br>
                    <div class="text-center">
                        <a href="{{ route('login') }}" class="d-flex align-items-center justify-content-center">
                            <i class="fa fa-chevron-left scaleX-n1-rtl"></i>
                            Back to login
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Forgot Password -->
        </div>
    </div>
</div>
<!-- /.login-box -->
@endsection
@push('scripts')
<script>
    documentReady(function() {
        $('.ajax-form').validate({
            submitHandler: function(form) {
                app.ajaxForm(form,function(response){
                    if($('#step').val()=='1'){
                        try{
                            grecaptcha.reset();
                        }catch(e){}
                    }
                    if (response.status) {
                        if (response.message) {
                            Swal.fire("", response.message, "success").then(function() {
                                if(response.next=='step_2'){
                                    $('.otp-block').show();
                                    $('.email-block').hide();
                                    $('#step').val('2');
                                } if(response.next=='redirect'){
                                    window.location.href = response.url;
                                }
                            });
                        }
                    }else if (response.message) {
                        Swal.fire("Warning", response.message, "warning");
                    }
                });
            }
        });
    });
</script>
@endpush