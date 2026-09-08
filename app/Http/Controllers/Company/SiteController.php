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
    $redirect = $user ? $user->checkCompanyPlanAccess() : redirect('login');
    if ($redirect) {
        return $redirect;
    }

    $effectiveCompanyId = $user->getCompanyOwnerId();
    $documentTypeData = DocumentType::where('is_hidden', 0)->get();
    $status = isset($_GET['status']) ? $_GET['status'] : 'Active';
    $allowedStatuses = ['Active', 'Expiring Soon', 'Expired'];
    if (!in_array($status, $allowedStatuses)) {
        abort(404); // Show 404 Page
    }
    $authId = $effectiveCompanyId;
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
}

    
    public function expirationalList(Request $request){
        $authId = auth()->user()->getCompanyOwnerId();
        return response()->json((new Document())->expirationalList($request->all(),$authId));
    }
    
    public function support(){
       $user = auth()->user();
       $effectiveCompanyId = $user->getCompanyOwnerId();
       $companyPlanId = (new User())->getCompanyPlanInfo($effectiveCompanyId);
       return view('company/support/index',compact('companyPlanId','user'));
    }
}