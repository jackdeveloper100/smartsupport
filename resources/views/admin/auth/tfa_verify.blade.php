@extends('admin.layouts.blank')
@section('title', 'Two Step Verification')
@section('content')
<div class="authentication-wrapper authentication-basic px-4 verify-page">
  <div class="authentication-inner pt -4 child-verify-div">
    <!-- Two Steps Verification -->
    <div class="card">
      <div class="card-body">
        <!-- Logo -->
        <div class="app-brand justify-content-center text-center mb-4 mt-2">
          <a href="admin/login" class="app-brand-link gap-2">
            <span class="app-brand-logo demo ">
              <img src="{{ $general->getFileUrl(config('setting.app_logo'),'setting') }}" class="brand-image img-circle elevation-3 preview-app-logo" style="height: 200%;"> 
            </span>
          </a>
        </div>
        <h4 class="mb-1 pt-2">Two Step Verification 💬</h4>
        <p class="text-start mb-4">
          We sent a verification code to your Email. Enter the code from the Email in the field below.
        </p>
        <p class="mb-0 fw-semibold">Type your 6-digit security code</p>
        
        {{ view('common/message_alert') }}
        <!-- OTP Form -->
        <form class="ajax-form" id="otp-form" action="{{ route('admin/auth/tfa-verify-process') }}" method="post">
          @csrf
          
          <input type="hidden" name="type" value="tfa" />
          <div class="mb-3 {{ $errors->has('otp') ? 'has-error' : '' }}">
            <label for="otp" class="form-label">Code</label>
            <input name="otp" type="number" class="form-control" required maxlength="6" minlength="6" autofocus />
          </div>

          <div class="mb-3">
            <div class="form-check ">
              <input class="form-check-input" type="checkbox" id="skip_tfa" name="skip_tfa" value="1" checked />
              <label class="form-check-label" for="skip_tfa">Ignore this device next time</label>
            </div>
          </div>

          <button type="submit" class="btn btn-primary d-grid w-100 mb-3">Submit</button>

          <div class="form-group mb-3">
            <a href="{{ route('admin/auth/logout') }}" class="btn btn-default d-grid w-100">Logout</a>
          </div>

          <div class="text-center">
            Didn't get the code?
            <a href="javascript:void(0)" onclick="sendOtp();" id="resend-otp-link">Resend</a>
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

  // Function to resend OTP
  function sendOtp() {
    app.ajaxRequest('{{ route("admin/auth/resend-otp") }}', $('#otp-form').serialize(), function(response) {
      if (response.status) {
        Swal.fire("", response.message, "success");
      } else {
        Swal.fire("Warning", response.message, "warning");
      }
    });
  }
</script>
@endpush