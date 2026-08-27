<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Admin\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Document;
use App\Models\DocumentType;
use App\Services\GeneralService;
use App\Models\Plan;
/**
 * Class SiteController
 * 
 * Controller for handling admin site functionalities such as dashboard statistics and chart data.
 */
class PlanController extends Controller
{
    public function index(Request $request){
        $token = $request->token;
        $user = auth()->user();
        if($user){
            if($user->unlimited_conractors == 1){
                return redirect()->route('company/dashboard')->with('info','You are using a Free Plan');
            }
            $subscriptionData = Subscription::where('user_id',$user->id)->first();
            
            $plans = Plan::where('status',1)->get();
            return view('company/plan/index',compact('plans','token','subscriptionData'));
        }else{
            return redirect()->route('login')->with('error','You are not authorized');
        }
    }
    
    public function contractorList(Request $request,$planId){
        $planDetails = Plan::where('id',$planId)->first();
        $contractorLimit = $planDetails->contractor_limit;
        $authUser = auth()->user();
        $contractorList = User::where('company_id',$authUser->id)->where('company_approved_status',1)->get();
        return view('company/plan/contractor_list',compact('contractorList','contractorLimit','planId'));
    }
    
    public function selectContractors(Request $request){
        $authUser = auth()->user();
        $allContractors = User::where('company_id',$authUser->id)->where('is_add_on',0)->get();
        $selectedContractors = $request->contractors ?? [];   
        $userModel = new User();    
        foreach($allContractors as $contractors){
            if(in_array($contractors->id,$selectedContractors)){
                $contractors->company_approved_status = 1;
            }else{
                $contractors->company_approved_status = 2;
            }
            $contractors->save();
       }
       
       return response()->json(['status'=>1, 'message'=>'Contrators Selected','next' => route('plan-select'),
        'plan_id' => $request->plan_id,]);
    }
    
}