<?php

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DateTimeZone;
use App\Models\User;

/**
 * Class SettingController
 *
 * Handles the management of application settings.
 *
 * @package App\Http\Controllers\Admin
 */
class SettingController extends Controller
{
    /**
     * Display the settings update form.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function update(Request $request)
    {
        $setting = $this->general->getAllSettings();
        $timezonelist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

        return view('admin/setting/update', ['setting' => $setting, 'timezonelist' => $timezonelist]);
    }

    /**
     * Save application settings.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request)
    {
        return response()->json((new Setting())->store($request->all()));
    }

    /**
     * Save an uploaded file for the setting.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveLogo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string',
            'image'=>'file|mimes:png|max:1024'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $this->general->getError($validator)]);
        }
        $key=$request->input('key');
        $fileName = $this->general->uploadFile($request->file('image'), 'setting',$key);
        if ($fileName) {
            $setting = Setting::where('key', 'app_'.$key)->first();
            if ($setting) {
                if ($setting->value) {
                    $this->general->deleteFile($setting->value, 'setting');
                }
                $setting->value = $fileName;
                $setting->save();
                $setting->clearCache();
            }
        }
        return response()->json(['status' => 1, 'message' => 'Data Saved Successfully']);
    }
    
     public function saveFile(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'key' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $this->general->getError($validator)]);
        }

        $key = $request->key;
        $file = $request->file($key);

        if ($file) {
            $maxSize = match ($key) {
                'app_fevicon' => 512,
                'app_logo' => 1024,
                default => 1024,
            };
            $fileValidator = Validator::make($request->all(), [
                $key => $this->general->fileRules('', $maxSize),
            ]);

            if ($fileValidator->fails()) {
                return response()->json(['status' => 0, 'message' => $fileValidator->errors()->first()]);
            }

            $fileName = $this->general->uploadFile($file, 'setting');
            
            if ($fileName) {
                $setting = Setting::where('key', $key)->first();
                if ($setting) {
                    if ($setting->value) {
                        $this->general->deleteFile($setting->value, 'setting');
                    }
                    $setting->value = $fileName['file_name'];
                    $setting->save();
                    $setting->clearCache();
                }
            }
        }
        $message = '';
        
        if($request->key == 'app_logo'){
            $message = 'Logo Updated Successfully';
        }elseif($request->key == 'app_fevicon'){
            $message = 'Fevicon Updated Successfully';
        }

        return response()->json(['status' => 1, 'message' => $message, 'next' => 'refresh']);
    }
    /**
     * Clear the settings cache.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cacheClear(Request $request)
    {
        (new Setting())->clearCache();
        return redirect('admin/setting/update')->with('success', 'Setting Cache Cleared');
    }

    /**
     * Save SMTP settings.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function smtp(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'host' => 'required|string',
            'username' => 'required|string',
            'password' => 'required|string',
            'encryption' => 'required|string',
            'port' => 'required|integer',
            'mail_from_address' => [
                'required',
                'email',
            ],
            'mail_from_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }

        $setting = $request->only([
            'host',
            'username',
            'password',
            'encryption',
            'port',
            'mail_from_address',
            'mail_from_name',
        ]);

        (new Setting())->updateAll($setting);
        return response()->json(['status' => 1, 'message' => 'Data Saved Successfully']);
    }

    /**
     * Save CAPTCHA settings.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function captcha(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'google_recaptcha' => 'required|string',
            'google_recaptcha_secret_key' => 'required|string',
            'google_recaptcha_public_key' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }

        $setting = $request->only([
            'google_recaptcha',
            'google_recaptcha_secret_key',
            'google_recaptcha_public_key',
        ]);

        (new Setting())->updateAll($setting);
        return response()->json(['status' => 1, 'message' => 'Data Saved Successfully']);
    }

    /**
     * Save social login settings.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function social(Request $request)
    
    {
        
        $validator = Validator::make($request->all(),[
            'google_login' => 'required',
            'google_client_id' => 'required|string',
            'google_client_secret' => 'required|string'
        ]);

        if($validator->fails()){
            return response()->json(['status'=> 0, 'message'=> $validator->errors()->first()]);
        }

        $setting = $request->only([
            'google_client_id',
            'google_client_secret',
            'google_login',
        ]);

        (new Setting())->updateAll($setting);
        return response()->json(['status' => 1, 'message' => 'Data Saved Successfully']);
    }

    /**
     * Save header and footer content settings.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function content(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'header_content' => 'required',
            'footer_content' => 'required'
        ]);

        if($validator->fails()){
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }

        $setting = $request->only([
            'header_content',
            'footer_content',
        ]);

        (new Setting())->updateAll($setting);
        return response()->json(['status' => 1, 'message' => 'Data Saved Successfully']);
    }

    public function notification(Request $request){
        
        $setting = $request->only([
            'notification_time']);
            
        (new Setting())->updateAll($setting);
        return response()->json(['status' => 1, 'message' => 'Data Saved Successfully']);
    }
    
    /**
     * Save payment settings.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function payment(Request $request)
    {
      $validator = Validator::make($request->all(),[
            'stripe_enable' => 'required',
            'stripe_secret_key' => 'required|string',
            'stripe_public_key' => 'required|string'
        ]);

        if($validator->fails()){
            return response()->json(['status'=> 0, 'message'=> $validator->errors()->first()]);
        }
        
        $setting = $request->only([
            'stripe_enable',
            'stripe_secret_key',
            'stripe_public_key',
        ]);

        (new Setting())->updateAll($setting);
        return response()->json(['status' => 1, 'message' => 'Data Saved Successfully']);
    }

    /**
     * Send a test email.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function mailprocess(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $this->general->getError($validator)]);
        }
        $email = $request->email;
        $user = User::where('email',$email)->first();

        if($user !== null && $user !== '' && $user->type == 1){
            $company = User::where('id',$user->company_id)->first();
            $this->general->sendMail($request->input('email'), 'email | ' . $company->company_name, view('/email/admin/admin-email',compact('company'))->render());
        }else{
            $this->general->sendMail($request->input('email'), 'email | ' . config('setting.app_name'), view('/email/admin/admin-email')->render());    
        }
        return response()->json(['status' => 1, 'message' => 'Email Sent Successfully']);
    }
}
