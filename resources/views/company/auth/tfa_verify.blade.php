@extends('company.layouts.blank')
@section('title')
Company Verify Process
@endsection
@section('content')

<?php $code = isset($_GET['code']) ? $_GET['code'] : ''; ?>
<?php $token = isset($_GET['token']) ? $_GET['token'] : ''; ?>
<?php $type = isset($_GET['type']) ? $_GET['type'] : '' ?>

<div class="authentication-wrapper authentication-basic px-4 verify-page">
  <div class="authentication-inner pt-4 child-verify-div">
    <!--  Two Steps Verification -->
    <div class="card">
      <div class="card-body">
        <!-- Logo -->
        <div class="app-brand text-center mb-4 mt-2">
          <a class="app-brand-link gap-2">
            <span class="app-brand-logo demo">
              <img src="{{$general->getFileUrl(config('setting.app_logo'),'setting')}}" class="brand-image img-circle elevation-3 preview-app-logo text-center" style="height: 200%;">
            </span>
            <!--<span class="app-brand-text demo text-body fw-bold ms-1">{{ Config::get('setting.app_name') }}</span>-->
          </a>
        </div>
        <!-- /Logo -->
        <h4 class="mb-1 pt-2 text-center">
          @if($type=='email')
          Verify Your Email
          @elseif($type=='tfa')
          Two Step Verification 💬
          @endIf
        </h4>
        <p class="text-start mb-4 text-center">
          OTP is Sent on Your Email Address.
        </p>
        {{ view('common/message_alert') }}
        <p class="mb-0 fw-semibold">Type Your 6 Digit OTP</p>
        <form class="ajax-form" action="{{route('company/auth/tfa-verify-process')}}" method="post">
          {{ csrf_field() }}
  
          <input type="hidden" name="type" value="{{$type}}">
          <input type="hidden" name="code" value="{{@$code}}">
          <input type="hidden" name="token" value="{{@$token}}">
          
          <div class="mb-3">
            <label class="form-label">OTP <span class="star">*</span></label>
            <input name="otp" type="text" class="form-control" autofocus />
          </div>
          @if($type=='tfa')
          <div class="mb-3">
              <div class="form-check" >
                <input class="form-check-input" type="checkbox" id="skip_tfa" name="skip_tfa" value="1" checked />
                <label class="form-check-label" for="skip_tfa" style="padding-right: 95px;"> Ignore this device next time </label>
              </div>
            </div>
            @endIf
          <button class="btn btn-primary d-grid w-100 mb-3">Submit</button>
          @if($type=='tfa')
          <div class="form-group mb-8">
              <a href="{{route('company/auth/logout')}}" class="btn btn-default d-grid w-100 noroute">Logout</a>
            </div>
            @endIf
          <div class="text-center">
            Didn't get the code?
            <a href="javascript:void(0)" onclick="resendOtp()" id="resend-otp-link">Resend</a>
          </div>
        </form>
      </div>
    </div>
    <!-- / Two Steps Verification -->
  </div>
</div>
@endsection
@push('scripts')
<script type="text/javascript">
  documentReady(function() {
    $('.ajax-form').validate({
      submitHandler: function(form) {
          app.ajaxForm(form);
      }
    });
  });
  function resendOtp(){
    app.ajaxPost('{{ route('company/auth/resend-otp') }}',{type:'{{$type}}',code:'{{@$code}}',token:'{{@$token}}'})
  }
</script>
@endpush
