<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Log;
use App\Models\User;
use App\Services\TfaService;
use App\Services\AccountService;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Display the user dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        return redirect('contractor/document');
        // return view('account/dashboard');
    }

    /**
     * Show the registration form.
     *
     * @return \Illuminate\View\View
     */
    public function register()
    {
        $modelList = User::where('type', 2)
            ->where('status', 1)
            ->where(function($q) {
                $q->whereNull('company_id')
                  ->orWhere('company_id', 0);
            })
            ->get();

        $model = [];
        foreach($modelList as $data){
            if($data->unlimited_conractors == 1){
                $model[] = $data;
            }else{
                $companyPlanId = (new User())->getCompanyPlanInfo($data->id);
                
                if($companyPlanId != 1 && $companyPlanId != null){
                    $model[] = $data;
                }
            }
        }
        return view('account/register',compact('model'));
    }

    /**
     * Handle the registration process.
     *
     * @param Request $request The incoming request.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function registerProcess(Request $request)
    {
        return response()->json((new AccountService())->registerProcess($request->all()));
    }


     /**
     * Show the registration form.
     *
     * @return \Illuminate\View\View
     */
    public function companyRegister()
    {
        return view('company/account/register');
    }
    
    /**
     * Handle the registration process.
     *
     * @param Request $request The incoming request.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function companyRegisterProcess(Request $request)
    {
        return response()->json((new AccountService())->companyRegisterProcess($request->all()));
    }

    
    /**
     * Display the account update form.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function update(Request $request)
    {
        $data = auth()->user();
        return view('account/update', compact('data'));
    }

    /**
     * Save updated account details.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request)
    {
        return response()->json((new AccountService())->save($request, auth()->user()));
    }

    /**
     * Display the change password form.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function passwordChange(Request $request)
    {
        $data = auth()->user();
        return view('account.change_password',compact('data'));
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

    /**
     * Display the profile image update form.
     *
     * @return \Illuminate\View\View
     */
    public function image()
    {
        $model = auth()->user();
        return view('account/image', compact('model'));
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
     * Show TFA settings page.
     */
    public function tfa()
    {
        return view('account/tfa', ['data' => auth()->user()]);
    }

    /**
     * Toggle TFA status.
     *
     * @return JsonResponse
     */
    public function tfaStatusChange()
    {
        $user = auth()->user();
        $result = (new TfaService())->tfaStatusChange(auth()->user());
        return response()->json($result);
        // $status_tfa=$user->getData()->status_tfa;
        // $user->save();
        // return response()->json(['status' => 1, 'next' => 'refresh', 'message' => $status_tfa ? 'Two Factor Authentication is enabled' : 'Two Factor Authentication is disabled']);
    }

    /**
     * Delete the current user's profile image.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function imageDelete()
    {
        return response()->json((new AccountService())->deleteImage(auth()->user()));
    }

    /**
     * Display the device management view.
     *
     * @return \Illuminate\View\View
     */
    public function device()
    {
        $data = auth()->user();
        return view('account/device',compact('data'));
    }

    /**
     * Retrieve the list of user's devices.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deviceList(Request $request)
    {
        return response()->json((new Device())->list($request->all(), auth()->id()));
    }

    /**
     * Log out the specified device.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deviceLogout(Request $request)
    {
        (new Device())->forceLogout($request->input('id'));

        return response()->json(['status' => 1, 'message' => 'Device Logout Successfully', 'next' => 'reload']);
    }

    /**
     * Display the user activity log view.
     *
     * @return \Illuminate\View\View
     */
    public function log()
    {
        $data = auth()->user();
        return view('account/log',compact('data'));
    }

    /**
     * Retrieve the list of user activity logs.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logList(Request $request)
    {
        return response()->json((new Log())->list($request->all(), auth()->id()));
    }

    /**
     * Revoke all trusted devices for the current user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function revokeAll(Request $request)
    {
        return response()->json((new AccountService())->revokeAll2FADevices(auth()->user()));
    }
}
