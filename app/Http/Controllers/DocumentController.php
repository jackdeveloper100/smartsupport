<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use App\Models\DocumentActivity;
use App\Models\DocumentFile;
use App\Models\Document;
use App\Models\User;
use App\Models\UserAccount;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Helpers\General;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Helpers\DocumentHelper;


class DocumentController extends Controller
{
    public function listDocument(Request $request){ 
        return response()->json((new Document())->list($request->all()));
    }

    public function index(){
        $user = auth()->user();
        $companyId = $user->company_id;
        $companyPlanId = (new User())->getCompanyPlanInfo($user->company_id);
        $isCompanyUnlimitedContractor = (new User())->isUnlimitedContractor($user->company_id);
        
        if($user->company_approved_status == 1){
            
            // --- Use helper to get allowed documents ---
            $allowedDocTypeIds = DocumentHelper::getAllowedDocTypeIds($companyId);
            $documentTypes = DocumentHelper::getAllowedDocumentTypes($companyId);
        
            // Fallback if company has no allowed docs
            if ($documentTypes->isEmpty()) {
                $documentTypes = DocumentType::where('is_hidden', 0)->get();
                $allowedDocTypeIds = $documentTypes->pluck('id');
            }
            
            $documentModel = new Document();
            $totalDocument = $documentTypes->count();
            $uploadedDocumet = $documentModel::where('user_id',auth()->id())->count();
            $documents = [];
            foreach($documentTypes as $type){
                $documents[$type->id]['type'] = $type->id;
                $documents[$type->id]['name'] = $type->name;
                $documents[$type->id]['document_reqiure'] = $type->type;
                $doc = Document::where('user_id',auth()->id())->where('type',$type->id)->first();
                if($doc){
                   $documents[$type->id]['status'] = (new Document)->getStatusBadge($doc->status);
                       
                    if ($documents[$type->id]['type'] == '2') {
                        $documents[$type->id]['expired_at'] = 'Never';
                    }else{
                        if(empty($doc->expired_at)){
                            $documents[$type->id]['expired_at'] = 'Not Available'; 
                        }else{
                            $documents[$type->id]['expired_at'] = date('d M, Y', strtotime($doc->expired_at)); 
                        }
                    }
                   $documents[$type->id]['uploaded_at'] = date('d M, Y', strtotime($doc->updated_at));
                }else{
                   $documents[$type->id]['status'] = (new Document)->getStatusBadge(0);
                   $documents[$type->id]['expired_at'] = '';
                   $documents[$type->id]['uploaded_at'] = '';
                }
            }
            $documentData = Document::where('user_id',auth()->id())->get();
    
            //$ComplianceStatus = Document::where('user_id', auth()->id())->get();
            // $compliantDocuments = Document::where('user_id', auth()->id())
            //     ->where('approve_status', 1)
            //      ->whereIn('type', $allowedDocTypeIds)
            //     ->whereHas('documentType', function ($query) {
            //         $query->where('is_hidden', 0);
            //     })
            // ->count();
    
            // $totalDocumentTypes = $documentTypes->count();
            // $compliancePercentage = 0;
            // if ($totalDocumentTypes > 0) {
            //     $compliancePercentage = ($compliantDocuments / $totalDocumentTypes) * 100;
            //     $compliancePercentage = round($compliancePercentage);
            // }
            $compliancePercentage = DocumentHelper::getComplianceStatus(auth()->id());

            $userAccountData = UserAccount::where('user_id',auth()->id())->first();

            $userId = auth()->id();
            $userAccountModel = UserAccount::where('user_id',$userId)->first();
            $extension = '';
            if(isset($userAccountModel)){
                $image = (new General())->getFileUrl($userAccountModel->file_name,'document');
                $extension = strtolower(pathinfo($image, PATHINFO_EXTENSION)); 
            }
            
            //$totalRequiredDocument = DocumentType::whereIn('id', $allowedDocTypeIds)->where('type', 1)->count();
            $totalRequiredDocument = DocumentHelper::getTotalRequiredDocument($companyId);
            $uploadRequiredDocument = $documentModel->getUploadedRequiredDocument(auth()->id());
            
            $documentTypeModel = new DocumentType();
            $missingDocument = $documentTypeModel->getMissingDocument(auth()->id());
            $expiredDocument = $documentTypeModel->getExpiredDocument(auth()->id());
            
            $actionMessage = '';
            
            if(!empty($missingDocument)){
               $missingDocumentMessage = ' Your '.implode(', ', $missingDocument).' are Missing. Please Upload Them.';
               $actionMessage .= $missingDocumentMessage;
            }
    
           if (!empty($expiredDocument)) {
                if (!empty($actionMessage)) {
                    $actionMessage .= " Also ";
                }
                $expiredDocumentMessage = 'Your '.implode(', ', $expiredDocument).' have Expired,  Kindly Renew Them.';
                $actionMessage .= $expiredDocumentMessage;  
            }
            
            return view('document/index',compact('documents','totalDocument','uploadedDocumet','compliancePercentage','userAccountData','userAccountModel','extension','totalRequiredDocument','uploadRequiredDocument','actionMessage','expiredDocument','companyPlanId','isCompanyUnlimitedContractor'));
        }elseif($user->company_approved_status == 2){
            return redirect()->route('account/update')->with('warning','Your request has been rejected at this time. Please try again later.');
        }else{
            return redirect()->route('account/update')->with('info','Your request is awaiting approval. Please try again later.');
        }
    }
    
    public function loadMoreNotification(Request $request){
        $limit = $request->limit;
        $userId = $request->user_id;

        $notificationData = Notification::where('user_id',$userId)
                                        ->where('type',1)
                                        ->orderBy('created_at','desc')
                                        ->skip($limit)  
                                        ->take(10)->get();

        $notifications = $notificationData->map(function($notification) {
        return [
            'badge' => (new Notification())->getNotificationBadge($notification->type),  
            'description' => $notification->description,
            'created_at' => \Carbon\Carbon::createFromTimestamp($notification->created_at)->format('Y-m-d h:i A')
        ];
    });

    return response()->json(['notifications' => $notifications]);
                
    }

    public function create(Request $request){
        $user = auth()->user();
        $companyPlanId = (new User())->getCompanyPlanInfo($user->company_id);
        $isCompanyUnlimitedContractor = (new User())->isUnlimitedContractor($user->company_id);
        if($isCompanyUnlimitedContractor == 0){
            if(!session('came_from_company') && !session('came_from_admin')){
                if($companyPlanId == 1 || $companyPlanId == null){
                   return redirect()->route('contractor/document')->with('warning','You cannot upload documents at the moment.');
                }
            }
        }
        $name = $request->name;
        $type = $request->type;
       return view('document/create',compact('name','type'));
    }

    public function view(Request $request){
        if(auth()->user()){
            $name = $request->name;
            $type = $request->type;
            $documentModel = new Document();
            $model = Document::where('user_id',auth()->id())->where('type',$type)->first();
            if(!$model){
                return redirect('contractor/document')->with('error','Document not found');
            }
            $userData  =  User::where('id',$model->user_id)->first();

            $documentFileModel = DocumentFile::where('id',$model->filename)->first();
            $fileName = $documentFileModel->filename;

            $image = (new General())->getFileUrl($fileName,'document');
            $extension = strtolower(pathinfo($image, PATHINFO_EXTENSION)); 

            $latestDocument = Document::where('id',$model->id)->first();
            $orderNumber = (new DocumentFile())->getOrderNumber($latestDocument->filename, $latestDocument->id); 
            $currentVersion = $documentModel->getDocumentName($model->type) . '_V' . str_pad($orderNumber, 3, '0', STR_PAD_LEFT);
                  
            return view('document/view',compact('model','name','type','userData','fileName','extension','documentModel','currentVersion'));
         }else{
            return redirect('login')->with('error', 'You are Not Authorized');
         } 
    }

    public function update(Request $request){
        $user = auth()->user();
        $companyPlanId = (new User())->getCompanyPlanInfo($user->company_id);
        $isCompanyUnlimitedContractor = (new User())->isUnlimitedContractor($user->company_id);
        if($isCompanyUnlimitedContractor == 0){
            if(!session('came_from_company') && !session('came_from_admin')){
                if($companyPlanId == 1 || $companyPlanId == null){
                   return redirect()->route('contractor/document')->with('warning','You cannot upload documents at the moment.');
                }
            }
             
        }
        $type = $request->type;
        $name = $request->name;
        $model = Document::where('user_id',auth()->id())->where('type',$type)->first();
        if(!$model){
            return redirect('contractor/document')->with('error','Document not found');
        }
        return view('document/update',compact('model','name','type'));
    }

    public function save(Request $request){
        return response()->json((new Document())->storeDocument($request->all()));
    }
    
    public function checkExpired(){
        // return (new Document())->checkDocumnetExpired();
         return (new Document())->checkDocumnetExpiredNew();
    }
    
    public function DocumentHistory(Request $request){
        $id = auth()->id();
        return response()->json((new DocumentActivity())->listOfDocumentActivity($request->all(),$id));
    }
  
    public function accountSave(Request $request){  
        return response()->json((new UserAccount())->store($request->all()));
    }
  
    public function paymentCreate(Request $request){
        $userId = auth()->id();
        $model = UserAccount::where('user_id',$userId)->first();
        return view('payment/create',compact('model'));
    }

    public function notificationView(){
        $notificationModel = new Notification();
        $newNotification = Notification::where('user_id',auth()->id())->where('type',1)->where('is_read',0)->count();
        
        $notificationData = Notification::where('user_id',auth()->id())
                                        ->where('type',1)
                                        ->orderBy('created_at', 'desc')
                                        ->limit(10)
                                        ->get();
        // dd($notificationData);
        $totalNotification = Notification::where('user_id',auth()->id())->where('type',1)->count();
        // dd($totalNotification);
        $notificationDatas = Notification::where('user_id',auth()->id())->where('type',1)->orderBy('created_at', 'desc')->get();

        foreach($notificationDatas as $notification){
            $notification->is_read = 1;
            $notification->save();
        }
        

        return view('notification/index',compact('notificationData','totalNotification','notificationModel','newNotification'));
    }

    public function documentsVersion(Request $request,$id){
        return response()->json((new DocumentFile())->documentsVersionList($request->all(),$id));
    }

    public function documentFileView(Request $request, $documentId){
        $model = DocumentFile::where('id',$documentId)->first();
        return view('document_file/view',compact('model'));
    }

    public function documentPreview(Request $request, $documentFileId){
        
        $documentId = isset($_GET['type']) ? $_GET['type'] : '0';
        
        if($documentId){
            $documentData = Document::where('id',$documentFileId)->first();
            $documentFileId = $documentData->filename;
        }
        
        $model = DocumentFile::where('id',$documentFileId)->first();
        $extension = strtolower(pathinfo($model->filename, PATHINFO_EXTENSION)); 
        return view('document_file/preview',compact('model','extension'));
    }

    public function restoreDocument(Request $request){
        return response()->json((new DocumentFile())->restoreDocument($request->all()));     
    }

    public function downloadFile($fileName)
    {
        $fileType = 'document';
        // dd($fileType);
        // $filePath = public_path('files/sample.pdf'); // Adjust the path accordingly
        $filePath = (new General())->getfilePath($fileType);
        // dd($filePath,$fileName);
        return response()->download($filePath, $fileName);
    }

    public function showImage($fileName)
    {
        $user = auth()->user();
        if($user){
            if (!Auth::check()) {
                abort(403, 'Unauthorized');
            }
            
            $fileType = 'document';
            $filePath = (new General())->getFileUrl($fileName, $fileType);
            $baseUrl = url('/');
            $filePath = str_replace($baseUrl, '', $filePath);
            $fullPath = public_path($filePath);
            
            if (file_exists($fullPath)) {
                return response()->file($fullPath);
            } else {
                abort(404, 'File Not Found');
            }
        }else{
            return redirect('login')->with('error', 'You are Not Authorized');
        }
    }

}