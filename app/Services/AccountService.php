<?php
namespace App\Services;

use App\Helpers\General;
use App\Models\Log;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Notification;
use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AccountService
{
    /**
     * Process user registration.
     *
     * @param array $postData
     * @return array
     */
    public function registerProcess(array $postData): array
    {
        $general = new General();

        if ($general->rateLimit('register')) {
            return ['status' => 0, 'message' => 'Too many attempts, please try again later.'];
        }
        
        $validator = Validator::make($postData, [
            'first_name' => 'required|alpha|max:255',
            'last_name' => 'nullable|alpha|max:255',
            // 'business_name' => 'required|max:255',
            'company_name' => 'required|max:255',
            'email' => 'required|email|unique:user',
            'password' => [ 
                'required', 
                (new General())->passwordType()
            ],
            'password_confirm' => 'required|same:password',
        ],[
            'company_name.required' => 'The company selection is required',
        ]);
        
        if ($validator->fails()) {
            return ['status' => 0, 'message' => $validator->errors()->first()];
        }
        
          if ($general->recaptchaFails()) {
            return ['status'=>0,'message' => 'Please complete the captcha.'];
        }
         $validator = Validator::make($postData, [
            'terms' => 'required',
          
        ]);
        
        if ($validator->fails()) {
            return ['status' => 0, 'message' => $validator->errors()->first()];
        }

        $result = $general->verifyEmail($postData['email']);
        if (!$result['status']) {
            return $result;
        }

        $ip = $general->getClientIp();
        $ipInfo = $general->getIpInfo($ip);

        $userObj = new User();
        $service = new AuthService();
        $user = $userObj->create([
            'first_name' => $postData['first_name'],
            'last_name' => $postData['last_name'] ?? null,
            'business_name' => $postData['business_name'] ?? null,
            'company_id' => $postData['company_name'],
            'email' => $postData['email'],
            'password' => (new AuthService())->encryptPassword($postData['password']),
            'country' => isset($ipInfo->country_name) ? $ipInfo->country_name : '',
            'timezone' => config('app.timezone'),
            'data'=>$userObj->setData('registered_ip',$ip)
        ]);
        // $user->save();

        (new Log())->add($user->id, 3);
        (new Notification())->registerEntry($user->id);

        if (config('setting.user_email_verify')) {
            (new \App\Services\TfaService())->sendOTP($user, $template = 'register_otp');
            $token = base64_encode($user->email);
            return ['status' => 1, 'message' => 'Thank You For Registration, Please Verify Your Email.', 'next' => 'redirect','url'=> 'auth/verify?type=email&token=' . $token];
        } else {
            Auth::guard()->login($user);
            (new Device())->login($user->id, 0);
            (new Log())->add($user->id, 1);
        }
            
        return ['status' => 1, 'message' => 'Thank You For Registration','next'=>'redirect','url'=>config('setting.login_redirect_url')];
    }
    
    
   public function companyRegisterProcess(array $postData): array
    {

        $general = new General();

        if ($general->rateLimit('register')) {
            return ['status' => 0, 'message' => 'Too many attempts, please try again later.'];
        }
        
        $validator = Validator::make($postData, [
            'company_name' => 'required',
            'email' => 'required|email|unique:user',
            'password' => [ 
                'required', 
                (new General())->passwordType()
            ],
            'password_confirm' => 'required|same:password',
        ]);
        
        if ($validator->fails()) {
            return ['status' => 0, 'message' => $validator->errors()->first()];
        }
        
          if ($general->recaptchaFails()) {
            return ['status'=>0,'message' => 'Please complete the captcha.'];
        }
         $validator = Validator::make($postData, [
            'terms' => 'required',
          
        ]);
        
        if ($validator->fails()) {
            return ['status' => 0, 'message' => $validator->errors()->first()];
        }

        $result = $general->verifyEmail($postData['email']);
        if (!$result['status']) {
            return $result;
        }
       
        $ip = $general->getClientIp();
        $ipInfo = $general->getIpInfo($ip);
        
        $userObj = new User();
        $service = new AuthService();
        $user = $userObj->create([
            'company_name' => $postData['company_name'],
            'type' => $postData['type'] ?? null,
            'email' => $postData['email'],
            'password' => (new AuthService())->encryptPassword($postData['password']),
            'country' => isset($ipInfo->country_name) ? $ipInfo->country_name : '',
            'timezone' => config('app.timezone'),
            'data'=>$userObj->setData('registered_ip',$ip)
        ]);
        $user->save();
        
        // Save default required documents for this newly registered company.
        DB::table('company_allowed_documents')->updateOrInsert(
            ['company_id' => $user->id],
            [
                'document_type_id' => json_encode([1, 2, 4]),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        
        (new Log())->add($user->id, 3);
        (new Notification())->registerEntry($user->id);

        if (config('setting.user_email_verify')) {
            (new \App\Services\TfaService())->sendOTP($user, $template = 'register_otp');
            $token = base64_encode($user->email);
            return ['status' => 1, 'message' => 'Thank You For Registration, Please Verify Your Email.', 'next' => 'redirect','url'=> 'company/auth/verify?type=email&token=' . $token];
        } else {
            Auth::guard()->login($user);
            (new Device())->login($user->id, 0);
            (new Log())->add($user->id, 1);
        }
            
        return ['status' => 1, 'message' => 'Thank You For Registration','next'=>'redirect','url'=>config('setting.login_redirect_url')];
    }
    
    public function save($request, $user)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|alpha',
            // 'last_name' => 'required|alpha',
            'email' => 'required|email|regex:/(.+)@(.+)\.(.+)/i',
        ]);

        if ($validator->fails()) {
            return [
                'status' => 0,
                'message' => $validator->errors()->first()
            ];
        }
        $user->update([
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'email' => $request->input('email')
        ]);

        return ['status' => 1, 'message' => 'Account Updated Successfully', 'next' => 'reload'];
    }

    public function changePassword($request, $user)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => [
                'required',
                (new General())->passwordType() 
            ],
            'confirm_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return ['status' => 0, 'message' => $validator->errors()->first()];
        }

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return ['status' => 0, 'message' => 'Old password does not match!'];
        }

        $user->update(['password' => bcrypt($request->input('confirm_password'))]);

        return ['status' => 1, 'message' => 'Password Updated Successfully', 'next' => 'refresh'];
    }

    public function saveImage($request, $user)
    {
        // dd(123);
        $general = new General();
        $validator = Validator::make($request->all(), [
            'image' => 'required|' . $general->fileRules('image'),
        ]);
        if ($validator->fails()) {
            return ['status' => 0, 'message' => $validator->errors()->first()];
        }
        $uploadResult = $general->uploadFile($request->file('image'), 'profile');
        // dd($uploadResult);
        if (!$uploadResult['status']) {
            return $uploadResult;
        }
        if ($uploadResult['file_name']) {
            if ($user->image) {
                $general->deleteFile($user->image, 'profile');
            }
            $user->update(['image' => $uploadResult['file_name']]);
            return ['status' => 1, 'message' => 'Account Updated Successfully', 'next' => 'reload'];
        }
        return ['status' => 0, 'message' => 'Upload Unsuccessful'];
    }

    public function deleteImage($user)
    {
        $general = new General();
        if (!$user->image) {
            return response()->json(['success' => false, 'message' => 'Image not found'], 404);
        }
        if ($user->image) {
            $general->deleteFile($user->image, 'profile');
        }
        $user->update(['image' => null]);
        return ['status' => 1, 'message' => 'Image Deleted Successfully', 'next' => 'reload'];
    }

    public function revokeAll2FADevices($user)
    {
        // dd($user);
        // $user->update(['ignore_2fa_device' => null]);
        $user->ignore_2fa_device = null;
        $user->save();
        return ['status' => 1, 'message' => 'Your Devices Revoked Successfully.', 'next' => 'refresh'];
    }
}
