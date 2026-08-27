<?php 
use Illuminate\Support\Facades\Route;

Route::any('webhook/company', 'App\Http\Controllers\Company\SubscriptionController@handleStripeWebhook')->name('webhook/company');