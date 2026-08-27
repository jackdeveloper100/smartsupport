<?php

namespace App\Http\Controllers\Company;

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
    public function contractorDocument(Request $request,$id){
        return response()->json((new Document())->listByContractor($request->all(),$id));
    }
    
    public function contractorDocumentActivity(Request $request,$id){
        return response()->json((new DocumentActivity())->listOfDocumentActivityAdmin($request->all(),$id));
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

    public function contractorView(Request $request,$userId){
        
        $sessionUser = auth()->user();

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

            return view('company/document/view',compact('model','userData','documentModel','extension','fileName','currentVersion'));
        }
        
       public function documentPreview(Request $request, $documentFileId){
        
        $documentId = isset($_GET['type']) ? $_GET['type'] : '0';
        if($documentId){
            $documentData = Document::where('id',$documentFileId)->first();
            $documentFileId = $documentData->filename;
        }
        
        $model = DocumentFile::where('id',$documentFileId)->first();
        $extension = strtolower(pathinfo($model->filename, PATHINFO_EXTENSION)); 
        return view('company/document_file/preview',compact('model','extension'));
    }

    public function documentsVersion(Request $request,$id){
        return response()->json((new DocumentFile())->documentsVersionListAdmin($request->all(),$id));
    }
    
    public function pdfPreview(Request $request){
        $fileName = $request->fileName;
        return view('company/document/preview',compact('fileName'));
        
    }
}