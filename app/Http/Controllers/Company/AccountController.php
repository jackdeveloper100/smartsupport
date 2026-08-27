<?php

namespace App\Http\Controllers\Company;

use App\Models\Device;
use App\Models\Log;
use App\Helpers\General;
use App\Services\AccountService;
use App\Services\TfaService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    /**
     * Display the account update form.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function update(Request $request)
    {
        $model = auth()->user();
        return view('company/account/update', compact('model'));
    }
    
      /**
     * Save updated account details.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request)
       {
            $user = auth()->user();
        
            $rules = [
                'company_name' => 'required|string|max:255',
                'email'        => 'required|email',
          
            ];
        
            $validator = Validator::make($request->all(), $rules);
        
            if ($validator->fails()) {
                return response()->json([
                    'status'  => 0,
                    'message' => $validator->errors()->first()
                ]);
            }
        
            $data = [
                'company_name'    => $request->company_name,
                'email'           => $request->email,
                'email_reminders' => $request->has('email_reminders') ? 1 : 0,
                'is_affiliate'    => $request->has('is_affiliate') ? 1 : 0,
                'is_company_email_enabled' => $request->has('is_company_email_enabled') ? 1 : 0,
                'is_contractor_email_enabled' => $request->has('is_contractor_email_enabled') ? 1 : 0,
               
                
            ];

            $user->update($data);
        
            return response()->json([
                'status'  => 1,
                'message' => 'Account Updated Successfully',
                'next'    => 'reload'
            ]);
        }
    
    public function affiliate(Request $request)
        {
            $model = auth()->user();
            return view('company/account/affiliate', compact('model'));
        }
    public function affiliateSave(Request $request)
    {
        $user = auth()->user();
    
        $rules = [
         
            'first_name'               => 'required|string|max:255',
            'last_name' => 'nullable|string|max:100',
            'affiliate_company_name' => 'nullable|string|max:255',
            'affiliate_website' => 'nullable|url|max:255',
            'affiliate_role_type' => 'required|string',
            'affiliate_role_other' => 'nullable|string|max:255|required_if:affiliate_role_type,other',
            'affiliate_referral_description' => 'nullable|string|max:255',
            'affiliate_reach_volume' => 'required|string',
            'affiliate_referral_methods' => 'nullable|array',
            'affiliate_referral_methods.*' => 'string',
            'affiliate_commission_consent' => 'required|in:1,0',
            'affiliate_motivation' => 'nullable|string|max:255',
            'affiliate_notes' => 'nullable|string|max:255',

        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status'  => 0,
                'message' => $validator->errors()->first()
            ]);
        }

        if (empty($user->affiliate_code)) {
            $user->affiliate_code = base64_encode($user->id); 
        } 
        
        $data = [
               'is_affiliate' =>1,
            'first_name'                      => $request->first_name,
            'last_name'                       => $request->last_name,
            'affiliate_company_name'          => $request->affiliate_company_name,
            'affiliate_website'               => $request->affiliate_website,
            'affiliate_role_type'             => $request->affiliate_role_type,
            'affiliate_role_other'            => $request->affiliate_role_other,
            'affiliate_referral_description'  => $request->affiliate_referral_description,
            'affiliate_reach_volume'           => $request->affiliate_reach_volume,
            'affiliate_commission_consent'     => $request->affiliate_commission_consent,
            'affiliate_motivation'             => $request->affiliate_motivation,
            'affiliate_notes'                  => $request->affiliate_notes,
            'affiliate_terms_accepted'         => 1,
            'affiliate_referral_methods'       => $request->affiliate_referral_methods
                                                    ? json_encode($request->affiliate_referral_methods)
                                                    : null,
        ];
        $data['affiliate_code'] = base64_encode($user->id);
        $user->update($data);

        return redirect()->route('company/account/update')->with('success', 'Affiliate details Apply successfully');

    }
    /**
     * Display the change password form.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function passwordChange(Request $request)
    {
        $model = auth()->user();
        return view('company.account.change_password', compact('model'));
    }
    
     /**
     * Process password change request.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePasswordProcess(Request $request)
    {
        return response()->json((new AccountService())->changePassword($request, auth()->user()));
    }
    
     public function tfa()
    {
        // dd(auth()->user());
        return view('company/auth/tfa', ['user' => auth()->user()]);
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
     * Revoke all trusted devices for the current user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function revokeAll()
    {
         return response()->json((new AccountService())->revokeAll2FADevices(auth()->user()));
    }
    
        /**
     * Display the profile image update form.
     *
     * @return \Illuminate\View\View
     */
    public function image()
    {
        $model = auth()->user();
        return view('company.account.image', compact('model'));
    }

    /**
     * Save updated profile image.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function imagesave(Request $request)
    {
        return response()->json((new AccountService())->saveImage($request, auth()->user()));
    }

    /**
     * Delete the current user's profile image.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteImage()
    {
        return response()->json((new AccountService())->deleteImage(auth()->user()));
    }
    
    /**
     * Display the profile image update form.
     *
     * @return \Illuminate\View\View
     */
    public function companyLogo(){
        $model = auth()->user();
        return view('company.account.company_logo', compact('model'));
    }
    
    /**
     * Delete the current user's profile image.
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function companyLogoSave(Request $request){
        $general = new General();
        $user = auth()->user();
        // dd($user);
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
            if ($user->company_logo) {
                $general->deleteFile($user->company_logo, 'profile');
            }
            $user->update(['company_logo' => $uploadResult['file_name']]);
            return ['status' => 1, 'message' => 'Logo Saved Successfully', 'next' => 'reload'];
        }
    }
    
    public function deleteCompanyLogo(){
        $user = auth()->user();
        $general = new General();
        if (!$user->company_logo) {
            return response()->json(['success' => false, 'message' => 'Image not found'], 404);
        }
        if ($user->company_logo) {
            $general->deleteFile($user->company_logo, 'profile');
        }
        $user->update(['company_logo' => null]);
        return ['status' => 1, 'message' => 'Logo Deleted Successfully', 'next' => 'reload'];
    }
}