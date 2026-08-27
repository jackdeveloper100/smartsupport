<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Notification;
use App\Models\Log;
use App\Services\PermissionService;
use App\Models\Device;
use Illuminate\Support\Facades\DB;
use App\Helpers\DocumentHelper;


class CompanyController extends Controller
{
    /**
     * Display the admin dashboard index page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin/company/index');
    }
    
    public function create(){
        return view('admin/company/create');
    }
    
    public function list(Request $request)
    {
        return response()->json((new User())->companyList($request->all()));
    }
    
    public function update(Request $request){
        
        $model = User::find($request->id);
        $docCompanyAllowed = DB::table('company_allowed_documents')->where('company_id', $request->id)->pluck('document_type_id')->toArray();
        $docCompanyAllowed = array_map('intval', $docCompanyAllowed);
        if(!$model){
             return response()->json(['status' => 0, 'message' => 'No data found']);
        }
        
        return view('admin/company/update',compact('model', 'docCompanyAllowed' ));
    }
    
    public function view(Request $request){
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
        
        $documentTypes = \App\Models\DocumentType::orderBy('type')
            ->orderBy('name')
            ->where('is_hidden', 0)
            ->get();

        $allowedDocs = DocumentHelper::getAllowedVisibleDocTypeIds((int) $id)
            ->toArray();

        if (empty($allowedDocs)) {
            $allowedDocs = $documentTypes->pluck('id')
                ->map(fn($docId) => (int) $docId)
                ->toArray();
        }

        $docTypes = $documentTypes->groupBy('type');
        
        $notificationData = Notification::where('type',0)->where('user_id',$id)->where('is_read',0)->get();
        foreach($notificationData as $notification){
            $notification->is_read = 1;
            $notification->save();
        }
        
        return view('admin/company/view', compact('model', 'logData', 'deviceData','allowedDocs', 'docTypes'));
    }
    
    public function save(Request $request){
        return response()->json((new User())->storeCompany($request->all()));
    }
    
    public function delete(Request $request){
        
        $model = User::find($request->id);

        if(!$model){
             return response()->json(['status' => 0, 'message' => 'No data found']);
        }
        
        if($model){
            $contractorData = User::where('company_id',$model->id)->get();
            if($contractorData->isEmpty()) {
                $model->delete();
                return response()->json(['status' => 1, 'message' => 'Company Removed Successfully','next' => 'table_refresh']);
            }else{
                return response()->json(['status'=>0, 'message'=> 'Company deletion is not allowed until any contractor have exited']);
            }
        }
        
        
    }
    
       /**
     * Save company allowed documents from multi-select dropdown.
     */
   public function saveAllowedDocuments(Request $request)
    {
        $companyId = $request->company_id;
        $documents = $request->documents_type ?? [];
    
        $documentsJson = json_encode(array_map('intval', $documents));
    
        DB::table('company_allowed_documents')->updateOrInsert(
            ['company_id' => $companyId], 
            [
                'document_type_id' => $documentsJson,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
    
        return response()->json([
            'status' => 1,
            'message' => 'Documents updated successfully',
            'next'    => 'reload'
        ]);
    }
}