<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;
use App\Models\Plan;
use App\Models\User;
use App\Services\AuthService;
use App\Services\TfaService;

/**
 * Class AuthController
 *
 * Manages user authentication, including login, TFA (Two-Factor Authentication), OTP-based login, social login, and password recovery.
 */
class AuthController extends Controller
{
    /**
     * Display the login view or redirect if the user is authenticated via cookie.
     *
     * @param Request $request
     * @return RedirectResponse|View
     */
    public function login(Request $request)
    {
       if (!$request->session()->has('data-theme')) {
            $request->session()->put('data-theme', 'light');
        }
        $plan = $request->plan ?? '';
        
        if (!empty($plan)) {
            $plan = (new Plan())->getPlanId($plan);
            if(!empty($plan)){
                $request->session()->put('selected_plan', $plan);
            }
        }
        
        $userToken = $request->cookie(config('setting.app_uid') . '_user_token');
        if ($userToken && !$this->general->rateLimit('remember_login')) {
            $result = (new AuthService())->loginByAuthToken($userToken);
            if ($result['status']) {
                if ($request->session()->has('selected_plan')) {
                    $plan = $request->session()->pull('selected_plan');
                    return redirect()->route('plan-select', ['plan_id' => $plan]);
                }
                return redirect($this->general->authRedirectUrl('dashboard'));
            }
        }
        return view('auth/login');
    }

    /**
     * Process login with validation, rate limiting, and authentication.
     *
     * @param Request $request
     * @return RedirectResponse
     */
        public function loginProcess(Request $request)
    {
        $result = (new AuthService())->loginProcess($request->only(['email', 'password']));
        if ($request->ajax()) {
            return response()->json($result);
        }
        if (!$result['status']) {
            return redirect()->back()->with('error', $result['message'])->withInput();
        }
        
        if($result['type'] == 1){
            return redirect('contractor/document')->with('success', $result['message']);
        }elseif($result['type'] == 2){
            if ($request->session()->has('selected_plan')) {
                    $plan = $request->session()->pull('selected_plan');
                    return redirect()->route('plan-select', ['plan_id' => $plan]);
                }
           return redirect('company/dashboard')->with('success', $result['message']);
        }
    }


    /**
     * Log out the authenticated user and clear session data.
     *
     * @return RedirectResponse
     */
    public function logout()
    {
        if (Auth::check()) {
            Auth::logout();
            (new \App\Models\Device())->logout();
        }
        return redirect('login')->withCookie(cookie()->forget(config('setting.app_uid') . '_user_token'));
    }

    /**
     * Process the OTP login.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function loginOtp()
    {
        return view('auth/login_otp'); 
    }

    /**
     * Process the OTP login.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function loginOtpProcess(Request $request)
    {
        return (new AuthService())->loginOtpProcess($request->only(['email','otp','step']));
    }
    
    
    /**
     * Display the password forgot view.
     *
     * @return \Illuminate\View\View
     */
    public function passwordForgot()
    {
        return view('auth/password_forgot');
    }

   /**
     * Process password forgot request.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function passwordForgotProcess(Request $request)
    {
      
        return (new AuthService())->passwordForgotProcess($request->only(['email','otp','password','password_confirm','step']));
    }

    // ----------------- Two-Factor Authentication (TFA) Methods -------------------
    /**
     * Show the TFA verification page.
     *
     * @return View
     */
    public function verify(Request $request)
    {
        $type=$request->get('type');
        $token=$request->get('token','');
        return view('auth/verify',compact('type','token'));
    }

    /** 
     * Process TFA OTP verification.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function verifyProcess(Request $request)
    {
       
        return response()->json((new TfaService())->verifyProcess($request->only(['otp','type','code','token','skip_tfa','fromCame'])));
    }

    /**
     * resend otp.
     *
     * @return \Illuminate\View\View
     */
    public function resendOTP(Request $request)
    {
        return response()->json((new TfaService())->resendOTP($request->only(['type','code','token','fromCame'])));
    }

    // ----------------- Two-Factor Authentication (TFA) Methods END-------------------

    
    
    // Social Login methods

    /**
     * Redirect to social login provider.
     *
     * @param Request $request
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function socialLogin(Request $request)
    {
        return Socialite::driver($request->route('type'))->redirect();
    }

    /**
     * Handle social login callback and process user data.
     *
     * @param string $provider
     * @return RedirectResponse
     */
    public function socialLoginCallback(string $type): RedirectResponse
    {
        $socialUser = Socialite::driver($type)->stateless()->user();
        $user=(new User())->where('email',$socialUser->email)->first();
        Auth::login($user);
        return redirect($this->general->authRedirectUrl(config('setting.login_redirect_url')));
        
    }
}
