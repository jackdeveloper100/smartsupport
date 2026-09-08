<?php

use Illuminate\Support\Facades\Route;


/* User routes =========================================================================== */

Route::get('cron/schedule/run', function () {
    $artisan = new \Illuminate\Support\Facades\Artisan();
    $artisan::call("schedule:run");
    return $artisan::output();
})->name('cron/schedule/run');

Route::any('webhook/company', 'App\Http\Controllers\Company\SubscriptionController@handleStripeWebhook')->name('webhook/company');

Route::group(['middleware' => ['web']], function () {
    Route::get('/', '\App\Http\Controllers\FrontController@index')->name('home');
    Route::get('page/{slug}', '\App\Http\Controllers\FrontController@page')->name('page');
    Route::get('page/contact/{slug}', '\App\Http\Controllers\FrontController@help')->name('page/contact');
    Route::get('contact', '\App\Http\Controllers\FrontController@contact')->name('contact');
    Route::post('contact-process', '\App\Http\Controllers\FrontController@contactProcess')->name('contact-process');

    Route::get('contractor/document/check-expire', '\App\Http\Controllers\DocumentController@checkExpired')->name('contractor/document/check-expire');
    Route::get('company/check-subscription-expire', '\App\Http\Controllers\Company\SubscriptionController@checkSubscriptionExpires')->name('company/check-subscription-expire');
    Route::get('contractor/delete-temp-file', '\App\Http\Controllers\Admin\ReportController@deleteTempFiles')->name('contractor/delete-temp-file');
    Route::get('contractor/send-document-notification', '\App\Http\Controllers\CronController@sendDocumentStatusMail')->name('contractor/send-document-notification');

    Route::get('account/register', '\App\Http\Controllers\AccountController@register')->name('account/register');
    Route::post('account/register-process', '\App\Http\Controllers\AccountController@registerProcess')->name('account/register-process');

    // Route::get('company/account/register', '\App\Http\Controllers\AccountController@companyRegister')->name('company/account/register');
    Route::post('company/account/register-process', '\App\Http\Controllers\AccountController@companyRegisterProcess')->name('company/account/register-process');
 
    Route::get('login', '\App\Http\Controllers\AuthController@login')->name('login');
    Route::post('login-process', '\App\Http\Controllers\AuthController@loginProcess')->name('login-process');
    Route::get('logout', '\App\Http\Controllers\AuthController@logout')->name('logout');
    
    Route::get('auth/login-otp', '\App\Http\Controllers\AuthController@loginOtp')->name('auth/login-otp');
    Route::post('auth/login-otp-process', '\App\Http\Controllers\AuthController@loginOtpProcess')->name('auth/login-otp-process');
    Route::get('auth/password-forgot', '\App\Http\Controllers\AuthController@passwordForgot')->name('auth/password-forgot');
    Route::post('auth/password-forgot-process', '\App\Http\Controllers\AuthController@passwordForgotProcess')->name('auth/password-forgot-process');
    
    Route::get('auth/verify', '\App\Http\Controllers\AuthController@verify')->name('auth/verify');
    Route::post('auth/verify-process', '\App\Http\Controllers\AuthController@verifyProcess')->name('auth/verify-process');
    Route::post('auth/resend-otp', '\App\Http\Controllers\AuthController@resendOTP')->name('auth/resend-otp');
    
    Route::get('oauth/login/{type}', '\App\Http\Controllers\AuthController@socialLogin')->name('oauth/login');
    Route::get('oauth/callback/{type}', '\App\Http\Controllers\AuthController@socialLoginCallback')->name('oauth/callback');
    
    Route::get('w9-form/{token}/', '\App\Http\Controllers\Company\VendorController@showW9')->name('w9_form');
    Route::post('/w9-form/{token}', '\App\Http\Controllers\Company\VendorController@saveStep');
    Route::get('/w9-form/{token}/pdf', '\App\Http\Controllers\Company\VendorController@fw9')->name('fw9/pdf');
});

Route::group(['middleware' => ['web', 'user']], function () {
    Route::get('dashboard', '\App\Http\Controllers\AccountController@dashboard')->name('dashboard');
    
    Route::get('contractor/document', '\App\Http\Controllers\DocumentController@index')->name('contractor/document');
    Route::post('contractor/document/list', '\App\Http\Controllers\DocumentController@listDocument')->name('contractor/document/list');
    Route::get('contractor/document/create', '\App\Http\Controllers\DocumentController@create')->name('contractor/document/create');
    Route::get('contractor/document/update', '\App\Http\Controllers\DocumentController@update')->name('contractor/document/update');
    Route::get('contractor/document/view', '\App\Http\Controllers\DocumentController@view')->name('contractor/document/view');
    Route::post('contractor/document/save', '\App\Http\Controllers\DocumentController@save')->name('contractor/document/save');
    
    
    Route::post('contractors/{id}/documents-version', '\App\Http\Controllers\DocumentController@documentsVersion')->name('contractor/documents-version');
    Route::get('contractor/{id}/document-file/view', '\App\Http\Controllers\DocumentController@documentFileView')->name('contractor/document-file/view');
    Route::get('contractor/{id}/document-file/preview', '\App\Http\Controllers\DocumentController@documentPreview')->name('contractor/document-file/preview');
    Route::post('contractor/document-file/restore', '\App\Http\Controllers\DocumentController@restoreDocument')->name('contractor/document-file/restore');
    Route::any('document/download-file/{fileName}','\App\Http\Controllers\DocumentController@downloadFile')->name('document/download-file');
    Route::get('document/{fileName}', '\App\Http\Controllers\DocumentController@showImage')->name('document/show');

    // Route::get('contractor/documents/payment/create','\App\Http\Controllers\DocumentController@paymentCreate')->name('contractor/documents/payment/create');
    
   
    
    Route::get('contractor/notifications/load-more','\App\Http\Controllers\DocumentController@loadMoreNotification')->name('contractor/notifications/load-more');
    Route::get('contractor/notifications','\App\Http\Controllers\DocumentController@notificationView')->name('contractor/notifications');
    
    Route::post('contractors/documents-history', '\App\Http\Controllers\DocumentController@DocumentHistory')->name('contractor/documents-history');
    Route::post('contractors/document/save-account','\App\Http\Controllers\DocumentController@accountSave')->name('contractors/document/save-account');

    Route::get('account/update', '\App\Http\Controllers\AccountController@update')->name('account/update');
    Route::post('account/save', '\App\Http\Controllers\AccountController@save')->name('account/save');
    Route::get('account/image', '\App\Http\Controllers\AccountController@image')->name('account/image');
    Route::post('account/image-save', '\App\Http\Controllers\AccountController@imageSave')->name('account/image-save');
    Route::post('account/image-delete', '\App\Http\Controllers\AccountController@imageDelete')->name('account/image-delete');
    Route::get('account/password-change', '\App\Http\Controllers\AccountController@passwordChange')->name('account/password-change');
    Route::post('account/password-change-process', '\App\Http\Controllers\AccountController@changePasswordProcess')->name('account/password-change-process');
    Route::get('account/tfa', '\App\Http\Controllers\AccountController@tfa')->name('account/tfa');
    Route::post('account/tfa-status-change', '\App\Http\Controllers\AccountController@tfaStatusChange')->name('account/tfa-status-change');
    Route::post('account/revoke-all', '\App\Http\Controllers\AccountController@revokeAll')->name('account/revoke-all');
    
    Route::get('account/device', '\App\Http\Controllers\AccountController@device')->name('account/device');
    Route::post('account/device-list', '\App\Http\Controllers\AccountController@deviceList')->name('account/device-list');
    Route::post('account/device-logout', '\App\Http\Controllers\AccountController@deviceLogout')->name('account/device-logout');

    Route::get('account/log', '\App\Http\Controllers\AccountController@log')->name('account/log');
    Route::post('account/log-list', '\App\Http\Controllers\AccountController@logList')->name('account/log-list');
          

});


/* Admin routes =========================================================================== */
Route::group(['prefix' => 'admin', 'middleware' => 'web'], function () {
    Route::get('login', '\App\Http\Controllers\Admin\AuthController@login')->name('admin/login');
    Route::post('auth/login-process', '\App\Http\Controllers\Admin\AuthController@loginProcess')->name('admin/auth/login-process');
    Route::get('auth/logout', '\App\Http\Controllers\Admin\AuthController@logout')->name('admin/auth/logout');

    Route::get('auth/tfa-verify', '\App\Http\Controllers\Admin\AuthController@tfaVerify')->name('admin/auth/tfa-verify');
    Route::post('auth/tfa-verify-process', '\App\Http\Controllers\Admin\AuthController@tfaVerifyProcess')->name('admin/auth/tfa-verify-process');
    Route::post('auth/tfa-resend-otp', '\App\Http\Controllers\Admin\AuthController@tfaResendOTP')->name('admin/auth/tfa-resend-otp');

    Route::get('auth/password-forgot', '\App\Http\Controllers\Admin\AuthController@passwordForgot')->name('admin/auth/password-forgot');
    Route::post('auth/password-forgot-process', '\App\Http\Controllers\Admin\AuthController@passwordForgotProcess')->name('admin/auth/password-forgot-process');
    Route::post('auth/resend-otp', '\App\Http\Controllers\Admin\AuthController@resendOtp')->name('admin/auth/resend-otp');
    
    Route::get('login-as-contractor/{id}','\App\Http\Controllers\Admin\AuthController@loginAsContractor')->name('admin/login-as-contractor');
    
     Route::get('login-back','\App\Http\Controllers\Admin\AuthController@loginBackAsAdmin')->name('admin/login-back');
});

Route::group(['prefix' => 'admin', 'middleware' => ['web', 'admin']], function () {
    Route::get('dashboard', '\App\Http\Controllers\Admin\SiteController@dashboard')->name('admin/dashboard');
    Route::post('site/get-chart-user', '\App\Http\Controllers\Admin\SiteController@getChartUser')->name('admin/site/get-chart-user');
    
    Route::get('account/tfa', '\App\Http\Controllers\Admin\AccountController@tfa')->name('admin/account/tfa');
    Route::post('account/tfa-status-change', '\App\Http\Controllers\Admin\AccountController@tfaStatusChange')->name('admin/account/tfa-status-change');
    Route::post('account/revoke-all', '\App\Http\Controllers\Admin\AccountController@revokeAll')->name('admin/account/revoke-all');
    Route::get('document/{fileName}', '\App\Http\Controllers\DocumentController@showImage')->name('admin/document/show');

    Route::get('account/update', '\App\Http\Controllers\Admin\AccountController@update')->name('admin/account/update');
    Route::post('account/save', '\App\Http\Controllers\Admin\AccountController@save')->name('admin/account/save');
    Route::get('account/image', '\App\Http\Controllers\Admin\AccountController@image')->name('admin/account/image');
    Route::post('account/image-save', '\App\Http\Controllers\Admin\AccountController@imageSave')->name('admin/account/image-save');
    Route::post('account/image-delete', '\App\Http\Controllers\Admin\AccountController@deleteImage')->name('admin/account/image-delete');
    Route::get('account/password-change', '\App\Http\Controllers\Admin\AccountController@passwordChange')->name('admin/account/password-change');
    Route::post('account/password-change-process', '\App\Http\Controllers\Admin\AccountController@changePasswordProcess')->name('admin/account/password-change-process');

    Route::get('account/device', '\App\Http\Controllers\Admin\AccountController@device')->name('admin/account/device');
    Route::any('account/device-list', '\App\Http\Controllers\Admin\AccountController@deviceList')->name('admin/account/device-list');
    Route::any('account/device-logout', '\App\Http\Controllers\Admin\AccountController@deviceLogout')->name('admin/account/device-logout');

    Route::get('account/log', '\App\Http\Controllers\Admin\AccountController@log')->name('admin/account/log');
    Route::any('account/log-list', '\App\Http\Controllers\Admin\AccountController@logList')->name('admin/account/log-list');

    Route::get('content/update', '\App\Http\Controllers\Admin\ContentController@update')->name('admin/content/update');
    Route::post('content/save', '\App\Http\Controllers\Admin\ContentController@save')->name('admin/content/save');


    Route::any('contractor/dashboardlist', '\App\Http\Controllers\Admin\SiteController@dashboardList')->name('admin/contractor/dashboardlist');
    Route::any('dashboard/expirationalList', '\App\Http\Controllers\Admin\SiteController@expirationalList')->name('admin/dashboard/expirationalList');
    
    Route::get('contractor/documents', '\App\Http\Controllers\Admin\ContractorController@allDocumentView')->name('admin/contractor/documents');
    Route::any('contractor/documentlist', '\App\Http\Controllers\Admin\ContractorController@allDocumentsList')->name('admin/contractor/documentlist');
    
    Route::any('contractor/documentlist-bystatus', '\App\Http\Controllers\Admin\ContractorController@DocumentListByStatus')->name('admin/contractor/documentlist-bystatus');
    Route::get('contractors/{id}/document/show', '\App\Http\Controllers\Admin\ContractorController@contractorDocumentView')->name('admin/contractor/document/show');
    Route::post('contractors/{id}/filtered-docs/{status?}', '\App\Http\Controllers\Admin\ContractorController@showFilteredDocumements')->name('admin/contractor/filtered-docs');
    
    Route::any('dashboard/totalReceivedPayment', '\App\Http\Controllers\Admin\SiteController@getTotalReceivedPayment')->name('admin/dashboard/totalReceivedPayment');
    
    // Route::get('contractors/report', '\App\Http\Controllers\Admin\ReportController@index')->name('admin/contractor/report');
    Route::any('contractor/report-list', '\App\Http\Controllers\Admin\ReportController@list')->name('admin/contractor/report-list');
    Route::get('contractors/report-export', '\App\Http\Controllers\Admin\ReportController@exportData')->name('admin/contractor/report-export');
    
    // Route::get('contractors/export-data', '\App\Http\Controllers\Admin\ReportController@exportIndex')->name('admin/contractors/export-data');
    Route::post('contractors/export-documents', '\App\Http\Controllers\Admin\ReportController@ExportDocuments')->name('admin/contractors/export-documents');

    Route::get('company/subscription', '\App\Http\Controllers\Company\SubscriptionController@index')->name('admin/company/subscription');
    Route::any('company/subscription-list', '\App\Http\Controllers\Company\SubscriptionController@list')->name('admin/company/subscription-list');

    Route::get('contractors', '\App\Http\Controllers\Admin\ContractorController@index')->name('admin/contractor');
    Route::any('contractor/list', '\App\Http\Controllers\Admin\ContractorController@list')->name('admin/contractor/list');
    Route::get('contractor/create', '\App\Http\Controllers\Admin\ContractorController@create')->name('admin/contractor/create');
    Route::get('contractor/update', '\App\Http\Controllers\Admin\ContractorController@update')->name('admin/contractor/update');
    Route::post('contractor/save', '\App\Http\Controllers\Admin\ContractorController@save')->name('admin/contractor/save');
    Route::get('contractor/view', '\App\Http\Controllers\Admin\ContractorController@view')->name('admin/contractor/view');
    Route::post('contractor/delete', '\App\Http\Controllers\Admin\ContractorController@delete')->name('admin/contractor/delete');
    Route::post('contractor/change_status', '\App\Http\Controllers\Admin\ContractorController@changeStatus')->name('admin/contractor/change_status');
    Route::post('contractor/comment-save', '\App\Http\Controllers\Admin\ContractorController@commentSave')->name('admin/contractor/comment-save');
    
    Route::get('contractor/notifications','\App\Http\Controllers\Admin\ContractorController@notificationView')->name('admin/contractor/notifications');
    Route::get('contractor/notifications/load-more','\App\Http\Controllers\Admin\ContractorController@loadMoreNotification')->name('admin/contractor/notifications/load-more');

    Route::post('contractor/delete_multiple', '\App\Http\Controllers\Admin\ContractorController@deleteMultiple')->name('admin/contractor/delete_multiple');
    Route::get('contractor/send-remindermail', '\App\Http\Controllers\Admin\ContractorController@sendReminderMail')->name('admin/contractor/send-remindermail');
    Route::get('contractor/document/send-remindermail', '\App\Http\Controllers\Admin\ContractorController@sendReminderMail')->name('admin/contractor/document/send-remindermail');
    Route::get('contractor/document/send-remindermail-new', '\App\Http\Controllers\Admin\ContractorController@sendReminderMailnew')->name('admin/contractor/document/send-remindermail-new');
    Route::post('contractor/send-remindermail-save', '\App\Http\Controllers\Admin\ContractorController@sendReminderEmail')->name('admin/contractor/send-remindermail-save');
    Route::get('contractor/export-data', '\App\Http\Controllers\Admin\ContractorController@exportData')->name('admin/contractor/export-data');
   
    Route::post('contractors/{id}/documents', '\App\Http\Controllers\Admin\DocumentController@contractorDocument')->name('admin/contractor/document');
    Route::get('contractors/{id}/document/create', '\App\Http\Controllers\Admin\DocumentController@create')->name('admin/document/create');
    Route::get('contractors/{id}/document/update', '\App\Http\Controllers\Admin\DocumentController@update')->name('admin/document/update');
    Route::get('contractors/{id}/document/view', '\App\Http\Controllers\Admin\DocumentController@contractorView')->name('admin/document/view');
    Route::post('contractors/{id}/document/save', '\App\Http\Controllers\Admin\DocumentController@save')->name('admin/document/save');
    Route::post('document/{id}/change_status/{type}', '\App\Http\Controllers\Admin\DocumentController@changeStatus')->name('admin/document/change_status');

    Route::get('contractor/{id}/document-file/view', '\App\Http\Controllers\Admin\DocumentController@documentFileView')->name('admin/contractor/document-file/view');
    Route::get('contractor/{id}/document-file/preview', '\App\Http\Controllers\Admin\DocumentController@documentPreview')->name('admin/contractor/document-file/preview');

    Route::get('contractor/document-file/pdf-preview', '\App\Http\Controllers\Admin\DocumentController@pdfPreview')->name('admin/contractor/document-file/pdf-preview');

    Route::post('contractors/{id}/documents-activity', '\App\Http\Controllers\Admin\DocumentController@contractorDocumentActivity')->name('admin/contractor/documents-activity');
    Route::post('contractors/{id}/documents-version', '\App\Http\Controllers\Admin\DocumentController@documentsVersion')->name('admin/contractor/documents-version');
    Route::get('document/reject', '\App\Http\Controllers\Admin\DocumentController@rejectDocument')->name('admin/document/reject');    
    //  Route::get('document/demo', '\App\Http\Controllers\Admin\DocumentController@demo')->name('admin/document/demo');    

    Route::get('admin', '\App\Http\Controllers\Admin\AdminController@index')->name('admin/admin');
    Route::post('admin/list', '\App\Http\Controllers\Admin\AdminController@list')->name('admin/admin/list');
    Route::get('admin/create', '\App\Http\Controllers\Admin\AdminController@create')->name('admin/admin/create');
    Route::get('admin/update', '\App\Http\Controllers\Admin\AdminController@update')->name('admin/admin/update');
    Route::post('admin/save', '\App\Http\Controllers\Admin\AdminController@save')->name('admin/admin/save');
    Route::get('admin/view', '\App\Http\Controllers\Admin\AdminController@view')->name('admin/admin/view');
    Route::post('admin/delete', '\App\Http\Controllers\Admin\AdminController@delete')->name('admin/admin/delete');
    Route::post('admin/status-save', '\App\Http\Controllers\Admin\AdminController@statusSave')->name('admin/admin/status-save');
    
    Route::get('users', '\App\Http\Controllers\Admin\AdminController@index')->name('admin/users');
    Route::post('user/list', '\App\Http\Controllers\Admin\AdminController@list')->name('admin/user/list');
    Route::get('user/create', '\App\Http\Controllers\Admin\AdminController@create')->name('admin/user/create');
    Route::get('user/update', '\App\Http\Controllers\Admin\AdminController@update')->name('admin/user/update');
    Route::post('user/save', '\App\Http\Controllers\Admin\AdminController@save')->name('admin/user/save');
    Route::get('user/view', '\App\Http\Controllers\Admin\AdminController@view')->name('admin/user/view');
    Route::post('user/delete', '\App\Http\Controllers\Admin\AdminController@delete')->name('admin/user/delete');
    Route::post('user/status-save', '\App\Http\Controllers\Admin\AdminController@statusSave')->name('admin/user/status-save');

    Route::get('company', '\App\Http\Controllers\Admin\CompanyController@index')->name('admin/company');
    Route::post('company/list', '\App\Http\Controllers\Admin\CompanyController@list')->name('admin/company/list');
    Route::get('company/create', '\App\Http\Controllers\Admin\CompanyController@create')->name('admin/company/create');
    Route::get('company/update', '\App\Http\Controllers\Admin\CompanyController@update')->name('admin/company/update');
    Route::post('company/save', '\App\Http\Controllers\Admin\CompanyController@save')->name('admin/company/save');
    Route::get('company/view', '\App\Http\Controllers\Admin\CompanyController@view')->name('admin/company/view');
    Route::post('company/save-allowed-documents', '\App\Http\Controllers\Admin\CompanyController@saveAllowedDocuments')->name('admin/company/save-allowed-documents');
    Route::post('company/delete', '\App\Http\Controllers\Admin\CompanyController@delete')->name('admin/company/delete');

    Route::get('seo/meta', '\App\Http\Controllers\Admin\SeoController@index')->name('admin/seo/meta');
    Route::post('seo/list', '\App\Http\Controllers\Admin\SeoController@list')->name('admin/seo/list');
    Route::get('seo/create', '\App\Http\Controllers\Admin\SeoController@create')->name('admin/seo/create');
    Route::get('seo/update', '\App\Http\Controllers\Admin\SeoController@update')->name('admin/seo/update');
    Route::post('seo/save', '\App\Http\Controllers\Admin\SeoController@save')->name('admin/seo/save');
    Route::post('seo/delete', '\App\Http\Controllers\Admin\SeoController@delete')->name('admin/seo/delete');
    Route::get('seo/sitemap-update', '\App\Http\Controllers\Admin\SeoController@sitemapUpdate')->name('admin/seo/sitemap-update');

    Route::get('pages', '\App\Http\Controllers\Admin\PageController@index')->name('admin/page');
    Route::get('page/list', '\App\Http\Controllers\Admin\PageController@list')->name('admin/page/list');
    Route::get('page/update', '\App\Http\Controllers\Admin\PageController@update')->name('admin/page/update');
    Route::post('page/save', '\App\Http\Controllers\Admin\PageController@save')->name('admin/page/save');
    Route::post('page/save-image', '\App\Http\Controllers\Admin\PageController@saveFile')->name('admin/page/save-image');

    Route::get('setting/update', '\App\Http\Controllers\Admin\SettingController@update')->name('admin/setting/update');
    Route::post('setting/save', '\App\Http\Controllers\Admin\SettingController@save')->name('admin/setting/save');
    Route::post('setting/save-file', '\App\Http\Controllers\Admin\SettingController@saveFile')->name('admin/setting/save-file');
    Route::post('setting/smtp-save', '\App\Http\Controllers\Admin\SettingController@smtp')->name('admin/setting/smtp-save');
    Route::post('setting/captcha', '\App\Http\Controllers\Admin\SettingController@captcha')->name('admin/setting/captcha');
    Route::post('setting/social', '\App\Http\Controllers\Admin\SettingController@social')->name('admin/setting/social');
    Route::post('setting/content', '\App\Http\Controllers\Admin\SettingController@content')->name('admin/setting/content');
    Route::post('setting/payment', '\App\Http\Controllers\Admin\SettingController@payment')->name('admin/setting/payment');
    Route::post('setting/notification', '\App\Http\Controllers\Admin\SettingController@notification')->name('admin/setting/notification');

    Route::post('setting/save-logo', '\App\Http\Controllers\Admin\SettingController@saveLogo')->name('admin/setting/save-logo');
    Route::get('setting/cache-clear', '\App\Http\Controllers\Admin\SettingController@cacheClear')->name('admin/setting/cache-clear');
    Route::post('setting/mailprocess', '\App\Http\Controllers\Admin\SettingController@mailprocess')->name('admin/setting/mailprocess');
    
    Route::get('setting/document_type','App\Http\Controllers\Admin\DocumentTypeController@index')->name('admin/setting/document_type');
    Route::post('setting/document_type/list','App\Http\Controllers\Admin\DocumentTypeController@list')->name('admin/setting/document_type/list');
    Route::get('setting/document_type/create','App\Http\Controllers\Admin\DocumentTypeController@create')->name('admin/setting/document_type/create');
    Route::get('setting/document_type/update','App\Http\Controllers\Admin\DocumentTypeController@update')->name('admin/setting/document_type/update');
    Route::post('setting/document_type/save','App\Http\Controllers\Admin\DocumentTypeController@save')->name('admin/setting/document_type/save');
    Route::post('setting/document_type/delete','App\Http\Controllers\Admin\DocumentTypeController@delete')->name('admin/setting/document_type/delete');



    Route::get('log', '\App\Http\Controllers\Admin\LogController@index')->name('admin/log');
    Route::post('log/list', '\App\Http\Controllers\Admin\LogController@list')->name('admin/log/list');

    Route::get('device', '\App\Http\Controllers\Admin\DeviceController@index')->name('admin/device');
    Route::post('device/list', '\App\Http\Controllers\Admin\DeviceController@list')->name('admin/device/list');
    Route::post('device/logout', '\App\Http\Controllers\Admin\DeviceController@logout')->name('admin/device/logout');

    Route::get('email-template','\App\Http\Controllers\Admin\EmailTemplateController@index')->name('admin/email-template');
    Route::get('email-template/list', '\App\Http\Controllers\Admin\EmailTemplateController@list')->name('admin/email-template/list');
    Route::get('email-template/update', '\App\Http\Controllers\Admin\EmailTemplateController@update')->name('admin/email-template/update');
    Route::post('email-template/save', '\App\Http\Controllers\Admin\EmailTemplateController@save')->name('admin/email-template/save');
    Route::post('email-template/save-image', '\App\Http\Controllers\Admin\EmailTemplateController@saveFile')->name('admin/email-template/save-image');
    
    
});

Route::group(['prefix' => 'company', 'middleware' => 'web'], function () {
    // Route::get('login', '\App\Http\Controllers\Company\AuthController@login')->name('company/login');
     
    Route::post('auth/login-process', '\App\Http\Controllers\Company\AuthController@loginProcess')->name('company/auth/login-process');
    
    Route::get('auth/logout', '\App\Http\Controllers\Company\AuthController@logout')->name('company/auth/logout');
     
     
    Route::get('auth/verify', '\App\Http\Controllers\Company\AuthController@tfaVerify')->name('company/auth/verify');
    Route::post('auth/tfa-verify-process', '\App\Http\Controllers\Company\AuthController@tfaVerifyProcess')->name('company/auth/tfa-verify-process');
    Route::post('auth/tfa-resend-otp', '\App\Http\Controllers\Company\AuthController@tfaResendOTP')->name('company/auth/tfa-resend-otp');
    
    
    Route::post('auth/resend-otp', '\App\Http\Controllers\Company\AuthController@resendOtp')->name('company/auth/resend-otp');
    
    Route::get('auth/password-forgot', '\App\Http\Controllers\Company\AuthController@passwordForgot')->name('company/auth/password-forgot');
    Route::post('auth/password-forgot-process', '\App\Http\Controllers\Company\AuthController@passwordForgotProcess')->name('company/auth/password-forgot-process');   
    
    /* Password Setup for Invited Team Members */
    Route::get('auth/setup-password', '\App\Http\Controllers\Company\AuthController@setupPassword')->name('company/auth/setup-password');
    Route::post('auth/setup-password-process', '\App\Http\Controllers\Company\AuthController@setupPasswordProcess')->name('company/auth/setup-password-process');

    Route::get('login-as-company/{id}','\App\Http\Controllers\Admin\AuthController@loginAsCompany')->name('company/login-as-company');
   
   Route::get('company/plan','App\Http\Controllers\Company\PlanController@index')->name('company/plan');
   Route::any('plan-select','App\Http\Controllers\Company\SubscriptionController@planSelect')->name('plan-select');
     
   Route::get('checkout/success','App\Http\Controllers\Company\SubscriptionController@chekoutSuccess')->name('company/checkout/success');
   Route::get('checkout/cancel','App\Http\Controllers\Company\SubscriptionController@chekoutCancel')->name('company/checkout/cancel');
   
   Route::get('contractors/add-extra','App\Http\Controllers\Company\SubscriptionController@payExtraContractor')->name('company/contractors/add-extra');
   Route::get('contractor/extra/success','App\Http\Controllers\Company\SubscriptionController@extraPaymentSuccess')->name('company/contractor/extra/success');
   Route::get('contractor/extra/cancel','App\Http\Controllers\Company\SubscriptionController@extraPaymentCancel')->name('company/contractor/extra/cancel');
   
   Route::get('login-as-contractor/{id}','\App\Http\Controllers\Company\AuthController@loginAsContractor')->name('company/login-as-contractor');
   Route::get('login-back','\App\Http\Controllers\Company\AuthController@loginBackAsCompany')->name('company/login-back');
     
});

/* Company routes =========================================================================== */
Route::group(['prefix' => 'company', 'middleware' => ['web', 'company']], function () {
    Route::get('dashboard', '\App\Http\Controllers\Company\SiteController@dashboard')->name('company/dashboard');
    Route::get('support', '\App\Http\Controllers\Company\SiteController@support')->name('company/support');

    /* Team Members Management Routes */
    Route::get('team-members', '\App\Http\Controllers\Company\TeamMemberController@index')->name('company/team-members');
    Route::post('team-member/list', '\App\Http\Controllers\Company\TeamMemberController@list')->name('company/team-member/list');
    Route::get('team-member/create', '\App\Http\Controllers\Company\TeamMemberController@create')->name('company/team-member/create');
    Route::get('team-member/update', '\App\Http\Controllers\Company\TeamMemberController@update')->name('company/team-member/update');
    Route::post('team-member/save', '\App\Http\Controllers\Company\TeamMemberController@save')->name('company/team-member/save');
    Route::post('team-member/status-save', '\App\Http\Controllers\Company\TeamMemberController@statusSave')->name('company/team-member/status-save');
    Route::post('team-member/delete', '\App\Http\Controllers\Company\TeamMemberController@delete')->name('company/team-member/delete');

    Route::any('dashboard/expirationalList', '\App\Http\Controllers\Company\SiteController@expirationalList')->name('company/dashboard/expirationalList');
    
    // Route::get('contractor/documents', '\App\Http\Controllers\Company\ContractorController@allDocumentView')->name('company/contractor/documents');
    Route::any('contractor/documentlist', '\App\Http\Controllers\Company\ContractorController@allDocumentsList')->name('company/contractor/documentlist');
    
    Route::any('contractor/documentlist-bystatus', '\App\Http\Controllers\Company\ContractorController@DocumentListByStatus')->name('company/contractor/documentlist-bystatus');
    Route::get('contractors/{id}/document/show', '\App\Http\Controllers\Company\ContractorController@contractorDocumentView')->name('company/contractor/document/show');
    Route::post('contractors/{id}/filtered-docs/{status?}', '\App\Http\Controllers\Company\ContractorController@showFilteredDocumements')->name('company/contractor/filtered-docs');


    Route::get('contractor_list/{id}','App\Http\Controllers\Company\PlanController@contractorList')->name('company/contractor_list');
    Route::post('selected-contrators','App\Http\Controllers\Company\PlanController@selectContractors')->name('company/selected-contrators');
    
    /* Company Account routes  */
    Route::get('account/update', '\App\Http\Controllers\Company\AccountController@update')->name('company/account/update');
     Route::post('account/save', '\App\Http\Controllers\Company\AccountController@save')->name('company/account/save');
     
    Route::get('account/image', '\App\Http\Controllers\Company\AccountController@image')->name('company/account/image');
    Route::post('account/image-save', '\App\Http\Controllers\Company\AccountController@imageSave')->name('company/account/image-save');
    Route::post('account/image-delete', '\App\Http\Controllers\Company\AccountController@deleteImage')->name('company/account/image-delete');
     
    Route::get('account/company-logo', '\App\Http\Controllers\Company\AccountController@companyLogo')->name('company/account/company-logo');
    Route::post('account/company-logo-save', '\App\Http\Controllers\Company\AccountController@companyLogoSave')->name('company/account/company-logo-save');
    Route::post('account/logo-delete', '\App\Http\Controllers\Company\AccountController@deleteCompanyLogo')->name('company/account/logo-delete');

    Route::get('account/password-change', '\App\Http\Controllers\Company\AccountController@passwordChange')->name('company/account/password-change');
    Route::post('account/password-change-process', '\App\Http\Controllers\Company\AccountController@changePasswordProcess')->name('company/account/password-change-process');
    
    Route::get('account/tfa', '\App\Http\Controllers\Company\AccountController@tfa')->name('company/account/tfa');
    Route::post('account/tfa-status-change', '\App\Http\Controllers\Company\AccountController@tfaStatusChange')->name('company/account/tfa-status-change');
    Route::post('account/revoke-all', '\App\Http\Controllers\Company\AccountController@revokeAll')->name('company/account/revoke-all');
    Route::get('document/{fileName}', '\App\Http\Controllers\DocumentController@showImage')->name('company/document/show');
    
    /* Company Contractor routes  */
   Route::get('contractors', '\App\Http\Controllers\Company\ContractorController@index')->name('company/contractor');
    Route::any('contractor/list', '\App\Http\Controllers\Company\ContractorController@list')->name('company/contractor/list');
    Route::get('contractor/create', '\App\Http\Controllers\Company\ContractorController@create')->name('company/contractor/create');
    Route::get('contractor/update', '\App\Http\Controllers\Company\ContractorController@update')->name('company/contractor/update');
    Route::post('contractor/save', '\App\Http\Controllers\Company\ContractorController@save')->name('company/contractor/save');
    Route::get('contractor/view', '\App\Http\Controllers\Company\ContractorController@view')->name('company/contractor/view');
    Route::post('contractor/delete', '\App\Http\Controllers\Company\ContractorController@delete')->name('company/contractor/delete');
    Route::post('contractor/change_status', '\App\Http\Controllers\Company\ContractorController@changeStatus')->name('company/contractor/change_status');
    Route::post('contractor/comment-save', '\App\Http\Controllers\Company\ContractorController@commentSave')->name('company/contractor/comment-save');
    
    Route::get('requests', '\App\Http\Controllers\Company\VendorController@index')->name('company/request');
    Route::get('requests/create', '\App\Http\Controllers\Company\VendorController@create')->name('company/request/create');
    Route::post('request/store', '\App\Http\Controllers\Company\VendorController@save')->name('company/request/store');
    Route::post('request/list', '\App\Http\Controllers\Company\VendorController@list')->name('company/request/list');
    Route::get('request/update', '\App\Http\Controllers\Company\VendorController@update')->name('company/request/update');
    Route::post('request/delete-request', '\App\Http\Controllers\Company\VendorController@delete')->name('company/request/delete-request');
    
    Route::get('w9/request/received', '\App\Http\Controllers\Company\VendorReceivedontroller@index')->name('company/w9/request/received');
    Route::post('w9/request/received/list', '\App\Http\Controllers\Company\VendorReceivedontroller@receivedList')->name('company/w9/request/received/list');
    Route::get('request/audit/{id}', '\App\Http\Controllers\Company\VendorReceivedontroller@audit')->name('company/request/audit/{id}');
    Route::get('request/usps', '\App\Http\Controllers\Company\VendorReceivedontroller@usps')->name('company/w9/request/usps');
    Route::get('w9/status/{token}', '\App\Http\Controllers\Company\VendorReceivedontroller@checkW9Status')->name('company/w9/status');
    Route::post('w9/request/resend-request', '\App\Http\Controllers\Company\VendorReceivedontroller@resendRequest')->name('company/w9/request/resend-request');
    Route::post('w9/request/delete', '\App\Http\Controllers\Company\VendorReceivedontroller@delete')->name('company/w9/request/delete');
    
    /* Company Contractor Bulk Option routes  */
    Route::get('contractor/export-data', '\App\Http\Controllers\Company\ContractorController@exportData')->name('company/contractor/export-data');
    Route::get('contractor/send-remindermail', '\App\Http\Controllers\Company\ContractorController@sendReminderMail')->name('company/contractor/send-remindermail');
    Route::post('contractor/send-remindermail-save', '\App\Http\Controllers\Company\ContractorController@sendReminderEmail')->name('company/contractor/send-remindermail-save');
    Route::post('contractor/delete_multiple', '\App\Http\Controllers\Company\ContractorController@deleteMultiple')->name('company/contractor/delete_multiple');

    
    /* Company Contractor Report routes  */
    Route::get('contractors/report', '\App\Http\Controllers\Company\ReportController@index')->name('company/contractor/report');
    Route::any('contractor/report-list', '\App\Http\Controllers\Company\ReportController@list')->name('company/contractor/report-list');
    Route::get('contractors/report-export', '\App\Http\Controllers\Company\ReportController@exportData')->name('company/contractor/report-export');
    
    Route::get('contractors/export-data', '\App\Http\Controllers\Company\ReportController@exportIndex')->name('company/contractors/export-data');
    Route::post('contractors/export-documents', '\App\Http\Controllers\Company\ReportController@ExportDocuments')->name('company/contractors/export-documents');

    /* Company Contractor Views Functionality routes  */
    
   Route::post('contractors/{id}/documents', '\App\Http\Controllers\Company\DocumentController@contractorDocument')->name('company/contractor/document');
   Route::get('contractors/{id}/document/view', '\App\Http\Controllers\Company\DocumentController@contractorView')->name('company/document/view');
   Route::post('contractors/{id}/documents-activity', '\App\Http\Controllers\Company\DocumentController@contractorDocumentActivity')->name('company/contractor/documents-activity');
   Route::post('contractors/{id}/documents-version', '\App\Http\Controllers\Company\DocumentController@documentsVersion')->name('company/contractor/documents-version');

   
   Route::post('document/{id}/change_status/{type}', '\App\Http\Controllers\Company\DocumentController@changeStatus')->name('company/document/change_status');
   Route::get('contractor/document/send-remindermail', '\App\Http\Controllers\Company\ContractorController@sendReminderMail')->name('company/contractor/document/send-remindermail');
   Route::get('contractor/document/send-remindermail-new', '\App\Http\Controllers\Company\ContractorController@sendReminderMailnew')->name('company/contractor/document/send-remindermail-new');
   Route::get('contractor/{id}/document-file/preview', '\App\Http\Controllers\Company\DocumentController@documentPreview')->name('company/contractor/document-file/preview');
   Route::get('contractor/document-file/pdf-preview', '\App\Http\Controllers\Company\DocumentController@pdfPreview')->name('company/contractor/document-file/pdf-preview');

   /* Company Notification  Functionality routes  */
   Route::get('contractor/notifications','\App\Http\Controllers\Company\ContractorController@notificationView')->name('company/contractor/notifications');
   Route::get('contractor/notifications/load-more','\App\Http\Controllers\Company\ContractorController@loadMoreNotification')->name('company/contractor/notifications/load-more');
   
   Route::get('contractor/new-register-request','\App\Http\Controllers\Company\ContractorController@newRegisterRequest')->name('company/contractor/new-register-request');
   Route::post('contractor/register-approve', '\App\Http\Controllers\Company\ContractorController@registerApprove')->name('company/contractor/register-approve');
   Route::post('contractor/register-reject', '\App\Http\Controllers\Company\ContractorController@registerReject')->name('company/contractor/register-reject');


});   