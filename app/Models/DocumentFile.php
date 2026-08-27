<?php

namespace App\Models;

use App\Helpers\General;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Document;
use App\Models\Notification;
use App\Helpers\Pagination;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class DocumentFile extends Model
{
    use HasFactory;
    
      protected $table = 'document_file';
      public $timestamps = true;
      protected $dateFormat = 'U';
      protected $fillable = ['document_id','filename','created_at','updated_at'];

    public function documentsVersionListAdmin($postData,$id){
      $currentDocument = Document::where('id', $id)->first();
      $query = DB::table($this->table)->where('document_id',$id)->where('id','!=',$currentDocument->filename);
      $searchText = $postData['search']['value'] ?? '';

      if (strlen($searchText) > 2) {
          $query->where(function ($q) use ($searchText) {
              $q->where('created_at', 'like', '%' . $searchText . '%'); 
          });
      }

      $result = (new Pagination())->getDataTable($query, $postData);
      $sessionUser = auth()->user();
        
        foreach ($result['data'] as $key => $row) {
          $documentModel = Document::where('id',$row->document_id)->first();
          $documentName = (new Document())->getDocumentName($documentModel->type);
          $orderNumber = $this->getOrderNumber($row->id, $row->document_id);
          $versionNumber = str_pad($orderNumber, 3, '0', STR_PAD_LEFT);

          $result['data'][$key]->document_version = $documentName . '_V' . $versionNumber;
          $result['data'][$key]->created_at = date('d M, Y', $row->created_at);
          
          if($sessionUser->type == 2){
              $result['data'][$key]->action = '
            <a onclick="app.showModalView(\'' . route('company/contractor/document-file/preview', ['id' => $row->id]) . '\')" class="act-btns tool-btn me-2" ><i class="bi bi-eye-fill"></i>  <span class="tooltip-text">View</span></a>&nbsp;</div>';
          }else{
              $result['data'][$key]->action = '
              <a onclick="app.showModalView(\'' . route('admin/contractor/document-file/preview', ['id' => $row->id]) . '\')" class="act-btns tool-btn me-2" ><i class="bi bi-eye-fill"></i>  <span class="tooltip-text">View</span></a>&nbsp;</div>';
              // <a href="' . route('admin/contractor/document-file/view', ['id' => $row->id]) . '" class="text-body pjax" title="View"><i class="bi bi-eye-fill"></i></a>&nbsp;</div>';
    
          }
        }
      return $result;
    }

    public function documentsVersionList($postData, $id)
{
    $currentDocument = Document::where('id', $id)->first();
    $query = DB::table($this->table)->where('document_id', $id)->where('id','!=',$currentDocument->filename);
    $searchText = $postData['search']['value'] ?? '';
    
    if (strlen($searchText) > 2) {
        $query->where(function ($q) use ($searchText) {
            $q->where('created_at', 'like', '%' . $searchText . '%');
        });
    }

    $result = (new Pagination())->getDataTable($query, $postData);
    $sessionUser = auth()->user();
      foreach ($result['data'] as $key => $row) {
        $documentModel = Document::where('id', $row->document_id)->first();
        $documentName = (new Document())->getDocumentName($documentModel->type);
        $orderNumber = $this->getOrderNumber($row->id, $row->document_id);
        $versionNumber = str_pad($orderNumber, 3, '0', STR_PAD_LEFT);

        $result['data'][$key]->document_version = $documentName . '_V' . $versionNumber;
        $result['data'][$key]->created_at = date('d M, Y', $row->created_at);
        $result['data'][$key]->action = '
            <a onclick="app.showModalView(\'' . route('contractor/document-file/preview', ['id' => $row->id]) . '\')" class="act-btns tool-btn me-2" >
                <i class="bi bi-eye-fill"></i>  
                <span class="tooltip-text">View</span>
            </a>&nbsp;';
        if($sessionUser->type == 1){
            $companyPlanId = (new User())->getCompanyPlanInfo($sessionUser->company_id);
            $isCompanyUnlimitedContractor = (new User())->isUnlimitedContractor($sessionUser->company_id);
            if ($isCompanyUnlimitedContractor == 1) {
                // Always show Restore if company has unlimited contractor
                $result['data'][$key]->action .= '
                    <a onclick="app.confirmRestoreAction(this);" data-action="contractor/document-file/restore?id=' . $row->id . '" 
                    class="act-btns tool-btn me-2">
                        <i class="bi bi-arrow-clockwise"></i><span class="tooltip-text">Restore</span>
                    </a>';
            } else {
                // If came from company or admin, show Restore
                if (session('came_from_company') || session('came_from_admin')) {
                    $result['data'][$key]->action .= '
                        <a onclick="app.confirmRestoreAction(this);" data-action="contractor/document-file/restore?id=' . $row->id . '" 
                        class="act-btns tool-btn me-2">
                            <i class="bi bi-arrow-clockwise"></i><span class="tooltip-text">Restore</span>
                        </a>';
                } else {
                    // Show Restore only if planId is not 1 or null
                    if ($companyPlanId != 1 && $companyPlanId !== null) {
                        $result['data'][$key]->action .= '
                            <a onclick="app.confirmRestoreAction(this);" data-action="contractor/document-file/restore?id=' . $row->id . '" 
                            class="act-btns tool-btn me-2">
                                <i class="bi bi-arrow-clockwise"></i><span class="tooltip-text">Restore</span>
                            </a>';
                    }
                }
            }

        }
        // <a href="' . route('contractor/document-file/view', ['id' => $row->id]) . '" class="text-body pjax act-btns tool-btn me-2" ><i class="bi bi-eye-fill"></i><span class="tooltip-text">View</span></a>&nbsp;</div>
    }

    return $result;
}

    public function getOrderNumber($id,$document_id)
    {
      $documentFiles = self::where('document_id', $document_id)
          ->orderBy('id', 'asc') 
          ->pluck('id')
          ->toArray();
      return array_search($id, $documentFiles) + 1;
    }

    public function restoreDocument($postData){
      $documentFileId = $postData['id'];
      $model = DocumentFile::where('id',$documentFileId)->first();
      $documentModel = Document::where('id',$model->document_id)->first();
      $documentModel->update(['filename' => $documentFileId,'status'=> 1,'approve_status'=> 0]);

      return ['status'=> 1, 'message' => 'Document Restore Successfully','next'=> 'refresh'];
    }


}