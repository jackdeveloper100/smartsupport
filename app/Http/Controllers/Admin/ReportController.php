<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Helpers\General;
use App\Models\Document;
use App\Models\DocumentFile;
use App\Models\DocumentType;
use Illuminate\Support\Facades\File as Files;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use ZipArchive;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
// use Barryvdh\DomPDF\Facade\Pdf;



class ReportController extends Controller
{
    public function index(){
        $sessionUser = auth()->user();
        if($sessionUser && $sessionUser->hasPermission('admin_report')){
            $company = User::where('type',2)->get();
            return view('admin/report/index',compact('company'));
        }else{
            return redirect('admin/dashboard')->with('error', 'You are Not Authorized');
        }
    }    
    
    
    public function exportIndex(){
        $sessionUser = auth()->user();
        if($sessionUser && $sessionUser->hasPermission('admin_report')){
            $company = User::where('type',2)->get();
            $companyList = User::where('type',2)->get();
            $contractorList = \App\Models\User::where('type',1)->get();
            return view('admin/export_documents/index',compact('company','companyList','contractorList'));
        }else{
            return redirect('admin/dashboard')->with('error', 'You are Not Authorized');
        }
    }
    
    public function list(Request $request){
        return response()->json((new User())->reportList($request->all()));
    }
    
   public function ExportDocuments(Request $request)
    {
    
        $contractorStatus = $request->status;
        $docStatus = $request->docStatus;
        $companyName = $request->companyName;
        $contractorId = $request->contractorId;
    
        $query = Document::query()
            ->join('user', 'document.user_id', '=', 'user.id')
            ->join('document_type as dt', 'document.type', '=', 'dt.id')
            ->where('user.type', 1)
            ->where('dt.is_hidden', 0)
            ->whereExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('company_allowed_documents as cad')
                    ->whereColumn('cad.company_id', 'user.company_id')
                    ->where(function ($allowed) {
                        $allowed->whereRaw("JSON_VALID(cad.document_type_id) AND (JSON_CONTAINS(cad.document_type_id, CONCAT(CHAR(34), CAST(document.type AS CHAR), CHAR(34))) OR JSON_CONTAINS(cad.document_type_id, CAST(document.type AS UNSIGNED)))")
                            ->orWhereRaw("cad.document_type_id = CAST(document.type AS CHAR)");
                    });
            });
    
        if ($contractorStatus !== null && $contractorStatus !== '') {
            $contractors = User::where('status', $contractorStatus)->pluck('id')->toArray();
            $query->whereIn('document.user_id', $contractors);
        }
    
        if ($docStatus !== null && $docStatus !== '') {
            $query->where('document.status', $docStatus);
        }
    
        if (!empty($companyName)) {
            $contractorIds = User::where('company_id', $companyName)->pluck('id')->toArray();
            $query->whereIn('document.user_id', $contractorIds);
        }
    
        if (!empty($contractorId)) {
            $query->where('document.user_id', $contractorId);
        }
    
        $documents = $query->select('document.*')->get();
    
        $fileName = 'contractor_documents_' . now()->format('Y-m-d_H-i-s') . '.zip';
        $zipPath = public_path('temp_docs/' . $fileName);
        $zip = new ZipArchive;
    
        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
    
            if ($documents->isEmpty()) {
                return response()->json(['warning' => 'No documents found to export'], 404);
            }
    
            $total = count($documents);
            $index = 0;
            $general = new \App\Helpers\General();
    
            foreach ($documents as $doc) {
                $index++;
                $documentFile = DocumentFile::find($doc->filename);
                $documentTypeModel = DocumentType::find($doc->type);
                $documentName = $documentTypeModel ? $documentTypeModel->name : 'Document';
    
                if ($documentFile) {
                    $filePath = $general->getFileUrl($documentFile->filename, 'document');
    
                    if (filter_var($filePath, FILTER_VALIDATE_URL)) {
                        $filePath = str_replace(asset('/'), public_path('/'), $filePath);
                    }
    
                    if (!file_exists($filePath)) continue;
    
                    $contractor = User::find($doc->user_id);
                    $company = User::find($contractor->company_id ?? 0);
                    $companyName = $company ? $company->company_name : 'Unknown_Company';
                    $contractorName = $contractor->first_name . ' ' . $contractor->last_name;
    
                    $companyFolder = $companyName;
                    $contractorFolder = $contractorName;
                    $cleanFileName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $documentName);
                    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
    
                    $folderPath = "{$companyFolder}/{$contractorFolder}/";
                    $zip->addFile($filePath, $folderPath . $cleanFileName . '.' . $extension);
                    
                    $fileDestination = asset('temp_docs/' . $fileName);
                }
    
            }
    
            $zip->close();
    
            return response()->json([
                'file_url' => $fileDestination,
                'file_name' => $fileName
            ]);
        }
    
        return response()->json(['error' => 'Failed to create ZIP file'], 500);
    }

    public function exportData(Request $request)
    {
        $status = $request->status;
        $docStatus = $request->docStatus;
        $exportType = $request->type;
        $companyName = $request->companyName;

        if ($exportType == 'csv') {
            $query = User::query()
                ->leftJoin('document', 'user.id', '=', 'document.user_id')
                ->leftJoin('document_type as dt', 'document.type', '=', 'dt.id')
                ->where('user.type', 1)
                ->where('dt.is_hidden', 0)
                ->whereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('company_allowed_documents as cad')
                        ->whereColumn('cad.company_id', 'user.company_id')
                        ->where(function ($allowed) {
                            $allowed->whereRaw("JSON_VALID(cad.document_type_id) AND (JSON_CONTAINS(cad.document_type_id, CONCAT(CHAR(34), CAST(document.type AS CHAR), CHAR(34))) OR JSON_CONTAINS(cad.document_type_id, CAST(document.type AS UNSIGNED)))")
                                ->orWhereRaw("cad.document_type_id = CAST(document.type AS CHAR)");
                        });
                });
        
            if ($status !== null) {
                $query->where('user.status', $status);
            }
        
            if ($docStatus !== null) {
                $query->where('document.status', $docStatus); 
            }
            
            if($companyName !== null){
                $query->where('user.company_id',$companyName);
            }
        
            $users = $query->get(['user.*', 'user.contractor_internal_note' ,'document.type as doctype', 'document.status as docstatus', 'document.expired_at']); //
    
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="contractor_report.csv"');
    
            $output = fopen('php://output', 'w');
    
            $headers = ['Id', 'First Name', 'Last Name', 'Email','Business Name','Company Name',
                         'Country', 'Status','Compliance Status','Email Verified', 
                        'Timezone', 'Created At', 'Updated At',
                        'Document Name', 'Document Status','Notes' , 'Expired Date'];
    
            fputcsv($output, $headers);
    
            foreach ($users as $user) {
                $userStatus = $user->status == 1 ? 'Active' : 'Inactive';
                $userEmailVerified = $user->email_verified == 1 ? 'Yes' : 'No';
                $companyName = (new User())->getCompanyName($user->company_id);
                $complianceStatus = (new User())->getComplianceStatus($user->id);
                
                 $note = $user->contractor_internal_note ?? '';
                 $note = str_replace(["\r", "\n"], ' ', $note);
                 $note = \Illuminate\Support\Str::limit($note, 300);
        
                $data = [
                    $user->id,$user->first_name,$user->last_name,$user->email,$user->business_name,$companyName,
                    $user->country,$userStatus,$complianceStatus,$userEmailVerified,
                    $user->timezone,$user->created_at,$user->updated_at,
                    (new Document())->getDocumentName($user->doctype),  
                    (new Document())->getStatus($user->docstatus),
                     $note,
                    $user->expired_at ? $user->expired_at : '',
                ];
    
                fputcsv($output, $data);
            }
    
            fclose($output);
    
            exit;
        }
        
        if ($exportType == 'pdf'){
         
            $query = User::query()
                        ->leftJoin('document', 'user.id', '=', 'document.user_id')
                        ->leftJoin('document_type as dt', 'document.type', '=', 'dt.id')
                        ->where('user.type', 1)
                        ->where('dt.is_hidden', 0)
                        ->whereExists(function ($sub) {
                            $sub->select(DB::raw(1))
                                ->from('company_allowed_documents as cad')
                                ->whereColumn('cad.company_id', 'user.company_id')
                                ->where(function ($allowed) {
                                    $allowed->whereRaw("JSON_VALID(cad.document_type_id) AND (JSON_CONTAINS(cad.document_type_id, CONCAT(CHAR(34), CAST(document.type AS CHAR), CHAR(34))) OR JSON_CONTAINS(cad.document_type_id, CAST(document.type AS UNSIGNED)))")
                                        ->orWhereRaw("cad.document_type_id = CAST(document.type AS CHAR)");
                                });
                        }); 
    
    
            if ($status !== null) {
                $query->where('user.status', $status);
            }
    
            if ($docStatus !== null) {
                $query->where('document.status', $docStatus); 
            }
            
             if($companyName !== null){
                $query->where('user.company_id',$companyName);
            }
    
            $users = $query->get(['user.*','user.contractor_internal_note', 'document.type as doctype', 'document.status as docstatus', 'document.expired_at']);
            $htmlContent = view('admin/report/pdf_preview', compact('users','docStatus'))->render(); 

            $response = (new General())->generatePdf($htmlContent);
            
            // dd($response);
            if (isset($response['status']) && $response['status']) {
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="contractor_report.pdf"'); 
                header('Content-Transfer-Encoding: binary');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
            
                 // Ensure the content key exists
                if (!empty($response['data']['content'])) {
                    echo base64_decode($response['data']['content']);
                    exit;
                } else {
                    // Handle missing content
                    return redirect()->back()->with('error', 'PDF content is missing.');
                }
                
            } else {
                return redirect()->back()->with('error', $response['message'] ?? 'Unknown error.');
            }
            
        }
    }

    // public function generatePdfFile()
    //     {
    //         $data = (new Contact())->list(null);
    //         $htmlContent = view('customer_inquires/contact_us/pdf_view',compact('data'))->render();
    //         $response= (new General())->generatePdf($htmlContent);
    //         if(isset($response['status']) && $response['status']){
    //             header('Content-Type: application/pdf');
    //             echo base64_decode($response['data']['content']);
    //         }else{
    //             return redirect()->back()->with('error',$response['message']);
    //         }
    //     }
    


    public function deleteTempFiles(){
        $folderPath = public_path('temp_docs');
    
        // Get all .zip files in the folder
        $zipFiles = Files::glob($folderPath . '/*.zip');
    
        foreach ($zipFiles as $file) {
            
            if (filemtime($file) < (time() - 3600)) {
                Files::delete($file);
            }
                // Files::delete($file);
        }
    
        return 'ok';
    }

}