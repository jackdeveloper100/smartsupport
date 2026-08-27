<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Document;
use App\Helpers\General;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Jobs\SendDocumentNotificationEmail; 
use App\Models\EmailQueue;
use App\Models\User;

class Notification extends Model
{
    use HasFactory;
    /**
     * The name of the table associated with the model.
     *
     * @var string
     */
    protected $table = 'notification';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    protected $dateFormat = 'U';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['user_id', 'document_status', 'type', 'title', 'description' ,'created_at','is_read'];

    public function store($DescriptionType,$documentId){
        $documentModel = new Document();
        $documentData = Document::where('id', $documentId)->first();
        $documentType = $documentData->type;
        $documentType = $documentModel->getDocumentName($documentType);
        
        $user = User::where('id',$documentData['user_id'])->first();
        
        $model = new Notification();
        // dd(time());  
        if($DescriptionType == 'approve'){
            $documentStatus = 1; 
            $title = "Approval";
            $type = 1;
            $description = $documentType.' '. "Approved";
        }

        if($DescriptionType == 'reject'){
            $documentStatus = 2;
            $type = 1;
            $title = "Rejection";
            $description = $documentType. ' ' . 'Rejected For Reason of ' .$documentData->reject_reason;
        }
        
        if($DescriptionType == 'Upload'){
            $documentStatus = 1; 
            $type = 0;
            $title = "Uploaded";
            $description = $user->first_name . ' ' .$user->last_name. ' has Uploaded '. $documentType;
        }

        if($DescriptionType == 'Update'){
            $documentStatus = 1; 
            $type = 0;
            $title = "Updated";
            $description = $user->first_name . ' ' .$user->last_name. ' has Updated '. $documentType;
        }
       
        $model->user_id = $documentData['user_id'];
        $model->document_status = $documentStatus;
        $model->title = $title;
        $model->type = $type;
        $model->description = $description;
        $model->created_at = time();
        $model->save();
        
        $documentName = $description;
        
        if($DescriptionType == 'approve'){
            $template = 'document_approve';
        }elseif($DescriptionType == 'reject'){
            $template = 'document_reject';
        }

        if(isset($template) && $template != ''){
            $userObj = new User();
            $enableNotification = $userObj->isEnableCompanyEmail($user->company_id);

            if ($enableNotification == 1) {
                
                $data = [
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'description' => $description,
                    'DescriptionType' => $DescriptionType,
                    'document_name' => $documentType
                    ];
                $company = '';
                $templateData= (new \App\Models\EmailTemplate())->getEmailTemplate($template,$data);
                $body=view('email/template',['body'=>$templateData['body'], 'company'=>$company])->render();
                
                $emailQueue = new EmailQueue();
                $emailQueue->email_to = $user->email;
                $emailQueue->email_subject = $templateData['subject'];
                $emailQueue->email_body = $body;
                $emailQueue->is_sent = 0;
                $emailQueue->save();
                
                // (new General())->sendEmail($user->email,  $template, [
                //     'name' => $user->first_name . ' ' . $user->last_name,
                //     'description' => $description,
                //     'DescriptionType' => $DescriptionType,
                //     'document_name' => $documentType
                // ]);
                    // SendDocumentNotificationEmail::dispatch($user, $description, $DescriptionType, $documentType,$template);
            }
        }

    }

    public function loadMoreNotificationAdmin($postData){
        $userId = $postData['user_id'];
        $limit = $postData['limit'];
        
        $notificationData = Notification::where('type',0)
                                        ->orderBy('created_at','desc')
                                        ->skip($limit)
                                        ->take(10)->get();
        // dd($notificationData);
        $notifications = $notificationData->map(function($notification) {
            return [
                'badge' => $this->getNotificationBadge($notification->document_status),  
                'description' => $notification->description,
                'created_at' => \Carbon\Carbon::createFromTimestamp($notification->created_at)->format('Y-m-d h:i A')
            ];
        });

        return ['notifications' => $notifications];
        // dd($userId,$limit,$notificationData,$notifications);

    }
    
     public function loadMoreNotificationCompany($postData){
        $userId = $postData['user_id'];
        $limit = $postData['limit'];
        
        $notificationData = Notification::where('notification.type',0)
                                        ->leftjoin('user','user.id','=','notification.user_id')
                                        ->where('user.company_id',auth()->id())
                                        ->orderBy('notification.created_at','desc')
                                        ->skip($limit)
                                        ->take(10)->get();
        // dd($notificationData);
        $notifications = $notificationData->map(function($notification) {
            return [
                'badge' => $this->getNotificationBadge($notification->document_status),  
                'description' => $notification->description,
                'created_at' => \Carbon\Carbon::createFromTimestamp($notification->created_at)->format('Y-m-d h:i A')

            ];
        });

        return ['notifications' => $notifications];
        // dd($userId,$limit,$notificationData,$notifications);

    }
    
    public function getNotificationBadge($documentStatus)
    {    
        switch ($documentStatus) {
            case 1:
                return '<span class="badge bg-success"><i class="bi bi-check-circle"></i></span>';
                break;
            case 2:
                return '<span class="badge bg-danger"><i class="bi bi-x-circle"></i></span>';
                break;
            case 3:
                return '<span class="badge bg-warning"><i class="bi bi-hourglass-split"></i></span>';
                break;
            case 4:
                return '<span class="badge bg-primary"><i class="bi bi-person"></i></span>';
                break;
            default:
                return '<span class="badge bg-danger"><i class="bi bi-slash-circle"></i></span>';
                break;
        }
        
    }

    public function registerEntry($userId){
        $user = User::where('id',$userId)->first();
        
        $model = new Notification();

        $model->user_id = $userId;
        $model->document_status = 4;
        $model->type = 0;
        $model->is_read =0;
        if($user->type == 2 ){
            $model->title = " New Registration " ;
            $model->description = $user->company_name . "is Newly Registred" ;
        }else{
            $model->title = " New Request " ;
            $model->description = $user->first_name. ' '. $user->last_name ." is Requested to Registered";
        }
        
        $model->created_at = time();
        $model->save();

    }
    
    public function planEntry($userId,$planId){
        $user = User::where('id',$userId)->first();
        $plan = Plan::where('id',$planId)->first();
        $model = new Notification();
        
        $model->user_id = $userId;
        $model->document_status = 1;
        $model->type = 0;
        $model->is_read = 0; 
        $model->title = " Plan Upgraded " ;
        $model->description = $user->company_name .' has Upgraded their plan to '. $plan->title;
        $model->created_at = time();
        $model->save();
    }
    
    public function getNotificationCount($userId){
        $result = Notification::where('user_id',$userId)
                                    ->where('type',1)
                                    ->where('is_read',0)
                                    ->count();
        return $result;
    }
    
    public function getLatestNotification($userId){
        $result = Notification::where('user_id',$userId)
                            ->orderBy('created_at','desc')
                            ->where('type',1)
                            ->where('is_read',0)
                            ->limit(2)
                            ->get();
        return $result;
    }

    public function getAdminNotificationCount(){
        $result = Notification::where('type',0)->where('is_read',0)->count();
        return $result;
    }

    public function getLatestNotificationAdmin(){
        $result = Notification::orderBy('created_at','desc')
                                ->where('type',0)
                                ->where('is_read',0)
                                ->limit(2)
                                ->get();
        return $result;
    }
    
    public function getCompanyNotificationCount(){
        $result = Notification::where('notification.type',0)
                                ->where('notification.is_read',0)
                                ->leftjoin('user','user.id','=','notification.user_id')
                                ->where('user.company_id',auth()->id())
                                ->count();
        return $result;
    }
    
     public function getLatestNotificationCompany(){
        $result = Notification::orderBy('notification.created_at','desc')
                                ->leftjoin('user','user.id', '=','notification.user_id')
                                ->where('user.company_id',auth()->id())
                                ->where('notification.type',0)
                                ->where('notification.is_read',0)
                                ->limit(2)
                                ->get();
        return $result;
    }
}
