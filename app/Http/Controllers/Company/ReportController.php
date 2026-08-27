<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Subscription;
use App\Helpers\General;
use App\Models\Document;
use Barryvdh\DomPDF\Facade\Pdf;
use ZipArchive;
use App\Models\DocumentFile;
use App\Models\DocumentType;
use App\Helpers\DocumentHelper;


class ReportController extends Controller
{
    public function index(){
        
        $user = auth()->user();
        $subscriptionData = Subscription::where('user_id',$user->id)->first();
        if(isset($subscriptionData->status) && $subscriptionData->status == 'active' || $user->unlimited_conractors == 1){
            return view('company/report/index');
        }else{
            return redirect()->route('company/plan')->with('error', 'Your plan was expired, please upgrade your plan');
        }
    }
    
    public function list(Request $request){
        $authId = auth()->id();
        return response()->json((new User())->reportList($request->all(),$authId));
    }
    
    
    public function exportIndex(){
        $user = auth()->user();
        $subscriptionData = Subscription::where('user_id',$user->id)->first();
        $contractorList = User::where('company_id',$user->id)->get();
        if(isset($subscriptionData->status)  &&  $subscriptionData->status == 'active' || $user->unlimited_conractors == 1){
            return view('company/export_documents/index',compact('contractorList'));
        }else{
            return redirect()->route('company/plan')->with('error', 'Your plan was expired, please upgrade your plan');
        }
    }
    
    
    public function ExportDocuments(Request $request)
    {
        $sessionUser = auth()->user();
        $contractorStatus = $request->status;
        $docStatus = $request->docStatus;
        $contractorId = $request->contractorId;
    
        $allowedDocTypeIds = DocumentHelper::getAllowedVisibleDocTypeIds($sessionUser->id);
    
        if ($allowedDocTypeIds->isEmpty()) {
            return response()->json(['warning' => 'No allowed documents configured for export'], 404);
        }
    
        // Get contractors based on filters
        $contractorQuery = User::where('company_id', $sessionUser->id);
    
        if ($contractorStatus !== null && $contractorStatus !== '') {
            $contractorQuery->where('status', $contractorStatus);
        }
    
        if (!empty($contractorId)) {
            $contractorQuery->where('id', $contractorId);
        }
    
        $contractors = $contractorQuery->get();
    
        $fileName = 'contractor_documents_' . now()->format('Y-m-d_H-i-s') . '.zip';
        $zipPath = public_path('temp_docs/' . $fileName);
    
        $zip = new ZipArchive;
    
        if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
            return response()->json(['error' => 'Failed to create ZIP file'], 500);
        }
    
        $general = new \App\Helpers\General();
    
        $hasExportData = false;
    
        foreach ($contractors as $contractor) {
    
            // Get contractor documents
            $documentsQuery = Document::query()
                ->join('document_type as dt', 'document.type', '=', 'dt.id')
                ->where('dt.is_hidden', 0)
                ->whereIn('document.type', $allowedDocTypeIds->toArray())
                ->where('document.user_id', $contractor->id);
    
            if ($docStatus !== null && $docStatus !== '') {
                $documentsQuery->where('document.status', $docStatus);
            }
    
            $documents = $documentsQuery->select('document.*')->get();
    
            $hasNote = !empty(trim($contractor->contractor_internal_note));
    
            // Skip contractor if nothing to export
            if ($documents->isEmpty() && !$hasNote) {
                continue;
            }
    
            $hasExportData = true;
    
            $contractorFolder = trim($contractor->first_name . ' ' . $contractor->last_name);
    
            // Create contractor folder
            $zip->addEmptyDir($contractorFolder);
    
            // Add Internal Note
            if ($hasNote) {
                $zip->addFromString(
                    $contractorFolder . '/Internal_Note.txt',
                    $contractor->contractor_internal_note
                );
            }
    
            // Add Documents
            foreach ($documents as $doc) {
    
                $documentFile = DocumentFile::find($doc->filename);
    
                if (!$documentFile) {
                    continue;
                }
    
                $documentTypeModel = DocumentType::find($doc->type);
                $documentName = $documentTypeModel ? $documentTypeModel->name : 'Document';
    
                $filePath = $general->getFileUrl($documentFile->filename, 'document');
    
                if (filter_var($filePath, FILTER_VALIDATE_URL)) {
                    $filePath = str_replace(asset('/'), public_path('/'), $filePath);
                }
    
                if (!file_exists($filePath)) {
                    continue;
                }
    
                $cleanFileName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $documentName);
                $extension = pathinfo($filePath, PATHINFO_EXTENSION);
    
                $zip->addFile(
                    $filePath,
                    $contractorFolder . '/' . $cleanFileName . '.' . $extension
                );
            }
        }
    
        $zip->close();
    
        if (!$hasExportData) {
            @unlink($zipPath);
            return response()->json(['warning' => 'No documents or internal notes found to export'], 404);
        }
    
        return response()->json([
            'file_url' => asset('temp_docs/' . $fileName),
            'file_name' => $fileName
        ]);
    }
    
    public function exportData(Request $request)
    {

    $status = $request->status;
    $docStatus = $request->docStatus;
    $exportType = $request->type;
    $companyId = auth()->id();
    $allowedDocTypeIds = DocumentHelper::getAllowedVisibleDocTypeIds($companyId);

    
    if ($exportType == 'csv') {
         $query = User::query()
            ->leftJoin('document', 'user.id', '=', 'document.user_id')
            ->leftJoin('document_type as dt', 'document.type', '=', 'dt.id')
            ->where('user.company_id', $companyId)
            ->where('user.type', 1)
            ->where('dt.is_hidden', 0)
            ->whereIn('document.type', $allowedDocTypeIds->toArray()); 
    
        if ($status !== null) {
            $query->where('user.status', $status);
        }
    
        if ($docStatus !== null) {
            $query->where('document.status', $docStatus); 
        }
    
        $users = $query->get(['user.*', 'user.contractor_internal_note' ,'document.type as doctype', 'document.status as docstatus', 'document.expired_at']); 

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="contractor_report.csv"');

        $output = fopen('php://output', 'w');

        $headers = ['Id', 'First Name', 'Last Name', 'Email',
                     'Country', 'Status','Compliance Status','Email Verified', 
                    'Timezone', 'Created At', 'Updated At',
                    'Document Name', 'Document Status', 'Notes' ,'Expired Date'];

        fputcsv($output, $headers);

        foreach ($users as $user) {
            $userStatus = $user->status == 1 ? 'Active' : 'Inactive';
            $userEmailVerified = $user->email_verified == 1 ? 'Yes' : 'No';
            
            $complianceStatus = (new User())->getComplianceStatus($user->id);
            
            $note = $user->contractor_internal_note ?? '';
            $note = str_replace(["\r", "\n"], ' ', $note);
            $note = \Illuminate\Support\Str::limit($note, 300);

            $data = [
                $user->id,$user->first_name,$user->last_name,$user->email,
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
                        ->where('user.company_id', $companyId)
                        ->where('user.type', 1)
                        ->where('dt.is_hidden', 0)
                        ->whereIn('document.type', $allowedDocTypeIds->toArray());
    
            if ($status !== null) {
                $query->where('user.status', $status);
            }
    
            if ($docStatus !== null) {
                $query->where('document.status', $docStatus); 
            }
    
            $users = $query->get(['user.*', 'user.contractor_internal_note', 'document.type as doctype', 'document.status as docstatus', 'document.expired_at']);
            $htmlContent = view('company/report/pdf_preview', compact('users','docStatus'))->render(); 
            $response = (new General())->generatePdf($htmlContent);

          
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
    
        
}