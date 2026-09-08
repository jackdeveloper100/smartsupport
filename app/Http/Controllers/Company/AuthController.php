<?php

namespace App\Http\Controllers\Company;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;   
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Services\AuthService;
use App\Services\TfaService;
use App\Models\User;
use App\Http\Controllers\Controller;

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
     
     public function login(Request $request){
         
        $userToken = $request->cookie(config('setting.app_uid') . '_user_token');
        if ($userToken && !$this->general->rateLimit('remember_login')) {
            $result = (new AuthService())->loginByAuthToken($userToken);
            if ($result['status']) {
                return redirect($this->general->authRedirectUrl('admin/dashboard'));
            }
        }
        return view('company/auth/login');
     }
     
    public function loginProcess(Request $request){
        $result = (new AuthService())->loginProcess($request->only(['email', 'password']), 2);
        if ($request->ajax()) {
        return response()->json($result);
        }
        
        if (!$result['status']) {
            return redirect()->back()->with('error', $result['message'])->withInput();
        }

        return redirect('company/dashboard')->with('success', $result['message']);
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
        return redirect('login')->withCookie(cookie()->forget(config('setting.app_uid') . '_user_token'));
    }
    
      /**
     * Show the TFA verification page.
     *
     * @return View
     */
    public function tfaVerify(): View
    {
        return view('company/auth/tfa_verify');
    }
    
      public function tfaVerifyProcess(Request $request)
    {
        return response()->json((new TfaService())->verifyProcess($request->all()));
   
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
     * Display the password forgot view.
     *
     * @return \Illuminate\View\View
     */
    public function passwordForgot()
    {
        return view('company/auth/password_forgot');
    }
    
     /**
     * Process password forgot request.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function passwordForgotProcess(Request $request)
    {
        return (new AuthService())->passwordForgotProcess($request->only(['email','otp','password','password_confirm','step']),2);
    }
    
    
    
    public function loginAsContractor($id){
        
        $contractor = User::find($id);

        session(['came_from_company' => true]);
        session(['company_id' => Auth::id()]);
        
         if (!$contractor) {
            // Handle the error or redirect back
            return redirect()->route('company/dashboard')->with('error', 'Contractor Not Found.');
        }
        
        Auth::login($contractor);

        return redirect()->route('contractor/document');
    }
    
     public function loginBackAsCompany(){
        $companyId = session('company_id');
        
        if (!$companyId) {
            return redirect()->route('login')->with('error', 'Company Session Not Found.');
        }
        $company = User::find($companyId);
        
         if (!$company) {
            return redirect()->route('login')->with('error', 'Company not found.');
        }
        
        Auth::logout(); // Log out contractor
        session()->forget(['came_from_company', 'company_id']); // Clear the session flags
    
        Auth::login($company); // Log back in as admin

        return redirect()->route('company/dashboard')->with('success', 'Welcome Back ');
    }

    /**
     * Show password setup page for invited team members.
     */
    public function setupPassword(Request $request)
    {
        $token = $request->query('token');
        if (empty($token)) {
            return redirect()->route('login')->with('error', 'This password setup link is invalid or has expired.');
        }

        $user = User::where('invite_token', $token)->first();
        if (!$user) {
            return redirect()->route('login')->with('error', 'This password setup link is invalid or has expired.');
        }

        // Check if token is older than 7 days (604800 seconds)
        if (!empty($user->invite_token_created_at) && (time() - $user->invite_token_created_at) > 604800) {
            return redirect()->route('login')->with('error', 'This password setup link is invalid or has expired.');
        }

        return view('company/auth/setup_password', compact('user', 'token'));
    }

    /**
     * Process password setup form submission.
     */
    public function setupPasswordProcess(Request $request)
    {
        $rules = [
            'token' => 'required',
            'password' => ['required', (new \App\Helpers\General())->passwordType()],
            'password_confirm' => 'required|same:password',
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $response = [
                'status' => 0,
                'message' => $validator->errors()->first()
            ];
            return $request->ajax() 
                ? response()->json($response) 
                : redirect()->back()->with('error', $response['message'])->withInput();
        }

        $user = User::where('invite_token', $request->input('token'))->first();
        if (!$user) {
            $response = [
                'status' => 0,
                'message' => 'Invalid or expired invitation token.'
            ];
            return $request->ajax() 
                ? response()->json($response) 
                : redirect()->back()->with('error', $response['message'])->withInput();
        }

        $user->password = (new AuthService())->encryptPassword($request->input('password'));
        $user->status = 1; // Mark active
        $user->email_verified = 1; // Mark email verified (received & clicked token link)
        $user->invite_token = null;
        $user->save();

        $response = [
            'status' => 1,
            'message' => 'Password set up successfully! You can now log in.',
            'next' => 'redirect',
            'url' => route('login')
        ];

        return $request->ajax() ? response()->json($response) : redirect($response['url'])->with('success', $response['message']);
    }
}