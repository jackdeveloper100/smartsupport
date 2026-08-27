@extends('admin.layouts.blank')
@section('title')
Forgot Password 
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
                                <img src="{{$general->getFileUrl(config('setting.app_logo'))}}" class="brand-image img-circle elevation-3 preview-app-logo" style="height: 200%;">
                            </span>
                            <!--<span class="app-brand-text demo text-body fw-bold">{{ Config::get('setting.app_name') }}</span>-->
                        </a>
                    </div>
                    <!-- /Logo -->
                    <h4 class="mb-1 pt-2" style="text-align: center;">Froget Password With OTP </h4>
                    <p class="mb-4" style="text-align: center;">Enter your email and we'll send you OTP for reset your password</p>
                    {{ view('common/message_alert') }}
                    <form class="ajax-form" action="{{ route('admin/auth/forgot_otp-login-process') }}" method="POST">
                        {{ csrf_field() }}
                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="star">*</span></label>
                            <input type="email" required class="form-control" id="email" name="email" placeholder="Enter your email" autofocus value="{{ old('email') }}" />
                        </div>
                        <button type="submit" class="btn btn-primary d-grid w-100" >Send OTP</button>
                    </form>
                    <br>
                    <div class="text-center">
                        <a href="{{ route('admin/auth/login') }}" class="d-flex align-items-center justify-content-center">
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
        $('.ajax-form').validate();
    });
</script>
@endpush<?php 


?>