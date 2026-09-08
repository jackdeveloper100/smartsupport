<?php

namespace App\Models;

use App\Helpers\Pagination;
use App\Helpers\General;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


/**
 * Class Page
 *
 * Model for the `page` table.
 * Handles listing pages for admin with search and pagination.
 *
 * @package App\Models
 */
class Vendor extends Model
{
    /**
     * The name of the table associated with the model.
     *
     * @var string
     */
    protected $table = 'w9';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;
    // protected $dateFormat = 'U';


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
        protected $fillable = ['representative_of','vendor_company_name','vendor_contact_person','vendor_email','vendor_phone','vendor_account_number','notify_me','entity_name','entity_type','tax_id_number','list_account_number','no_foreign_beneficiaries','address_lookup','address','city','state','zip_code','signature','request_status','token','is_request_resend','encryption_key','encrypted_pdf_path','business_name','other_entity_name','email_open_at','entity_specification','submited_at'];
        
        protected $casts = [
            'notify_me' => 'boolean',
            'entity_name' => 'boolean',
            'no_foreign_beneficiaries' => 'boolean',
        ];
        
    /**
     * Retrieves paginated list of pages for admin with search capability.
     *
     * @param array $postData The data passed for pagination and search.
     * @return array The paginated and formatted list of pages.
     */
           public function list($postData)
    {
        $sessionUser = auth()->user();
        $effectiveCompanyId = $sessionUser ? $sessionUser->getCompanyOwnerId() : 0;
        $query = DB::table('w9')
            ->leftJoin('user', 'w9.representative_of', '=', 'user.id')
            ->select('w9.*', 'user.company_name as representative_company_name')
            ->where('request_status', 0)
            ->where('representative_of', $effectiveCompanyId);
    
        $columns = [
            0 => 'w9.id',
            1 => 'user.company_name',
            2 => 'w9.vendor_company_name',
            3 => 'w9.request_status',
            4 => 'w9.created_at',
            5 => 'w9.email_open_at',
        ];
    
        $orderColumnIndex = $postData['order'][0]['column'] ?? 0;
        $orderDir = $postData['order'][0]['dir'] ?? 'desc';
        $orderColumn = $columns[$orderColumnIndex] ?? 'w9.updated_at';
        $query->orderBy($orderColumn, $orderDir);
     
        $searchText = $postData['search']['value'] ?? '';
        if (strlen($searchText) > 2) {
            $searchText = '%' . $searchText . '%';
            $query->where(function ($q) use ($searchText) {
                $q->where('vendor_company_name', 'like', $searchText)
                  ->orWhere('vendor_contact_person', 'like', $searchText)
                  ->orWhere('representative_of', 'like', $searchText);
            });
        }
    
        $result = (new Pagination())->getDataTable($query, $postData);
    
        foreach ($result['data'] as $key => $row) {
            $result['data'][$key]->representative_of = $row->representative_company_name ?? $row->representative_of;
            $result['data'][$key]->vendor_company_name = $row->vendor_company_name;
            $result['data'][$key]->entity_type = $row->entity_type;
    
            // Progress bar
            $stepsCompleted = 0;
            if ($row->representative_of) $stepsCompleted++;
            if ($row->entity_name) $stepsCompleted++;
            if ($row->address) $stepsCompleted++;
            if ($row->signature) $stepsCompleted++;
            $totalSteps = 4;
            $percentage = ($stepsCompleted / $totalSteps) * 100;
    
            $color = $percentage < 50 ? 'bg-light-warning' : ($percentage < 100 ? 'bg-warning' : 'bg-success');
    
            $result['data'][$key]->request_status = '
            <div class="progress" style="height: 25px; position: relative;">
                <div class="progress-bar ' . $color . '" role="progressbar" style="width: ' . $percentage . '%;" aria-valuenow="' . $percentage . '" aria-valuemin="0" aria-valuemax="100">
                    Step ' . $stepsCompleted . ' / ' . $totalSteps . ' (' . round($percentage) . '%)
                </div>
            </div>';
    
            $result['data'][$key]->updated_at = $row->email_open_at
                ? date('D, M d, Y g:i A', strtotime($row->email_open_at))
                : 'Unopened';
            $result['data'][$key]->created_at = date('D, M d, Y g:i A', strtotime($row->created_at));
    
            $actions = '<div class="act-btns">';
            if ($sessionUser->hasPermission('company/request') || $sessionUser->hasPermission('company/vendor/send_mail')) {
                $actions .= '<a href="javascript:void(0)" class="text-body pjax tool-btn me-2" onclick="app.showModalView(\'company/request/update?id=' . $row->id . '\')">
                    <i class="bi bi-pencil-square"><span class="tooltip-text">Update</span></i>
                </a>';
            }
            if ($sessionUser->hasPermission('company/vendor/send_mail')) {
                $actions .= '<button style="border:none; background:none;" onclick="app.confirmW9Action(this);" data-action="company/request/delete-request" data-id="' . $row->id . '" class="text-body tool-btn me-2">
                    <i class="bi bi-trash-fill"><span class="tooltip-text">Delete</span></i>
                </button>';
            }
            $actions .= '</div>';
            $result['data'][$key]->action = $actions;
        }
    
        return $result;
    }

    
        public function receivedList($postData)
    {
        $sessionUser = auth()->user();
        $effectiveCompanyId = $sessionUser ? $sessionUser->getCompanyOwnerId() : 0;
        $rows = DB::table('w9')
            ->leftJoin('user', 'w9.representative_of', '=', 'user.id')
            ->select('w9.*', 'user.company_name as representative_company_name')
            ->where('request_status', 1)->where('representative_of', $effectiveCompanyId)->orderBy('updated_at', 'desc')
            ->get();
    
        $decrypt = function($value, $key) {
            if (!$value) return null;
            $data = base64_decode($value);
            $iv = substr(hash('sha256', $key), 0, 16);
            return openssl_decrypt(
                $data,
                'aes-256-cbc',
                hash('sha256', $key, true),
                OPENSSL_RAW_DATA,
                $iv
            );
        };
    
        $searchText = strtolower($postData['search']['value'] ?? '');
        $filtered = [];
    
        foreach ($rows as $row) {
            $serviceKey = $row->encryption_key ?? null;
            $entityDecrypted = $serviceKey ? $decrypt($row->entity_type, $serviceKey) : null;
            $row->entity_type = $entityDecrypted;
    
            $haystack = strtolower(
                $row->vendor_company_name.' '.
                $row->vendor_contact_person.' '.
                ($entityDecrypted ?? '')
            );
    
            if ($searchText === '' || strpos($haystack, $searchText) !== false) {
                $filtered[] = $row;
            }
            
              $orderColIndex = $postData['order'][0]['column'];  // 0..6
            $orderDir      = $postData['order'][0]['dir'];     // asc/desc
            $columnName    = $postData['columns'][$orderColIndex]['data'];
        
            $nonSortable = ['audit_icon', 'action'];
        
            if (!in_array($columnName, $nonSortable)) {
                usort($filtered, function ($a, $b) use ($columnName, $orderDir) {
        
                    $valA = $a->{$columnName};
                    $valB = $b->{$columnName};
        
                    if ($columnName === 'updated_at') {
                        $valA = strtotime($valA);
                        $valB = strtotime($valB);
                    }
        
                    if ($valA == $valB) return 0;
        
                    return ($orderDir === 'asc')
                        ? ($valA <=> $valB)
                        : ($valB <=> $valA);
                });
            }
        }
    
        $start = intval($postData['start'] ?? 0);
        $length = intval($postData['length'] ?? 10);
        $paged = array_slice($filtered, $start, $length);
    
        $data = [];
        foreach ($paged as $row) {
            $data[] = [
                'id' =>$row->id,
                'vendor_company_name'   => $row->vendor_company_name,
                'vendor_contact_person' => $row->vendor_contact_person,
                'entity_type'           => $row->entity_type, 
                'request_status'        => $this->getStatusBadge($row->request_status),
                'updated_at'            => date('D, M d, Y g:i A', strtotime($row->updated_at)),
                'representative_of'     => $row->representative_company_name ?? $row->representative_of,
                'audit_icon'            => 
                sprintf(
                        '<a onclick="app.showModalView(\'company/request/audit/%d/\')" class="text-body d-flex justify-content-center align-items-center pjax act-btns tool-btn me-2"><i class="fa fa-clipboard fa-lg"></i> <span class="tooltip-text">View</span></a>',
                        $row->id),
                'action' => (function() use ($row, $sessionUser) {
                    $actions = '<div class="act-btns">';
                    if ($sessionUser->hasPermission('company/w9/request/received')) {
                        $actions .= '<a href="javascript:void(0);" onclick="app.confirmW9ResendAction(this);" data-action="company/w9/request/resend-request" data-id="' . $row->id . '" class="text-body tool-btn me-2">
                           <i class="fa fa-paper-plane"><span class="tooltip-text mb-2 p-2">Resend W-9 Request</span></i>
                        </a>
                        <a href="'.route('fw9/pdf', ['token' => $row->token]).'" target="_blank" class="text-body tool-btn me-2">
                            <i class="fa fa-download"><span class="tooltip-text mb-2 p-2">Download</span></i>
                        </a>
                        <button style="border:none; background:none;" onclick="app.confirmW9ActionRecived(this);" data-action="company/w9/request/delete" data-id="' . $row->id . '" class="text-body tool-btn me-2"><i class="bi bi-trash-fill"><span class="tooltip-text">Delete</span></i></button>';
                    }
                    $actions .= '</div>';
                    return $actions;
                })()
            ];
        }
    
        return [
            "draw"            => intval($postData['draw'] ?? 1),
            "recordsTotal"    => count($rows),
            "recordsFiltered" => count($filtered),
            "data"            => $data
        ];
    }


        public function getStatusBadge($status)
    {
        if ($status == 0) {
            return '<span class="badge bg-success"><i class="bi bi-check-circle"></i> send </span>';
        } elseif ($status == 1) {
            return '<span class="badge bg-danger"><i class="bi bi-slash-circle"></i> receive</span>';
        }
    }


    /**
     * Stores or updates a page record based on provided data.
     *
     * @param array $postData The data for creating or updating a page.
     * @return array The status and message of the operation.
     */
     
       public function store(array $postData): array
    {
        $postData['notify_me'] = !empty($postData['notify_me']) ? 1 : 0;
        $sendAdditional = !empty($postData['send_additional']) ? 1 : 0;
    
        $validator = Validator::make($postData, [
            'representative_of'     => 'required|string|max:128',
            'vendor_company_name'   => 'nullable|string|max:128',
            'vendor_contact_person' => 'nullable|string|max:128',
            'vendor_email'          => 'nullable|email|max:128',
            'vendor_phone'          => 'nullable|string|max:32',
            'vendor_account_number' => 'nullable|string|max:32',
            'notify_me'             => 'nullable|boolean',
        ]);
        if ($validator->fails()) {
            return [
                'status'  => 0,
                'message' => $validator->errors()->first(),
            ];
        }
        // dd($postData);
    $id = $postData['id'] ?? null;
     $model = Vendor::where('vendor_contact_person', $postData['vendor_contact_person'])
        ->where('vendor_email', $postData['vendor_email'])
        ->first();

        if($postData['vendor_contact_person']){
            $vendorData = Vendor::where('vendor_contact_person',$postData['vendor_contact_person'])->first();
            if($vendorData){
                $w9Id = $vendorData->id;
                $this->InsertNullData($w9Id);
            }
        }
        
        if($id){
           $this->InsertNullData($id);
        }
      if (!$model) {
        // Create new Vendor
        $model = new Vendor();
        $model->token = Str::random(40);

        // Initialize nullable-safe fields
        $model->entity_name = $postData['entity_name'] ?? '';
        $model->entity_type = $postData['entity_type'] ?? '';
        $model->entity_specification = $postData['entity_specification'] ?? '';
        $model->submited_at = $postData['submited_at'] ?? null;
        $model->other_entity_name = $postData['other_entity_name'] ?? '';
        $model->business_name = $postData['business_name'] ?? '';
        $model->tax_id_number = $postData['tax_id_number'] ?? '';
        $model->no_tax_id = $postData['no_tax_id'] ?? '';
        $model->list_account_number = $postData['list_account_number'] ?? '';
        $model->no_foreign_beneficiaries = $postData['no_foreign_beneficiaries'] ?? '';
        $model->address_lookup = $postData['address_lookup'] ?? '';
        $model->address = $postData['address'] ?? '';
        $model->city = $postData['city'] ?? '';
        $model->state = $postData['state'] ?? '';
        $model->zip_code = $postData['zip_code'] ?? '';
        $model->signature = $postData['signature'] ?? '';
        $model->is_request_resend = $postData['is_request_resend'] ?? 0;
        $model->encryption_key = $postData['encryption_key'] ?? '';
        $model->encrypted_pdf_path = $postData['encrypted_pdf_path'] ?? '';
        $model->email_open_at = null; // always null on new record
    }
    
    $contact = User::find($postData['vendor_contact_person']);
    if (!$contact) {
        return [
            'status' => 0,
            'message' => 'Vendor contact person not found.',
        ];
    }
    
    
    $model->representative_of = $postData['representative_of'];
    $model->vendor_contact_person = $postData['vendor_contact_person'] ?? '';
    $model->vendor_company_name = $postData['vendor_company_name'] ?? '';
    $model->vendor_email = $postData['vendor_email'] ?? '';
    $model->vendor_phone = $postData['vendor_phone'] ?? null;
    $model->vendor_account_number = $postData['vendor_account_number'] ?? null;
    $model->notify_me = $postData['notify_me'];
    $model->request_status = 0;
    $model->email_open_at = null;
    
    
    if (!$model->token) {
        $model->token = Str::random(40);
    }
    $model->save();
    $contact = User::find($postData['vendor_contact_person']);
    $vendorName = $contact->business_name ?? $contact->name ?? '';
    $company = User::find($postData['representative_of'] ?? null);
    $companyName = $company->company_name ?? $company->name ?? '';
        
    $sessionUser = auth()->user();
        $url = route('w9_form', ['token' => $model->token]);
        if ($model->notify_me && !empty($model->vendor_email)) {
            $template = 'w9_request';
            (new General())->sendEmail($model->vendor_email, $template, [
                'name' => $vendorName, 
                'company_name' => $companyName,
                'email' => $sessionUser->email, 
                'link' => $url, 
            ]);
        }
        
        $message = $id ? 'W-9 Request updated successfully.' : 'W-9 Request saved successfully.';
            
        return [
            'status'      => 1,
            'message'     => $message,
            'next'        => 'load',
            'url'         =>  route('company/request'),
            'isOpenModel' => $sendAdditional == 1,
        ];
    }
    
    public function InsertNullData($id){
        $w9Data = Vendor::find($id);
            if($w9Data){
                $w9Data->entity_name =  null;
                $w9Data->entity_type = null;
                $w9Data->entity_specification = null;
                $w9Data->submited_at = null;
                $w9Data->other_entity_name = null;
                $w9Data->business_name = null;
                $w9Data->tax_id_number = null;
                $w9Data->no_tax_id = null;
                $w9Data->list_account_number = null;
                $w9Data->no_foreign_beneficiaries = null;
                $w9Data->address_lookup = null;
                $w9Data->address = null;
                $w9Data->city = null;
                $w9Data->state = null;
                $w9Data->zip_code = null;
                $w9Data->signature = null;
                $w9Data->is_request_resend = null;
                $w9Data->encryption_key = null;
                $w9Data->encrypted_pdf_path = null;
                $w9Data->email_open_at = null;
                
                $w9Data->save();
            }
    }
}
