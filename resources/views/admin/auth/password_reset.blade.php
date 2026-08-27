@extends('admin.layouts.blank')
@section('title')
Reset Password
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
            <!-- Reset Password -->
            <div class="card">
                <div class="card-body">
                    <!-- Logo -->
                    <div class="app-brand justify-content-center mb-4 mt-2">
                        <a href="auth/password-reset" class="app-brand-link gap-2">
                            <span class="app-brand-logo demo">
                            <img src="{{$general->getFileUrl(config('setting.app_logo'))}}" class="brand-image img-circle elevation-3 preview-app-logo" style="height: 200%;">
                            </span>
                            <!--<span class="app-brand-text demo text-body fw-bold ms-1">{{ Config::get('setting.app_name') }}</span>-->
                        </a>
                    </div>
                    <!-- /Logo -->
                    <h4 class="mb-1 pt-2" style="text-align: center;">Reset Password <span class="star"></span></h4>
                    {{ view('common/message_alert')}}
                    <form id="formAuthentication" action="admin/auth/password-reset-process" method="POST">
                        {{ csrf_field() }}
                        <input type="hidden" name="code" value="{{ $code }}">
                        <div class="mb-3 form-password-toggle">
                            <label class="form-label" for="password">New Password <span class="star">*</span></label>
                            <div class="input-group input-group-merge" {{$errors->has('password')?'has-error':''}}>
                                <input type="password" id="password" class="form-control" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" />
                                <span class="input-group-text cursor-pointer"><i class="fa fa-eye-off"></i></span>
                            </div>
                        </div>
                        <div class="mb-3 form-password-toggle">
                            <label class="form-label" for="confirm-password">Confirm Password <span class="star">*</span></label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="password_confirm" class="form-control" name="password_confirmation" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" />
                                <span class="input-group-text cursor-pointer"><i class="fa fa-eye-off"></i></span>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary d-grid w-100 mb-3">Set new password <span class="star"></span></button>
                        <div class="text-center">
                            <a href="{{route('admin/auth/login')}}">
                                <i class="fa fa-chevron-left scaleX-n1-rtl"></i>
                                Back to login
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            <!-- /Reset Password -->
        </div>
    </div>
</div>
<!-- /.login-box -->
@endsection
@push('scripts')
<script>
    documentReady(function() {
        $('.ajax-form').validate({
            rules: {
                password: {
                    minlength: 6,
                    maxlength: 32,
                    required: true,
                },
                password_confirm:{
                    equalTo:'#password'
                }
            },
            messages: {
                password: {
                    required: "Please Enter Your Password",
                    minlength: "Please enter at least 6 characters.",
                    maxlength: "Please enter no more than 32 characters.",
                }
            },
        })
    })
</script>
@endpush