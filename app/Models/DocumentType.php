<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Helpers\Pagination;
use App\Helpers\General;
/**
 * Class DocumentType
 *
 * Model for the `page` table.
 * Handles listing pages for admin with search and pagination.
 *
 * @package App\Models
 */
class DocumentType extends Model
{
    /**
     * The name of the table associated with the model.
     *
     * @var string
     */
    protected $table = 'document_type';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name','type','is_hidden'];
    
    
    public function list($postData){
        $sessionUser = auth()->user();
        $query = DB::table('document_type')->select('*')->where('is_hidden',0);
        
        $searchText = isset($postData['search']['value']) ? $postData['search']['value'] : '';
        if(strlen($searchText) >2 ){
            $query->where(function ($q) use ($searchText) {
                $q->where('name', 'like', '%' . $searchText . '%')
                  ->orWhere('type', 'like', '%' . $searchText . '%');
            });
        }
        
        $result = (new Pagination())->getDataTable($query, $postData);

         foreach ($result['data'] as $key => $row) {
            $result['data'][$key]->type = $this->getDocumentType($row->type);
            $result['data'][$key]->action = $this->generateActionLinks($row, $sessionUser);
        }
        return $result;
    }


    public function store($postData){
        
        $general = new General();
        $id = $postData['id'];
        $rules = [
            'name' => 'required|string|max:255',
            'document_type' => 'required',
        ];
        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) {
            return [
                'status' => 0,
                'message' => $validator->errors()->first(),
            ];
        }
        
        if($id){
            $model = DocumentType::find($id);
        }else{
            $model = new DocumentType();
        }
        
        $model->name = $postData['name'];
        $model->type = $postData['document_type'];
        $model->save();
        
        return [
        'status' => 1 , 
        'message' => $id ? 'DocumentType Updated Successfully' : 'DocumentType Created Successfully',
        'next'=>'load',
        'url' => 'admin/setting/document_type'
        ];
    }
    
    public function getDocumentType($type){
        if($type == 1){
            return "Required";
        }else{
            return "Optional";
        }
    }   
    
      protected function generateActionLinks(object $row, $sessionUser): string
    {
        $actionLinks = '';


        if ($sessionUser && $sessionUser->hasPermission('admin/setting/document_type/update')) {
            $actionLinks .= sprintf(
                '<a href="admin/setting/document_type/update?id=%d" class="text-body pjax act-btns tool-btn me-2" ><i class="bi bi-pencil-square"><span class="tooltip-text">Update</span></i></a>',
                $row->id
            );
        }

       if ($sessionUser && $sessionUser->hasPermission('admin/setting/document_type/delete')) {
            $actionLinks .= sprintf(
                '<button style="border:none; background:none;" onclick="app.confirmAction(this);" data-action="admin/setting/document_type/delete" data-id="' . $row->id . '" class="text-body act-btns tool-btn me-2" ><i class="bi bi-trash-fill"><span class="tooltip-text">Delete</span></i></button>'
            );
        }

        return $actionLinks;
    }
    
    public function getMissingDocument($userId)
    {
        $user = \App\Models\User::find($userId);
        if (!$user || !$user->company_id) return [];
    
        // Get allowed document IDs for this company
        $allowedDocTypeIds = \App\Helpers\DocumentHelper::getAllowedDocTypeIds($user->company_id)
            ->toArray();
    
        // Only consider visible & required documents (type=1)
        $requiredVisibleDocs = DocumentType::whereIn('id', $allowedDocTypeIds)
            ->where('is_hidden', 0)
            ->where('type', 1)
            ->pluck('id')
            ->toArray();
    
        // Documents already uploaded by user
        $userDocumentTypeIds = Document::where('user_id', $userId)
            ->whereIn('type', $requiredVisibleDocs)
            ->pluck('type')
            ->toArray();
    
        // Missing documents
        $missingDoc = DocumentType::whereIn('id', $requiredVisibleDocs)
            ->whereNotIn('id', $userDocumentTypeIds)
            ->pluck('name')
            ->toArray();
    
        return $missingDoc;
    }
    
    public function getExpiredDocument($userId)
    {
        $user = \App\Models\User::find($userId);
        if (!$user || !$user->company_id) return [];
    
        // Get allowed document IDs for this company
        $allowedDocTypeIds = \App\Helpers\DocumentHelper::getAllowedDocTypeIds($user->company_id)
            ->toArray();
    
        // Expired documents uploaded by user, and allowed by company
        $documentData = Document::where('user_id', $userId)
            ->where('status', 3) // expired status
            ->whereIn('type', $allowedDocTypeIds)
            ->get();
    
        $expiredDocument = [];
        foreach ($documentData as $document) {
            $expiredDocument[] = (new Document)->getDocumentName($document->type);
        }
    
        return $expiredDocument;
    }
    
    // public function getMissingDocument($userId)
    // {
    //     $visibleTypeIds = DocumentType::where('is_hidden', 0)->where('type', 1)->pluck('id')->toArray();
    
    //     $userDocumentTypeIds = Document::where('user_id', $userId)->whereIn('type', $visibleTypeIds)->pluck('type')->toArray();
    
    //     $missingDoc = DocumentType::whereIn('id', $visibleTypeIds)->whereNotIn('id', $userDocumentTypeIds)->pluck('name')->toArray();
    
    //     return $missingDoc;
    // }
    
    // public function getExpiredDocument($userId){
        
    //     $documentData = Document::where('user_id',$userId)->where('status',3)->get();
        
    //         $expiredDocument = [];
            
    //         if(!empty($documentData)){
    //             foreach($documentData as $document){
    //                 $expiredDocument[] = (new Document)->getDocumentName($document->type);
    //             }
    //         }
    //         return $expiredDocument;
        
    // }
}

