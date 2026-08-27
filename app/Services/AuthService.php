<?php 
namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Subscription;
use App\Helpers\General;
use App\Models\User;
use App\Models\Device;
use App\Models\Log;
use Illuminate\Support\Str;

class AuthService
{
    /**
     * Encrypt a password.
     *
     * @param string $password
     * @return string
     */
    public function encryptPassword(string $password): string
    {
        return Hash::make($password);
    }

    /**
     * Check if a given password matches the encrypted password.
     *
     * @param string $password
     * @param string $encryptedPassword
     * @return bool
     */
    public function checkPassword(string $password, string $encryptedPassword): bool
    {
        return Hash::check($password, $encryptedPassword);
    }

    /**
     * Generate a random token.
     *
     * @return string
     */
    public function generateToken(): string
    {
        return base64_encode(Str::random(32) . '_' . time());
    }

    /**
     * Check if a token has expired.
     *
     * @param string $token
     * @return bool
     */
    public function checkTokenIsExpired(string $token): bool
    {
        $token = base64_decode($token);
        $token = explode('_', $token);
        $timestamp = (int)(@$token[1] ?? 0);
        return time() > ($timestamp + (int)config('setting.token_expire_time'));
    }

    
    /**
     * Attempt to log in a user by authentication token.
     *
     * @param string $authToken
     * @return array
     */
    public function loginByAuthToken(string $authToken): array
    {
        if ($this->checkTokenIsExpired($authToken)) {
            return ['status' => 0, 'message' => 'Login failed'];
        }

        $deviceObj = new Device();
        $device = $deviceObj->where(['remember_token' => $authToken, 'device_uid' => @$_COOKIE[config("setting.app_uid") . '_token']])
            ->where('remember_expire_at', '>', time())
            ->first();

        if ($device && $device->user_id) {
            $user = User::where(['id' => $device->user_id, 'status' => 1])->first();
            if ($user) {
                auth()->login($user);
                (new Log)->add($user->id, 2);
                (new Device())->login($user->id);
                return ['status' => 1, 'message' => 'Login success'];
            }
        }

        return ['status' => 0, 'message' => 'Login failed'];
    }
    
    /**
     * Process the login.
     *
     * @param array $postData
     * @param int $type
     * @return array
     */
    public function loginProcess($postData, $type = 1): array
    {
        $general = new General();
        if ($general->rateLimit('login')) {
            return ['status'=>0,'message' => 'Too many attempts, please try again later.'];
        }
        $validator = Validator::make($postData, [
            'email' => 'required|email',
            'password' => 'required',
        ]);     
        if ($validator->fails()) {
            return ['status' => 0,'message' => $validator->errors()->first()];
        }
        if($type == 0){
             $user = User::where(['email' => $postData['email']])->where('type', $type)->first();
        }else{
            $user = User::where(['email' => $postData['email']])->whereIn('type', [1,2])->first();;
        }
       
        if (!$user) {
            return ['status' => 0, 'message' => 'Email or Password is Not Valid'];
        }
        $userData=$user->getData();
        if ($user->status == 0) {
            return ['status' => 0, 'message' => 'Your Account is Blocked'];
        }
            
        if ($user->login_failed >= config('setting.login_max_attempt') && $user->login_failed_at > (time() - config('setting.login_ban_time'))) {
            return ['status' => 0, 'message' => 'Max login attempt exceed. Please Try after ' . ceil((config('setting.login_ban_time') - (time() - $user->login_failed_at)) / 60) . ' Minutes'];
        }
        $LogObj = new Log();
        // dd($postData['password'],$user->password);
        if (!$this->checkPassword($postData['password'], $user->password)) {
            $user->updateData([
                'login_failed' => $userData->login_failed + 1,
                'login_failed_at' => time(),
            ]);
            
            $LogObj->add($user->id, 0);
            return ['status' => 0, 'message' => 'Email or Password is Not Valid'];
        }
        // dd($user->email,base64_encode($user->email));
        if (config('setting.user_email_verify') && $user->email_verified != 1) 
        {
            (new \App\Services\TfaService())->sendOTP($user, $template = 'login_otp');
            return ['status' => 2, 'message' => 'Please Verify Your Email, <a class="noroute" href="' . route('auth/verify', ['type'=>'email','fromCame'=>'login','token' => base64_encode($user->email)]) . '">Click here</a> to Verify Your Email Address.'];
        }
        
        // if($user->type == 1){
        //     if($user->company_approved_status == 0 ){
        //         return ['status' => 2, 'message'=> 'Your Registration Request is Under Process'];
        //     }
            
        //     if($user->company_approved_status == 2){
        //         return ['status' => 0, 'message'=> 'Your Registration Request has been Rejected'];
        //     }
        // }
        
        if ($userData->login_failed){
            $user->updateData([
                'login_failed' => 0,
            ]);
        }
        
        $type = '';
        if($user->type == 1 ){
            $type = 1;
        }elseif($user->type == 2){
             $type = 2  ;
        }
        
        Auth::guard()->login($user);
        $LogObj->add($user->id, 1);
        
        (new Device())->login($user->id, @$postData['remember']);
        $LogObj->sendNewDeviceMail($user);
        if ($user->status_tfa == 1) {
            if (!in_array(@$_COOKIE[config("setting.app_uid") . '_token'], explode(',', $user->ignore_2fa_device))) {
                session(['tfa_verify' => 1]);
                (new \App\Services\TfaService())->sendOTP($user , $template ='login_otp');
            }
        }
        return ['status' => 1, 'message' => 'Login success', 'type' => $type];
    }

    /**
     * Process the otp login.
     *
     * @param array $postData
     * @param int $type
     * @return array
     */
    public function loginOtpProcess($postData)
    {
        $general = new General();
        if ($general->rateLimit('otp_login')) {
            return ['status'=>0,'message' => 'Too many attempts, please try again later.'];
        }
        $step=$postData['step'];
        if($step==1){
            if ($general->recaptchaFails()) {
                return ['status'=>0,'message' => 'Please complete the captcha.'];
            }
        }
        
        $validationRules=['email' => 'required|email'];
        if($step==2){
            $validationRules['otp'] = 'required';
        }
        
        $validator = Validator::make($postData, $validationRules);
        if ($validator->fails()) {
            return ['status' => 0,'message' => $validator->errors()->first()];
        }

        // Fetch user by email
        $user = User::where('email', $postData['email'])->where('type',1)->first();
        if (!$user) {
            return ['status'=>0,'message' => 'Email Not Valid'];
        }
        $userData=$user->getData();
        // Check if the user's account is active
        if ($user->status == 0) {
            return ['status'=>0,'message' => 'Your Account is blocked'];
        }
        $general = new General();
        $tfaService = new TfaService();
        if($step==1){
            $tfaService->sendOTP($user);
            return ['status'=>1,'message' => 'Otp sent successfully','next'=>'step_2'];
        }
        
        if($userData->otp_failed>=config('setting.login_max_attempt')){
            return ['status' => 0, 'message' => 'Too many attempts, please try again later.'];
        }
        dd($user);
        $result=$tfaService->checkOtp($postData['otp'], $user->otp);
        if (!$result['status']) {
            $user->updateData(['otp_failed',$userData->otp_failed+1]);
            return $result;
        }
        $user->setData([
            'otp' => null,
            'otp_failed'=>0
        ]);
        $user->save();
        Auth::login($user);

        (new Log())->add($user->id, 4);
        (new Device())->login($user->id);

        return ['status'=>1,'message' => 'Login successful','next'=>'redirect','url'=>$general->authRedirectUrl(config('setting.login_redirect_url'))];
        
    }
    
    /**
     * Process the password forgot.
     *
     * @param array $postData
     * @param int $type
     * @return array
     */
    public function passwordForgotProcess($postData,$type=1){
        $general = new General();
        if ($general->rateLimit('password_forgot')) {
            return ['status'=>0,'message' => 'Too many attempts, please try again later.'];
        }
        $step=$postData['step'];
        $validationRules=['email' => 'required|email'];
        $validator = Validator::make($postData, $validationRules);
        
          if ($validator->fails()) {
            return ['status' => 0,'message' => $validator->errors()->first()];
        }
        
        
        if ($step==1 && $general->recaptchaFails()) {
            return ['status'=>0,'message' => 'Please complete the captcha.'];
        }
        
        if($step==2){
            $validationRules['otp'] = 'required|numeric|digits:6';
        }
        if($step==3){
            $validationRules['otp'] = 'required|numeric|digits:6';
            $validationRules['password'] = [
                'required',
                $general->passwordType()
            ];
            $validationRules['password_confirm'] = 'required|same:password';
        }
        
        $validator = Validator::make($postData, $validationRules);
        if ($validator->fails()) {
            return ['status' => 0,'message' => $validator->errors()->first()];
        }
        
        if($type == 0){
            $user = User::where('email', $postData['email'])->where('type',$type)->first();
        }else{
              $user = User::where('email', $postData['email'])->whereIn('type',[1,2])->first();}
        
        
        if (!$user) {
            return ['status'=>0,'message' => 'Email Not Valid'];
        }
        // $userData=$user->getData();
        $tfaService = new TfaService();
        if($step==1){
            $tfaService->sendOTP($user,'forgot_password');
            return ['status'=>1,'message' => 'Otp sent successfully','next'=>'step_2'];
        }

        if($user->login_attempt >=config('setting.login_max_attempt')){
            return ['status' => 0, 'message' => 'Too many attempts, please try again later.'];
        }
        
        $result=$tfaService->checkOtp($postData['otp'], $user->login_otp);
        if (!$result['status']) {
            $user->updateData(['login_attempt',$user->login_attempt+1]);
            return $result;
        }
        
        if($step==2){
            return ['status'=>1,'message' => 'Otp is valid','next'  => 'step_3'];
        }else{
                $user->update([
                'password' => $this->encryptPassword($postData['password']),
                'login_otp' => '',
            ]);
        
        if($type == 0){
            $redirectUrl = 'admin/login';
        }elseif($type == 2){
            $redirectUrl = 'login';
        }else{
            $redirectUrl = 'login';
        }
            return ['status'=>1,'message' => 'Password Reset Successfully. You Can Now Log In','next'=>'redirect','url'=> $redirectUrl ];
        }
    }
    
}