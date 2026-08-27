<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use App\Helpers\General;
use App\Models\Subscription;
use Stripe\Checkout\Session;
use Illuminate\Support\Facades\Log;


class SubscriptionController extends Controller
{
    public function index(){
        $sessionUser = auth()->user();
        if($sessionUser->hasPermission('admin/company/subscription')){
            return view('admin/subscription/index'); 
        }
    }
    
    public function list(Request $request){
        $sessionUser = auth()->user();
        if($sessionUser->hasPermission('admin/company/subscription')){
            return response()->json((new Subscription())->list($request->all()));
        }else{
            return redirect('admin/dashboard')->with('error','You are not authorized');
        }
    }
    
    public function planSelect(Request $request){
        $user = $user = auth()->user();
        // dd($user);
        if($user){
            
            $planId = $request->plan_id;
            
            if(!$planId){
                return redirect('login')->with('error', 'Invalid Plan');
            }
            
            $planData = Plan::where('id',$planId)->first();
            $planDataStripePriceId = $planData->stripe_price_id;
            
            $stripeSecretKey = config('setting.stripe_secret_key');
            \Stripe\Stripe::setApiKey($stripeSecretKey);
            
            if($stripeSecretKey){
                $stripe = new \Stripe\StripeClient($stripeSecretKey);
                
                $customer = null ;
                
               if ($user->stripe_customer_id) {
                    try {
                        $customer = $stripe->customers->retrieve($user->stripe_customer_id);
            
                        // If customer was deleted, discard
                        if (isset($customer->deleted) && $customer->deleted) {
                            $customer = null;
                        }
                    } catch (\Exception $e) {
                        // Log the error or ignore it if customer not found
                        \Log::warning("Stripe customer not found: " . $e->getMessage());
                        $customer = null;
                    }
                }
            
                if (!$customer) {
                    try {
                        $customer = $stripe->customers->create([
                            'name' => $user->company_name,
                            'email' => $user->email,
                        ]);
            
                        $user->stripe_customer_id = $customer->id;
                        $user->save();
                    } catch (\Exception $e) {
                        return redirect('login')->with('error', 'Unable to create Stripe customer: ' . $e->getMessage());
                    }
                }
                try {
                    
                    $lineItems = [
                         [
                            'price' => $planData->stripe_price_id,
                            'quantity' => 1,
                        ],
                    ];
                    $extraContractors = 0;
                    // if ($planId == 3) {
                        $totalContractors = User::where('company_id',$user->id)->where('company_approved_status',1)->count();
                        $addOnContractors = User::where('company_id',$user->id)->where('company_approved_status',1)->where('is_add_on',1)->where('add_on_expired_at','<',now())->count();
 
                        $contractorLimit = $planData->contractor_limit;
                        if ($totalContractors > $contractorLimit) {
                            $extraContractors = $totalContractors - $contractorLimit;
                            $extraContractors = $addOnContractors;
                            if($extraContractors > 0){
                                $lineItems[] = [
                                    'price' => config('setting.stripe_extra_contractor_price_id'), // $15 per extra contractor
                                    'quantity' => $extraContractors,    
                                ];
                            }
                        }
                    // }
                    $addOnContractorsIds = [];
                    $addOnContractorsData = User::where('company_id',$user->id)->where('company_approved_status',1)->where('is_add_on',1)->where('add_on_expired_at','<',now())->get();
                    $addOnContractorsIds = $addOnContractorsData->pluck('id')->toArray();
                    $addOnContractorsIdsString = implode(',', $addOnContractorsIds);
                    
                    $checkoutSession = $stripe->checkout->sessions->create([
                        'customer' => $customer->id,
                        'payment_method_types' => ['card'],
                        'line_items' =>$lineItems,
                        'mode' => 'subscription',
                        'success_url' => route('company/checkout/success') . '?session_id={CHECKOUT_SESSION_ID}',
                        'cancel_url' => route('company/checkout/cancel') . '?session_id={CHECKOUT_SESSION_ID}',
                        'metadata' => [
                            'plan_id' => $planData->id,
                            'user_id' => $user->id,
                            'add_on_user_ids' =>$addOnContractorsIdsString,
                            'extra_contractor' => $extraContractors,
                        ],
                    ]);
            
                    return redirect($checkoutSession->url);
                } catch (\Exception $e) {
                    return redirect('login')->with('error', 'Stripe Checkout error: ' . $e->getMessage());
                }
            }
            
        }else{
            return redirect('login')->with('error', 'Company Not Found');;
        }
    }
    
    public function chekoutSuccess(Request $request){
        $notificationModel = new Notification();
        $sessionId = $request->get('session_id');
        
        $stripeSecretKey = config('setting.stripe_secret_key');
        \Stripe\Stripe::setApiKey($stripeSecretKey);
        
        try{
            $session = \Stripe\Checkout\Session::retrieve($sessionId);
            $subscriptionId = $session->subscription;
            $planId = $session->metadata->plan_id ?? null;
            
            $addOnContractorsIdsString = $session->metadata->add_on_user_ids ?? '';
            $addOnContractorsIds = explode(',', $addOnContractorsIdsString);
            $extraContractors = $session->metadata->extra_contractor ?? 0;

            $user = User::where('stripe_customer_id',$session->customer)->first();
            $planData = Plan::where('id',$planId)->first();
            $adminList = User::where('type',0)->get();
            
            if(!$user){
                return redirect()->route('login')->with('error', 'Company not found');
            }
            
            // if($planId == 3){
            //     $user->available_contractor += $extraContractors;
            //     $user->save();
            // }
            
            $subscription = \Stripe\Subscription::retrieve([
               'id' => $subscriptionId,
               'expand' => ['items'],
                ]);

            \Stripe\Subscription::update($subscriptionId, [
                'cancel_at_period_end' => true,
            ]);
            
            $subscriptionEndDate = $subscription->items->data[0]['current_period_end'];
            $endDate = date('Y-m-d H:i:s', $subscriptionEndDate);
            $existingSubscription = Subscription::where('user_id',$user->id)->first();
            
           if (!empty($addOnContractorsIds)) {  
                foreach ($addOnContractorsIds as $contractorId) {
                    $contractor = User::where('id', $contractorId)->first();
                    
                    if ($contractor) {  
                        $contractor->add_on_expired_at = $endDate;
                        $contractor->save();
                    }
                }
            }
             
            if($existingSubscription){
                if($existingSubscription->stripe_subscription_id){
                    try{
                        $stripe = new \Stripe\StripeClient($stripeSecretKey);
                        $stripe->subscriptions->cancel($existingSubscription->stripe_subscription_id);
                    }catch(\Exception $e){
                      \Log::error('Stripe Subscription cancel error:', ['exception' => $e]);
                    }
                }

            $existingSubscription->stripe_subscription_id = $subscriptionId;
            $existingSubscription->status = $subscription->status;
            $existingSubscription->plan_id = $planId;
            $existingSubscription->expired_at = $endDate;
            $existingSubscription->save();
            
            $notificationModel->planEntry($user->id,$planId);
            
            $template = 'upgrade_plan';
            (new General())->sendEmail($user->email, $template, [
                'company_name' => $user->company_name,
                'plan_name' => $planData->title,
                'price' => $planData->amount,
                'contractor_limit' => $planData->contractor_limit,
                'duration' => $planData->duration,
            ]);
            
                foreach($adminList as $admin){
                    (new General())->sendEmail($admin->email, 'send_plan_info_to_admin', [
                        'name' => $admin->first_name. ' '.$admin->last_name,
                        'company_name' => $user->company_name,
                        'plan_name' => $planData->title,
                        'price' => $planData->amount,
                        'contractor_limit' => $planData->contractor_limit,
                        'duration' => $planData->duration,
                    ]);
                }
            
            }else{
                $subscriptionModel = new Subscription;
                
                $subscriptionModel->user_id = $user->id;
                $subscriptionModel->stripe_subscription_id = $subscriptionId;
                $subscriptionModel->status = $subscription->status;
                $subscriptionModel->plan_id = $planId;
                $subscriptionModel->expired_at = $endDate;
                $subscriptionModel->save();
                
                 $notificationModel->planEntry($user->id,$planId);
                 
                $template = 'upgrade_plan';
                (new General())->sendEmail($user->email, $template, [
                    'company_name' => $user->company_name,
                    'plan_name' => $planData->title,
                    'price' => $planData->amount,
                    'contractor_limit' => $planData->contractor_limit,
                    'duration' => $planData->duration,
                ]);
                
                foreach($adminList as $admin){
                    
                    (new General())->sendEmail($admin->email, 'send_plan_info_to_admin', [
                        'name' => $admin->first_name. ' '.$admin->last_name,
                        'company_name' => $user->company_name,
                        'plan_name' => $planData->title,
                        'price' => $planData->amount,
                        'contractor_limit' => $planData->contractor_limit,
                        'duration' => $planData->duration,
                    ]);
                
                }
            }
            
            
            return redirect('company/dashboard')->with('success','Subscription Updated Successfully');
        }catch(Exception $e){
               return redirect()->route('company/plan')->with('error','Error Completing Payment');
        }   
    }
    
    public function chekoutCancel(Request $request){
        $sessionId = $request->get('session_id');
        
        $stripeSecretKey = config('setting.stripe_secret_key');
        \Stripe\Stripe::setApiKey($stripeSecretKey);
       
       
       try{
           $session = \Stripe\Checkout\Session::retrieve($sessionId);
           $subscriptionId = $session->subscription;
           
           if($subscriptionId){
                return redirect()->route('company/plan')->with('error','Payment Incomplete');
           }else{
               return redirect()->route('company/plan')->with('error','Payment Incomplete');
           }
       }catch(Exception $e){
           return redirect()->route('company/plan')->with('error','Payment Incomplete');
       }
    }
    
    
    public function payExtraContractor(Request $request){
    $user = auth()->user();
    if(!$user){
        return redirect()->route('login')->with('error','Company Not Found');
    }
    
    $subscriptionData = Subscription::where('user_id',$user->id)->first();
    // if($subscriptionData->plan_id != 3){
    //     return redirect()->route('company/contractor')->with('warning','You cannot add a contractor right now under your current plan');
    // }
    
    $stripeSecretKey = config('setting.stripe_secret_key');
    \Stripe\Stripe::setApiKey($stripeSecretKey);
    
    if($stripeSecretKey){
        $stripe = new \Stripe\StripeClient($stripeSecretKey);
        
        $customer = null;
        
        try {
            // Log the current customer ID to check it
            \Log::info("Stripe Customer ID being used: " . $user->stripe_customer_id);
            
            // Check if the user has a Stripe customer ID
            if (!$user->stripe_customer_id) {
                // No customer ID, create one
                $customer = $stripe->customers->create([
                    'email' => $user->email,
                    'name' => $user->company_name,
                ]);
        
                // Save the new Stripe customer ID
                $user->stripe_customer_id = $customer->id;
                $user->save();
            } else {
                // If there’s an existing customer ID, fetch the customer to validate
                try {
                    $customer = $stripe->customers->retrieve($user->stripe_customer_id);
                } catch (\Stripe\Exception\ApiErrorException $e) {
                    // Log Stripe error if retrieval fails and recreate the customer
                    \Log::error("Error retrieving customer from Stripe: " . $e->getMessage());
                    // Recreate the customer if the ID is invalid or deleted
                    $customer = $stripe->customers->create([
                        'email' => $user->email,
                        'name' => $user->company_name,
                    ]);
                    $user->stripe_customer_id = $customer->id;
                    $user->save();
                }
            }

            // Proceed with creating a checkout session
            $checkoutSession = $stripe->checkout->sessions->create([
                'customer' => $user->stripe_customer_id,
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price' => config('setting.stripe_extra_contractor_price_id'), // Ensure this exists and is active
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'success_url' => route('company/contractor/extra/success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('company/contractor/extra/cancel'),
            ]);
    
            return redirect($checkoutSession->url);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            \Log::error('Stripe API error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Stripe payment failed.');
        } catch (\Exception $e) {
            \Log::error('General error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An unexpected error occurred.');
        }
    } else {
        return redirect()->route('company/contractors')->with('warning','You cannot add a contractor right now under your current plan');
    }
}

    
    public function extraPaymentSuccess(Request $request){
        $sessionId = $request->get('session_id');
        $user = auth()->user();
        
        if (!$sessionId || !$user) {
            return redirect()->route('company/contractor')->with('error', 'Invalid payment session.');
        }
             $stripeSecretKey = config('setting.stripe_secret_key');
            \Stripe\Stripe::setApiKey($stripeSecretKey);
         try {
            $stripe = new \Stripe\StripeClient(config('setting.stripe_secret_key'));
            $session = $stripe->checkout->sessions->retrieve($sessionId);
            $subscriptionId = $session->subscription;
            $subscription = \Stripe\Subscription::retrieve([
               'id' => $subscriptionId,
               'expand' => ['items'],
                ]);
    
            \Stripe\Subscription::update($subscriptionId, [
                'cancel_at_period_end' => true,
            ]);
            
            $subscriptionEndDate = $subscription->items->data[0]['current_period_end'];
            $endDate = date('Y-m-d H:i:s', $subscriptionEndDate) ?? date('Y-m-d');
            $approvedId = session()->get('approved_id');
           
            
            if(isset($approvedId) && $approvedId != ''){
                return $this->registerApproveProcess($approvedId,$endDate);
            }else{
                session()->put('allow_extra_contractor', true);
                session()->put('end_date',$endDate);
                $user->available_contractor += 1;
                $user->save();
                return redirect()->route('company/contractor/create')->with('success', 'Payment successful! You can now add 1 extra contractor.');
            }
            
            
        } catch (\Exception $e) {
            \Log::error('Stripe extra contractor success error: ' . $e->getMessage());
            return redirect()->route('company/contractor')->with('error', 'Unable to verify payment.');
        }
    }
    
    public function registerApproveProcess($userId,$endDate){
       
        $general = new General();
        $sessionUser = auth()->user();
        $user = User::find($userId);
        $expDate = $endDate ?? date('Y-m-d');
        
        if(!$user){
              return redirect()->route('company/contractor')->with('error', 'Contractor Not Found.');
        }
        
        $user->company_approved_status = 1;
        $user->add_on_expired_at = $expDate;
        $user->is_add_on = 1; 
        $result = $user->save();
        
        if($result){
            $general->sendEmail($user->email, $template='register_approve',[
                'name' => $user->first_name . ' ' . $user->last_name,
                'company_name' => $sessionUser->company_name,
             ]);
             session()->forget('approved_id');
            return redirect()->route('company/contractor')->with('success', 'Payment successful! Your contractor Approved Successfully.');
        }
    }
    
    
    public function extraPaymentCancel(){
        return redirect('company/contractors')->with('error', 'Payment was canceled.');
    }
    
    public function checkSubscriptionExpires(){
        return (new Subscription())->checkSubscriptionExpires();
    }



public function handleStripeWebhook(Request $request)
{
    $endpointSecret = env('STRIPE_WEBHOOK_SECRET_KEY');

    $payload = $request->getContent();
    $sigHeader = $request->header('Stripe-Signature');

    Log::info('Stripe Webhook Received', [
        'payload' => $payload,
        'signature' => $sigHeader,
    ]);

    try {
        $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
    } catch (\UnexpectedValueException $e) {
        Log::error('Invalid payload', ['error' => $e->getMessage()]);
        return response()->json(['message' => 'Invalid payload'], 400);
    } catch (SignatureVerificationException $e) {
        Log::error('Invalid signature', ['error' => $e->getMessage()]);
        return response()->json(['message' => 'Invalid signature'], 400);
    }

    Log::info('Stripe Event Type: ' . $event->type);

    switch ($event->type) {
        case 'customer.subscription.deleted':
            $subscription = $event->data->object;
            $this->handleSubscriptionCancelled($subscription);
            break;

        case 'customer.subscription.updated':
            $subscription = $event->data->object;
            if ($subscription->cancel_at_period_end) {
                Log::info('Subscription scheduled for cancellation', [
                    'subscription_id' => $subscription->id
                ]);
                // Optional: handle pre-cancellation logic here
            }
            break;

        default:
            Log::info('Unhandled event type', ['type' => $event->type]);
            break;
    }

    return response()->json(['status' => 'success'], 200);
}



    
   public function handleSubscriptionCancelled($subscription)
{
    Log::info('Handling subscription cancellation', [
        'subscription_id' => $subscription->id,
        'customer' => $subscription->customer
    ]);

    $user = User::where('stripe_customer_id', $subscription->customer)->first();

    if (!$user) {
        Log::warning(' User not found for customer ID', ['customer' => $subscription->customer]);
        return;
    }

    $existingSubscription = Subscription::where('user_id', $user->id)
        ->where('stripe_subscription_id', $subscription->id)
        ->first();

    if ($existingSubscription) {
        $existingSubscription->status = 'canceled';
        $existingSubscription->save();

        Log::info('Subscription marked as canceled in DB');
        
        // Send cancellation email
        if($user->unlimited_conractors != 1){
            $template = 'subscription_expired';
            (new General())->sendEmail($user->email, $template, [
                'company_name' => $user->company_name,
            ]);
        }
    } else {
        Log::warning('No matching subscription found in DB', [
            'subscription_id' => $subscription->id
        ]);
    }
}

}