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
      <div class="card">
        <div class="card-body">
          <div class="app-brand justify-content-center mb-4 mt-2">
            <a href="index" class="app-brand-link gap-2">
              <span class="app-brand-logo demo">
                <img src="{{$general->getFileUrl(config('setting.app_logo'))}}" class="brand-image img-circle elevation-3 preview-app-logo" style="height: 200%;">
              </span>
              <!--<span class="app-brand-text demo text-body fw-bold">{{ Config::get('setting.app_name') }}</span>-->
            </a>
          </div>
          <h4 class="mb-1 pt-2">Forgot Password With Otp</h4>
          <p class="mb-4">Enter your email and we'll send you instructions to reset your password</p>
          {{ view('common/message_alert') }}
          <form action="{{ route('admin/auth/forgot-otp-verify-process') }}" class="ajax-form" method="POST">
            {{ csrf_field() }}
            <input type="hidden" name="code" value="{{Request::get('code')}}">
            <div class="mb-3">
              <label for="email" class="form-label">Code <span class="star">*</span></label>
              <input name="otp" type="otp" class="form-control" required maxlength="6" minlength="6" autofocus />
            </div>
            <div class="form-group mb-8">
              <button type="submit" class="btn btn-primary d-grid w-100">Submit</button>  
            </div>
            <div class="text-center">
              Didn't get the code?
              <a class="noroute" href="javascript::void()" data-action="{{route('auth/otp-LoginSend-OTP')}}" data-id="{{Request::get('code')}}" onclick="app.confirmAction(this);"> Resend </a>
              </div>
          </div>
        <div class="row">
          <br>
          <div class="text-center">
            <a href="{{ route('admin/auth/login') }}" class="d-flex align-items-center justify-content-center">
              <i class="fa fa-chevron-left scaleX-n1-rtl"></i>
              Back to login
            </a>
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
<script>
  documentReady(function() {
    $('.ajax-form').validate();
  })
</script>
@endpush