@extends('admin.layouts.main')
@section('title')
    Setting Update
@endsection
@section('content')
    
   <?php 
    $timezone = config('app.timezone');  
    $date = new DateTime('now', new DateTimeZone($timezone));   
    $formattedDate = $date->format('d-m-Y h:i A'); 
    $formattedDate1 = $date->format('Y-m-d h:i A');
    $formattedDate2 = $date->format('m-d-Y h:i A');
?>
    <div class="page-title">
    <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
            <h3>Setting</h3>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-sm-end">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="admin/dashboard" class="pjax">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Setting</li>
                </ol>
            </nav>
        </div>
    </div>
</div>
    <div class="content-wrapper">
        <!-- Content -->
        <!-- Tabs -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                        <h5 class="card-title">Setting Update</h5>
                        <form action="{{ route('admin/setting/cache-clear') }}"
                            style="float: inline-end;">
                            <button type="submit" class="btn btn-primary">
                                Clear Cache
                            </button>
                        </form>
                    </div>
                    <div>
                        <div class="card-body">
                            <ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                                        data-bs-target="#navs-top-general" aria-controls="navs-top-general"
                                        aria-selected="true">
                                        General
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                        data-bs-target="#navs-top-mail" aria-controls="navs-top-mail" aria-selected="false">
                                        Mail
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                        data-bs-target="#navs-top-logo" aria-controls="navs-top-logo" aria-selected="false">
                                        Logo
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                        data-bs-target="#navs-top-recaptcha" aria-controls="navs-top-recaptcha"
                                        aria-selected="false">
                                        Google Recaptcha
                                    </button>
                                </li>
                                 <li class="nav-item">
                                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                        data-bs-target="#navs-top-payment" aria-controls="navs-top-payment"
                                        aria-selected="false">
                                        Payment
                                    </button>
                                </li>
                                <!--<li class="nav-item" role="presentation">-->
                                <!--    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"-->
                                <!--        data-bs-target="#navs-top-login" aria-controls="navs-top-login"-->
                                <!--        aria-selected="false">-->
                                <!--        Social Login-->
                                <!--    </button>-->
                                <!--</li>-->
                                <!--<li class="nav-item" role="presentation">-->
                                <!--    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"-->
                                <!--        data-bs-target="#navs-top-content" aria-controls="navs-top-content"-->
                                <!--        aria-selected="false">-->
                                <!--        Content-->
                                <!--    </button>-->
                                <!--</li>-->
                                <!--<li class="nav-item" role="presentation">-->
                                <!--    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"-->
                                <!--        data-bs-target="#navs-top-notification" aria-controls="navs-top-notification"-->
                                <!--        aria-selected="false">-->
                                <!--        Notification-->
                                <!--    </button>-->
                                <!--</li>-->
                            </ul>
                            <div class="tab-content" id="myTabContent">
                                <div class="tab-pane fade show active" id="navs-top-general" role="tabpanel"
                                    aria-labelledby="navs-top-general">
                                    <form action="{{ route('admin/setting/save') }}" class="ajax-form mt-2" method="post">
                                        {{ csrf_field() }}
                                        <div class="form-row row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="body">Admin Contact Email <span
                                                            class="star">*</span></label>
                                                        <input type="email" class="form-control"
                                                            placeholder="Admin Contact Email" name="admin_email"
                                                            value="{{ config('setting.admin_email') }}"  />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="body">Admin Contact Number <span
                                                            class="star">*</span></label>
                                                        <input type="tel" class="form-control"
                                                            placeholder="Admin Contact Number" name="admin_phone"
                                                            value="{{ config('setting.admin_phone') }}"  />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="body">Date Format </label>
                                                    <select class="form-select"
                                                        value="{{ config('setting.date_format') }}" name="date_format">
                                                        <option value="Y-m-d"
                                                            <?= config('setting.date_format') == 'Y-m-d' ? 'selected' : '' ?>>
                                                            {{ date('Y-m-d') }}</option>
                                                        <option value="d-m-Y"
                                                            <?= config('setting.date_format') == 'd-m-Y' ? 'selected' : '' ?>>
                                                            {{ date('d-m-Y') }}</option>
                                                        <option value="m-d-Y"
                                                            <?= config('setting.date_format') == 'm-d-Y' ? 'selected' : '' ?>>
                                                            {{ date('m-d-Y') }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="body">Timezone </label>
                                                    <select class="form-select" value="{{ config('setting.timezone') }}"
                                                        name="timezone" id="timezone'">
                                                        @foreach ($timezonelist as $timezone)
                                                            <option value="{{ $timezone }}"
                                                                <?= config('app.timezone') == $timezone ? 'selected' : '' ?>>
                                                                {{ $timezone }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="body">Date Time Format </label>
                                                    <select class="form-select"
                                                        value="{{ config('setting.date_time_format') }}"
                                                        name="date_time_format">
                                                        <option value="Y-m-d h:i A"
                                                            <?= config('setting.date_time_format') == 'Y-m-d h:i A' ? 'selected' : '' ?>>
                                                            {{ $formattedDate1 }}</option>
                                                                                            <option value="d-m-Y h:i A"
                                        <?= config('setting.date_time_format') == 'd-m-Y h:i A' ? 'selected' : '' ?>>
                                        {{ $formattedDate }}
                                    </option>
                                                        <option value="m-d-Y h:i A"
                                                            <?= config('setting.date_time_format') == 'm-d-Y h:i A' ? 'selected' : '' ?>>
                                                            {{ $formattedDate2 }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <!--<div class="col-md-6">-->
                                            <!--    <div class="form-group">-->
                                            <!--        <label class="body">Login With OTP <span class="star">*</span></label>-->
                                            <!--        <select class="form-select"-->
                                            <!--            value="{{ config('setting.user_login_with_otp') }}"-->
                                            <!--            name="user_login_with_otp">-->
                                            <!--            <option value="1"-->
                                                            <!--<?= config('setting.user_login_with_otp') == '1' ? 'selected' : '' ?>>-->
                                            <!--                Enable</option>-->
                                            <!--            <option value="0"-->
                                            <!--                <?= config('setting.user_login_with_otp') == '0' ? 'selected' : '' ?>>-->
                                            <!--                Disable</option>-->
                                            <!--        </select>-->
                                            <!--    </div>-->
                                            <!--</div>-->
                                            <!--<div class="col-md-6">-->
                                            <!--    <div class="form-group">-->
                                            <!--        <label class="body">Email Verify <span class="star">*</span></label>-->
                                            <!--        <select class="form-select"-->
                                            <!--            value="{{ config('setting.user_email_verify') }}"-->
                                            <!--            name="user_email_verify">-->
                                            <!--            <option value="1"-->
                                            <!--                <?= config('setting.user_email_verify') == '1' ? 'selected' : '' ?>>-->
                                            <!--                Enable</option>-->
                                            <!--            <option value="0"-->
                                            <!--                <?= config('setting.use   r_email_verify') == '0' ? 'selected' : '' ?>>-->
                                            <!--                Disable</option>-->
                                            <!--        </select>-->
                                            <!--    </div>-->
                                            <!--</div>-->
                                            <!--<div class="col-md-6">-->
                                            <!--    <div class="form-group">-->
                                            <!--        <label class="body">Cookie Consent <span class="star">*</span></label>-->
                                            <!--        <select class="form-select"-->
                                            <!--            value="{{ config('setting.cookie_consent') }}"-->
                                            <!--            name="cookie_consent">-->
                                            <!--            <option value="1"-->
                                            <!--                <?= config('setting.cookie_consent') == '1' ? 'selected' : '' ?>>-->
                                            <!--                Enable</option>-->
                                            <!--            <option value="0"-->
                                            <!--                <?= config('setting.cookie_consent') == '0' ? 'selected' : '' ?>>-->
                                            <!--                Disable</option>-->
                                            <!--        </select>-->
                                            <!--    </div>-->
                                            <!--</div>-->
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn-primary">Submit</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="tab-pane fade" id="navs-top-mail" role="tabpanel">
                                    <form action="{{ route('admin/setting/smtp-save') }}" class="ajax-form1 mt-2" method="post">
                                        {{ csrf_field() }}
                                        <div class="form-row row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="body">Host <span class="star">*</span></label>
                                                        <input type="text" class="form-control" placeholder="Host"
                                                            name="host" value="{{ config('mail.mailers.smtp.host') }}"
                                                             />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="body">Encryption </label>
                                                    <select class="form-select"
                                                        value="{{ config('mail.mailers.smtp.encryption') }}" name="encryption">
                                                        <option value="ssl"
                                                            <?= config('mail.mailers.smtp.encryption') == 'ssl' ? 'selected' : '' ?>>
                                                            SSL</option>
                                                        <option value="tls"
                                                            <?= config('mail.mailers.smtp.encryption') == 'tls' ? 'selected' : '' ?>>
                                                            TLS</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="body">Port <span class="star">*</span></label>
                                                        <input type="text" class="form-control" captcha="Port"
                                                            name="port" value="{{ config('mail.mailers.smtp.port') }}"
                                                             />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="body">Username <span class="star">*</span></label>
                                                        <input type="text" class="form-control" placeholder="Username"
                                                            name="username"
                                                            value="{{ config('mail.mailers.smtp.username') }}"  />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="body">Password <span class="star">*</span></label>
                                                        <input type="text" class="form-control" placeholder="Password"
                                                            name="password"
                                                            value="{{ config('mail.mailers.smtp.password') }}"  />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="body">Mail From Name <span
                                                            class="star">*</span></label>
                                                        <input type="text" class="form-control"
                                                            placeholder="Mail From Name" name="mail_from_name"
                                                            value="{{ config('setting.mail_from_name') }}"  />
                                                </div>
                                            </div>
                                         
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="body">Mail From Address <span
                                                            class="star">*</span></label>
                                                        <input type="text" class="form-control"
                                                            placeholder="Mail From Address" name="mail_from_address"
                                                            value="{{ config('setting.mail_from_address') }}"  />
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group form-submail  d-flex gap-2 align-items-center flex-wrap">
                                                    <button type="submit" class="btn btn-primary">Submit</button>
                                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                                        data-bs-target="#email-test">Send Test Email</button>
                                                </div>
                                            </div>

                                        </div>
                                    </form>
                                </div>
                                <div class="tab-pane fade" id="navs-top-logo" role="tabpanel">
                                    <div class="row" style="margin-left:0%">
                                        <div class="col-lg-6 col-md-6 col-12">
                                            <form action="{{ route('admin/setting/save-file') }}" id="file-form" class="ajax-file-form"
                                                method="post" enctype="multipart/form-data">
                                                {{ csrf_field() }}
                                                <input type="hidden" name="key" value="app_logo">
                                                <div class="form-row row">
                                                    <div class="col-md-12">
                                                        <div id="ajax-content">
                                                            <div class="col-sm-8 col-md-8 col-lg-8">
                                                                <label class="body">App Logo <span
                                                                        class="star">*</span></label>
                                                                <div class="logo-box">
                                                                    <div class="mb-3 logo-imgs app-logo-img">
                                                                        <img class="card-img-top preview-app-logo"
                                                                            src="{{ $general->getFileUrl(config('setting.app_logo'),'setting') }}"
                                                                            alt="Card image cap" />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-9 col-md-12 col-12">
                                                                <div class="mb-3">
                                                                    <div class="input-group input-group-merge">
                                                                        <input type="file" 
                                                                            name="app_logo"
                                                                            onchange="previewImage(this,'.preview-app-logo')"
                                                                            class="form-control" accept="image/*"
                                                                            id="basic-default-upload-file">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <button type="submit" class="btn btn-primary">Submit</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-12">
                                            <form action="{{ route('admin/setting/save-file') }}" id="file-form-1" class="ajax-file-form1"
                                                method="post" enctype="multipart/form-data">
                                                {{ csrf_field() }}
                                                <input type="hidden" name="key" value="app_fevicon">
                                                <div class="form-row row">
                                                    <div class="col-md-12">
                                                        <div id="ajax-content">
                                                            <div class="col-md-6">
                                                                <div>
                                                                    <label class="body">App Favicon <span
                                                                            class="star">*</span></label>
                                                                    <div class="col-sm-6 col-lg-4">
                                                                        <div class="mb-3 logo-imgs">
                                                                            <img class="card-img-top preview-app-fevicon"
                                                                                src="{{ $general->getFileUrl(config('setting.app_fevicon'),'setting') }}"
                                                                                alt="Card image cap" />
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-9 col-md-12 col-12">
                                                                <div class="mb-3">
                                                                    <div class="input-group input-group-merge">
                                                                        <input type="file" name="app_fevicon"
                                                                            onchange="previewImage(this,'.preview-app-fevicon')"
                                                                            class="form-control" accept="image/*"
                                                                            id="input-app-fevicon">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <button type="submit" class="btn btn-primary">Submit</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="navs-top-recaptcha" role="tabpanel">
                                    <form action="{{ route('admin/setting/captcha') }}" class="ajax-form2 mt-2" method="post">
                                        {{ csrf_field() }}
                                        <div class="form-row row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="body">Enable </label>
                                                    <select class="form-select"
                                                        value="{{ config('setting.google_recaptcha') }}"
                                                        name="google_recaptcha">
                                                        <option value="1"
                                                            <?= config('setting.google_recaptcha') == '1' ? 'selected' : '' ?>>Yes</option>
                                                        <option value="0"
                                                            <?= config('setting.google_recaptcha') == '0' ? 'selected' : '' ?>>No</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="body">Secret key <span class="star">*</span></label>
                                                        <input type="text" class="form-control" 
                                                            value="{{ config('setting.google_recaptcha_secret_key') }}"
                                                            name="google_recaptcha_secret_key"
                                                            placeholder="google_recaptcha_secret_key">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="body">Public key <span class="star">*</span></label>
                                                        <input type="text" class="form-control" 
                                                            value="{{ config('setting.google_recaptcha_public_key') }}"
                                                            name="google_recaptcha_public_key"
                                                            placeholder="google_recaptcha_public_key">
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn-primary">Submit</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="tab-pane fade" id="navs-top-login" role="tabpanel">
                                    <form action="{{ route('admin/setting/social') }}" class="ajax-form3 mt-2" method="post">
                                        {{ csrf_field() }}
                                        <div class="form-row row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="body">Google Login </label>
                                                    <select class="form-select"
                                                        value="{{ config('setting.google_login') }}" name="google_login">
                                                        <option value="1"
                                                            <?= config('setting.google_login') == '1' ? 'selected' : '' ?>>
                                                            Enable</option>
                                                        <option value="0"
                                                            <?= config('setting.google_login') == '0' ? 'selected' : '' ?>>
                                                            Disable</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="body">Google Client ID <span
                                                            class="star">*</span></label>
                                                        <input type="text" class="form-control" 
                                                            value="<?= $setting['services.google_client_id'] ?>"
                                                            name="google.client_id" placeholder="google.client_id" >
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="body">Google Client Secreat <span
                                                            class="star">*</span></label>
                                                        <input type="text" class="form-control" 
                                                            value="<?= $setting['services.google_client_secret'] ?>"
                                                            name="google.client_secret"
                                                            placeholder="google.client_secret">
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn-primary">Submit</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="tab-pane fade" id="navs-top-content" role="tabpanel">
                                    <form action="{{ route('admin/setting/content') }}" class="ajax-form4 mt-2" method="post">
                                        {{ csrf_field() }}
                                        <div class="form-row row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label">Header <span class="star">*</span></label>
                                                    <div class="input-group input-group-merge">
                                                        <textarea class="form-control h-50" rows="5" name="header_content" placeholder="Header content " ><?= $setting['setting.header_content'] ?></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label">Footer <span class="star">*</span></label>
                                                    <div class="input-group input-group-merge">
                                                        <textarea class="form-control h-50" rows="5" name="footer_content" placeholder="Footer content"><?= $setting['setting.footer_content'] ?></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn-primary">Submit</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                 <div class="tab-pane fade" id="navs-top-notification" role="tabpanel">
                                    <form action="{{ route('admin/setting/notification') }}" class="ajax-form5 mt-2" method="post">
                                        {{ csrf_field() }}
                                        <div class="form-row row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="body">Notification Timing </label>
                                                    <select class="form-select"
                                                        value="{{ config('setting.notification_time') }}"
                                                        name="notification_time">
                                                        <option value="7"
                                                            <?= config('setting.notification_time') == '7' ? 'selected' : '' ?>>7 Days</option>
                                                        <option value="15"
                                                            <?= config('setting.notification_time') == '15' ? 'selected' : '' ?>>15 Days</option>
                                                        <option value="30"
                                                            <?= config('setting.notification_time') == '30' ? 'selected' : '' ?>>30 Days</option>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn-primary">Submit</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                
                                <div class="tab-pane fade" id="navs-top-payment" role="tabpanel">
                                <form action="{{ route('admin/setting/payment') }}" class="ajax-form-payment" method="post">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="type" value="payment">
                                    <div class="form-row row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Stripe Enable</label>
                                                <select class="form-select" value="{{ $setting['setting.stripe_enable'] }}" name="stripe_enable">
                                                    <option value="1" {{ $setting['setting.stripe_enable'] == '1' ? 'selected' : '' }}>Enable</option>
                                                    <option value="0" {{ $setting['setting.stripe_enable'] == '0' ? 'selected' : '' }}>Disable</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Stripe Secret Key <span
                                                        class="text-danger">*</span></label>
                                                       <input type="text" class="form-control" 
                                                        value= "{{ $setting['setting.stripe_secret_key'] }}"
                                                        name="stripe_secret_key" placeholder="stripe secret key">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Public key <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" 
                                                        value="{{ $setting['setting.stripe_public_key'] }}"
                                                        name="stripe_public_key" placeholder="stripe public key">
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <button type="submit" class="btn btn-primary">Submit</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Mail Process start -->
        <div class="modal fade" id="email-test" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                <form action="{{ route('admin/setting/mailprocess') }}" id="ajax-form" method="POST" onsubmit="event.preventDefault()">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Mail</h5>
                        <button type="button" class="close closebtnmodal" data-bs-dismiss="modal"
                            aria-label="Close">
                            <span aria-hidden="false"><i class="fa-solid fa-xmark"></i></span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="email" class="form-control" id="email" placeholder="Email Address" name="email" aria-label="Name" required />
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" data-bs-dismiss="modal">Submit</button>
                    </div>
                </form>
                </div>
            </div>
        </div>
        <!-- mail Process End -->
    </div>
    @endsection
    @push('scripts')
    <script type="text/javascript">
        documentReady(function() {
            $('.ajax-file-form').validate({
                submitHandler: function(form) {
                    app.ajaxFileForm(form);
                }
            })
            $('.ajax-file-form1').validate({
                submitHandler: function(form) {
                    app.ajaxFileForm(form);
                }
            })
            $('#ajax-form').validate({
                submitHandler: function(form) {
                    app.ajaxForm(form);
                }
            })
            $('.ajax-form').validate({
                submitHandler: function(form) {
                    app.ajaxForm(form);
                }
            })
            $('.ajax-form1').validate({
                submitHandler: function(form) {
                    app.ajaxForm(form);
                }
            })
              $('.ajax-form2').validate({
                submitHandler: function(form) {
                    app.ajaxForm(form);
                }
            })
              $('.ajax-form3').validate({
                submitHandler: function(form) {
                    app.ajaxForm(form);
                }
            })
              $('.ajax-form4').validate({
                submitHandler: function(form) {
                    app.ajaxForm(form);
                }
            })
                $('.ajax-form5').validate({
                submitHandler: function(form) {
                    app.ajaxForm(form);
                }
            })
                 $('.ajax-form-payment').validate({
                submitHandler: function(form) {
                    app.ajaxForm(form);
                }
             })
        });
        
        
    </script>
@endpush
