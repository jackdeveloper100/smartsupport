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
        $isSubUser = ($user->type == 2 && !empty($user->company_id));

        if ($isSubUser) {
            // Team Member Personal Profile Update
            $rules = [
                'first_name' => 'required|string|max:255',
                'last_name'  => 'nullable|string|max:255',
                'email'      => 'required|email|max:255|unique:user,email,' . $user->id,
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status'  => 0,
                    'message' => $validator->errors()->first()
                ]);
            }

            $general = new General();
            $data = [
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name ?? '',
                'email'      => $request->email,
            ];

            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $uploadResult = $general->uploadFile($request->file('image'), 'profile');
                if (!$uploadResult['status']) {
                    return response()->json($uploadResult);
                }
                if ($uploadResult['file_name']) {
                    if ($user->image) {
                        $general->deleteFile($user->image, 'profile');
                    }
                    $data['image'] = $uploadResult['file_name'];
                }
            }

            $user->update($data);
        } else {
            // Main Company Owner Profile Update
            $rules = [
                'company_name' => 'required|string|max:255',
                'email'        => 'required|email|max:255|unique:user,email,' . $user->id,
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
                'is_company_email_enabled' => $request->has('is_company_email_enabled') ? 1 : 0,
                'is_contractor_email_enabled' => $request->has('is_contractor_email_enabled') ? 1 : 0,
            ];

            $user->update($data);
        }

        return response()->json([
            'status'  => 1,
            'message' => 'Profile Updated Successfully',
            'next'    => 'reload'
        ]);
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