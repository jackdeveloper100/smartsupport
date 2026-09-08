<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Helpers\General;
use App\Models\Device;
use App\Models\Log;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Document;
use App\Models\Notification;
use App\Models\UserAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use App\Models\DocumentType;
use Carbon\Carbon;
use Carbon\CarbonInterface; 
use Illuminate\Support\Facades\DB;


/**
 * Class ContractorController
 * @package App\Http\Controllers\Company
 *
 * Handles user management functionalities in the admin panel.
 */
class VendorReceivedontroller extends Controller
{
    /**
     * Display the user index view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $sessionUser = auth()->user();
        $redirect = $sessionUser ? $sessionUser->checkCompanyPlanAccess() : redirect('login');
        if ($redirect) {
            return $redirect;
        }

        $effectiveCompanyId = $sessionUser ? $sessionUser->getCompanyOwnerId() : 0;
        $companyOwner = ($effectiveCompanyId != $sessionUser->id) ? User::find($effectiveCompanyId) : $sessionUser;

        if (($companyOwner->unlimited_conractors ?? 0) != 1) {
            $companyplan = (new User())->getCompanyPlanInfo($effectiveCompanyId);
            if ($companyplan == 1 || $companyplan == null) {
                return redirect('company/dashboard')->with('warning', 'This feature isn’t available on your current plan. Please upgrade to access it.');
            }
        }

        return view('company/vendor_received/index');    
    }
    
     /**
     * Get a list of users.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function receivedList(Request $request)
    {
        return response()->json((new Vendor())->receivedList($request->all()));
    }
 
     /**
     * Show the form for creating a new user.
     *
     * @return \Illuminate\View\View
     */
    public function audit(Request $request,$id)
    {
        $w9 = Vendor::find($id);
    
        if (!$w9) {
            return response()->json(['html' => '<p>No audit data found.</p>']);
        }
    
        $decrypt = function($value, $key) {
            if (!$value) return null;
            $data = base64_decode($value);
            $iv = substr(hash('sha256', $key), 0, 16);
            return openssl_decrypt(
                $data,
                'aes-256-cbc',
                hash('sha256', $key, true),
                OPENSSL_RAW_DATA,
                $iv
            );
        };
    
        $serviceKey = $w9->encryption_key;
    
        $decryptedTIN = $decrypt($w9->tax_id_number, $serviceKey);
    
        if (!empty($decryptedTIN)) {
            $lastFour = substr($decryptedTIN, -4);
            $tinStatus = "SSN/ITIN provided (ending $lastFour)";
        } else {
            $tinStatus = "Applied For (no TIN provided)";
        }
    
        // AGE DATA
        $createdAt = Carbon::parse($w9->submited_at);
    
        $ageDisplay = $createdAt->diffForHumans([
            'parts' => 3,
            'short' => false,
            'syntax' => CarbonInterface::DIFF_ABSOLUTE 
        ]);
    
        $age = [
            'ageYears' => intval($createdAt->diffInYears(now(),false)),   
            'ageDays'  => intval($createdAt->diffInDays(now(),false)),    
            'ageHours' => intval($createdAt->diffInHours(now(),false)), 
            'ageDisplay' => $ageDisplay
        ];
    
        $signedDate = date("D, M d, Y g:i A", strtotime($w9->created_at));
    
        return view('company/vendor_received/audit', compact('w9', 'signedDate', 'age', 'tinStatus'));
    }

   
    public function delete(Request $request)
    {
        $w9Request = Vendor::find($request->input('id'));
        $w9Request->delete();
        return response()->json(['status' => 1, 'message' => 'W-9 Request delete successfully.', 'next' => 'refresh']);
    }
    
    public function resendRequest(Request $request){
        $id = $request->id;
        if(!$id){
            return response()->json(['status' => 0, 'message' => 'Request not Found.']);
        }

        $w9 = DB::table('w9')
        ->leftJoin('user', 'w9.vendor_contact_person', '=', 'user.id') 
        ->select('w9.*','user.business_name as vendor_name')->where('w9.id', $id)->first();

        if($w9 && $w9->vendor_contact_person){
            $contractor = User::find($w9->vendor_contact_person);
            if(!$contractor){
                return response()->json(['status' => 0, 'message' => 'Contractor not Found.']);
            }
        }

        $w9Request = Vendor::findOrFail($id);
        $w9Request->request_status = 0; 
        $w9Request->updated_at = now();
        $w9Request->entity_name = null;
        $w9Request->entity_type = null;
        $w9Request->business_name = null;
        $w9Request->tax_id_number = null;
        $w9Request->list_account_number = null;
        $w9Request->address  = null;
        $w9Request->address_lookup  = null;
        $w9Request->city  = null;
        $w9Request->state  = null;
        $w9Request->zip_code  = null;
        $w9Request->encryption_key  = null;
        $w9Request->encrypted_pdf_path  = null;
        $w9Request->signature = null;
        $w9Request->email_open_at = null;
        $w9Request->entity_specification = null;
        $w9Request->submited_at = null;
        $w9Request->is_request_resend = 1;
        $w9Request->save();
        $sessionUser = auth()->user();
        $vendorContact = $w9->vendor_name;
        
        $url = route('w9_form', ['token' => $w9Request->token]);
    
        $company = User::find($w9Request->representative_of ?? null);
        $companyName = $company->company_name ?? $company->name ?? '';

        (new General())->sendEmail($w9Request->vendor_email, 'w9_request', [
            'name' => $vendorContact,
            'company_name' => $companyName,
            'email' => $sessionUser->email,
            'link' => $url
        ]);
        
        return response()->json(['status' => 1, 'message' => 'W-9 request resent successfully.', 'next' =>'redirect', 'url'=> 'company/requests']);

    }
    
    public function checkW9Status($token){
        $w9 = Vendor::where('token', $token)->first();

        return response()->json([
            'submitted' => $w9 ? $w9->request_status == 1 : false
        ]);
    }
    
}
    