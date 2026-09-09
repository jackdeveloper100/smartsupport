<?php

namespace App\Models;

use App\Helpers\General;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Pagination;
use App\Models\Document;
use App\Models\User;
use Carbon\Carbon;



class DocumentActivity extends Model
{
     use HasFactory;
     
       protected $table = 'document_activity';
       public $timestamps = true;
       protected $dateFormat = 'U';
       protected $fillable = ['user_id', 'document_id','description','admin_description','created_at','updated_at'];

    
    //   public function listOfDocumentActivity($postData, $id)
    //   {
    //       $query = DB::table($this->table)->where('user_id', $id);
       
    //       $searchText = $postData['search']['value'] ?? '';
       
    //       if (strlen($searchText) > 2) {
    //           $normalizedSearch = strtolower(trim(preg_replace('/\s+/', ' ', $searchText)));
       
    //           $query->where(function ($q) use ($normalizedSearch) {
    //               $q->whereRaw("LOWER(TRIM(REPLACE(description, '  ', ' '))) LIKE ?", ['%' . $normalizedSearch . '%']);
    //           });
    //       }
       
    //       $result = (new Pagination())->getDataTable($query, $postData);
       
    //       foreach ($result['data'] as $key => $row) {
    //         //   dd(Carbon::parse($row->created_at)->format('d M, Y'));
    //         //   $result['data'][$key]->created_at = Carbon::parse($row->created_at)->format('d M, Y');
    //           $result['data'][$key]->created_at = Carbon::createFromTimestamp($row->created_at)->format('d M, Y');

    //       }
       
    //       return $result;
    //   }
       

    //   public function listOfDocumentActivityAdmin($postData,$id){
    //     $query = DB::table($this->table)->where('user_id',$id);
    //     $searchText = $postData['search']['value'] ?? '';
    //     if (strlen($searchText) > 2) {
    //         $query->where(function ($q) use ($searchText) {
    //             $q->where('admin_description', 'like', '%' . $searchText . '%'); 
    //         });
    //     }
    //     $result = (new Pagination())->getDataTable($query, $postData);
    //     $sessionUser = auth()->user();
    //       foreach ($result['data'] as $key => $row) {
    //         // $result['data'][$key]->created_at = Carbon::parse($row->created_at)->format('d M, Y');
    //         $result['data'][$key]->created_at = Carbon::createFromTimestamp($row->created_at)->format('d M, Y');
    //     }

    //     return $result;
    // }
    
        
       public function listOfDocumentActivity($postData, $id)
       {
           $query = DB::table($this->table)
               ->leftJoin('document', 'document.id', '=', "{$this->table}.document_id")
               ->leftJoin('user', 'user.id', '=', "{$this->table}.user_id")
               ->where("{$this->table}.user_id", $id)
               ->whereNotNull('document.type')
               ->whereExists(function ($subQuery) {
                   $subQuery->select(DB::raw(1))
                       ->from('company_allowed_documents as cad')
                       ->whereColumn('cad.company_id', 'user.company_id')
                        ->where(function ($allowed) {
                            $allowed->whereRaw("(JSON_VALID(cad.document_type_id) AND (JSON_CONTAINS(cad.document_type_id, CAST(document.type AS CHAR)) OR JSON_CONTAINS(cad.document_type_id, JSON_QUOTE(CAST(document.type AS CHAR))))) OR FIND_IN_SET(CAST(document.type AS CHAR), REPLACE(REPLACE(REPLACE(REPLACE(cad.document_type_id, '[', ''), ']', ''), '\"', ''), ' ', '')) > 0 OR cad.document_type_id = CAST(document.type AS CHAR)");
                        });
               })
               ->select("{$this->table}.*");
       
           $searchText = $postData['search']['value'] ?? '';
       
           if (strlen($searchText) > 2) {
               $normalizedSearch = strtolower(trim(preg_replace('/\s+/', ' ', $searchText)));
       
               $query->where(function ($q) use ($normalizedSearch) {
                   $q->whereRaw("LOWER(TRIM(REPLACE(description, '  ', ' '))) LIKE ?", ['%' . $normalizedSearch . '%']);
               });
           }
       
           $result = (new Pagination())->getDataTable($query, $postData);
       
           foreach ($result['data'] as $key => $row) {
             //   dd(Carbon::parse($row->created_at)->format('d M, Y'));
             //   $result['data'][$key]->created_at = Carbon::parse($row->created_at)->format('d M, Y');
                $result['data'][$key]->created_at = Carbon::createFromTimestamp($row->created_at)->format('d M, Y');

            }
        
            return $result;
        }
        

     public function listOfDocumentActivityAdmin($postData,$id){
         $query = DB::table($this->table)
             ->leftJoin('document', 'document.id', '=', "{$this->table}.document_id")
             ->leftJoin('user', 'user.id', '=', "{$this->table}.user_id")
             ->where("{$this->table}.user_id", $id)
             ->whereNotNull('document.type')
             ->whereExists(function ($subQuery) {
                 $subQuery->select(DB::raw(1))
                     ->from('company_allowed_documents as cad')
                     ->whereColumn('cad.company_id', 'user.company_id')
                     ->where(function ($allowed) {
                         $allowed->whereRaw("(JSON_VALID(cad.document_type_id) AND (JSON_CONTAINS(cad.document_type_id, CAST(document.type AS CHAR)) OR JSON_CONTAINS(cad.document_type_id, JSON_QUOTE(CAST(document.type AS CHAR))))) OR FIND_IN_SET(CAST(document.type AS CHAR), REPLACE(REPLACE(REPLACE(REPLACE(cad.document_type_id, '[', ''), ']', ''), '\"', ''), ' ', '')) > 0 OR cad.document_type_id = CAST(document.type AS CHAR)");
                     });
             })
             ->select("{$this->table}.*");
         $searchText = $postData['search']['value'] ?? '';
         if (strlen($searchText) > 2) {
             $query->where(function ($q) use ($searchText) {
                 $q->where('admin_description', 'like', '%' . $searchText . '%'); 
             });
         }
         $result = (new Pagination())->getDataTable($query, $postData);
           foreach ($result['data'] as $key => $row) {
             // $result['data'][$key]->created_at = Carbon::parse($row->created_at)->format('d M, Y');
             $result['data'][$key]->created_at = Carbon::createFromTimestamp($row->created_at)->format('d M, Y');
         }

         return $result;
     }
    
    public function store($type,$id){
        $model = new DocumentActivity();
        $documentModel = Document::find($id);
        $userModel =  User::where('id',$documentModel->user_id)->first();
        $documentActivityDesc = DocumentType::select('name')->where('id',$documentModel->type)->first();
        
        $model->user_id = $documentModel->user_id;
        $model->document_id = $documentModel->id;
        if($type == 'approve'){
            $model->admin_description = ' You Approved '. $documentActivityDesc->name;
            $model->description = ' Your '. $documentActivityDesc->name . ' are Approved';

        }elseif($type == 'reject'){
            $model->admin_description = ' You Rejected '. $documentActivityDesc->name;
            $model->description = ' Your '. $documentActivityDesc->name . ' are Rejected';
        }else{
            $model->description = "Not Found ";
        }
        $model->save();
    }
    
}