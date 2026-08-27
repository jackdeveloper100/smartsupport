<?php

namespace App\Models;

use App\Helpers\Pagination;
use App\Helpers\General;
use App\Models\DocumentActivity;
use App\Models\DocumentType;
use App\Models\DocumentFile;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use App\Models\Notification;

/**
 * Class Page
 *
 * Model for the `page` table.
 * Handles listing pages for admin with search and pagination.
 *
 * @package App\Models
 */
class Document extends Model
{
    use HasFactory;
    /**
     * The name of the table associated with the model.
     *
     * @var string
     */
    protected $table = 'document';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;
    protected $dateFormat = 'U';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['user_id', 'name', 'filename', 'type', 'status', 'reject_reason' ,
            'expired_at','reminder_at','created_at','updated_at','approve_status', 'created_at','updated_at',               
                ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function documentType()
    {
        return $this->belongsTo(DocumentType::class, 'type');
    }
        /**
     * Retrieves paginated list of pages for contractor with search capability.
     *
     * @param array $postData The data passed for pagination and search.
     *   @param int $id the data passed for pagination by contractor.
     * @return array The paginated and formatted list of pages.
     */
    public function list(array $postData): array
    {
        $sessionUser = auth()->user();
        $query = DB::table($this->table)->where('user_id',$sessionUser->id);

        // Apply search filter if search text is provided and is more than 2 characters long
        $searchText = $postData['search']['value'] ?? '';
        if (strlen($searchText) > 2) {
            $query->where(function ($q) use ($searchText) {
                $q->where('type', 'like', '%' . $searchText . '%')
                  ->orWhere('filename', 'like', '%' . $searchText . '%')
                  ->orWhere('reject_reason', 'like', '%' . $searchText . '%'); // Add more fields as needed
            });
        }

        // Retrieve paginated result using custom Pagination helper
        $result = (new Pagination())->getDataTable($query, $postData);
        
        // Append action links based on permissions
        foreach ($result['data'] as $key => $row) {
            $result['data'][$key]->created_at = date('d M, Y', $row->created_at);
            $result['data'][$key]->expired_at = date('d M, Y', strtotime($row->expired_at));
            $result['data'][$key]->status = $this->getStatusBadge($row->status);
            $result['data'][$key]->action = $this->generateActionLinksUser($row, $sessionUser);
        }
        return $result;
    }            


    /**
     * Retrieves paginated list of pages for contractor with search capability.
     *
     * @param array $postData The data passed for pagination and search.
     *   @param int $id the data passed for pagination by contractor.
     * @return array The paginated and formatted list of pages.
     */
    public function listByContractor(array $postData, int $id): array
    {
        $query = DB::table($this->table)
            ->join('document_type', 'document_type.id', '=', "{$this->table}.type")
            ->join('user as contractor_user', 'contractor_user.id', '=', "{$this->table}.user_id")
            ->where("{$this->table}.user_id", $id)
            ->where('document_type.is_hidden', 0)
            // Restrict by company_allowed_documents (supports JSON array and scalar formats)
            ->whereExists(function ($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('company_allowed_documents as cad')
                    ->whereColumn('cad.company_id', 'contractor_user.company_id')
                    ->where(function ($allowed) {
                        $allowed->whereRaw("JSON_VALID(cad.document_type_id) AND (JSON_CONTAINS(cad.document_type_id, CONCAT(CHAR(34), CAST(document.type AS CHAR), CHAR(34))) OR JSON_CONTAINS(cad.document_type_id, CAST(document.type AS UNSIGNED)))")
                            ->orWhereRaw("cad.document_type_id = CAST(document.type AS CHAR)");
                    });
            })
            ->select("{$this->table}.*", "document_type.name as name");
    
        $searchText = $postData['search']['value'] ?? '';
        if (strlen($searchText) > 2) {
            $query->where(function ($q) use ($searchText) {
                $q->where('document_type.name', 'like', '%' . $searchText . '%')
                  ->orWhere('filename', 'like', '%' . $searchText . '%')
                  ->orWhere('reject_reason', 'like', '%' . $searchText . '%'); 
            });
        }
    
        if (isset($postData['documentType']) && $postData['documentType'] !== '') {
            $query->where("{$this->table}.type", $postData['documentType']);
        }
    
        if (isset($postData['docStatus']) && $postData['docStatus'] !== '') {
            $query->where("{$this->table}.status", $postData['docStatus']);
        }
    
        if (isset($postData['startDateExp']) && $postData['startDateExp'] !== '') {
            $dates = explode(" to ", $postData['startDateExp']);
            $startDateExp = date('Y-m-d', strtotime($dates[0]));
    
            if (count($dates) === 2) {
                $endDate = date('Y-m-d', strtotime($dates[1]));
            } else {
                $endDate = date('Y-m-d'); 
            }
    
            $query->whereBetween("{$this->table}.expired_at", [$startDateExp, $endDate]);
        }
    
        $result = (new Pagination())->getDataTable($query, $postData);
        
        $sessionUser = auth()->user();
        foreach ($result['data'] as $key => $row) {
            // dd(Carbon::parse($row->updated_at)->format('d M, Y'));
            // $result['data'][$key]->created_at = date('d M, Y', strtotime($row->created_at));
            // $result['data'][$key]->created_at = Carbon::parse($row->updated_at)->format('d M, Y');
            
            $result['data'][$key]->created_at = Carbon::createFromTimestamp($row->updated_at)->format('d M, Y');
            
             if ($row->expired_at) {
            // $result['data'][$key]->expired_at = date('d M, Y', strtotime($row->expired_at));
            } else {
                $result['data'][$key]->expired_at = 'Never';
             }
            // $result['data'][$key]->expired_at = date('d M, Y', strtotime($row->expired_at));
            $result['data'][$key]->status = $this->getStatusBadge($row->status);
            $result['data'][$key]->type = $this->getDocumentName($row->type);
            $result['data'][$key]->action = $this->generateActionLinks($row, $sessionUser);
        }
        
        return $result;
    }


    public function expirationalList(array $postData, $authId = ''): array
    {
        $query = DB::table('document')
            ->select(
                'user.id as user_id',
                'user.first_name as first_name',
                'user.last_name as user_last_name',
                'user.company_id',
                'company_user.company_name as company_name', // from self-join
                'document.id',
                'document.type',
                'document.expired_at',
                'document.status'
            )
            ->leftJoin('user', 'document.user_id', '=', 'user.id')
            ->leftJoin('user as company_user', 'user.company_id', '=', 'company_user.id') // self-join
            ->leftJoin('document_type', 'document_type.id', '=', 'document.type')
            ->whereIn('document.status', [2, 3])
            // ->whereNotNull('user.first_name')
            ->whereNotNull('document.type')
            // Restrict by company_allowed_documents (supports JSON array and scalar formats)
            ->whereExists(function ($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('company_allowed_documents as cad')
                    ->whereColumn('cad.company_id', 'user.company_id')
                    ->where(function ($allowed) {
                        $allowed->whereRaw("JSON_VALID(cad.document_type_id) AND (JSON_CONTAINS(cad.document_type_id, CONCAT('\"', CAST(document.type AS CHAR), '\"')) OR JSON_CONTAINS(cad.document_type_id, CAST(document.type AS UNSIGNED)))")
                            ->orWhereRaw("cad.document_type_id = CAST(document.type AS CHAR)");
                    });
            })
            ->orderBy('document.status');
    
        if (!empty($authId)) {
            $query->where('user.company_id', $authId);
        }
    
        $searchText = $postData['search']['value'] ?? '';
        if (strlen($searchText) > 2) {
            $query->where(function ($q) use ($searchText) {
                $q->where('document_type.name', 'like', '%' . $searchText . '%')
                  ->orWhere('document.filename', 'like', '%' . $searchText . '%')
                  ->orWhere('document.reject_reason', 'like', '%' . $searchText . '%')
                  ->orWhere('user.first_name', 'like', '%' . $searchText . '%')
                  ->orWhere('user.last_name', 'like', '%' . $searchText . '%')
                  ->orWhere(DB::raw("CONCAT(user.first_name, ' ', user.last_name)"), 'like', '%' . $searchText . '%')
                  ->orWhere(DB::raw("FROM_UNIXTIME(document.expired_at, '%d-%m-%Y')"), 'LIKE', '%' . $searchText . '%')
                  ->orWhere(DB::raw("FROM_UNIXTIME(document.updated_at, '%d-%m-%Y')"), 'LIKE', '%' . $searchText . '%')
                  ->orWhere(DB::raw("FROM_UNIXTIME(document.created_at, '%d-%m-%Y')"), 'LIKE', '%' . $searchText . '%')
                  ->orWhere('company_user.company_name', 'like', '%' . $searchText . '%'); 
            });
        }
    
        if (!empty($postData['documentTypeExp'])) {
            $query->where('document.type', $postData['documentTypeExp']);
        }
    
        if (!empty($postData['statusExp'])) {
            $query->where('document.status', $postData['statusExp']);
        }
    
        if (!empty($postData['startDateExp'])) {
            $dates = explode(" to ", $postData['startDateExp']);
            $startExpDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = isset($dates[1]) ? date('Y-m-d', strtotime($dates[1])) : date('Y-m-d');
            $query->whereBetween('document.expired_at', [$startExpDate, $endDate]);
        }
    
        if (!empty($postData['company_name'])) {
            $query->where('user.company_id', 'like', '%' . $postData['company_name'] . '%');
        }
    
        $result = (new Pagination())->getDataTable($query, $postData);
        // dd($result);
        foreach ($result['data'] as $key => $row) {
            $result['data'][$key]->first_name = $row->first_name . ' ' . $row->user_last_name;
            $result['data'][$key]->company_id = $row->company_name ?? ''; // already selected
            $result['data'][$key]->type = $this->getDocumentName($row->type);
            $result['data'][$key]->expired_at = date('d M, Y', strtotime($row->expired_at));
            $result['data'][$key]->status = $this->getStatusBadge($row->status);
        }
    
        return $result;
    }

    
     public function getAllDocumentsList(array $postData, $authId = ''): array
    {
        
        $currentDate = date('Y-m-d');
        $documentsTypeIds = DB::table('document_type')->pluck('id')->toArray();
    
        $query = DB::table('document')
            ->select(
                'user.id as user_id',
                'user.first_name as user_name',
                'user.last_name as user_last_name',
                'user.company_id',
                'document.id',
                'document.type',
                'document.expired_at',
                'document.status'
            )
            ->leftJoin('user', 'document.user_id', '=', 'user.id')
            ->leftJoin('document_type', 'document_type.id', '=', 'document.type')
            // ->whereNotNull('user.first_name')
            ->whereNotNull('document.type')
            ->whereIn('document.type', $documentsTypeIds)
             // Restrict by company_allowed_documents (supports JSON array and scalar formats)
            ->whereExists(function ($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('company_allowed_documents as cad')
                    ->whereColumn('cad.company_id', 'user.company_id')
                    ->where(function ($allowed) {
                        $allowed->whereRaw("JSON_VALID(cad.document_type_id) AND (JSON_CONTAINS(cad.document_type_id, CONCAT(CHAR(34), CAST(document.type AS CHAR), CHAR(34))) OR JSON_CONTAINS(cad.document_type_id, CAST(document.type AS UNSIGNED)))")
                            ->orWhereRaw("cad.document_type_id = CAST(document.type AS CHAR)");
                    });
            });
    
        // Company scoping if authId is passed
        if (!empty($authId)) {
            $query->where('user.company_id', $authId);
        }
    
        // Search logic
        $searchText = $postData['search']['value'] ?? '';
        if (strlen($searchText) > 2) {
            $query->where(function ($q) use ($searchText) {
                $q->where('document_type.name', 'like', '%' . $searchText . '%')
                  ->orWhere('document.filename', 'like', '%' . $searchText . '%')
                  ->orWhere('document.reject_reason', 'like', '%' . $searchText . '%')
                  ->orWhere('user.first_name', 'like', '%' . $searchText . '%')
                  ->orWhere('user.last_name', 'like', '%' . $searchText . '%')
                  ->orWhere(DB::raw("CONCAT(user.first_name, ' ', user.last_name)"), 'like', '%' . $searchText . '%')
                  ->orWhere(DB::raw("FROM_UNIXTIME(document.expired_at, '%d-%m-%Y')"), 'like', '%' . $searchText . '%')
                  ->orWhere(DB::raw("FROM_UNIXTIME(document.updated_at, '%d-%m-%Y')"), 'like', '%' . $searchText . '%')
                  ->orWhere(DB::raw("FROM_UNIXTIME(document.created_at, '%d-%m-%Y')"), 'like', '%' . $searchText . '%');
    
                // Search by company name via subquery
                $q->orWhereIn('user.company_id', function ($sub) use ($searchText) {
                    $sub->select('id')
                        ->from('user')
                        ->where('company_name', 'like', '%' . $searchText . '%');
                });
            });
        }
    
        // Filter by document type
        if (!empty($postData['documentTypeExp'])) {
            $query->where('document.type', $postData['documentTypeExp']);
        }
    
        // Filter by document status
        if (!empty($postData['statusExp'])) {
            $query->where('document.status', $postData['statusExp']);
        }
    
        // Filter by expiration date range
        if (!empty($postData['startDateExp'])) {
            $dates = explode(" to ", $postData['startDateExp']);
            $startExpDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = isset($dates[1]) ? date('Y-m-d', strtotime($dates[1])) : $startExpDate;
            $query->whereBetween('document.expired_at', [$startExpDate, $endDate]);
        }
    
        // Filter by company
        if (!empty($postData['company_name'])) {
            $query->where('user.company_id', $postData['company_name']);
        }
    
        // Handle dynamic ordering from DataTables
        $columns = [
            'document.id',
            DB::raw("CONCAT(user.first_name, ' ', user.last_name)"),
            'user.company_id',
            'document.type',
            'document.expired_at',
            'document.status'
        ];
    
        if (!empty($postData['order'][0]['column']) && isset($columns[$postData['order'][0]['column']])) {
            $orderCol = $columns[$postData['order'][0]['column']];
            $orderDir = $postData['order'][0]['dir'] ?? 'asc';
            $query->orderBy($orderCol, $orderDir);
        } else {
            // Default ordering by status ASC
            $query->orderBy('document.status', 'asc');
        }

        // Paginate
        $result = (new Pagination())->getDataTable($query, $postData);
    
        // Format the results
        foreach ($result['data'] as $key => $row) {
            $result['data'][$key]->first_name = $row->user_name . ' ' . $row->user_last_name;
            $result['data'][$key]->company_id = (new User())->getCompanyName($row->company_id);
            $result['data'][$key]->type = $this->getDocumentName($row->type);
            $result['data'][$key]->expired_at = $row->expired_at ? date('d M, Y', strtotime($row->expired_at)) : 'Never';
            $result['data'][$key]->status = $this->getStatusBadge($row->status);
        }
    
        return $result; 
    }
    
    
  public function getDocumentListByStatus(array $postData, $authId = ''): array
    {
        $currentDate = date('Y-m-d');
        $documentsTypeIds = DB::table('document_type')->pluck('id')->toArray();

        // Build base query for company and status
        $baseQuery = DB::table('document')
            ->leftJoin('user', 'document.user_id', '=', 'user.id')
            ->leftJoin('document_type', 'document_type.id', '=', 'document.type')
            ->whereNotNull('document.type')
            ->whereIn('document.type', $documentsTypeIds)
            ->where('document_type.is_hidden', 0)
             // Restrict by company_allowed_documents (supports JSON array and scalar formats)
            ->whereExists(function ($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('company_allowed_documents as cad')
                    ->whereColumn('cad.company_id', 'user.company_id')
                    ->where(function ($allowed) {
                        $allowed->whereRaw("JSON_VALID(cad.document_type_id) AND (JSON_CONTAINS(cad.document_type_id, CONCAT(CHAR(34), CAST(document.type AS CHAR), CHAR(34))) OR JSON_CONTAINS(cad.document_type_id, CAST(document.type AS UNSIGNED)))")
                            ->orWhereRaw("cad.document_type_id = CAST(document.type AS CHAR)");
                    });
            });

        if (!empty($authId)) {
            $baseQuery->where('user.company_id', $authId);
        }

        // For recordsTotal: count all matching company/status (before select)
        $recordsTotal = (clone $baseQuery)->count();

        // Clone for filtered query
        $filteredQuery = clone $baseQuery;

        $searchText = $postData['search']['value'] ?? '';
        if (strlen($searchText) > 2) {
            $filteredQuery->where(function ($q) use ($searchText) {
                $q->where('document_type.name', 'like', '%' . $searchText . '%')
                  ->orWhere('document.filename', 'like', '%' . $searchText . '%')
                  ->orWhere('document.reject_reason', 'like', '%' . $searchText . '%')
                  ->orWhere('user.first_name', 'like', '%' . $searchText . '%')
                  ->orWhere('user.last_name', 'like', '%' . $searchText . '%')
                  ->orWhere(DB::raw("CONCAT(user.first_name, ' ', user.last_name)"), 'like', '%' . $searchText . '%')
                  ->orWhere(DB::raw("FROM_UNIXTIME(document.expired_at, '%d-%m-%Y')"), 'like', '%' . $searchText . '%')
                  ->orWhere(DB::raw("FROM_UNIXTIME(document.updated_at, '%d-%m-%Y')"), 'like', '%' . $searchText . '%')
                  ->orWhere(DB::raw("FROM_UNIXTIME(document.created_at, '%d-%m-%Y')"), 'like', '%' . $searchText . '%');
                $q->orWhereIn('user.company_id', function ($sub) use ($searchText) {
                    $sub->select('id')
                        ->from('user')
                        ->where('company_name', 'like', '%' . $searchText . '%');
                });
            });
        }
        $docStatus = $postData['statusExp'] ?? '';

        // Filter by document status
        if (!empty($postData['statusExp'])) {
            $filteredQuery->where('document.status', $postData['statusExp']);
        }

        // For recordsFiltered: count after filters (before select)
        $recordsFiltered = (clone $filteredQuery)->count();

        // Now build paginated query for DataTables
        $dataQuery = clone $filteredQuery;
        $dataQuery->select(
            'user.id as user_id',
            'user.first_name as user_name',
            'user.last_name as user_last_name',
            'user.company_id',
            'document.id',
            'document.type',
            'document.expired_at',
            'document.status'
        );

        // DataTables ordering
        $columns = [
            'document.id',
            DB::raw("CONCAT(user.first_name, ' ', user.last_name)"),
            'user.company_id',
            'document.type',
            'document.expired_at',
            'document.status'
        ];
        if (!empty($postData['order'][0]['column']) && isset($columns[$postData['order'][0]['column']])) {
            $orderCol = $columns[$postData['order'][0]['column']];
            $orderDir = $postData['order'][0]['dir'] ?? 'asc';
            $dataQuery->orderBy($orderCol, $orderDir);
        } else {
            $dataQuery->orderBy('document.id', 'desc');
        }

        // Use Pagination helper for correct offset/limit
        $result = (new Pagination())->getDataTable($dataQuery, $postData);

        // Format the results
        foreach ($result['data'] as $key => $row) {
            $result['data'][$key]->first_name = $row->user_name . ' ' . $row->user_last_name;
            $result['data'][$key]->company_id = (new User())->getCompanyName($row->company_id);
            $result['data'][$key]->status = $this->getStatusBadge($row->status);
            if(!empty($authId)){
                  $result['data'][$key]->action = sprintf(
                    '<a onclick="app.showModalView(\'company/contractors/%d/document/show?documentId=%d&status=%d\')" class="text-body pjax act-btns tool-btn me-2"><i class="bi bi-eye-fill"></i> <span class="tooltip-text">View</span></a>',
                    $row->user_id,
                    $row->id,
                    $docStatus
                );
            }else{
                $result['data'][$key]->action = sprintf(
                    '<a onclick="app.showModalView(\'admin/contractors/%d/document/show?documentId=%d&status=%d\')" class="text-body pjax act-btns tool-btn me-2"><i class="bi bi-eye-fill"></i> <span class="tooltip-text">View</span></a>',
                    $row->user_id,
                    $row->id,
                    $docStatus
                );
            }
        }

        // Overwrite DataTables counts
      //  $result['recordsTotal'] = $recordsTotal;
        $result['recordsFiltered'] = $recordsFiltered;

        return $result;
    }
    
    public function getFilteredDocuments($postData,$contractorId,$status='',$authId='')
    {
        $contractor = User::find($contractorId);
        
        if(!$contractor){
            return ['status'=>0, 'message'=>'contractor not found'];
        }
        
        $currentDate = date('Y-m-d');
        $documentsTypeIds = DB::table('document_type')->pluck('id')->toArray();
    
        $query = DB::table('document')
            ->select(
                'user.id as user_id',
                'user.first_name as user_name',
                'user.last_name as user_last_name',
                'user.company_id',
                'document.id',
                'document.type',
                'document.expired_at',
                'document.status'
            )
            ->leftJoin('user', 'document.user_id', '=', 'user.id')
            ->leftJoin('document_type', 'document_type.id', '=', 'document.type')
            ->whereNotNull('document.type')
            ->whereIn('document.type', $documentsTypeIds)
            ->where('document_type.is_hidden', 0)
             // Restrict by company_allowed_documents (supports JSON array and scalar formats)
            ->whereExists(function ($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('company_allowed_documents as cad')
                    ->whereColumn('cad.company_id', 'user.company_id')
                    ->where(function ($allowed) {
                        $allowed->whereRaw("JSON_VALID(cad.document_type_id) AND (JSON_CONTAINS(cad.document_type_id, CONCAT(CHAR(34), CAST(document.type AS CHAR), CHAR(34))) OR JSON_CONTAINS(cad.document_type_id, CAST(document.type AS UNSIGNED)))")
                            ->orWhereRaw("cad.document_type_id = CAST(document.type AS CHAR)");
                    });
            })
            ->where('document.user_id', $contractorId);
        
        if(!empty($status)){
            $query->where('document.status',$status);    
        }
        
        $searchText = $postData['search']['value'] ?? '';
        if (strlen($searchText) > 2) {
            $query->where(function ($q) use ($searchText) {
                $q->where('document_type.name', 'like', '%' . $searchText . '%')
                  ->orWhere('document.filename', 'like', '%' . $searchText . '%')
                  ->orWhere('document.reject_reason', 'like', '%' . $searchText . '%')
                  ->orWhere(DB::raw("CONCAT(user.first_name, ' ', user.last_name)"), 'like', '%' . $searchText . '%')
                  ->orWhere(DB::raw("FROM_UNIXTIME(document.expired_at, '%d-%m-%Y')"), 'like', '%' . $searchText . '%')
                  ->orWhere(DB::raw("FROM_UNIXTIME(document.updated_at, '%d-%m-%Y')"), 'like', '%' . $searchText . '%')
                  ->orWhere(DB::raw("FROM_UNIXTIME(document.created_at, '%d-%m-%Y')"), 'like', '%' . $searchText . '%');
    
                // Search by company name via subquery
                $q->orWhereIn('user.company_id', function ($sub) use ($searchText) {
                    $sub->select('id')
                        ->from('user')
                        ->where('company_name', 'like', '%' . $searchText . '%');
                });
            });
        }
        
        if (!empty($authId)) {
            $query->where('user.company_id', $authId);
        }
    
        $result = (new Pagination())->getDataTable($query, $postData);
         
       foreach ($result['data'] as $key => $row) {
            // $result['data'][$key]->company_id = (new User())->getCompanyName($row->company_id);
            $result['data'][$key]->document_id = $this->getDocumentName($row->type);
            $result['data'][$key]->expired_at = $row->expired_at ? date('d M, Y', strtotime($row->expired_at)) : 'Never';
            $result['data'][$key]->status = $this->getStatusBadge($row->status);
        }
    
        return $result; 
    }


    public function getDocumentName($type){
        $query = DB::table('document_type')
        ->select('document_type.name')
        ->leftjoin('document','document.type','=','document_type.id')
        ->where('document_type.id',$type)->first();

        return $query ? $query->name : null;
    }

    public function getUploadedRequiredDocument($userId){
        
        $query = DB::table('document')
        ->leftjoin('document_type','document.type', '=', 'document_type.id')
        ->where('document.user_id',$userId)
        ->where('document_type.is_hidden',0)
        ->where('document_type.type',1)->count();
        
        return $query ? $query : null;
    }

    public function getStatusBadge($status)
    {    
        switch ($status) {
            case 0:
                return '<span class="badge bg-secondary"><i class="bi bi-x-circle"></i> Missing</span>';
                break;
            case 1:
                return '<span class="badge bg-light-primary"><i class="bi bi-hourglass-split"></i> Pending</span>';
                break;
            case 2:
                return '<span class="badge bg-warning"><i class="bi bi-hourglass-split"></i> Expiring Soon</span>';
                break;
            case 3:
                return '<span class="badge bg-danger"><i class="bi bi-clock-history"></i> Expired</span>';
                break;
            case 4:
                return '<span class="badge bg-danger"><i class="bi bi-slash-circle"></i> Rejected</span>';
                break;
            case 5:
                return '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Active</span>';
                break;
            default:
                return '<span class="badge bg-danger"><i class="bi bi-slash-circle"></i> In Active</span>';
                break;
        }
        
    }
    
    public function getStatus($status){
        switch ($status) {
            case 0:
                return 'Missing';
                break;
            case 1:
                return 'Pending';
                break;
            case 2:
                return 'Expiring Soon';
                break;
            case 3:
                return 'Expired';
                break;
            case 4:
                return 'Rejected';
                break;
            case 5:
                return 'Active';
                break;
            default:
                return 'In Active';
                break;
        }
    }

    public function getStatusToStatusName($status){
          switch ($status) {
            case 'Active':
                return 5;
                break;
            case 'Expiring Soon':
                return 2;
                break;
            case 'Expired':
                return 3;
                break;
            default:
                return 1;
                break;
        }
    }
    
    public function getApprovedStatusBadge($status)
    {    
        switch ($status) {
            case 0:
                return '<span class="badge bg-light-primary"><i class="bi bi-hourglass-split"></i> Pending </span>';
                break;
            case 1:
                return '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Approved </span>';
                break;
            case 2:
                return '<span class="badge bg-danger"><i class="bi bi-slash-circle"></i> Rejected </span>';
                break;
            default:
                return '<span class="badge bg-danger"><i class="bi bi-slash-circle"></i> In Active</span>';
                break;
        }
        
    }

    /**
     * Generates action links based on user permissions for each row.
     *
     * @param object $row The row data.
     * @param \Illuminate\Contracts\Auth\Authenticatable|null $sessionUser The authenticated user.
     * @return string The generated HTML action links.
     */
    protected function generateActionLinks(object $row, $sessionUser): string
    {
        $actionLinks = '';
        if($sessionUser->type == 2 ){
            // dd($sessionUser);
            // company section
            $companyPlanId = (new User())->getCompanyPlanInfo($sessionUser->id);
           
            $actionLinks .= sprintf(
                '<a onclick="app.showModalView(\'company/contractors/%d/document/view?id=%d\')" class="text-body pjax act-btns tool-btn me-2"><i class="bi bi-eye-fill"></i> <span class="tooltip-text">View</span></a>',
                $row->user_id,
                $row->id
            );
            if($sessionUser->unlimited_conractors == 1){
                if($row->approve_status == 0 || $row->approve_status == 2){
                    $actionLinks .= sprintf(
                    '<a onclick="app.confirmApproveAction(this);" data-action="company/document/%d/change_status/%s" 
                            class="act-btns tool-btn me-2" >
                            <i class="bi bi-check-circle-fill"><span class="tooltip-text">Approve</span></i>
                    </a>',
                        $row->id,'approve'
                    );
                }
                
                if ($row->approve_status == 0 || $row->approve_status == 1 ) {
                
                $actionLinks .= sprintf( 
                        '<a onclick="app.showModalView(\'' . route('company/contractor/document/send-remindermail-new', ['id' => $row->id ,'type'=>'Reject']) . '\')" class="act-btns tool-btn me-2">    <i class="bi bi-x-circle-fill"></i><span class="tooltip-text">Reject</span></a>',
                        $row->id,'reject'
                    );
                }
            }elseif($companyPlanId != 1 && $companyPlanId != null){
                if($row->approve_status == 0 || $row->approve_status == 2){
                    $actionLinks .= sprintf(
                    '<a onclick="app.confirmApproveAction(this);" data-action="company/document/%d/change_status/%s" 
                            class="act-btns tool-btn me-2" >
                            <i class="bi bi-check-circle-fill"><span class="tooltip-text">Approve</span></i>
                    </a>',
                        $row->id,'approve'
                    );
                }
                
                if ($row->approve_status == 0 || $row->approve_status == 1 ) {
                
                $actionLinks .= sprintf( 
                        '<a onclick="app.showModalView(\'' . route('company/contractor/document/send-remindermail-new', ['id' => $row->id ,'type'=>'Reject']) . '\')" class="act-btns tool-btn me-2">    <i class="bi bi-x-circle-fill"></i><span class="tooltip-text">Reject</span></a>',
                        $row->id,'reject'
                    );
                }
            }
        }
        
        if ($sessionUser && $sessionUser->hasPermission('admin/document/view')) {
           
            $actionLinks .= sprintf(
                '<a onclick="app.showModalView(\'admin/contractors/%d/document/view?id=%d\')" class="text-body pjax act-btns tool-btn me-2"><i class="bi bi-eye-fill"></i> <span class="tooltip-text">View</span></a>',
                $row->user_id,
                $row->id
            );

        }
        
        
        
        if ($sessionUser && $sessionUser->hasPermission('admin/document/change_status')) {
            if($row->approve_status == 0 || $row->approve_status == 2){
                $actionLinks .= sprintf(
                '<a onclick="app.confirmApproveAction(this);" data-action="admin/document/%d/change_status/%s" 
                        class="act-btns tool-btn me-2" >
                        <i class="bi bi-check-circle-fill"><span class="tooltip-text">Approve</span></i>
                </a>',
                    $row->id,'approve'
                );
            }
            
            if ($row->approve_status == 0 || $row->approve_status == 1 ) {
            
                $actionLinks .= sprintf( 
                    '<a onclick="app.showModalView(\'' . route('admin/contractor/document/send-remindermail-new', ['id' => $row->id ,'type'=>'Reject']) . '\')" class="act-btns tool-btn me-2">    <i class="bi bi-x-circle-fill"></i><span class="tooltip-text">Reject</span></a>',
                    $row->id,'reject'
                );
            }
        }
  
        return $actionLinks;
    }


    protected function generateActionLinksUser(object $row, $sessionUser): string
    {
        $actionLinks = '';

        // Add update link if user has permission
        if ($sessionUser && $sessionUser->hasPermission('contractor/document/update')) {
            $actionLinks .= sprintf(
                '<a href="contractor/document/update?id=%d" class="text-body pjax" title="Update"><i class="fa fa-edit me-2"></i></i></a>',
                $row->id
            );
        }

    

        return $actionLinks;
    }
    
    //  public function documentType(){
    //         $document = [1 => 'Insurance Certificate',2 => 'W-9 Form', 3 => 'Contractor Agreement', 4 => 'Business License', 5 =>'Portfolio / Work Samples'];
    //     return $document;
    //     }
        
    /**
     * Stores or updates a page record based on provided data.
     *
     * @param array $postData The data for creating or updating a page.
     * @return array The status and message of the operation.
     */
    public function store(array $postData,$userId): array
    {
        $general = new General();
        $id = $postData['id'];
        $rules = [
            'title' => 'required|string|max:255',
            'expired_at' => 'required',
            'status' => 'required',
            'description' => 'required',
            'type' => 'required',

        ];
        if($postData['approved_status'] == 2){
            $rules['rejected_reason'] = 'required|string|max:255';
        }

        if (!$id) {
            $rules['filepond'] = 'required|max:10240';
        } else {
            $rules['filepond'] = 'nullable|max:10240';
        }
        $validator = Validator::make($postData, $rules,[
            'filepond.required'=>'Document file is required',
            'filepond.max'=>'Document file size is not more than 10 MB'
        ]);
        if ($validator->fails()) {
            return [
                'status' => 0,
                'message' => $validator->errors()->first(),
            ];
        }

        if($id){
            $model = self::find($id);
        }else{
            $model = new Document();
        }
        // Handle profile image upload
        if (isset($postData['filepond']) && $postData['filepond']->isValid()) {
            $uploadResult = $general->uploadFile($postData['filepond'], 'document');
            if (!$uploadResult['status']) {
                return $uploadResult;
            }
            $image=$uploadResult['file_name'];
            if ($image) {
                if ($model->filename) {
                    $general->deleteFile($model->image, 'document');
                }
                $model->filename = $image;
            }
        }

        $model->name = $postData['title'];
        $model->user_id = $userId;
        $model->status = $postData['status'];
        $model->approve_status = $postData['approved_status'];
        $model->description = $postData['description'] ?? '';
        $model->reject_reason = isset($postData['rejected_reason']) ? $postData['rejected_reason'] : '';
        $model->expired_at = $postData['expired_at'];
        $model->save();

        $message = $id ? 'Document updated successfully.' : 'Document created successfully.';

        return [
            'status' => 1,
            'message' => $message,
            'next' => 'load',
            'url' => 'admin/contractor/view?id='.$userId,
        ];
        
    }

    public function storeDocument($postData){
        $general = new General();
        $id = $postData['id'];
        $user = auth()->user();
        $userId = $user->id;
        $rules = [
            'title' => 'required|string|max:255',   
            'description' => 'nullable|string|max:255',   
        ];
        if($postData['type'] == '2'){
            $rules['expired_at'] = 'nullable';
        }else{
            $rules['expired_at'] = 'nullable';
        }

        if (!$id) {
            $rules['filepond'] = 'required|max:51200';
        } else {
            $rules['filepond'] = 'nullable|max:51200';
        }
        $validator = Validator::make($postData, $rules,[
            'filepond.required'=>'Document file is required',
            'filepond.max'=>'Document file size is not more than 50 MB'
        ]);
        if ($validator->fails()) {
            return [
                'status' => 0,
                'message' => $validator->errors()->first(),
            ];
        }
        
        $documentFileModel = new DocumentFile();

        if($id){
            $model = Document::find($id);
        }else{
            $model = new Document();
        }
        
        $documentActivityModel = new DocumentActivity();

        if (isset($postData['filepond']) && $postData['filepond']->isValid()) {
            $uploadResult = $general->uploadFile($postData['filepond'], 'document');
            // dd($uploadResult);
            if (!$uploadResult['status']) {
                return $uploadResult;
            }
            $image=$uploadResult['file_name'];
            if ($image) {
                // if ($model->filename) {
                //     $general->deleteFile($model->image, 'document');
                // }
                $documentFileModel->filename = $image;
            }
        }
        $model->type = $postData['type'];
        $model->user_id = $userId;
        if(session('came_from_company') || session('came_from_admin')){
            $model->approve_status = 1;
            $model->status = 5;
        }else{
            $model->approve_status = 0;
            $model->status = 1;
        }
        $model->description = $postData['description'] ?? null;
        // $model->description = $postData['description'] ?? '';
        if($postData['type'] == '2'){
            $model->expired_at = null;
        }else{
        $model->expired_at = $postData['expired_at'] ?? null;
        }
        
        if($model->save()){
            //document_file save
            if(isset($postData['filepond'])){
                $documentFileModel->document_id = $model->id;
                $documentFileModel->save();
            }

            //document filename save
            if(isset($postData['filepond'])){
                $model->filename = $documentFileModel->id;
                $model->save();
            } 

            //document_history save
            $docomentActivityDesc = DocumentType::select('name')->where('id',$model->type)->first();
            $userModel = User::where('id',$model->user_id)->first();
        
            if($id){
                $documentActivityModel->document_id = $id;
                $documentActivityModel->user_id = $model->user_id;
                $documentActivityModel->admin_description = $userModel->first_name.' '. $userModel->last_name .' Updated Their '.$docomentActivityDesc->name;
                $documentActivityModel->description = 'You are Updated  '.$docomentActivityDesc->name;
                (new Notification())->store('Update',$model->id);
            }else{
                $documentActivityModel->document_id = $model->id;
                $documentActivityModel->user_id = $model->user_id;
                $documentActivityModel->admin_description = $userModel->first_name.' '. $userModel->last_name .' Uploaded Their '.$docomentActivityDesc->name;
                $documentActivityModel->description = ' You are Uploaded  '. $docomentActivityDesc->name;
                (new Notification())->store('Upload',$model->id);
            }
            $documentActivityModel->save();

        }
            
        $message = $id ? 'Document Updated Successfully.' : 'Document Upload Successfully.';

        return [
            'status' => 1,
            'message' => $message,
            'next' => 'reload',
        ];
    }
    
    public function checkDocumnetExpiredNew()
    {
        // dd("ok");
        $userObj = new User();
        $now = Carbon::now();
        $today = $now->toDateString();
        $expiringDates = [
            $now->copy()->addDays(7)->toDateString(),
            $now->copy()->addDays(15)->toDateString(),
            $now->copy()->addDays(30)->toDateString(),
        ];

         $allowedDocCondition = function ($subQuery) {
            $subQuery->select(DB::raw(1))
                ->from('company_allowed_documents as cad')
                ->whereColumn('cad.company_id', 'user.company_id')
                ->where(function ($allowed) {
                    $allowed->whereRaw("JSON_VALID(cad.document_type_id) AND (JSON_CONTAINS(cad.document_type_id, CONCAT(CHAR(34), CAST(document.type AS CHAR), CHAR(34))) OR JSON_CONTAINS(cad.document_type_id, CAST(document.type AS UNSIGNED)))")
                        ->orWhereRaw("cad.document_type_id = CAST(document.type AS CHAR)");
                });
        };
        

        // 1) Expiring soon documents (7, 15, 30 days)
        $expiringSoonDocuments = DB::table('document')
            ->leftJoin('user', 'document.user_id', '=', 'user.id')
            ->whereIn('document.status', [1, 2, 5])
            ->whereNotNull('document.expired_at')
            ->whereIn(DB::raw('DATE(document.expired_at)'), $expiringDates)
            ->whereExists($allowedDocCondition)
            ->select('document.id', 'document.user_id', 'document.type', 'document.expired_at')
            ->get();
        // dd($expiringSoonDocuments);
        foreach ($expiringSoonDocuments as $document) {
            $remainingDays = (int) round($now->diffInDays(Carbon::parse($document->expired_at), false));
            if (!in_array($remainingDays, [7, 15, 30], true)) {
                continue;
            }

            DB::table('document')->where('id', $document->id)->update([
                'status' => 2,
                'updated_at' => time(),
            ]);

            $user = User::find($document->user_id);
            if (!$user) {
                continue;
            }

            $notificationDescription = $this->getDocumentName($document->type);

            $notificationModel = new Notification();
            $notificationModel->user_id = $document->user_id;
            $notificationModel->document_status = 3;
            $notificationModel->description = "Your " . $notificationDescription . " expires in " . $remainingDays . " days.";
            $notificationModel->created_at = time();
            $notificationModel->title = 'Expires Soon';
            $notificationModel->save();
            $template = 'document_expire_soon';
            $enableNotification = $userObj->isEnableCompanyEmail($user->company_id);
            $contractorEmailEnabled = $userObj->isContractorEmailEnabled($user->company_id);
            
            if ($enableNotification == 1 && $contractorEmailEnabled == 1) {
                (new General())->sendEmail($user->email, $template, [
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'expiringDescription' => $notificationModel->description,
                    'document_name' => $notificationDescription,
                    'remaining_days' => $remainingDays
                ]);
            }
        }
        
        // 2) Expired documents
        $expiredDocuments = DB::table('document')
            ->leftJoin('user', 'document.user_id', '=', 'user.id')
            ->where('document.status', '!=', 3)
            ->whereNotNull('document.expired_at')
            ->whereDate('document.expired_at', '<=', $today)
            ->whereExists($allowedDocCondition)
            ->select('document.id', 'document.user_id', 'document.type')
            ->get();

        foreach ($expiredDocuments as $document) {
            $user = User::find($document->user_id);
            if (!$user) {
                continue;
            }

            DB::table('document')->where('id', $document->id)->update([
                'status' => 3,
                'updated_at' => time(),
            ]);

            $notificationDescription = $this->getDocumentName($document->type);
            $notificationModel = new Notification();
            $notificationModel->user_id = $document->user_id;
            $notificationModel->document_status = 2;
            $notificationModel->description = 'Your ' . $notificationDescription . ' Was Expired';
            $notificationModel->created_at = time();
            $notificationModel->title = 'Expired';
            $notificationModel->save();
            
            $masterEmailEnabled = ((int) $userObj->isEnableCompanyEmail($user->company_id) === 1);
            $contractorEmailEnabled = $masterEmailEnabled && $userObj->isContractorEmailEnabled($user->company_id);
            $companyEmailEnabled = $masterEmailEnabled && $userObj->isCompanyEmailEnabled($user->company_id);


            if ($contractorEmailEnabled) {
                (new General())->sendEmail($user->email, 'document_expired', [
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'document_name' => $notificationDescription,
                ]);
            }

            if ($companyEmailEnabled) {
                $company = User::find($user->company_id);
                if ($company) {
                     (new General())->sendEmail($company->email, 'contractor_document_expired_mail', [
                        'company_name' => $company->company_name,
                        'name' => $user->first_name . ' ' . $user->last_name,
                        'document_name' => $notificationDescription,
                    ]);
                }
            }
        }

        // 3) Missing documents based on company_allowed_documents mapping
        $contractors = User::where('type', 1)
            ->whereNotNull('company_id')
            ->select('id', 'first_name', 'last_name', 'email', 'company_id')
            ->get();

        foreach ($contractors as $contractor) {
            $allowedDocTypeIds = DB::table('company_allowed_documents')
                ->where('company_id', $contractor->company_id)
                ->pluck('document_type_id')
                ->map(function ($doc) {
                    if (is_string($doc)) {
                        $decoded = json_decode($doc, true);
                        return is_array($decoded) ? $decoded : [$doc];
                    }

                    return is_array($doc) ? $doc : [$doc];
                })
                ->flatten()
                ->map(fn($id) => (int) $id)
                ->filter()
                ->unique()
                ->values();

            if ($allowedDocTypeIds->isEmpty()) {
                continue;
            }

            $allowedVisibleIds = DB::table('document_type')
                ->whereIn('id', $allowedDocTypeIds->toArray())
                ->where('is_hidden', 0)
                ->pluck('id')
                ->map(fn($id) => (int) $id)
                ->values();

            if ($allowedVisibleIds->isEmpty()) {
                continue;
            }

            $uploadedTypeIds = DB::table('document')
                ->where('user_id', $contractor->id)
                ->whereIn('type', $allowedVisibleIds->toArray())
                ->pluck('type')
                ->map(fn($id) => (int) $id)
                ->unique()
                ->values();

            $missingTypeIds = $allowedVisibleIds->diff($uploadedTypeIds)->values();
            if ($missingTypeIds->isEmpty()) {
                continue;
            }

            $missingNames = DB::table('document_type')
                ->whereIn('id', $missingTypeIds->toArray())
                ->pluck('name')
                ->toArray();

            if (empty($missingNames)) {
                continue;
            }

            $missingDocString = implode(', ', array_unique($missingNames));
            
            $company = User::find($contractor->company_id);
            $enableNotification = $userObj->isEnableCompanyEmail($contractor->company_id);
             if ($enableNotification == 1) {
                         (new General())->sendEmail($contractor->email, 'document_missing', [
                            'name' => $contractor->first_name . ' ' . $contractor->last_name,
                            'document_name' => $missingDocString,
                        ]);
                    }
           
        }
         

        return 'ok';
    }
}