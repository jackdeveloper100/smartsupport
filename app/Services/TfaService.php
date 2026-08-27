<?php
namespace App\Services;

use App\Helpers\General;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
class TfaService
{
    /**
     * Generate a random OTP.
     *
     * @return int
     */
    public function generateOtp(): int
    {
        return random_int(100000, 999999);
    }

    /**
     * Check if the provided OTP matches the user's login OTP.
     *
     * @param int $otp
     * @param string $loginOtp
     * @return array
     */
 
public function checkOtp(int $otp, ?string $loginOtp): array
{
    if (empty($loginOtp)) {
        return ['status' => 0, 'message' => 'OTP is invalid'];
    }

    $loginOtpParts = explode('_', $loginOtp);
    $otpFromLogin = $loginOtpParts[0] ?? null;
    $time = (int)($loginOtpParts[1] ?? 0);

    if ($otp != $otpFromLogin) {
        return ['status' => 0, 'message' => 'OTP is invalid'];
    }


     if ($time < (time() - config('setting.token_expire_time'))) {
        return ['status' => 0, 'message' => 'OTP is expired'];
    }

    return ['status' => 1, 'message' => 'Success'];
}

    /**
     * Send login OTP to the user.
     *
     * @param User $user
     * @return void
     */
    public function sendOTP($user,$template='otp')
    {
        
        $otp = $this->generateOtp();
        $user->login_otp = $otp . '_' . time();
        $user->login_attempt =0;
        $user->save();
        // $user->updateData(['login_otp'=>$otp . '_' . time(),'login_attempt'=>0]);
        
        if($user->type == 2){
            (new General())->sendEmail($user->email, $template, [
                'name' => $user->company_name,
                'otp' => $otp
            ]);
        }else{
            (new General())->sendEmail($user->email, $template, [
                'name' => $user->first_name . ' ' . $user->last_name,
                'otp' => $otp
            ]);
        }

        return ['status' => 1, 'message' => 'OTP Sent Successfully'];
    }
    
 

    public function resendOTP($postData){
        $general = new General();
        if ($general->rateLimit('resend_otp',5)) {
            return ['status' => 0, 'message' => 'Too many attempts, please try again later.'];
        }
        if($postData['type'] == 'email'){
            $token=$postData['token'];
            $user = User::where('email', base64_decode($token))->first();
        
        }elseif ($postData['type'] == 'tfa') {
            $user = auth()->user();
        }else{
            $token=$postData['token'];
            $user = User::where('email', base64_decode($token))->first();
        }

        if ($user->status == 0) {
            return ['status' => 0, 'message' => 'Your Account is blocked'];
        }
        return (new TfaService())->sendOTP($user,'login_otp'); 
    }

    public function verifyProcess($postData)
    {
        $general = new General();
        
        if ($general->rateLimit('tfa_verify')) {
            return ['status' => 0, 'message' => 'Too many attempts, please try again later.'];
        }

        $validator = Validator::make($postData, [
            'otp' => 'required|numeric|digits:6',
        ]);
        
        
        $user = auth()->user();
        
        if ($validator->fails()) {
            return ['status' => 0, 'message' => $validator->errors()->first()];
        }
        if($postData['type'] =='email'){
            $code=$postData['token'];
            $user = User::where('email', base64_decode($code))->first();
            // dd($user);
        }elseif ($postData['type'] == 'tfa') {
            $user = auth()->user();
         }else{
            $token=$postData['token'];
            $user = User::where('email', base64_decode($token))->first();
            // dd($user);
        }
        // dd($user);
        if ($user->status == 0) {
            return ['status' => 0, 'message' => 'Your Account is Blocked'];
        }
        if($user->otp_failed>=config('setting.login_max_attempt')){
            return ['status' => 0, 'message' => 'Too Many Attempts, Please Try Again Later.'];
        }
        
        $result=$this->checkOtp($postData['otp'], $user->login_otp);
        if (!$result['status']) {
            $user->login_attempt=$user->login_attempt+1;
            $user->save();
            return $result;
        }

        $user->login_otp = '';
        if($postData['type']=='tfa'){
            if (@$postData['skip_tfa']) {
                $ignoredDevices = explode(',',$user->ignore_2fa_device);
                $token = $_COOKIE[config('setting.app_uid') . '_token'] ?? null;
                if ($token && !in_array($token, $ignoredDevices)) {
                    $ignoredDevices[] = $token;
                    // dd($user);
                    $user->ignore_2fa_device = implode(',', $ignoredDevices);
                }
            }
            Session::forget('tfa_verify');
        }
        $user->save();
        
        if($user->type == 0){
            $redirectUrl=config('setting.admin_login_redirect_url');
        }elseif($user->type == 2){
            $redirectUrl=config('setting.company_login_redirect_url');
        }else{
            $redirectUrl=config('setting.login_redirect_url');
        }
       
       if(isset($postData['fromCame']) == 'login' && $postData['type'] == 'email' ){
            $user->email_verified = 1;
            $user->save();
            if($user->type == 2 ){
              Auth::login($user);
                $redirectUrl=config('setting.company_login_redirect_url');
            }elseif($user->type == 1){
                Auth::login($user);
                $redirectUrl=config('setting.login_redirect_url');
            }
       }elseif($postData['type']=='email'){
            $user->email_verified = 1;
            $user->save();
            $redirectUrl='login';
        }elseif($postData['type'] =='email' && $user->type == 0){
            $user->email_verified = 1;
            $user->save();
            Auth::login($user);
            $redirectUrl=config('setting.admin_login_redirect_url');
        }
        
        if($postData['type'] == 'email' && $user->type == 0){
           $user->email_verified = 1;
            $user->save();
            Auth::login($user);
            $redirectUrl=config('setting.admin_login_redirect_url');
        }

        if($user->type == 1 && $postData['type'] == 'email'){
            $companyName =  (new User())->getCompanyName($user->company_id);
            $general->sendEmail($user->email, $template='register_success',[
                'name' => $user->first_name . ' ' . $user->last_name,
                'company_name' => $companyName,
            ]);
        }
        
        if($user->type == 2 && $postData['type'] == 'email'){
            
            $general->sendEmail($user->email, $template='free_trail_start',[
                'company_name' => $user->company_name,
            ]);
        }
        
        return ['status' => 1, 'message' => 'OTP Verified Successfully.','next'=>'redirect','url'=>$redirectUrl];
    }

    public function tfaStatusChange(User $user): array
    {
        $user->status_tfa = !$user->status_tfa;
        $user->save();

        return [
            'status' => 1,
            'next' => 'refresh',
            'message' => $user->status_tfa
                ? 'Two Factor Authentication Enabled'
                : 'Two Factor Authentication Disabled',
        ];
    }
}
