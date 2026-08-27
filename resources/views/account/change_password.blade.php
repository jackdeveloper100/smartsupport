@extends('layouts.main')
@section('title')
Change password
@endsection
@section('content')
@php
/** @var \App\Models\User|null $user */
$user = auth()->user();
@endphp
<style>
    .star{
        color: red;
    }
</style>

<div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Account Security</h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-sm-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="contractor/document" class="pjax">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Security</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

<div class="section">
    <div class="row">
          <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Change Password</h5>
                        </div>
                        <div class="card-body">
                        <form action="account/password-change-process" method="post" class="ajax-form">
                        {{ csrf_field() }}
                        <div class="row">
                            <div class="col-lg-6 col-sm-6 col-12">
                                <div class="form-group my-2">
                                    <label for="current_password" class="form-label">Current Password <span class="star">*</span></label>
                                    <div class="page-eye position-relative">
                                        <input class="form-control"  type="password" name="current_password" id="current_password" />
                                        <span class="input-group-text cursor-pointer toggle-password"><i class="bi bi-eye"></i></span>
                                    </div>
                                </div>
                            </div>
                            </div>
                            <div class="row">
                            <div class="col-lg-6 col-sm-6 col-12">
                                <div class="form-group my-2">
                                    <label for="password" class="form-label">New Password <span class="star">*</span></label>
                                    <div class="page-eye position-relative">
                                        <input class="form-control"  type="password" id="password" name="password" />
                                        <span class="input-group-text cursor-pointer toggle-password"><i class="bi bi-eye"></i></span>  
                                    </div>
                                </div>
                                </div>
                                <div class="col-lg-6 col-sm-6 col-12">
                                <div class="form-group my-2">
                                    <label for="confirm_password" class="form-label">Confirm Password <span class="star">*</span></label>
                                    <div class="page-eye position-relative">
                                        <input class="form-control"  type="password" name="confirm_password" id="confirm_password" />
                                        <span class="input-group-text cursor-pointer toggle-password"><i class="bi bi-eye"></i></span>  
                                    </div>
                                </div>
                                </div>
                                <div class="form-group my-2 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
            
             <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Two Factor Authentication</h5>
                </div>
                <div class="card-body">
                    @if($user && $user->status_tfa)
                    <b>Your Account Two Factor Authentication is Enabled</b><br><br>
                    <button
                        data-action="{{ route('account/tfa-status-change') }}"
                        onclick="app.confirmAction(this);"
                        class="noroute btn btn-primary text-white"
                        style="background-color:#685dd8;">
                        Disable
                    </button>
                    @else
                    <b>Your Account Two Factor Authentication is Disabled</b><br><br>
                    <button
                        data-action="{{ route('account/tfa-status-change') }}"
                        onclick="app.confirmAction(this);"
                        class="noroute btn btn-primary text-white"
                        style="background-color:#685dd8;">
                        Enable
                    </button>
                    @endif
                </div>
            </div>
        </div>
        
         <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Devices That Don't Need a Second Step</h5>
                    </div>
                    <div class="card-body">
                        <b>You Can Skip The Second Step on Devices You Trust, Such as Your Own Computer.</b><br><br>
                        <div class="d-flex flex-wrap" >
                            <div class="ms-0 ms-md-5 me-2 ">
                                <i>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-devices-check" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" style="margin-top: 25px;">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M13 15.5v-6.5a1 1 0 0 1 1 -1h6a1 1 0 0 1 1 1v4" />
                                        <path d="M18 8v-3a1 1 0 0 0 -1 -1h-13a1 1 0 0 0 -1 1v12a1 1 0 0 0 1 1h7" />
                                        <path d="M16 9h2" />
                                        <path d="M15 19l2 2l4 -4" />
                                    </svg>
                                </i>
                            </div>
                            <div class="" style="margin-bottom: 15px;margin-top: 8px;">
                                <b>Device You Trust</b><br>
                                <h6>Revoke Trusted Status From Your Device That Skips 2-Step Verification.</h6>
                                <button
                                    onclick="app.confirmAction(this);"
                                    data-action="{{ route('account/revoke-all') }}"
                                    class="btn btn-primary text-white"
                                    style="background-color: #685dd8;"
                                    title="Revoke All">
                                    Revoke All
                                </button>
                            </div>
                    </div>
                </div>
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
        })
    });
</script>
@endpush

