<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Helpers\General;
use App\Models\Device;
use App\Models\Log;
use App\Models\User;
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
use App\Helpers\DocumentHelper;

/**
 * Class ContractorController
 * @package App\Http\Controllers\Company
 *
 * Handles user management functionalities in the admin panel.
 */
class ContractorController extends Controller
{
    /**
     * Display the user index view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = auth()->user();
        $redirect = $user ? $user->checkCompanyPlanAccess() : redirect('login');
        if ($redirect) {
            return $redirect;
        }
        return view('company/contractor/index');
    }
    
     /**
     * Get a list of users.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $authId = auth()->user()->getCompanyOwnerId();
        return response()->json((new User())->list($request->all(),$authId));
    }
    
    
    public function allDocumentView(){
        $authId = auth()->user()->getCompanyOwnerId();
        $documentTypeData = DocumentHelper::getAllowedDocumentTypes($authId);
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        
        $allowedStatuses = ['Active', 'Expiring Soon', 'Expired'];
        if (!in_array($status, $allowedStatuses)) {
            abort(404); // Show 404 Page
        }
        
        $dashboardStats = DocumentHelper::getCompanyDashboardDocumentStats($authId);
        $activeUser = $dashboardStats['activeUser'];
        $deactiveUser = $dashboardStats['deactiveUser'];
        $expiredUser = $dashboardStats['expiredUser'];
        $compliancePercentage = $dashboardStats['compliancePercentage'];
        $expiringPercentage = $dashboardStats['expiringPercentage'];
        $expiredPercentage = $dashboardStats['expiredPercentage'];
                
                
        return view('company/contractor_document/index',compact('documentTypeData','activeUser','deactiveUser','expiredUser','compliancePercentage','expiringPercentage','expiredPercentage'));
    }
    
    public function allDocumentsList(Request $request){
        $authId = auth()->user()->getCompanyOwnerId();
        return response()->json((new Document())->getAllDocumentsList($request->all(),$authId));
    }
    
    public function DocumentListByStatus(Request $request){
        return response()->json((new Document())->getDocumentListByStatus($request->all(), auth()->user()->getCompanyOwnerId()));
    }
    
    
    public function contractorDocumentView(Request $request, $contractorId){
        $status = $request->status;
        if(!$contractorId){
            return response()->json(['status'=>0,'message'=>'Contractor not Found']);
        }
        
        if(!$request->documentId){
            return response()->json(['status'=>0,'message'=>'Document Not Exits']);
        }

        return view('company/contractor_document/document_list',compact('contractorId','status'));
    }
    
    public function showFilteredDocumements(Request $request,$contractorId,$status){
        return response()->json((new Document())->getFilteredDocuments($request->all(),$contractorId,$status, auth()->user()->getCompanyOwnerId()));
    }
    
    
     /**
     * Show the form for creating a new user.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {   
        $sessionUser = auth()->user();
        $companyOwnerId = $sessionUser->getCompanyOwnerId();
        
        $checkLimit = User::canAddContractor($companyOwnerId);
        if (!$checkLimit['allowed']) {
            if (session()->pull('allow_extra_contractor')) {
                return view('company/contractor/create')->with('success', 'You have used your paid extra contractor slot.');
            }
            $priceLabel = ($checkLimit['plan_id'] == 4) ? 'Pay $2' : 'Pay $15';
            $payNow = '<a href="' . route('company/contractors/add-extra') . '" > ' . $priceLabel . '</a>';
            return redirect('company/contractors')->with('warning', 'You have reached your contractor limit. To create an additional contractor, please ' . $payNow);
        }

        return view('company/contractor/create');
    }
    
     /**
     * Show the form for updating a specific user.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $model = User::find($request->id);
        $user = auth()->user();
        $permission = explode(',', $user->permission);
        // dd($model);
        if ($user->type == 1 && !in_array('admin/contractor/update', $permission)) {
            return redirect('company/contractors')->with('error', 'No permission To Update contractor');
        }

        return view('company/contractor/update', compact('permission', 'model'));
    }
    
     /**
     * Save or update user data.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request)
    {
        // dd($request->all());
        return response()->json((new User())->store($request->all()));
    }
    
     /**
     * Delete a specific contractor.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function delete(Request $request)
    {
        $model = User::find($request->input('id'));
        if (!$model) {
            return response()->json(['status' => 0, 'message' => 'No data found']);
        }
        $model->delete();
        return response()->json(['status' => 1, 'message' => 'Contractor Remove Successfully.', 'next' => 'load', 'url' => 'company/contractors']);
    }
    
    /**
     * Change the status of a contractor.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }

        $id = $request->input('id');
        $model = User::find($id);
        
        if (!$model) {
            return response()->json(['status' => 0, 'message' => 'Contractor not found']);
        }

        $model->update(['status' => !$model->status]);  // Toggle the status
        return response()->json(['status' => 1, 'message' => 'Contractor status updated successfully.', 'next' => 'refresh']);
    }
    
    /**
     * View a specific user's details along with logs and devices.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function view(Request $request)
    {   
        $id = $request->input('id');
        $logData = Log::where('user_id', $id)
            ->orderBy('id', 'desc')
            ->limit(10)     
            ->get();
        $deviceData = Device::where('user_id', $id)
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();
        $model = User::where('id', $id)->first();
        if (!$model) {
            return redirect()->route('company/dashboard')->with('error', 'Contractor not found.');
        }

        $allowedTypeIds = DocumentHelper::getAllowedVisibleDocTypeIds((int) $model->company_id);
        $documentTypeData = DocumentHelper::getAllowedDocumentTypes((int) $model->company_id);
        
        $documentData = Document::where('user_id', $id)
            ->whereIn('type', $allowedTypeIds->toArray())
            ->get();
        
        $compliancePercentage = DocumentHelper::getComplianceNumber($id);
        
        $userAccountModel = UserAccount::where('user_id',$model->id)->first();
        $extension = '';
        if(isset($userAccountModel)){
            $image = (new General())->getFileUrl($userAccountModel->file_name,'document');
            $extension = strtolower(pathinfo($image,PATHINFO_EXTENSION));
        }
        $notificationType = isset($_GET['type']) ? $_GET['type'] : '';
        if($notificationType){
            $notificationData = Notification::where('type',0)->where('user_id',$id)->where('is_read',0)->get();
            foreach($notificationData as $notification){
                $notification->is_read = 1;
                $notification->save();
            }
        }
        return view('company/contractor/view', compact('model', 'logData', 'deviceData','documentTypeData','documentData','compliancePercentage','userAccountModel','extension'));
    }
    
    public function exportData(Request $request)
{
    $id = $request->id;
  
    if (!$id) {
        return response()->json(['status' => 0, 'message' => 'No data found']);
    }
    
    $ids = explode(',',$id);
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="contractor_data.csv"');

    $output = fopen('php://output', 'w');

    $headers = ['Id', 'First Name', 'Last Name',
                'Email',
                'country','Status', 
                'email_verified','timezone',
                'Created At','Updated At',
                'Total Documents', 'Compliance Status'];
    fputcsv($output, $headers);

    $contractors = User::whereIn('id', $ids)->get();

    if ($contractors) {
        foreach ($contractors as $contractorData){
            $totalDocuments = (new User())->getTotalDocument($contractorData->id);
            // $complianceStatus = $contractorData->compliance_status ? 'Compliant' : 'Non-Compliant'; 
            
            $complianceStatus = (new User())->getComplianceStatus($contractorData->id);
            
            $contractorStatus = $contractorData->status == 1 ? 'Active' : 'In Active';
            $contractorTfaStatus = $contractorData->status_tfa == 1 ? 'Enable' : 'Deasable';
            $contractorEmailVerified = $contractorData->email_verified == 1 ? 'Yes' : 'No';
            
            // $createdAt = $contractorData->created_at ? $contractorData->created_at->format('Y-m-d H:i:s') : 'Not Available';
            $data = [
                $contractorData->id, $contractorData->first_name, $contractorData->last_name,  
                $contractorData->email, 
                $contractorData->country, $contractorStatus,
                $contractorEmailVerified, $contractorData->timezone, 
                $contractorData->created_at, $contractorData->updated_at, 
                $totalDocuments,
                $complianceStatus,
            ];
            fputcsv($output, $data);
        }
    } else {
        fputcsv($output, ['No contractor data found']);
    }

    fclose($output);
    exit;
    
    }
    
    public function sendReminderMail(Request $request){
        $ids = $request->id;
        return view('company/contractor/email/email',compact('ids'));
    }
    
    public function sendReminderEmail(Request $request){
        return response()->json((new User())->sendReminderEmail($request->all()));
    }
    
   public function deleteMultiple(Request $request){
       return response()->json((new User())->deleteMultiple($request->all()));
    }
    

    public function sendReminderMailnew(Request $request){
       $id= $request->id;
        $type = $request->type;
        return view('company/document/reject',compact('id','type'));
    }
    
    public function notificationView(){

        $notificationModel = new Notification();
        $newNotfication = Notification::where('notification.type',0)
                                        ->leftjoin('user','user.id','=','notification.user_id')
                                        ->where('user.company_id',auth()->id())
                                        ->where('notification.is_read',0)->count();
        
        $notificationData = Notification::where('notification.type',0)
                                        ->leftjoin('user','user.id','=','notification.user_id')
                                        ->where('user.company_id',auth()->id())
                                        ->orderBy('notification.created_at','desc')
                                        ->limit(10)
                                        ->get();
                                        
        $notificationDatas = Notification::select('notification.id', 'notification.user_id', 'notification.document_status', 'notification.type', 'notification.created_at')
                                        ->where('notification.type',0)
                                        ->leftjoin('user','user.id','=','notification.user_id')
                                        ->where('user.company_id',auth()->id())
                                        ->orderBy('notification.created_at','desc')->get();

        foreach($notificationDatas as $notification){
            $notification->is_read = 1;
            $notification->save();
        }
        
        $totalNotification = Notification::where('notification.type',0)
                                            ->leftjoin('user','user.id','=','notification.user_id')
                                            ->where('user.company_id',auth()->id())->count();
                                        
        return view('company/notification/index',compact('notificationModel','notificationData','totalNotification','newNotfication'));
    }
    
     public function loadMoreNotification(Request $request){
        return response()->json((new Notification())->loadMoreNotificationCompany($request->all()));
    }

    public function newRegisterRequest(Request $request){
        $userId = $request->id;
        $user = User::find($userId);
        return view('company/notification/register',compact('user'));
    }
    
    public function registerApprove(Request $request){
        $general = new General();
        $userId = $request->id;
        $user = User::find($userId);
        $sessionUser = auth()->user();
        if(!$user){
            return response()->json(['status' => 0, 'message' => 'No data found']);
        }
        
        if($user->company_approved_status == 1){
            return response()->json(['status' => 0, 'message' => 'Contractor is already approved.']);
        }

        $companyOwnerId = $sessionUser->getCompanyOwnerId();
        $companyOwner = User::find($companyOwnerId) ?: $sessionUser;

        $checkLimit = User::canAddContractor($companyOwnerId);
        if (!$checkLimit['allowed']) {
            session()->put('approved_id', $userId);
            $priceLabel = ($checkLimit['plan_id'] == 4) ? 'Pay $2' : 'Pay $15';
            $payNow = '<a href="' . route('company/contractors/add-extra') . '" >' . $priceLabel . '</a>';
            return response()->json([
                'status' => 0,
                'message' => 'You have reached the maximum number of included contractors allowed on your current plan. To approve this contractor, please ' . $payNow . '.'
            ]);
        }

        $contractorLimit = $checkLimit['contractor_limit'];
        $planId = $checkLimit['plan_id'];
        $totalContractor = User::getContractorCountForPlan($companyOwnerId, $planId);
        $availableContractor = $checkLimit['available_contractor'];

        if ($contractorLimit !== 'Unlimited' && $totalContractor >= (int)$contractorLimit && $availableContractor > 0) {
            $user->company_approved_status = 1;
            $user->is_add_on = 1;
            $user->add_on_expired_at = session()->get('end_date', false) ?? Carbon::now()->addDays(30)->format('Y-m-d H:i:s');
            $result = $user->save();

            if ($companyOwner->available_contractor > 0) {
                $companyOwner->available_contractor -= 1;
                $companyOwner->save();
            }
        } else {
            $user->company_approved_status = 1;
            $result = $user->save();
        }
        
        if($result){
             $notificationDatas = Notification::select('notification.id', 'notification.user_id', 'notification.document_status', 'notification.type', 'notification.created_at')
                                     ->where('notification.type', 0)
                                     ->where('notification.document_status', 4)
                                     ->leftjoin('user','user.id','=','notification.user_id')
                                     ->where('user.company_id', $companyOwnerId)
                                     ->where('notification.user_id', $userId)
                                     ->orderBy('notification.created_at', 'desc')
                                     ->get();

            foreach($notificationDatas as $notification){
                $notification->is_read = 1;
                $notification->save();
            }
        }
        
       $companyName = $companyOwner->company_name ?: $sessionUser->company_name;
       $general->sendEmail($user->email, $template='register_approve',[
            'name' => $user->first_name . ' ' . $user->last_name,
            'company_name' => $companyName,
        ]);
        
        return response()->json(['status' => 1, 'message' => 'Request Approved Successfully', 'next' => 'reload']);
    }
    
    public function registerReject(Request $request){
        
        $sessionUser = auth()->user();
        $general = new General();
        
        $userId = $request->id;
        $user = User::find($userId);
        
        $companyOwnerId = $sessionUser->getCompanyOwnerId();
        $companyOwner = User::find($companyOwnerId) ?: $sessionUser;

        $subscriptionData = Subscription::where('user_id', $companyOwnerId)->first();
        $planId = $subscriptionData->plan_id ?? null;
        $totalContractor = User::getContractorCountForPlan($companyOwnerId, $planId);
        $totalCompanyContractor = User::where('type', 1)->where('company_id', $companyOwnerId)->count();
        $availableContractor = $companyOwner->available_contractor;
        $contractorLimit = (new Subscription())->getContractorLimit($companyOwnerId);
        
        if(!$user){
            return response()->json(['status' => 0, 'message' => 'No data found']);
        }
        
        if($user->company_approved_status == 1 && (int)$planId !== 4){
             if ($totalContractor >= $contractorLimit) {
                if ($totalCompanyContractor > $contractorLimit) {
                    $companyOwner->available_contractor += 1;
                    $companyOwner->save();
                }
            }
        }
        
        $user->company_approved_status	= 2;
        $result = $user->save();
        
        if($result){
             $notificationDatas = Notification::select('notification.id', 'notification.user_id', 'notification.document_status', 'notification.type', 'notification.created_at')
                                     ->where('notification.type', 0)
                                     ->where('notification.document_status', 4)
                                     ->leftjoin('user','user.id','=','notification.user_id')
                                     ->where('user.company_id', $companyOwnerId)
                                     ->where('notification.user_id', $userId)
                                     ->orderBy('notification.created_at', 'desc')
                                     ->get();

            foreach($notificationDatas as $notification){
                $notification->is_read = 1;
                $notification->save();
            }
        }
        
        $companyName = $companyOwner->company_name ?: $sessionUser->company_name;
        $general->sendEmail($user->email, $template='register_reject',[
            'name' => $user->first_name . ' ' . $user->last_name,
            'company_name' => $companyName,
        ]);
        
        return response()->json(['status' => 1, 'message' => 'Request Rejected Successfully', 'next' => 'reload']);
    }
    
    public function commentSave(Request $request){
        return response()->json((new User())->noteStore($request->all()));
    }
}