<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Notification;
use App\Helpers\General;
use App\Models\DocumentActivity;
use App\Models\DocumentFile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Class DocumentController
 * @package App\Http\Controllers\Admin
 *
 * Handles user management functionalities in the admin panel.
 */
class DocumentController extends Controller
{
    /**
     * Display the user index view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin/contractor/index');
    }


    public function contractorDocument(Request $request,$id){
        return response()->json((new Document())->listByContractor($request->all(),$id));
    }


    /**
     * Get a list of users.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        return response()->json((new Document())->list($request->all()));
    }

    /**
     * Show the form for creating a new user.
     *
     * @return \Illuminate\View\View
     */
    public function create($userId)
    {
        return view('admin/document/create',['userId'=> $userId]);
    }

    /**
     * Show the form for updating a specific user.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request,$userId)
    {
        $model = Document::find($request->id);
        $user = auth()->user();
        $permission = explode(',', $user->permission);
        if ($user->type == 1 && !in_array('admin/contractor/update', $permission)) {
            return redirect('admin/contractors')->with('error', 'No permission To Update contractor');
        }

        return view('admin/document/update', compact('permission', 'model','userId'));
    }

    /**
     * Save or update user data.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request,$userId)
    {
        return response()->json((new Document())->store($request->all(),$userId));
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
        
        return view('admin/contractor/view', compact('model', 'logData', 'deviceData'));
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
        return response()->json(['status' => 1, 'message' => 'Contractor deleted successfully.', 'next' => 'load', 'url' => 'admin/contractors']);
    }

    /**
     * Change the status of a contractor.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeStatus(Request $request,$id,$type)
    {  
        $data = $request->all();
        if($type == 'reject'){
           $rules = [
            'rejected_reason' => 'required|string|max:255',
            ];
            
            $validator = Validator::make($data, $rules);

               if ($validator->fails()) {
                return [
                    'status' => 0,
                    'message' => $validator->errors()->first(),
                ];
            }

        }

        $model = Document::find($id);
        $documentName = (new Document())->getDocumentName($model->type);
        
        $notificationModel = new Notification();
        $documentActivityModel = new DocumentActivity();
        if (!$model) {
            return response()->json(['status' => 0, 'message' => 'Document not found']);
        }
        
        if($type == 'approve'){
           $result = $model->update(['approve_status' => 1,'status'=> 5]);  
           if($result){
            $notificationModel->store($type,$id);
            $documentActivityModel->store($type,$id);
           }
        }elseif($type == 'reject'){
            $result = $model->update(['approve_status' => 2,'reject_reason' => $request->rejected_reason,'status'=> 4]);  
            if($result){
                $notificationModel->store($type,$id);
                $documentActivityModel->store($type,$id);
            }
        }
        return response()->json(['status' => 1, 'message' => $documentName.' Status Updated Successfully.', 'next' => 'reload']);
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
    
    
    public function contractorView(Request $request,$userId){
        $sessionUser = auth()->user();
        if ($sessionUser && $sessionUser->hasPermission('admin/document/view')) {
            $model = Document::find($request->id);
            $user = auth()->user();
            $documentModel = new Document();
            $permission = explode(',', $user->permission);

            if ($user->type == 1 && !in_array('admin/contractor/view', $permission)) {
                return redirect('admin/contractors')->with('error', 'No permission To view contractor');
            }
            $userData = User::where('id',$model->user_id)->first();
            
            $documentFileModel = DocumentFile::where('id',$model->filename)->first();
            $fileName = $documentFileModel->filename;

            $latestDocument = Document::where('id',$model->id)->first();
            $orderNumber = (new DocumentFile())->getOrderNumber($latestDocument->filename, $latestDocument->id); 
            $currentVersion = $documentModel->getDocumentName($model->type) . '_V' . str_pad($orderNumber, 3, '0', STR_PAD_LEFT);
            // dd($fileName);
            $image = (new General())->getFileUrl($fileName,'document');
            $extension = strtolower(pathinfo($image, PATHINFO_EXTENSION)); 

            return view('admin/document/view',compact('model','userData','documentModel','extension','fileName','currentVersion'));
        }else{
            return redirect('admin/dashboard')->with('error', 'You are Not Authorized');
        }
        
    }
    
      public function rejectDocument(Request $request){
        $id= $request->id;
        $type = $request->type;
        return view('admin/document/reject',compact('id','type'));
        
    }
    
  
    
    public function contractorDocumentActivity(Request $request,$id){
        return response()->json((new DocumentActivity())->listOfDocumentActivityAdmin($request->all(),$id));
    }

    public function pdfPreview(Request $request){
        $fileName = $request->fileName;
        return view('admin/document/preview',compact('fileName'));
    }

    public function documentsVersion(Request $request,$id){
        return response()->json((new DocumentFile())->documentsVersionListAdmin($request->all(),$id));
    }

    public function documentFileView(Request $request,$documentId){
        $model = DocumentFile::where('id',$documentId)->first();
        return view('admin/document_file/view',compact('model'));
    }
    
    public function documentPreview(Request $request, $documentFileId){
        
        $documentId = isset($_GET['type']) ? $_GET['type'] : '0';
        if($documentId){
            $documentData = Document::where('id',$documentFileId)->first();
            $documentFileId = $documentData->filename;
        }
        
        $model = DocumentFile::where('id',$documentFileId)->first();
        $extension = strtolower(pathinfo($model->filename, PATHINFO_EXTENSION)); 
        return view('admin/document_file/preview',compact('model','extension'));
    }
    
    
}
