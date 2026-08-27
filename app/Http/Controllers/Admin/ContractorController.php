<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Helpers\General;
use App\Models\Log;
use App\Models\User;
use App\Models\Document;
use App\Models\DocumentFile;
use App\Models\Notification;
use App\Models\UserAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use App\Models\DocumentType;
use App\Helpers\DocumentHelper;

/**
 * Class ContractorController
 * @package App\Http\Controllers\Admin
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
       $company = User::where('type',2)->get();
        return view('admin/contractor/index',compact('company'));
    }

    /**
     * Get a list of users.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        return response()->json((new User())->list($request->all()));
    }
    
    public function allDocumentView(){
        
        $documentTypeData = DocumentType::where('is_hidden',0);
        $company = User::where('type',2)->get();
        
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        $allowedStatuses = ['Active', 'Expiring Soon', 'Expired'];
        if (!in_array($status, $allowedStatuses)) {
            abort(404); // Show 404 Page
        }
         $dashboardStats = DocumentHelper::getAdminDashboardDocumentStats();
        $activeUser = $dashboardStats['activeUser'];
        $deactiveUser = $dashboardStats['deactiveUser'];
        $expiredUser = $dashboardStats['expiredUser'];
        $compliancePercentage = $dashboardStats['compliancePercentage'];
        $expiringPercentage = $dashboardStats['deactivePercentage'];
        $expiredPercentage = $dashboardStats['expiredPercentage'];
        
        // $documentData = Document::whereHas('documentType', function($q){
        //         $q->where('is_hidden', 0);
        //     })->count();
            
        // $totalUser = User::where('type', 1)->count();
        // $activeUser = Document::where('status', 5)->count();
        // $deactiveUser = Document::where('status', 2)->count();
        // $expiredUser = Document::where('status', 3)->count();
    
        // if ($documentData > 0) {
        //     $compliancePercentage = round(($activeUser / $documentData) * 100);
        // } else {
        //     $compliancePercentage = 0; 
        // }
        
        // if ($documentData > 0) {
        //     $expiringPercentage = round(($deactiveUser / $documentData) * 100);
        // } else {
        //     $expiringPercentage = 0; 
        // }
        
        // if ($documentData > 0) {
        //     $expiredPercentage = round(($expiredUser / $documentData) * 100);
        // } else {
        //     $expiredPercentage = 0; 
        // }
            
        if($status == '' || $status == null){
            return redirect('admin/dashboard')->with('warning','Cannot Access Documents Page Directly');
        }
        
        return view('admin/contractor_document/index',compact('documentTypeData','company','activeUser','deactiveUser','expiredUser','compliancePercentage','expiringPercentage','expiredPercentage'));
    }
    
    public function allDocumentsList(Request $request){
        return response()->json((new Document())->getAllDocumentsList($request->all()));
    }
    
    public function DocumentListByStatus(Request $request){
        return response()->json((new Document())->getDocumentListByStatus($request->all()));
    }
    
    public function contractorDocumentView(Request $request, $contractorId){
        $status = $request->status;
        if(!$contractorId){
            return response()->json(['status'=>0,'message'=>'Contractor not Found']);
        }
        
        if(!$request->documentId){
            return response()->json(['status'=>0,'message'=>'Document Not Exits']);
        }

        return view('admin/contractor_document/document_list',compact('contractorId','status'));
    }
    
    public function showFilteredDocumements(Request $request,$contractorId,$status){
        return response()->json((new Document())->getFilteredDocuments($request->all(),$contractorId,$status));
    }
    
    
    /**
     * Show the form for creating a new user.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $companyName = User::where('type',2)->where('status',1)->get();
        return view('admin/contractor/create',compact('companyName'));
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
        $companyName = User::where('type',2)->where('status',1)->get();
        // dd($model);
        if ($user->type == 1 && !in_array('admin/contractor/update', $permission)) {
            return redirect('admin/contractors')->with('error', 'No permission To Update contractor');
        }

        return view('admin/contractor/update', compact('permission', 'model','companyName'));
    }

    /**
     * Save or update user data.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request)
    {
        return response()->json((new User())->store($request->all()));
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
        if(!$model){
            return redirect()->route('admin/dashboard')->with('error', 'Contractor Not Found.');
        }

        $documentTypeData = DocumentType::where('is_hidden', 0)->get();
        $documentData = Document::all();
        
        $documentTypes =  DocumentType::all();
        $documentData = Document::where('user_id',$id)->get();
        
        // $totalDocumentTypes = DocumentType::where('is_hidden', 0)->count();
        // $compliantDocuments = Document::join('document_type', 'document.type', '=', 'document_type.id')
        //     ->where('document.user_id', $id)
        //     ->where('document.approve_status', 1)
        //     ->where('document_type.is_hidden', 0)
        //     ->distinct('document.type')
        //     ->count('document.type');

        // $compliancePercentage = $totalDocumentTypes > 0 ? round(($compliantDocuments / $totalDocumentTypes) * 100) : 0;\\
        
        $compliancePercentage = DocumentHelper::getComplianceNumber($id);
        
        $userAccountModel = UserAccount::where('user_id',$model->id)->first();

         $extension = '';
        if(isset($userAccountModel)){
            $image = (new General())->getFileUrl($userAccountModel->file_name,'document');
            $extension = strtolower(pathinfo($image, PATHINFO_EXTENSION)); 
        }
        
        
        $notificationType = isset($_GET['type']) ? $_GET['type'] : '';
        if($notificationType){
           
            $notificationData = Notification::where('type',0)->where('user_id',$id)->where('is_read',0)->get();
            foreach($notificationData as $notification){
                $notification->is_read = 1;
                $notification->save();
            }
        }
        $documentModel = new Document();
        $totalRequiredDocument = DocumentType::where('type',1)->count();
        $uploadRequiredDocument = $documentModel->getUploadedRequiredDocument($id);
        
        $documentTypeModel = new DocumentType();
        $expiredDocument = $documentTypeModel->getExpiredDocument($id);
        $missingDocument = $documentTypeModel->getMissingDocument($id);
        $actionMessage = ''; 
        
        if (!empty($missingDocument)) {
            $actionMessage .= 'Your ' . implode(', ', $missingDocument) . ' are Missing. Please Upload Them.';
        }
        
        if (!empty($expiredDocument)) {
            if (!empty($actionMessage)) {
                $actionMessage .= ' Also ';
            }
            $actionMessage .= 'Your ' . implode(', ', $expiredDocument) . ' have Expired. Kindly Renew Them.';
        }
        
        return view('admin/contractor/view', compact('model', 'logData', 'deviceData','documentTypeData','documentData','compliancePercentage','userAccountModel','totalRequiredDocument','uploadRequiredDocument','extension','expiredDocument','actionMessage'));
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
    
        // Get all documents related to the user
        $documents = Document::where('user_id', $model->id)->get();
        foreach ($documents as $document) {
            // Delete all related document files
            DocumentFile::where('document_id', $document->id)->delete();

            // Delete the document itself
            $document->delete();
        }
    
        // Delete the user
        $model->delete();
    
        return response()->json([
            'status' => 1,
            'message' => 'Contractor removed successfully.',
            'next' => 'load',
            'url' => 'admin/contractors'
        ]);
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
     * Revoke all devices for the currently authenticated contractor.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function revokeAll()
    {
        $model = Auth::user();
        $model->updateData(['ignore_tfa_device','']);
        return response()->json(['status' => 1, 'message' => 'Your devices revoked successfully.']);
    }
    
    public function deleteMultiple(Request $request){
       return response()->json((new User())->deleteMultiple($request->all()));
    }
    
    public function sendReminderMail(Request $request){
        $ids = $request->id;
        return view('admin/contractor/email/email',compact('ids'));
    }
    public function sendReminderMailnew(Request $request){
       $id= $request->id;
        $type = $request->type;
        return view('admin/document/reject',compact('id','type'));
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
                'Email','Business Name',
                'country','Status', 
                'email_verified','timezone',
                'Created At','Updated At',
                'Total Documents', 'Compliance Status'];
    fputcsv($output, $headers);

    $contractors = User::whereIn('id', $ids)->get();

    if ($contractors) {
        foreach ($contractors as $contractorData){
            $totalDocuments = $contractorData->documents()->count();
            // $complianceStatus = $contractorData->compliance_status ? 'Compliant' : 'Non-Compliant'; 
            
   
            $complianceStatus = (new User())->getComplianceStatus($contractorData->id);
            
            $contractorStatus = $contractorData->status == 1 ? 'Active' : 'In Active';
            $contractorTfaStatus = $contractorData->status_tfa == 1 ? 'Enable' : 'Deasable';
            $contractorEmailVerified = $contractorData->email_verified == 1 ? 'Yes' : 'No';
            
            // $createdAt = $contractorData->created_at ? $contractorData->created_at->format('Y-m-d H:i:s') : 'Not Available';
            $data = [
                $contractorData->id, $contractorData->first_name, $contractorData->last_name,  
                $contractorData->email, $contractorData->business_name,
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
    
    public function sendReminderEmail(Request $request){
        
        return response()->json((new User())->sendReminderEmail($request->all()));
    }

    
    public function notificationView(){

        $notificationModel = new Notification();
        $newNotfication = Notification::where('type',0)->where('is_read',0)->count();

        $notificationData = Notification::where('type',0)
                                        ->orderBy('created_at','desc')
                                        ->limit(10)
                                        ->get();
                                        
        $notificationDatas = Notification::where('type',0)->orderBy('created_at','desc')->get();
        foreach($notificationDatas as $notification){
            $notification->is_read = 1;
            $notification->save();
        }
        $totalNotification = Notification::where('type',0)->count();

        return view('admin/notification/index',compact('notificationModel','notificationData','totalNotification','newNotfication'));
    }

    public function loadMoreNotification(Request $request){
        return response()->json((new Notification())->loadMoreNotificationAdmin($request->all()));
        // dd(1);
    }
    
    public function commentSave(Request $request){
        return response()->json((new User())->noteStore($request->all()));
    }
}
