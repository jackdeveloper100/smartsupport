<?php

namespace App\Models;

use App\Helpers\General;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Pagination;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use App\Services\PermissionService;
use App\Services\AuthService;
use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Helpers\DocumentHelper;


class User extends Authenticatable
{
    use HasFactory;
    protected $table = 'user';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $dateFormat = 'U';
    protected $fillable = [
        'first_name',
        'last_name',
        'business_name',
        'company_id',
        'company_name',
        'email',
        'email_reminders',
        'is_company_email_enabled',
        'is_contractor_email_enabled',
        'phone',
        'address',
        'password',
        'password_reset_token',
        'unlimited_conractors',
        'remember_token',
        'email_verified',
        'type',
        'role',
        'country',
        'timezone',
        'registered_ip',
        'image',
        'company_logo',
        'status',
         'affiliate_company_name',
         'affiliate_website',
         'affiliate_role_type',
         'affiliate_role_other',
         'affiliate_referral_description',
         'affiliate_reach_volume',
         'affiliate_referral_methods',
         'affiliate_commission_consent',
         'affiliate_terms_accepted',
         'affiliate_motivation',
         'affiliate_notes',
         'affiliate_status',
         'affiliate_code',
         'commission',
         'is_affiliate',
         'is_company',
         'payout_method',
        'payout_details',
        'stripe_customer_id',
        'stripe_account_id',
        'created_at',
        'updated_at',
        'permission',
        'comment'
    ];

    protected $hidden = [
        'password',
        'password_reset_token',
        'remember_token',
        'email_verified',
        'login_otp',
    ];
    
    protected $casts = [
    'payout_details' => 'array',
];

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function latestDocument()
    {
        return $this->hasOne(Document::class)->latestOfMany();
    }

    public function isAdmin()
    {
        return $this->type== 0 ? true : false;
    }
    

    public function isSuperAdmin()
    {
        return $this->type ==0 && $this->role == 1 ? true : false;
    }
    
    public function isCompany(){
        return $this->type == 2 ? true : false;
    }

    /**
     * Get the effective parent Company Owner ID for subscriptions, contractors, and documents.
     * If user is a sub-user (team member, type = 2) with company_id set, returns parent company_id.
     * Otherwise returns user's own id.
     *
     * @return int
     */
    public function getCompanyOwnerId()
    {
        if ($this->type == 2 && !empty($this->company_id)) {
            return (int) $this->company_id;
        }
        return (int) $this->id;
    }

    /**
     * Check if the company has an active plan or unlimited contractors.
     * Returns a redirect response if inactive/unsubscribed, or null if access is granted.
     *
     * @return \Illuminate\Http\RedirectResponse|null
     */
    public function checkCompanyPlanAccess()
    {
        $effectiveCompanyId = $this->getCompanyOwnerId();
        $companyOwner = ($effectiveCompanyId != $this->id) ? User::find($effectiveCompanyId) : $this;

        if (($companyOwner->unlimited_conractors ?? 0) == 1) {
            return null;
        }

        $subscriptionData = \App\Models\Subscription::where('user_id', $effectiveCompanyId)->first();

        if ($subscriptionData && $subscriptionData->status === 'active') {
            return null;
        }

        if ($subscriptionData) {
            return redirect()->route('company/plan')->with('error', 'Your plan has expired. Please upgrade your plan to continue.');
        } else {
            return redirect()->route('company/plan')->with('info', 'Please purchase a plan to activate your account.');
        }
    }

    /**
     * Get contractor count for a company based on plan rules.
     * For Standard Plan (ID 4): counts active contractors (type = 1 and status = 1).
     * For Legacy Plans (IDs 1, 2, 3): counts approved contractors (type = 1 and company_approved_status = 1).
     *
     * @param int $companyId
     * @param int|null $planId
     * @return int
     */
    public static function getContractorCountForPlan($companyId, $planId = null): int
    {
        return (int) self::where('company_id', $companyId)
            ->where('type', 1)
            ->where('company_approved_status', 1)
            ->count();
    }

    /**
     * Check if a company can add or approve an additional contractor.
     * Reusable check to prevent limit bypass on form submission or registration approval.
     *
     * @param int $companyOwnerId
     * @return array
     */
    public static function canAddContractor($companyOwnerId): array
    {
        $companyOwner = self::find($companyOwnerId);
        if (!$companyOwner) {
            return ['allowed' => false, 'reason' => 'Company Not Found', 'plan_id' => null, 'available_contractor' => 0, 'contractor_limit' => 0];
        }

        if (($companyOwner->unlimited_conractors ?? 0) == 1) {
            return ['allowed' => true, 'reason' => null, 'plan_id' => null, 'available_contractor' => 0, 'contractor_limit' => 'Unlimited'];
        }

        $contractorLimit = (new Subscription())->getContractorLimit($companyOwnerId);
        if ($contractorLimit === 'Unlimited') {
            return ['allowed' => true, 'reason' => null, 'plan_id' => null, 'available_contractor' => 0, 'contractor_limit' => 'Unlimited'];
        }

        $subscriptionData = Subscription::where('user_id', $companyOwnerId)->first();
        $planId = $subscriptionData->plan_id ?? null;
        $totalContractor = self::getContractorCountForPlan($companyOwnerId, $planId);
        $availableContractor = ((int)$planId === 4) ? 0 : ($companyOwner->available_contractor ?? 0);

        if ($totalContractor < (int)$contractorLimit) {
            return ['allowed' => true, 'reason' => null, 'plan_id' => $planId, 'available_contractor' => $availableContractor, 'contractor_limit' => $contractorLimit];
        }

        if ($availableContractor > 0) {
            return ['allowed' => true, 'reason' => null, 'plan_id' => $planId, 'available_contractor' => $availableContractor, 'contractor_limit' => $contractorLimit];
        }

        // Check if session has a single-use paid extra slot allowed
        $allowExtra = session()->get('allow_extra_contractor', false);
        $availableSession = session()->get('available_contractor', false);
        if ($allowExtra || $availableSession) {
            return ['allowed' => true, 'reason' => null, 'plan_id' => $planId, 'available_contractor' => $availableContractor, 'contractor_limit' => $contractorLimit];
        }

        return [
            'allowed' => false,
            'reason' => 'You have reached your contractor limit. Please purchase an additional contractor slot to continue.',
            'plan_id' => $planId,
            'available_contractor' => 0,
            'contractor_limit' => $contractorLimit
        ];
    }

    public function hasPermission($permission='')
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        if ((int)$this->type === 2) {
            return (new \App\Services\CompanyPermissionService())->hasPermission($permission, $this->permission);
        }
        return (new PermissionService())->hasPermission($permission, $this->permission); 
    }


    public function defaultData(){
        $data=new \stdClass();
        $data->status_tfa=0;
        $data->ignore_tfa_device='';
        $data->otp='';
        $data->otp_failed=0;
        $data->login_failed=0;
        $data->login_failed_at=false;
        $data->email_verified=false;
        $data->registered_ip=false;
        return $data;
    }

    public function getData(){
        if($this->setting){
            return json_decode($this->data);
        }else{
            return $this->defaultData();            
        }
    }

    public function updateData($newData){
        $data=$this->getData();
        $data = (array)$data;
        foreach($newData as $key=>$value){
            $data[$key]=$value;
        }
        $data = (object)$data;
        $this->update(['data' => json_encode($data)]);
    }

    public function setData($newData){
        $data=$this->getData();
        $data = (array)$data;
        
        foreach($data as $key=>$value){
            $data[$key]=$value;
        }
        $data = (object)$data;
        $this->data=json_encode($data);
        return $this->data;
    }

    public function getStatusBadge($status)
    {
        return $status == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
    }
    
    public function getCompanyApprovedStatusBadge($status){
        if($status == 1){
            return '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Approved </span>';
        }elseif($status == 2){
            return '<span class="badge bg-danger"><i class="bi bi-slash-circle"></i> Rejected</span>';
        }else{
            return '<span class="badge bg-light-primary"><i class="bi bi-hourglass-split"></i> Pending</span>';
        }
        
    }
    
     public function getAffiliateApprovedStatusBadge($status){
        if($status == 1){
            return '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Approved </span>';
        }elseif($status == 2){
            return '<span class="badge bg-danger"><i class="bi bi-slash-circle"></i> Rejected</span>';
        }else{
            return '<span class="badge bg-light-primary"><i class="bi bi-hourglass-split"></i> Pending</span>';
        }
        
    }



    public function listAdmin($postData)
    {
        // Initialize query to fetch users with role 1 (admin)
        $query = DB::table('user')->select('*')->where('type', 0);
        // Apply search filter if provided
        $searchText = isset($postData['search']['value']) ? $postData['search']['value'] : '';
        if (strlen($searchText) > 2) {
            $searchText = '%' . $searchText . '%';
            $query->where(function ($query) use ($searchText) {
                $query->whereRaw("concat(user.first_name,' ' ,user.last_name) like ?", $searchText)
                    ->orWhere("email", 'like', $searchText);
            });
        }
        /**/
        $userObj = new User();
        // Fetch paginated results using Pagination service
        $result = (new Pagination())->getDataTable($query, $postData);
        // Get current authenticated user for permission checks
        $sessionUser = auth()->user();
        // Process each user record
        foreach ($result['data'] as $key => $row) {
            // Get profile image URL if available
            $imageUrl = (new General())->getFileUrl($row->image, 'profile');
            if ($row->image) {
                $result['data'][$key]->image = '<a href="upload/profile/' . $row->image . '" data-toggle="lightbox" data-title="Image" class = "noroute" target = "_blank">
                <img style="width:30px;height:30px" src="' . $imageUrl . '" class="h-auto rounded-circle" alt="blog image"></a>';
                
            }
             // Concatenate first and last names
            $result['data'][$key]->first_name = $row->first_name . ' ' . $row->last_name;
            $result['data'][$key]->permission = $row->permission;
            // Set user status badge
            $result['data'][$key]->status = $userObj->getStatusBadge($row->status);
            // Format the creation date based on the application's date format setting
            $result['data'][$key]->created_at = date(config('setting.date_format'), $row->created_at);
            // Assign action buttons based on the role and permissions
            if (auth()->user()->role === 0) {
                $result['data'][$key]->action = '<div class="act-btns">
            <a href="admin/user/view?id=' . $row->id . '" class="text-body pjax tool-btn me-2" ><i class="fa fa-eye"><span class="tooltip-text">View</span></i></a>&nbsp;
            <a href="admin/user/update?id=' . $row->id . '" class="text-body pjax tool-btn me-2" ><i class="bi bi-pencil-square"><span class="tooltip-text">Update</span></i></a>
            <button style=" border:none; background:none;" onclick="app.confirmAction(this);" data-action="admin/user/delete" data-id="' . $row->id . '" class="text-body tool-btn me-2" ><i class="bi bi-trash-fill"><span class="tooltip-text">Delete</span></i></button></div>';
            } else {
                $result['data'][$key]->action = '';
                if ($sessionUser->hasPermission('admin/user/view')) {
                    $result['data'][$key]->action .= '
                    <a href="admin/user/view?id=' . $row->id . '" class="text-body  pjax act-btns tool-btn me-2" ><i class="bi bi-eye-fill"><span class="tooltip-text">View</span></i></a>&nbsp;';
                }
                if ($sessionUser->hasPermission('admin/user/update')) {
                    $result['data'][$key]->action .= '
                    <a href="admin/user/update?id=' . $row->id . '" class="text-body pjax act-btns tool-btn me-2" ><i class="bi bi-pencil-square"><span class="tooltip-text">Update</span></i></a>';
                }
                if ($sessionUser->hasPermission('admin/user/delete')) {
                    $result['data'][$key]->action .= '
                    <button style=" border:none; background:none;" onclick="app.confirmAction(this);" data-action="admin/user/delete" data-id="' . $row->id . '" class="text-body act-btns tool-btn me-2" ><i class="bi bi-trash-fill"><span class="tooltip-text">Delete</span></i></button>';
                }
            }
        }
        return $result;
    }
    
    public function list($postData, $authId = '')
    {
        
        $query = DB::table('user')
            ->select('user.*', 'latest_doc.updated_at as document_updated_at')
            ->where('user.type', 1)
            ->leftJoin(
                DB::raw('(SELECT user_id, MAX(updated_at) as updated_at FROM document GROUP BY user_id) as latest_doc'),
                'latest_doc.user_id', '=', 'user.id'
            );
    
        if (!empty($authId)) {
            $query->where('user.company_id', $authId);
        }
    
        // Search filter
        $searchText = $postData['search']['value'] ?? '';
        if (strlen($searchText) > 2) {
            $likeSearch = '%' . $searchText . '%';
            $query->where(function ($query) use ($likeSearch) {
                $query->whereRaw("concat(user.first_name, ' ', user.last_name) like ?", [$likeSearch])
                    ->orWhere("user.email", 'like', $likeSearch)
                    ->orWhere("user.business_name", 'like', $likeSearch)
                    ->orWhere(DB::raw("FROM_UNIXTIME(user.created_at, '%d-%m-%Y')"), 'LIKE', $likeSearch)
                    ->orWhere(function ($query) use ($likeSearch) {
                        if (stripos($likeSearch, '%Act%') !== false) {
                            $query->where('user.status', '=', 1);
                        } elseif (stripos($likeSearch, '%Inac%') !== false) {
                            $query->where('user.status', '=', 0);
                        }
                    });
            });
        }
    
        // Status filter
        if (!empty($postData['status']) || $postData['status'] === '0') {
            $query->where('user.status', $postData['status']);
        }
    
        // Date filter
        if (isset($postData['startingDate'])) {
            $dates = explode(" to ", $postData['startingDate']);
            $startingDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = isset($dates[1]) ? date('Y-m-d', strtotime($dates[1])) : date('Y-m-d');
            $query->where(function ($q) use ($startingDate, $endDate) {
                $q->whereBetween('latest_doc.updated_at', [strtotime($startingDate), strtotime($endDate)])
                  ->orWhereBetween('user.updated_at', [strtotime($startingDate), strtotime($endDate)]);
            });
        }
    
        // Company name filter
        if (!empty($postData['company_name'])) {
            $query->where('user.company_id', $postData['company_name']);
        }
    
        // Column mapping for sorting
       $columns = [
            0 => null,                  // checkbox column (not sortable)
            1 => 'user.id',
            2 => 'user.first_name',
            3 => 'user.business_name',
            4 => 'user.company_id',
            5 => 'user.status',
            6 => 'user.company_approved_status'
        ];

    
        $orderColumnIndex = $postData['order'][0]['column'] ?? 1;
        $orderDirection = $postData['order'][0]['dir'] ?? 'asc';
    
        $orderColumn = $columns[$orderColumnIndex] ?? 'user.id';
        if ($orderColumn === null) {
            $orderColumn = 'user.id'; // fallback if checkbox or invalid column
        }
        $query->orderBy($orderColumn, $orderDirection);
    
        // Total filtered before pagination
        $totalFiltered = $query->count();
    
        // Pagination
        $start = $postData['start'] ?? 0;
        $length = $postData['length'] ?? 10;
    
        $dataRows = $query->offset($start)->limit($length)->get();
    
        // Process data
        $data = [];
        $userObj = new User();
        $sessionUser = auth()->user();
    
        foreach ($dataRows as $key => $row) {
            // Serial number
                $serialId = $orderDirection === 'desc'
                ? $totalFiltered - $start - $key
                : $start + $key + 1;

    
            // Image
            $imageUrl = (new General())->getFileUrl($row->image, 'profile');
            $imageHtml = $row->image
                ? '<a href="upload/profile/' . $row->image . '" data-toggle="lightbox" data-title="Image" class="noroute" target="_blank">
                    <img style="width:30px;height:30px" src="' . $imageUrl . '" class="h-auto rounded-circle" alt="blog image"></a>'
                : '';
    
            // Action buttons
            $actions = '';
            if ($sessionUser->role == 1) {
                $actions .= '<div class="act-btns">
                    <a href="admin/contractor/view?id=' . $row->id . '" class="text-body pjax tool-btn me-2"><i class="bi bi-eye-fill"><span class="tooltip-text">View</span></i></a>
                    <a href="admin/contractor/update?id=' . $row->id . '" class="text-body pjax tool-btn me-2"><i class="bi bi-pencil-square"></i><span class="tooltip-text">Update</span></i></a>
                    <button style="border:none; background:none;" onclick="app.confirmAction(this);" data-action="admin/contractor/delete" data-id="' . $row->id . '" class="text-body tool-btn me-2 ps-0"><i class="bi bi-trash-fill"><span class="tooltip-text">Delete</span></i></button>
                </div>';
            } elseif ($sessionUser->type == 2) {
                $actions .= '<div class="act-btns">';
                if ($sessionUser->hasPermission('company/contractor/view')) {
                    $actions .= '<a href="company/contractor/view?id=' . $row->id . '" class="text-body pjax tool-btn me-2"><i class="bi bi-eye-fill"><span class="tooltip-text">View</span></i></a>';
                }
                if ($sessionUser->hasPermission('company/contractor/update')) {
                    $actions .= '<a href="company/contractor/update?id=' . $row->id . '" class="text-body pjax tool-btn me-2"><i class="bi bi-pencil-square"></i><span class="tooltip-text">Edit</span></i></a>';
                }
                if ($sessionUser->hasPermission('company/contractor/delete')) {
                    $actions .= '<button style="border:none; background:none;" onclick="app.confirmAction(this);" data-action="company/contractor/delete" data-id="' . $row->id . '" class="text-body tool-btn me-2 ps-0"><i class="bi bi-trash-fill"><span class="tooltip-text">Delete</span></i></button>';
                }

                if ($sessionUser->hasPermission('company/contractor/update')) {
                    if ($row->company_approved_status == 0) {
                        $actions .= '<a onclick="app.confirmApproveAction(this);" data-action="company/contractor/register-approve" data-id="' . $row->id . '" class="tool-btn me-2"><i class="bi bi-check-circle-fill"><span class="tooltip-text">Approve</span></i></a>
                            <a onclick="app.confirmAction(this);" data-action="company/contractor/register-reject" data-id="' . $row->id . '" class="tool-btn me-2"><i class="bi bi-x-circle-fill"><span class="tooltip-text">Reject</span></i></a>';
                    } elseif ($row->company_approved_status == 1) {
                        $actions .= '<a onclick="app.confirmRejectAction(this);" data-action="company/contractor/register-reject" data-id="' . $row->id . '" class="tool-btn me-2"><i class="bi bi-x-circle-fill"><span class="tooltip-text">Reject</span></i></a>';
                    } elseif ($row->company_approved_status == 2) {
                        $actions .= '<a onclick="app.confirmApproveAction(this);" data-action="company/contractor/register-approve" data-id="' . $row->id . '" class="tool-btn me-2"><i class="bi bi-check-circle-fill"><span class="tooltip-text">Approve</span></i></a>';
                    }
                }

                $actions .= '</div>';
            }
            // dd($row);
            // Final row
             $data[] = [
                'user_id' => $row->id, // <-- actual DB ID
                'id' => $serialId,      // <-- just a display serial number
                'first_name' => $row->first_name . ' ' . $row->last_name,
                'business_name' => $row->business_name,
                'email' => $row->email,
                'document_count' => $this->getTotalDocument($row->id),
                'latest_document_updated' => $this->lastActivity($row->id),
                'compliance_status' => $userObj->getComplianceStatus($row->id),
                'company_id' => $this->getCompanyName($row->company_id),
                'status' => $userObj->getStatusBadge($row->status),
                'company_approved_status' => $userObj->getCompanyApprovedStatusBadge($row->company_approved_status),
                'created_at' => date('d M, Y', $row->created_at),
                'updated_at' => date('d M, Y', $row->updated_at),
                'action' => $actions
            ];
        }
    
        // Return for DataTables
        $result = [
            'draw' => intval($postData['draw']),
            'recordsTotal' => $totalFiltered,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ];
        
        return $result;
    }


   public function reportList($postData, $authId = '') {
    $status = $postData['status'] ?? null;
    $documentStatus = $postData['docStatus'] ?? null;

    $query = DB::table('user')
        ->select(
            'user.*',
            'user.contractor_internal_note',
            'company_user.company_name as company_name_from_id',
            'document.type',
            'document.status as docstatus',
            'document.expired_at'
        )
        ->where('user.type', 1)
        ->leftJoin('user as company_user', 'user.company_id', '=', 'company_user.id')
        ->leftJoin('document', 'user.id', '=', 'document.user_id')
        ->leftJoin('document_type as dt', 'document.type', '=', 'dt.id')
        ->where('dt.is_hidden', 0)
        // Restrict by company_allowed_documents (supports JSON array and scalar formats)
        ->whereExists(function ($subQuery) {
            $subQuery->select(DB::raw(1))
                ->from('company_allowed_documents as cad')
                ->whereColumn('cad.company_id', 'user.company_id')
                ->where(function ($allowed) {
                    $allowed->whereRaw("(JSON_VALID(cad.document_type_id) AND (JSON_CONTAINS(cad.document_type_id, CAST(document.type AS CHAR)) OR JSON_CONTAINS(cad.document_type_id, JSON_QUOTE(CAST(document.type AS CHAR))))) OR FIND_IN_SET(CAST(document.type AS CHAR), REPLACE(REPLACE(REPLACE(REPLACE(cad.document_type_id, '[', ''), ']', ''), '\"', ''), ' ', '')) > 0 OR cad.document_type_id = CAST(document.type AS CHAR)");
                });
        });

    // Filter by company_id if provided
    if (!empty($authId)) {
        $query->where('user.company_id', $authId);
    }

    // Filter by user status
    if ($status !== null) {
        $query->where('user.status', $status);
    }

    // Filter by document status
    if ($documentStatus !== null) {
        $query->where('document.status', $documentStatus);
    }

    // Filter by company_name (via self-join)
    if (!empty($postData['company_name'])) {
        $query->where('user.company_id', $postData['company_name']);
    }

    // Global search text filtering
    $searchText = $postData['search']['value'] ?? '';
    if (strlen($searchText) > 2) {
        $searchTextLike = '%' . $searchText . '%';

        $query->where(function ($query) use ($searchText, $searchTextLike) {
            $query->whereRaw("concat(user.first_name, ' ', user.last_name) like ?", [$searchTextLike])
                ->orWhere("user.email", 'like', $searchTextLike)
                ->orWhere("user.business_name", 'like', $searchTextLike)
                ->orWhere("company_user.company_name", 'like', $searchTextLike) // <-- important
                ->orWhere(DB::raw("FROM_UNIXTIME(user.created_at, '%d-%m-%Y')"), 'LIKE', $searchTextLike)
                ->orWhere(function ($query) use ($searchText) {
                    if (stripos($searchText, 'Act') !== false) {
                        $query->where('user.status', '=', 1);
                    } elseif (stripos($searchText, 'Inac') !== false) {
                        $query->where('user.status', '=', 0);
                    }
                });
        });
    }

    $userObj = new User();
    $result = (new Pagination())->getDataTable($query, $postData);
    
    foreach ($result['data'] as $key => $row) {
        $imageUrl = (new General())->getFileUrl($row->image, 'profile');
        if ($row->image) {
            $result['data'][$key]->image = '<a href="upload/profile/' . $row->image . '" data-toggle="lightbox" data-title="Image" class="noroute" target="_blank">
                <img style="width:30px;height:30px" src="' . $imageUrl . '" class="h-auto rounded-circle" alt="profile image"></a>';
        }

        $result['data'][$key]->document_count = $this->getTotalDocument($row->id);
        $result['data'][$key]->latest_document_updated = $this->lastActivity($row->id);

        $result['data'][$key]->first_name = $row->first_name . ' ' . $row->last_name;
        $result['data'][$key]->compliance_status = $userObj->getComplianceStatus($row->id);

        // Display the joined company name
        $result['data'][$key]->company_id = $row->company_name_from_id ?? '';

        $result['data'][$key]->document_type = (new Document())->getDocumentName($row->type);
        $result['data'][$key]->document_status = (new Document())->getStatusBadge($row->docstatus);
        $result['data'][$key]->document_expired_at = $row->expired_at;
        $result['data'][$key]->contractor_internal_note = $row->contractor_internal_note ?? 'No note';


        $result['data'][$key]->status = $userObj->getStatusBadge($row->status);
        $result['data'][$key]->created_at = date('d M, Y', $row->created_at);
        $result['data'][$key]->updated_at = date('d M, Y', $row->updated_at);
    }

    return $result;
}


   public function companyList($postData) {
       
    // Build your base query
    $query = DB::table('user')
        ->select(
            'user.*',
            'subscription.user_id as sub_user_id',
            'subscription.plan_id',
            'subscription.expired_at',
            'subscription.status as sub_status',
            'plan.title',
            'plan.amount'
        )
        ->where('user.type', 2)
        ->where(function($q) {
            $q->whereNull('user.company_id')
              ->orWhere('user.company_id', 0);
        })
        ->leftJoin('subscription', 'user.id', '=', 'subscription.user_id')
        ->leftJoin('plan', 'plan.id', '=', 'subscription.plan_id');

    $searchText = $postData['search']['value'] ?? '';

    // Apply search filter if needed
    if (strlen($searchText) > 2) {
        $searchTextLike = '%' . $searchText . '%';
        $query->where(function ($q) use ($searchText, $searchTextLike) {
            $q->where('user.company_name', 'like', $searchTextLike)
              ->orWhere('user.company_id', 'like', $searchTextLike)
              ->orWhere('user.email', 'like', $searchTextLike)
              ->orWhere('plan.title', 'like', $searchTextLike);

            if (stripos('trial', strtolower($searchText)) === 0) {
                $q->orWhereNull('subscription.plan_id')
                  ->orWhere('subscription.plan_id', '=', 0);
            }
        });
    }

    // Pagination and sorting info from DataTables
    $start = $postData['start'] ?? 0;  // offset, e.g. 0 for first page, 10 for second page
    $length = $postData['length'] ?? 10; // number of records per page
    $orderColumnIndex = $postData['order'][0]['column'] ?? 0;
    $orderDirection = $postData['order'][0]['dir'] ?? 'asc';

    // Map column indexes to DB columns for ordering
    $columns = [
        0 => 'user.id', // assuming first column is id
        1 => 'user.company_name',
        2 => 'user.permission',
        3 => 'plan.title',
        4 => 'plan.amount',
        5 => 'subscription.expired_at',
        6 => 'user.status',
        7 => 'user.created_at',
        // extend this array as needed
    ];

    // Apply ordering to the query
    if (isset($columns[$orderColumnIndex])) {
        $query->orderBy($columns[$orderColumnIndex], $orderDirection);
    }

    // Get total filtered records count before applying limit for pagination
    $totalFiltered = $query->count();

    // Apply pagination limit and offset
    $dataRows = $query->offset($start)->limit($length)->get();

    $sessionUser = auth()->user();
    $userObj = new User();

    // Prepare data array for the datatable
    $data = [];
    foreach ($dataRows as $key => $row) {
        if ($columns[$orderColumnIndex] === 'user.id') {
            if ($orderDirection === 'asc') {
                $serialId = $start + $key + 1;
            } else {
                $serialId = $totalFiltered - ($start + $key);
            }
        } else {
            $serialId = $start + $key + 1;
        }

        $imageUrl = (new General())->getFileUrl($row->image, 'profile');
        $imageHtml = $row->image
            ? '<a href="upload/profile/' . $row->image . '" data-toggle="lightbox" data-title="Image" class="noroute" target="_blank">
                <img style="width:30px;height:30px" src="' . $imageUrl . '" class="h-auto rounded-circle" alt="blog image"></a>'
            : '';

        // Process subscription plan info
        if ($row->unlimited_conractors == 1) {
            $planTitle = 'Free Plan';
            $planAmount = 'Free';
            $expiredAt = 'Never Expired';
            $planBadge = (new Subscription())->getStatusBadge('free');
        } elseif (!empty($row->sub_user_id)) {
            $planTitle = $row->title ?? 'Trial';
            $planAmount = $row->amount ? '$' . $row->amount : 'Trial';
            $expiredAt = !empty($row->expired_at) ? date(config('setting.date_format'), strtotime($row->expired_at)) : 'N/A';
            $planBadge = (new Subscription())->getStatusBadge($row->sub_status);
        } else {
            $planTitle = 'No Plan';
            $planAmount = 'N/A';
            $expiredAt = 'N/A';
            $planBadge = '<span class="badge bg-secondary">Inactive</span>';
        }
        $statusBadge = $userObj->getStatusBadge($row->status);
        $createdAtFormatted = date(config('setting.date_format'), $row->created_at);

        if ($sessionUser->role === 0) {
            $actions = '<div class="act-btns">
                <a href="admin/company/view?id=' . $row->id . '" class="text-body pjax tool-btn me-2"><i class="fa fa-eye"><span class="tooltip-text">View</span></i></a>&nbsp;
                <a href="admin/company/update?id=' . $row->id . '" class="text-body pjax tool-btn me-2"><i class="bi bi-pencil-square"><span class="tooltip-text">Update</span></i></a>
                <button style="border:none; background:none;" onclick="app.confirmAction(this);" data-action="admin/company/delete" data-id="' . $row->id . '" class="text-body tool-btn me-2"><i class="bi bi-trash-fill"><span class="tooltip-text">Delete</span></i></button></div>';
        } else {
            $actions = '';
            if ($sessionUser->hasPermission('admin/company/view')) {
                $actions .= '<a href="admin/company/view?id=' . $row->id . '" class="text-body pjax act-btns tool-btn me-2"><i class="bi bi-eye-fill"><span class="tooltip-text">View</span></i></a>&nbsp;';
            }
            if ($sessionUser->hasPermission('admin/company/update')) {
                $actions .= '<a href="admin/company/update?id=' . $row->id . '" class="text-body pjax act-btns tool-btn me-2"><i class="bi bi-pencil-square"><span class="tooltip-text">Update</span></i></a>';
            }
            if ($sessionUser->hasPermission('admin/company/delete')) {
                $actions .= '<button style="border:none; background:none;" onclick="app.confirmAction(this);" data-action="admin/company/delete" data-id="' . $row->id . '" class="text-body act-btns tool-btn me-2"><i class="bi bi-trash-fill"><span class="tooltip-text">Delete</span></i></button>';
            }
        }

        $data[] = [
            'id' => $serialId,
            'image' => $imageHtml,
            'company_name' => $row->company_name,
            'permission' => $row->permission,
            'title' => $planTitle,
            'amount' => $planAmount,
            'expired_at' => $expiredAt,
            'plan_id' => $planBadge,
            'status' => $statusBadge,
            'created_at' => $createdAtFormatted,
            'action' => $actions,
        ];
    }

    // Return your result as expected by DataTables
    $result = [
        "draw" => intval($postData['draw']),
        "recordsTotal" => DB::table('user')->where('user.type', 2)->where(function($q) { $q->whereNull('user.company_id')->orWhere('user.company_id', 0); })->count(), // total unfiltered count
        "recordsFiltered" => $totalFiltered,
        "data" => $data,
    ];
    
    return $result;
}
    
    
    public function getTotalDocument($id){
       $contractor = User::select('id', 'company_id')->where('id', $id)->first();
        if (!$contractor || empty($contractor->company_id)) {
            return 0;
        }

        $allowedDocTypeIds = DocumentHelper::getAllowedVisibleDocTypeIds((int) $contractor->company_id);
        if ($allowedDocTypeIds->isEmpty()) {
            return 0;
        }

         return Document::where('user_id', $id)
            ->whereIn('type', $allowedDocTypeIds->toArray())
            ->distinct('type')
            ->count('type');
    }

    public function lastActivity($id){
        $result = Document::select('updated_at')->where('user_id', $id)->first();
    
        if ($result && $result->updated_at) {
            return date('d M, Y', strtotime($result->updated_at));  
        }else{
            $result = User::where('id',$id)->first();
            return date('d M, Y',strtotime($result->updated_at));
        }
    }
    

    public function dashboardList($postData)
    {
        $currentDate = Carbon::now()->toDateString();

        $query = DB::table('user')->leftJoin('document', 'user.id', '=', 'document.user_id')
        ->leftJoinSub(
            DB::table('document')
            ->select('id', 'user_id', 'type', 'filename','expired_at')
            ->whereRaw("DATE(expired_at) >= ?", [$currentDate]) 
            ->orderBy('expired_at', 'ASC') 
            ->limit(1),
        'next_expiring_document',
        'next_expiring_document.user_id',
        '=',
        'user.id'
        )
        ->select(
            'user.*',
            DB::raw('COUNT(document.id) AS total_documents'),
            'next_expiring_document.expired_at AS next_expired_at',

        )
        ->where('user.type', 1);  
            // dd(date('Y-m-d',strtotime($postData['startDate'])));
         
        /**/
        $searchText = isset($postData['search']['value']) ? $postData['search']['value'] : '';
        if (strlen($searchText) > 2) {
            $searchText = '%' . $searchText . '%';
            $query->where(function ($query) use ($searchText) {
                $query->whereRaw("concat(user.first_name,' ' ,user.last_name) like ?", $searchText)->orWhere("user.email", 'like', $searchText)->orWhere(DB::raw("FROM_UNIXTIME(user.created_at, '%d-%m-%Y')"), 'LIKE', '%' . $searchText . '%')->orWhere(function ($query) use ($searchText) {
                    if (stripos($searchText, '%Act%') !== false) {
                        $query->where('user.status', '=', 1);
                    } elseif (stripos($searchText, '%Inac%') !== false) {
                        $query->where('user.status', '=', 0);
                    }
                });
            });
            
        }
        

           if (isset($postData['status']) && $postData['status'] !== '') {
            $query->where('user.status', $postData['status']);
        }
        
           if(isset($postData['startDate'])){
                $dates = explode(" to ", $postData['startDate']);
                $startDate = date('Y-m-d', strtotime($dates[0]));
                if (count($dates) === 2) {
                    $endDate = date('Y-m-d', strtotime($dates[1]));
            // Parse and format both start and end dates
                }
            }
        
        $endDate = isset($endDate) ? $endDate : date('Y-m-d');
        // dd($startDate,$endDate);
        if(isset($startDate) && $startDate !== ''){
            if(isset($endDate) && $endDate !== ''){
                $query->whereBetween('document.expired_at',[$startDate,$endDate]);
            }else{
                $query->whereBetween('document.expired_at',[$startDate,$endDate]);
            }
        }
    
        
        $query->groupBy('user.id', 'next_expiring_document.id', 'next_expiring_document.type', 'next_expiring_document.filename');

        /**/
        $userObj = new User();
        $result = (new Pagination())->getDataTable($query, $postData);
       // dd($result);
        $sessionUser = auth()->user();
        foreach ($result['data'] as $key => $row) {
  
            $result['data'][$key]->document_count = $row->total_documents;
            $result['data'][$key]->next_expired_at = $row->next_expired_at ? date('d M, Y',strtotime($row->next_expired_at)):'N/A';

            $result['data'][$key]->first_name = $row->first_name . ' ' . $row->last_name;
            $result['data'][$key]->status = $userObj->getStatusBadge($row->status);
            $result['data'][$key]->action = '<div class="act-btns"><a href="admin/contractor/view?id=' . $row->id . '" class="text-body pjax" title="View"><i class="bi bi-eye-fill"></i></a></div>';

        }

        return $result;
    }


   
    public function storeAdmin(array $postData): array
    {
 
        $general = new General();
        $id = $postData['id'] ?? null;
        $existingPassword = $postData['pass'] ?? null;
        // Define validation rules
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:user,email,' . $id,
            'status' => 'required|boolean',
            'permission' => 'required|array',
            'type' => 'required|string|max:255',
        ];

        if (!$id) {
            $rules['image'] = 'required|image|mimes:jpeg,png,jpg,gif|max:2048';
            $rules['password'] = ['required', (new General())->passwordType()];
        } else {
            $rules['image'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048';
            $rules['password'] = ['nullable', (new General())->passwordType()];

        }

        // Validate the data
        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) {
            return [
                'status' => 0,
                'message' => $validator->errors()->first()
            ];
        }

        // Find or create a new user instance
        $model = $id ? User::find($id) : new User();

        // Handle profile image upload
        if (isset($postData['image']) && $postData['image']->isValid()) {
            $uploadResult = $general->uploadFile($postData['image'], 'profile');
            if (!$uploadResult['status']) {
                return $uploadResult;
            }
            $image=$uploadResult['file_name'];
            if ($image) {
                if ($model->image) {
                    $general->deleteFile($model->image, 'profile');
                }
                $model->image = $image;
            }
        }

        // Set model attributes
        $model->first_name = $postData['first_name'];
        $model->last_name = $postData['last_name'];
        $model->email = $postData['email'];
        $model->status = (bool) $postData['status'];
        $model->permission = implode(',', $postData['permission']);
        $model->type = $postData['type'];
        $model->registered_ip = $general->getClientIp();
        // Encrypt and set password
        $service = new AuthService();
        $model->password = !empty($postData['password'])
            ? $service->encryptPassword($postData['password'])
            : $existingPassword;
        // Save model
        $model->save();
        
        if(!$id){
              (new General())->sendEmail($postData['email'], 'user_invite', [
                'url' => url('admin/login'),
                'name' => $postData['first_name'].' '.$postData['last_name'],
                'email' => $postData['email'],
                'password' => $postData['password'],
            ]);
        }
        
        // Set response message
        $message = $id ? 'User Updated Successfully.' : 'User Created Successfully.';
        return [
            'status' => 1,
            'message' => $message,
            'next' => 'load',
            'url' => 'admin/users'
        ];
    }
    /**
     * Store or update a user in the database.
     *
     * This method handles both creating a new user and updating an existing user. It validates the input data,
     * handles image uploads, and encrypts the password if provided. The user’s IP address and country information
     * are also recorded. After saving the user, a response message is returned.
     *
     * @param array $postData The input data for the user (can be for new user creation or update).
     * @return array The response array containing status, message, and other related data.
     */
    public function store(array $postData): array
    {
        $sessionUser = auth()->user();
        $general = new General();
        $id = $postData['id'] ?? null;
        $pass = $postData['pass'] ?? null;
        // Define validation rules
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            // 'business_name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'email' => 'required|email:rfc,dns,spoof|max:255',
            'status' => 'required|boolean',
        ];
        
        if (!$id) {
            $rules['email'] .= '|unique:user';
            $rules['password'] = ['required', (new General())->passwordType()];
            $rules['image'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048';
        } else {
            $rules['email'] = 'required|email:rfc,dns,spoof|unique:user,email,' . $id;
            $rules['image'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048';
            $rules['password'] = ['nullable', (new General())->passwordType()];
        }
        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) {
            return [
                'status' => 0,
                'message' => $validator->errors()->first(),
            ];
        }
    
        $isAddOn = 0;
        $endDate = null;
        if(!$id){
            if($sessionUser->type == 2) {
                $companyOwnerId = $sessionUser->getCompanyOwnerId();
                $companyOwner = User::find($companyOwnerId) ?: $sessionUser;

                // Re-verify limit at submission time to prevent multi-tab & race condition bypasses
                $checkLimit = self::canAddContractor($companyOwnerId);
                if (!$checkLimit['allowed']) {
                    $priceLabel = ($checkLimit['plan_id'] == 4) ? 'Pay $2' : 'Pay $15';
                    $payNow = '<a href="' . route('company/contractors/add-extra') . '" >' . $priceLabel . '</a>';
                    return [
                        'status' => 0,
                        'message' => 'You have reached your contractor limit. To create an additional contractor, please ' . $payNow,
                    ];
                }

                $allowExtra = session()->get('allow_extra_contractor', false);
                // dd($allowExtra);
                $availableContractor = session()->get('available_contractor',false);
                $endDate = session()->get('end_date',false);
                if (empty($endDate)) {
                    $endDate = Carbon::now()->addDays(30)->format('Y-m-d H:i:s');
                }

                if(isset($allowExtra) && $allowExtra !== null && $allowExtra != ''){
                    $isAddOn = 1;
                    if ($companyOwner && $companyOwner->available_contractor > 0) {
                        $companyOwner->available_contractor -= 1;
                        $companyOwner->save();
                    }
                    session()->forget('allow_extra_contractor');
                    session()->forget('end_date');
                }
                
                if(isset($availableContractor) && $availableContractor !== null && $availableContractor != ''){
                    $isAddOn = 1;
                    if ($companyOwner && $companyOwner->available_contractor > 0) {
                        $companyOwner->available_contractor -= 1;
                        $companyOwner->save();
                    }
                    session()->forget('available_contractor');
                }
            }
            
        }
        
        $model = $id ? User::find($id) : new User();

        // Handle image upload if provided
        if (isset($postData['image']) && $postData['image']->isValid()) {
            $uploadResult = $general->uploadFile($postData['image'], 'profile');
            if (!$uploadResult['status']) {
                return $uploadResult;
            }
            $image=$uploadResult['file_name'];
            if ($image) {
                if ($model->image) {
                    $general->deleteFile($model->image, 'profile');
                }
                $model->image = $image;
            }
        }
        
        $model->first_name = $postData['first_name'];
        $model->last_name = $postData['last_name'] ?? null;
        $model->business_name = $postData['business_name'] ?? null;
        $model->company_id = $postData['company_name'];
        if ($id && isset($model->company_approved_status)) {
            $model->company_approved_status = $model->company_approved_status;
        } else {
            $model->company_approved_status = $postData['company_approved_status'] ?? 1;
        }
        $model->email = $postData['email'];
        $model->country = $postData['country'] ?? null;
        $model->status = $postData['status'];
        $model->comment = $postData['comment'];
        $model->role = $postData['role'] ?? null;
        $model->type = $postData['type'] ?? null;
        $model->add_on_expired_at = $endDate ?? null;
        $model->is_add_on = $isAddOn ?? 0;
        $model->registered_ip = $general->getClientIp();
        $model->email_verified = 1;
        // Encrypt password if provided
        if (!empty($postData['password'])) {
            $model->password = (new AuthService())->encryptPassword($postData['password']);
        } else {
            $model->password = $pass;
        }
        
        // Save the model
        $model->save();
        
        if(!$id){
          (new General())->sendEmail($postData['email'], 'contractor_invite', [
                'url' => url('login'),
                'name' => $postData['first_name'].' '.$postData['last_name'],
                'email' => $postData['email'],
                'password' => $postData['password'],
            ]);
        }
        
        if($sessionUser->type == 0){
            $redirectUrl = 'admin/contractors';
        }elseif($sessionUser->type == 2){
            $redirectUrl = 'company/contractors';
        }
        
        // Return response message
        return [
            'status' => 1,
            'message' => $id ? 'Contractor Updated Successfully.' : 'Contractor Created Successfully.',
            'next' => 'load',
            'url' => $redirectUrl,
        ];
    }
    
    public function storeCompany($postData){
        
        $general = new General();
        $id = $postData['id'] ?? null;
        $pass = $postData['pass'] ?? null;
        $rules = [
            'company_name' => 'required|string|max:255',
            'email' => 'required|email:rfc,dns,spoof|max:255',
            'status' => 'required|boolean',
        ];
        
        if (!$id) {
            $rules['email'] .= '|unique:user';
            $rules['image'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048';
            $rules['password'] = ['required', (new General())->passwordType()];
        } else {
             $rules['email'] = 'required|email:rfc,dns,spoof|unique:user,email,' . $id;
            $rules['image'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048';
            $rules['password'] = ['nullable', (new General())->passwordType()];
        }

        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) {
            return [
                'status' => 0,
                'message' => $validator->errors()->first(),
            ];
        }

        $model = $id ? User::find($id) : new User();

        if (isset($postData['image']) && $postData['image']->isValid()) {
            $uploadResult = $general->uploadFile($postData['image'], 'profile');
            if (!$uploadResult['status']) {
                return $uploadResult;
            }
            $image=$uploadResult['file_name'];
            if ($image) {
                if ($model->image) {
                    $general->deleteFile($model->image, 'profile');
                }
                $model->image = $image;
            }
        }

        $model->company_name = $postData['company_name'];
        $model->unlimited_conractors = $postData['unlimited_conractors'] ?? 0;
        $model->contractor_status = $postData['contractor_status'] ?? 0;
        $model->email = $postData['email'];
        $model->country = $postData['country'] ?? null;
        $model->status = $postData['status'];
        $model->type = $postData['type'];
        $model->registered_ip = $general->getClientIp();
        if (!empty($postData['password'])) {
            $model->password = (new AuthService())->encryptPassword($postData['password']);
        } else {
            $model->password = $pass;
        }
        
        if ($id) {
            $originalValue = (int) $model->getOriginal('unlimited_conractors'); // cast to int for reliable comparison
            $newValue = isset($postData['unlimited_conractors']) ? (int) $postData['unlimited_conractors'] : null;
        
            if ($originalValue === 0 && $newValue === 1) {
                (new General())->sendEmail($postData['email'], 'upgrade_plan_to_free', [
                    'company_name' => $postData['company_name'],
                ]);
            }
        }
        $model->save();
        
        // dd($postData['contractor_status']);
        if(isset($postData['contractor_status']) && $postData['contractor_status']  == 0 ){
            $contractorData = User::where('company_id',$model->id)->get();
            if($contractorData){
                foreach($contractorData as $contractor){
                    $contractor->status = 0;
                    $contractor->save();
                }
            }
        }elseif(isset($postData['contractor_status'])  && $postData['contractor_status'] == 1){
          $contractorData  = User::where('company_id',$model->id)->get();
            if($contractorData){
                foreach($contractorData as $contractor){
                    $contractor->status = 1;
                    $contractor->save();
                }
            }
        }
        
        // Save company_allowed_documents
        if(isset($postData['documents_type'])){
            $submittedDocs = $postData['documents_type'];
            
            // Delete documents not in submitted list
            if (!empty($submittedDocs)) {
                DB::table('company_allowed_documents')
                    ->where('company_id', $model->id)
                    ->whereNotIn('document_type_id', $submittedDocs)
                    ->delete();
            } else {
                DB::table('company_allowed_documents')->where('company_id', $model->id)->delete();
            }
            
            $documentsJson = json_encode(array_map('intval', $submittedDocs));

             DB::table('company_allowed_documents')->updateOrInsert(
                ['company_id' => $model->id], 
                [
                    'document_type_id' => $documentsJson,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
    
        }
        
        if(!$id && isset($postData['unlimited_conractors']) && $postData['unlimited_conractors'] == 1){
            $subscriptionModel = new Subscription();
            $subscriptionModel->user_id = $model->id;
            $subscriptionModel->status = 'active';
            $subscriptionModel->expired_at = date('Y-m-d H:i:s', strtotime($model->created_at . ' +14 days'));
            $subscriptionModel->save();
        }
        
        
        if(!$id){
            if(isset($postData['unlimited_conractors']) && $postData['unlimited_conractors'] == 1 ){
                (new General())->sendEmail($postData['email'], 'company_invite_with_free_contractor', [
                    'url' => url('login'),
                    'company_name' => $postData['company_name'],
                    'email' => $postData['email'],
                    'password' => $postData['password'],
                ]);
            }else{
                (new General())->sendEmail($postData['email'], 'company_invite', [
                    'url' => url('login'),
                    'company_name' => $postData['company_name'],
                    'email' => $postData['email'],
                    'password' => $postData['password'],
                ]);
            }
        }
        
        
        return [
            'status' => 1,
            'message' => $id ? 'Company Updated Successfully.' : 'Company Created Successfully.',
            'next' => 'load',
            'url' => 'admin/company',
        ];
    }
    
    public function getComplianceStatus1($id){
        
        $allowedDocTypeIds = DB::table('company_allowed_documents')
                ->where('company_id', $id)
                ->pluck('document_type_id');
        $totalDocumentTypes = DocumentType::where('is_hidden', 0)->whereIn('id', $allowedDocTypeIds)->count();
        $compliantDocuments = Document::join('document_type', 'document.type', '=', 'document_type.id')
            ->where('document.user_id', $id)
            ->where('document.approve_status', 1)
            ->where('document_type.is_hidden', 0)
            ->distinct('document.type')
            ->count('document.type');

        $compliancePercentage = 0;

        if ($totalDocumentTypes > 0) {
            $compliancePercentage = ($compliantDocuments / $totalDocumentTypes) * 100;
        }
        return round($compliancePercentage) ." %";    
        
    }
    
    public function getComplianceStatus($contractorId){
        return DocumentHelper::getComplianceStatus($contractorId);
    }
    
    public function deleteMultiple($postData){
        
        $ids = explode(',', $postData['id']);        
        foreach ($ids as $id){
            $user = User::find($id);
            if(!$user){
                return ['status'=>0, 'message'=>'contractor Not Found'];
            }
            
          $documents = Document::where('user_id', $user->id)->get();
        //   dd($documents);
            foreach ($documents as $document) {
                // Delete all related document files
                DocumentFile::where('document_id', $document->id)->delete();
    
                // Delete the document itself
                $document->delete();
            }
            // dd($user);
            $user->delete();
            DB::commit();        }
        return ['status'=>1,'message'=>'Contractor Removed Successfully','next'=>'reload'];
        // dd($ids);
    }
    
      public function getRole($role)
    {
        $result = DB::table('user_role')->where('id', $role)->first();
        return $result ? $result->title : null;
    }
    
       public function getContractorType($type)
    {
        if($type == 0){
            return "Admin";
        }else{
            return "User";
        }
        
    }
    
 public function sendReminderEmail($postData){
    $id = $postData['id'] ?? null;
    $rules = [
        'subject' => 'required|string|max:255',
        'message' => 'required|string|max:255',
    ];

    $validator = Validator::make($postData, $rules);
    if ($validator->fails()) {
        return [
            'status' => 0,
            'message' => $validator->errors()->first(),
        ];
    }

    $id = explode(',', $id);
    foreach ($id as $userId) {
        $user = User::find($userId);
        $company = '';
         if($user !== null && $user->type == 1 && $user->type != ''){
                $company = \App\Models\User::where('id',$user->company_id)->first();
         }
        if ($user) {
            $subject = $postData['subject'];
            $message = $postData['message'];
            (new General())->sendMail(
                $user->email, $subject . ' | ' . config('setting.app_name'), view('email/admin/contractor-email', [
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'subject' => $subject,
                    'message' => $message,
                    'company' => $company,
                ])->render()
            );
        }
    }
    
    return ['status' => 1, 'message' => 'Email sent successfully.','next'=>'reload'];
}

    public function getCompanyName($userId){
       $result = User::where('id',$userId)->first();
        return $result->company_name ?? '';
    }
    
    public function noteStore($postData){
        $id = $postData['contractor_id'];   
        if(!$id){
            return['status' => 0, 'message' => 'Contractor not found'];
        }
        
        $contractor = User::find($id);
        if(!$contractor){
            return ['status' => 0, 'message' => 'Contractor not found'];
        }
        $contractor->contractor_internal_note = $postData['contractor_internal_note'];
        $contractor->address = $postData['address'];
        $contractor->phone = $postData['phone'];
        $contractor->save();
        
        return ['status' => 1, 'message' => 'Saved successfully.', 'next' => 'refresh'];
    }
    
    public function getCompanyPlanInfo($companyId){
        if(!$companyId){
            return ['status'=>0, 'message'=> 'Company Not Found'];
        }
        $company = User::find($companyId);
        if($company){
            if ($company->type == 2 && !empty($company->company_id)) {
                $company = User::find($company->company_id) ?: $company;
            }
            $subscriptionData = Subscription::where('user_id',$company->id)->first();
            if($subscriptionData){
                return $subscriptionData->plan_id;  
            }
        }
    }
    
    public function isUnlimitedContractor($companyId){
        if(!$companyId){
            return ['status'=>0, 'message'=> 'Company Not Found'];
        }
        $company = User::find($companyId);
        if($company){
            if ($company->type == 2 && !empty($company->company_id)) {
                $company = User::find($company->company_id) ?: $company;
            }
            return $company->unlimited_conractors;
        }
    }
    
    public function isEnableCompanyEmail($companyId){
        if(!$companyId){
            return ['status'=>0, 'message'=> 'Company Not Found'];
        }
        $company = User::find($companyId);
        if($company){
            if ($company->type == 2 && !empty($company->company_id)) {
                $company = User::find($company->company_id) ?: $company;
            }
            return $company->email_reminders;
        }
    }
    
    public function isCompanyEmailEnabled($userId = null){
        $user = $userId ? User::find($userId) : auth()->user();
        return $user && $user->is_company_email_enabled ? true : false;
    }

    public function isContractorEmailEnabled($userId = null){
        $user = $userId ? User::find($userId) : auth()->user();
        return $user && $user->is_contractor_email_enabled ? true : false;
    }
    
    public function isAffiliate(): bool
    {
        return (int)$this->type === 3 && (int)$this->is_affiliate === 1;
    }
    
    public function affiliateList($postData)
    {
        // Base query for affiliates (type = 3)
        $query = DB::table('user')->select('*')->where('type', 3);
    
        // Search filter
        $searchText = $postData['search']['value'] ?? '';
        if (strlen($searchText) > 2) {
            $searchTextLike = '%' . $searchText . '%';
            $query->where(function ($q) use ($searchTextLike) {
                $q->whereRaw("concat(user.first_name,' ',user.last_name) like ?", [$searchTextLike])
                    ->orWhere('affiliate_company_name', 'like', $searchTextLike)
                    ->orWhere('email', 'like', $searchTextLike);
            });
        }
    
        // Pagination and sorting info from DataTables
        $start = $postData['start'] ?? 0;
        $length = $postData['length'] ?? 10;
        $orderColumnIndex = $postData['order'][0]['column'] ?? 0;
        $orderDirection = $postData['order'][0]['dir'] ?? 'desc';
    
        // Map column indexes to DB columns for ordering
        $columns = [
            0 => 'user.id',
            1 => 'user.first_name', // we concatenate later for full name
            2 => 'affiliate_company_name',
            3 => 'email',
            4 => 'user.status',
            5 => 'user.created_at',
        ];
    
        // Apply ordering
        if (isset($columns[$orderColumnIndex])) {
            $query->orderBy($columns[$orderColumnIndex], $orderDirection);
        } else {
            $query->orderBy('user.created_at', 'desc'); // default
        }
    
        // Get total filtered records count
        $totalFiltered = $query->count();
    
        // Apply pagination
        $rows = $query->offset($start)->limit($length)->get();
    
        $sessionUser = auth()->user();
        $userObj = new User();
        $data = [];
    
        foreach ($rows as $key => $row) {
            // Calculate total commission
            $totalCommission = \App\Models\AffiliatesWallet::where('user_id', $row->id)
                ->where('type', 1)
                ->sum('amount');
    
            // Full Name
            $fullName = $row->first_name . ' ' . $row->last_name;
            $companyName = !empty($row->affiliate_company_name) ? $row->affiliate_company_name : 'N/A';
            $commission = '$' . number_format($totalCommission, 2);
            $statusBadge = $userObj->getStatusBadge($row->status);
            $createdAtFormatted = date(config('setting.date_format'), $row->created_at);
    
            // Action buttons
            $actions = '';
            if ($sessionUser->role === 0) {
                $actions = '<div class="act-btns">
                    <a href="admin/affiliate/view?id=' . $row->id . '" class="text-body pjax tool-btn me-2"><i class="fa fa-eye"><span class="tooltip-text">View</span></i></a>&nbsp;
                    <a href="admin/affiliate/update?id=' . $row->id . '" class="text-body pjax tool-btn me-2"><i class="bi bi-pencil-square"><span class="tooltip-text">Update</span></i></a> &nbsp;
                    <button style="border:none; background:none;" onclick="app.confirmAction(this);" data-action="admin/affiliate/delete" data-id="' . $row->id . '" class="text-body tool-btn me-2 dlt-lnk"><i class="bi bi-trash-fill"><span class="tooltip-text">Delete</span></i></button>
                </div>';
            } else {
                if ($sessionUser->hasPermission('admin/affiliate/view')) {
                    $actions .= '<a href="admin/affiliate/view?id=' . $row->id . '" class="text-body pjax act-btns tool-btn me-2"><i class="bi bi-eye-fill"><span class="tooltip-text">View</span></i></a>&nbsp;';
                }
                if ($sessionUser->hasPermission('admin/affiliate/update')) {
                    $actions .= '<a href="admin/affiliate/update?id=' . $row->id . '" class="text-body pjax act-btns tool-btn me-2"><i class="bi bi-pencil-square"><span class="tooltip-text">Update</span></i></a>&nbsp;';
                }
                if ($sessionUser->hasPermission('admin/affiliate/delete')) {
                    $actions .= '<button style="border:none; background:none;" onclick="app.confirmAction(this);" data-action="admin/affiliate/delete" data-id="' . $row->id . '" class="text-body act-btns tool-btn me-2 dlt-lnk"><i class="bi bi-trash-fill"><span class="tooltip-text">Delete</span></i></button>';
                }
            }
    
            // Serial number calculation (optional)
            if ($columns[$orderColumnIndex] === 'user.id' && $orderDirection === 'desc') {
                $serialId = $totalFiltered - ($start + $key);
            } else {
                $serialId = $start + $key + 1;
            }
    
            $data[] = [
                'id' => $serialId,
                'name' => $fullName,
                'affiliate_company_name' => $companyName,
                'email' => $row->email,
                'commission' => $commission,
                'status' => $statusBadge,
                'created_at' => $createdAtFormatted,
                'action' => $actions,
            ];
        }
    
        return [
            "draw" => intval($postData['draw']),
            "recordsTotal" => DB::table('user')->where('type', 3)->count(),
            "recordsFiltered" => $totalFiltered,
            "data" => $data,
        ];
    }

    public function storeAffiliate($postData)
    {
        $general = new General();
        $id = $postData['id'] ?? null;
        $pass = $postData['pass'] ?? null;
    
        // Validation rules
        $rules = [
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'required|email:rfc,dns,spoof|max:255',
            'affiliate_company_name' => 'nullable|string|max:255',
            'affiliate_website' => 'nullable|url|max:255',
            'affiliate_role_type' => 'required|string',
            'affiliate_role_other' => 'nullable|string|max:255|required_if:affiliate_role_type,other', // Only required if role is 'other'
            'affiliate_referral_description' => 'nullable|string|max:255',
            'affiliate_reach_volume' => 'required|string',
            'affiliate_referral_methods' => 'nullable|array',
            'affiliate_referral_methods.*' => 'string',
            'affiliate_commission_consent' => 'required|in:1,0',
            'affiliate_terms_accepted' => 'required|boolean',
            'affiliate_motivation' => 'nullable|string|max:255',
            'affiliate_notes' => 'nullable|string|max:255',
        ];
    
        // Additional validation based on id (for update)
        if (!$id) {
            $rules['email'] .= '|unique:user,email';
            $rules['image'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048';
            $rules['password'] = ['required', (new General())->passwordType()];
        } else {
            $rules['email'] = 'required|email:rfc,dns,spoof|unique:user,email,' . $id;
            $rules['image'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048';
            $rules['password'] = ['nullable', (new General())->passwordType()];
        }
    
        // Validator for incoming data
        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) {
            return [
                'status' => 0,
                'message' => $validator->errors()->first(),
            ];
        }
    
        // Find existing or create new user model
        $model = $id ? User::find($id) : new User();
    
        // Handle profile image upload
        if (isset($postData['image']) && $postData['image']->isValid()) {
            $uploadResult = $general->uploadFile($postData['image'], 'profile');
            if (!$uploadResult['status']) return $uploadResult;
    
            $image = $uploadResult['file_name'];
            if ($image && $model->image) {
                $general->deleteFile($model->image, 'profile');
            }
            $model->image = $image;
        }
    
    
        // Prepare data for update or creation
        $model->first_name = $postData['first_name'];
        $model->last_name = $postData['last_name'];
        $model->email = $postData['email'];
        $model->status = $postData['status'] ?? 1; // Default to active
        $model->type = 3; // Affiliate type
        $model->affiliate_company_name = $postData['affiliate_company_name'] ?? null;
        $model->affiliate_website = $postData['affiliate_website'] ?? null;
        $model->affiliate_role_type = $postData['affiliate_role_type'];
        $model->affiliate_role_other = $postData['affiliate_role_other'] ?? null;
        $model->affiliate_referral_description = $postData['affiliate_referral_description'];
        $model->affiliate_reach_volume = $postData['affiliate_reach_volume'];
        $model->affiliate_referral_methods = json_encode($postData['affiliate_referral_methods'] ?? []);    
        $model->affiliate_commission_consent = $postData['affiliate_commission_consent'] ?? 0;
        $model->affiliate_terms_accepted = $postData['affiliate_terms_accepted'] ?? 0; // Terms accepted checkbox
        $model->affiliate_motivation = $postData['affiliate_motivation'] ?? null;
        $model->affiliate_notes = $postData['affiliate_notes'] ?? null;
    
        // Password handling
        if (!empty($postData['password'])) {
            $model->password = (new AuthService())->encryptPassword($postData['password']);
        } else {
            $model->password = $pass; // Preserve the old password if not updated
        }
    
        // Save the user model
        $model->save();
        
         if (empty($model->affiliate_code)) {
            $model->affiliate_code = base64_encode((string)$model->id);
            $model->save();   
        }
    
        // Send welcome email for new affiliates
       (new General())->sendEmail(
            $postData['email'] ?? '', 
            'affiliate_invite', 
            [
                'name' => trim(($postData['first_name'] ?? '') . ' ' . ($postData['last_name'] ?? '')),
                'email' => $postData['email'] ?? '',
                'affiliate_code' => $model->affiliate_code ?? '',
            ]
        );
       
    
        return [
            'status' => 1,
            'message' => $id ? 'Affiliate Updated Successfully.' : 'Affiliate Created Successfully.',
            'next' => 'load',
            'url' => 'admin/affiliate',
        ];
}

//     public function storeCompanyAllowedDocuments($postData)
//     {
//     $companyId = $postData['company_id'] ?? $postData['id'] ?? null;
//     $documentTypeIds = $postData['documents_type'] ?? [];

//     if (!$companyId) {
//         return [
//             'status' => 0,
//             'message' => 'Company ID required'
//         ];
//     }

//     if (empty($documentTypeIds)) {
//         return [
//             'status' => 0,
//             'message' => 'Please select at least one document'
//         ];
//     }

//     // Delete old allowed docs
//     DB::table('company_allowed_documents')
//         ->where('company_id', $companyId)
//         ->delete();

//     // Prepare insert data
//     $insertData = [];

//     foreach ($documentTypeIds as $docTypeId) {
//         $insertData[] = [
//             'company_id' => $companyId,
//             'document_type_id' => $docTypeId,
//             'created_at' => now(),
//             'updated_at' => now()
//         ];
//     }

//     // Bulk insert
//     DB::table('company_allowed_documents')->insert($insertData);

//     // return [
//     //     'status' => 1,
//     //     'message' => 'Company allowed documents saved successfully.',
//     //     'next' => 'load',
//     //     'url' => 'admin/company/view'
//     // ];
    
//      return response()->json([
//                 'status'  => 1,
//                 'message' => 'Company allowed documents saved successfully.,
//                 'next'    => 'reload'
//             ]);
// }





  
}