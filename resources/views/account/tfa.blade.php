@extends('layouts.main')
@section('title', 'Profile')
@section('content')
@php
/** @var \App\Models\User|null $user */
$user = auth()->user();
@endphp
<div class="row">
    <div class="col-md-12">
        <div class="row">
            @include('account/account_block')
        </div>
        <div class="col-lg-12 col-md-12">
            <div class="edit-profile_wrapper">
                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <h5>Two Factor Authentication</h5>
                        @if($user && $user->getData()->status_tfa)
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

                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <h5>Devices That Don't Need a Second Step</h5>
                        <b>You can skip the second step on devices you trust, such as your own computer.</b><br><br>
                        <div class="row" style="border-style: outset; border-radius: 15px;">
                            <div class="col-md-1">
                                <i>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-devices-check" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 50px;margin-top: 25px;">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M13 15.5v-6.5a1 1 0 0 1 1 -1h6a1 1 0 0 1 1 1v4" />
                                        <path d="M18 8v-3a1 1 0 0 0 -1 -1h-13a1 1 0 0 0 -1 1v12a1 1 0 0 0 1 1h7" />
                                        <path d="M16 9h2" />
                                        <path d="M15 19l2 2l4 -4" />
                                    </svg>
                                </i>
                            </div>
                            <div class="col-md-11" style="margin-bottom: 15px;margin-top: 8px;">
                                <b>Device You Trust</b><br>
                                <h6>Revoke trusted status from your device that skips 2-Step Verification.</h6>
                                <button
                                    onclick="app.confirmAction(this);"
                                    data-action="{{ route('account/revoke-all') }}"
                                    class="btn btn-primary text-white"
                                    style="background-color: #685dd8;"
                                    title="Revoke All">
                                    REVOKE ALL
                                </button>
                            </div>
                        </div>
                    </div>
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
        });
    });
</script>
@endpush

