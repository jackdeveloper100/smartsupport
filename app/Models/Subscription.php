<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\Pagination;
use App\Models\User;
use App\Models\Plan;
use App\Helpers\General;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class Subscription extends Model
{
      /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'subscription';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The format for the date columns.
     *
     * @var string
     */
    protected $dateFormat = 'U';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'stripe_subscription_id',
        'plan_id',
        'status',
        'created_at',
        'updated_at'
    ];
    
  public function list($postData) {
    $query = DB::table('subscription')
        ->select(
            'subscription.*',
            'subscription.status as subscription_status',
            'plan.title as plan_title'
        )
        ->leftJoin('user', 'user.id', '=', 'subscription.user_id')
        ->leftJoin('plan', 'plan.id', '=', 'subscription.plan_id')
        ->where('user.company_name', '!=', '');

    $searchText = isset($postData['search']['value']) ? $postData['search']['value'] : '';

    if (strlen($searchText) > 2) {
        $searchTextLike = '%' . $searchText . '%';

        $query->where(function ($query) use ($searchText, $searchTextLike) {
            $query->where('subscription.plan_id', 'like', $searchTextLike)
                ->orWhere('user.company_name', 'like', $searchTextLike)
                ->orWhere('plan.title', 'like', $searchTextLike)
                ->orWhere('plan.description', 'like', $searchTextLike);

            // Match if user search starts with "tri", "tria", "trial", etc.
            if (stripos('trial', strtolower($searchText)) === 0) {
                $query->orWhereNull('subscription.plan_id');
                $query->orWhere('subscription.plan_id', '=', 0);
            }
        });
    }

    $result = (new Pagination())->getDataTable($query, $postData);

    foreach ($result['data'] as $key => $row) {
        $user = User::where('id',$row->user_id)->first();
        
        $result['data'][$key]->user_id = $this->getCompanyName($row->user_id);
        
        if($user->unlimited_conractors == 1){
            $result['data'][$key]->stripe_subscription_id = 'Free Plan';
            $result['data'][$key]->expired_at = 'Never Expired';  
            $result['data'][$key]->amount = 'Free';
            $result['data'][$key]->status = $this->getStatusBadge('free');
        }else{
            $result['data'][$key]->stripe_subscription_id = $this->getSubscriptionName($row->plan_id);
            $result['data'][$key]->expired_at = date('d M, Y', strtotime($row->expired_at));
            $result['data'][$key]->amount = $this->getPriceDetails($row->plan_id);
            $result['data'][$key]->status = $this->getStatusBadge($row->subscription_status);
        }
        // $result['data'][$key]->amount = $this->getPriceDetails($row->plan_id);
        // $result['data'][$key]->status = $this->getStatusBadge($row->subscription_status);
        $result['data'][$key]->created_at = date('d M, Y', $row->created_at);
        $result['data'][$key]->updated_at = date('d M, Y', $row->updated_at);
        
        // $result['data'][$key]->expired_at = date('d M, Y', strtotime($row->expired_at));
    }

    return $result;
}


    
    public function checkSubscriptionExpires(){
        $general= new General();
        $subscriptionData = Subscription::where(function ($query) {
            $query->whereNull('stripe_subscription_id')
                  ->orWhere('stripe_subscription_id', '');
        })->where('status', 'active')->get();
        
        foreach($subscriptionData as $subscription){
            $user = User::find($subscription->user_id);
            if($user){

                $trialEndDate = Carbon::parse($user->created_at)->addDays(14);
                $currentDate = Carbon::now();

                if($currentDate->greaterThanOrEqualTo($trialEndDate)){
                    $subscription->status = 'canceled';
                    $subscription->save();
                    
                    if($user->unlimited_conractors != 1){
                         $template = 'trail_expired';
                        (new General())->sendEmail($user->email, $template,[
                            'company_name' => $user->company_name,
                        ]);
                    }
                }
           
            }
        }
         return 'ok';
    } 
    
    public function getContractorLimit($userId){
        $subcriptionData = Subscription::where('user_id',$userId)->latest()->first();
        if($subcriptionData){
            $planDetails = Plan::find($subcriptionData->plan_id);
            return $planDetails->contractor_limit ?? 10;
        }
    }

    public function isLegacyPlan($userId = null){
        $sub = $userId ? Subscription::where('user_id', $userId)->latest()->first() : $this;
        return $sub && Plan::isLegacyPlan($sub->plan_id);
    }

    public function isStandardPlan($userId = null){
        $sub = $userId ? Subscription::where('user_id', $userId)->latest()->first() : $this;
        return $sub && Plan::isStandardPlan($sub->plan_id);
    }
    
    public function getCompanyName($userId){
        $result = User::where('id',$userId)->first();
        return $result->company_name;
    }
    
    public function getSubscriptionName($planId){
        $result = Plan::where('id',$planId)->first();
        return isset($result->title) ? $result->title : 'Trial';
    }
    
    public function getSubscriptionDescription($planId){
        $result = Plan::where('id',$planId)->first();
        return isset($result->description) ? $result->description : 'Trial Period';
    }
    
    public function getPriceDetails($planId){
        $result = Plan::where('id',$planId)->first();
        return isset($result->amount) ? '$'.$result->amount : 'Trial';
    }

    public function getStatusBadge($status)
    {
        if($status == 'active'){
            return '<span class="badge bg-success">Active</span>';
        }elseif($status == 'canceled'){
           return '<span class="badge bg-danger">Expired</span>';
        }elseif($status == 'free'){
            return '<span class="badge bg-primary">Free</span>';
        }
        return '<span class="badge bg-secondary">Inactive</span>';
    }   
    

    public function getTotalPayment(){
        
        $subscriptionData = Subscription::all();
        
        $planAmount = 0;
        foreach($subscriptionData as $subscription){
            $planId = $subscription->plan_id;
            $planData = Plan::where('id',$planId)->first();
            if($planData && is_numeric($planData->amount)){
                 $planAmount += $planData->amount;
            }
        }

        return $planAmount;
    }
  
}