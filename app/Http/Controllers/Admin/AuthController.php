<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;   
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Services\AuthService;
use App\Services\TfaService;
use App\Models\User;

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
        $userToken = $request->cookie(config('setting.app_uid') . '_user_token');
        if ($userToken && !$this->general->rateLimit('remember_login')) {
            $result = (new AuthService())->loginByAuthToken($userToken);
            if ($result['status']) {
                return redirect($this->general->authRedirectUrl('admin/dashboard'));
            }
        }
        return view('admin/auth/login');
    }

    /**
     * Process login with validation, rate limiting, and authentication.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function loginProcess(Request $request)
    {
        $result = (new AuthService())->loginProcess($request->only(['email', 'password']), 0);
        
        if ($request->ajax()) {
        return response()->json($result);
        }
        
        if (!$result['status']) {
            return redirect()->back()->with('error', $result['message'])->withInput();
        }

          return redirect('admin/dashboard')->with('success', $result['message']);
    
        // if (!$result['status']) {
        //     return redirect()->back()->with('error', $result['message'])->withInput();
        // }
        // // return redirect($this->general->authRedirectUrl('admin/dashboard'))->with('success', $result['message']);
        //  return redirect('admin/dashboard')->with('success', $result['message']);
      
    }
    
    public function loginAsContractor($id){
        
        $contractor = User::find($id);
        
        session(['came_from_admin' => true]);
        session(['admin_id' => Auth::id()]);
        
         if (!$contractor) {
            // Handle the error or redirect back
            return redirect()->route('admin/dashboard')->with('error', 'Contractor Not Found.');
        }
        
        Auth::login($contractor);

        return redirect()->route('contractor/document');
    }
    
    public function loginAsCompany($id){
        $company = User::find($id);
        
        session(['came_from_admin' =>true]);
        session(['admin_id' => Auth::id()]);
        
        if(!$company){
            return redirect()->route('admin/dashboard')->with('error','Company Not Found');
        }
        
        Auth::login($company);
        
        return redirect()->route('company/dashboard');
    }
    
    public function loginBackAsAdmin(){
        $adminId = session('admin_id');
        
        if (!$adminId) {
            return redirect()->route('login')->with('error', 'Admin Session Not Found.');
        }
        $admin = User::find($adminId);
        
         if (!$admin) {
            return redirect()->route('login')->with('error', 'Admin not found.');
        }
        
        Auth::logout(); // Log out contractor
        session()->forget(['came_from_admin', 'admin_id']); // Clear the session flags
    
        Auth::login($admin); // Log back in as admin

        return redirect()->route('admin/dashboard')->with('success', 'Welcome Back Admin');
    }

    /**
     * Log out the authenticated user and clear session data.
     *
     * @return RedirectResponse
     */
    public function logout(): RedirectResponse
    {
        if (Auth::check()) {
            Auth::logout();
            (new \App\Models\Device())->logout();
        }
        return redirect('admin/login')->withCookie(cookie()->forget(config('setting.app_uid') . '_user_token'));
    }
    
    /**
     * Display the password forgot view.
     *
     * @return \Illuminate\View\View
     */
    public function passwordForgot()
    {
        return view('admin.auth.password_forgot');
    }
    
    /**
     * Process password forgot request.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function passwordForgotProcess(Request $request)
    {
       
        return (new AuthService())->passwordForgotProcess($request->only(['email','otp','password','password_confirm','step']),0);
    }

    /**
     * resend otp.
     *
     * @return \Illuminate\View\View
     */
    public function resendOtp(Request $request)
    {
         return response()->json((new TfaService())->resendOTP($request->only(['type','token'])));
    }
    
    /**
     * Display password reset view.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function passwordReset(Request $request)
    {   
        $postData=$request->only(['code']);
        if (!(new AuthService())->passwordResetLinkIsValid($postData,0)) {
            return redirect('login')->withErrors('error' , 'Link is invalid or expired');
        }

        return view('admin/auth/password_reset', ['code' => $request->input('code')]);
    }
    
    /**
     * Process password reset request.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function passwordResetProcess(Request $request)
    {
        $result=(new AuthService())->passwordResetProcess($request->only(['password','code','password_confirmation']));
        if (!$result['status']) {
            return redirect()->back()->with('error', $result['message'])->withInput();
        }
        return redirect('admin/login')->with('success', $result['message']);
    }
    
    // ------------------- Two-Factor Authentication (TFA) Methods -------------------

    /**
     * Show TFA settings page.
     *
     * @return View
     */
    public function tfa()
    {
        return view('admin/auth/tfa', ['user' => auth()->user()]);
    }

    /**
     * Toggle TFA status.
     *
     * @return JsonResponse
     */
    public function tfaStatusChange()
    {
        $result = (new TfaService())->tfaStatusChange(auth()->user());
        return response()->json($result);
    }

    /**
     * Show the TFA verification page.
     *
     * @return View
     */
    public function tfaVerify(): View
    {
        return view('admin/auth/tfa_verify');
    }

    /**
     * Send OTP for TFA.
     *
     * @return JsonResponse
     */
    public function tfaSendOTP(): JsonResponse
    {
        $result = (new TfaService())->sendOTP(auth()->user());
        if (is_array($result) && !$result['status']) {
            return response()->json(['status' => 0, 'message' => $result['message']]);
        }
        return response()->json(['status' => 1, 'message' => 'OTP sent successfully']);
    }
    
    
    public function tfaVerifyProcess(Request $request)
    {
        return response()->json((new TfaService())->verifyProcess($request->all()));
        // $result = (new TfaService())->verifyProcess($request, auth()->user());
        // if (!$result['status']) {
        //     return redirect()->back()->with('error', $result['message'])->withInput();
        // }
        // return redirect()->to('admin/dashboard')->with('success', 'TFA verified successfully');
    }
    
    public function otpLoginVerify(Request $request)
    {
        $code = $request->input('code');
        return view('admin/auth/otp_login_verify', compact('code'));
    }

}
