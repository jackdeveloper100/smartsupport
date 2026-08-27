<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Admin\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Document;
use App\Models\DocumentType;
use App\Services\GeneralService;
use App\Helpers\DocumentHelper;

/**
 * Class SiteController
 * 
 * Controller for handling admin site functionalities such as dashboard statistics and chart data.
 */
class SiteController extends Controller
{
    /**
     * Display the admin dashboard with user statistics.
     *
     * @return \Illuminate\View\View
     */
 public function dashboard(Request $request)
{   
    if (!session()->has('theme')) {
        session(['theme' => 'light']);
    }
    if ($request->session()->has('selected_plan')) {
            $plan = $request->session()->pull('selected_plan');
            return redirect()->route('plan-select', ['plan_id' => $plan]);
        }
    $user = auth()->user();

    // If unlimited_conractors is 1, skip subscription check
    if ($user->unlimited_conractors == 1 || 
        (Subscription::where('user_id', $user->id)->value('status') === 'active')) {
        
        $documentTypeData = DocumentType::where('is_hidden', 0)->get();
        $status = isset($_GET['status']) ? $_GET['status'] : 'Active';
        // dd($status);
        $allowedStatuses = ['Active', 'Expiring Soon', 'Expired'];
        if (!in_array($status, $allowedStatuses)) {
            abort(404); // Show 404 Page
        }
            $authId = auth()->id();
                $totalUser = User::where('type', 1)->where('company_id', $authId)->count();

                $dashboardStats     = DocumentHelper::getCompanyDashboardDocumentStats($authId);
                $activeUser         = $dashboardStats['activeUser'];
                $deactiveUser       = $dashboardStats['deactiveUser'];
                $expiredUser        = $dashboardStats['expiredUser'];
                $compliancePercentage = $dashboardStats['compliancePercentage'];
                $expiringPercentage = $dashboardStats['expiringPercentage'];
                $expiredPercentage  = $dashboardStats['expiredPercentage'];

        return view('company.site.dashboard', compact(
            'totalUser',
            'activeUser',
            'deactiveUser',
            'expiredUser',
            'compliancePercentage',
            'expiringPercentage',
            'expiredPercentage',
            'documentTypeData',
            'status'
        ));
    
    } else {
        return redirect()->route('company/plan')->with('error', 'Your plan was expired, please upgrade your plan');
    }
}

    
    public function expirationalList(Request $request){
        $authId = auth()->id();
        return response()->json((new Document())->expirationalList($request->all(),$authId));
    }
    
    public function support(){
       $user = auth()->user();
       $companyPlanId = (new User())->getCompanyPlanInfo($user->id);
       return view('company/support/index',compact('companyPlanId','user'));
    }
}