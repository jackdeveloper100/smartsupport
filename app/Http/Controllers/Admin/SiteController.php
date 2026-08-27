<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Plan;
use App\Services\GeneralService;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Invoice;
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
    public function dashboard()
    {   
            if (!session()->has('theme')) {
                session(['theme' => 'light']);
            }

            $totalUser = User::where('type', 1)->count();

            // $documentData = Document::whereHas('documentType', function($q){
            //     $q->where('is_hidden', 0);
            // })->count();

            // $activeUser = Document::where('status', 5)->count();
            //$deactiveUser = Document::where('status', 2)->count();

            // if ($documentData > 0) {
            //     $compliancePercentage = round(($activeUser / $documentData) * 100);
            // } else {
            //     $compliancePercentage = 0;
            // }

            // if ($documentData > 0) {
            //     $expiringPercentage = round(($deactiveUser / $documentData) * 100);
            // } else {
            //     $expiringPercentage = 0;
            // }

            // Only expired stats are taken from helper side as requested.
            $dashboardStats = DocumentHelper::getAdminDashboardDocumentStats();
            $activeUser = $dashboardStats['activeUser'];
            $compliancePercentage = $dashboardStats['compliancePercentage'];
            $expiredUser = $dashboardStats['expiredUser'];
            $expiredPercentage = $dashboardStats['expiredPercentage'];
            $deactiveUser = $dashboardStats['deactiveUser'];
            $expiringPercentage = $dashboardStats['deactivePercentage'];
            
            $documentTypeData = DocumentHelper::getAdminAllowedDocumentTypes();
            //$documentTypeData = DocumentType::all();
            $company = User::where('type',2)->get();
            
            $totalCompany = User::where('type',2)->count();
            $activeCompany = User::where('type',2)->where('status',1)->count();
            $inactiveCompany = User::where('type',2)->where('status',0)->count();
            
            // $totalPayments = $this->getTotalBalance();

            // $totalPayments = (new Subscription())->getTotalPayment();
            
            return view('admin.site.dashboard', compact('totalUser', 'activeUser', 'deactiveUser','expiredUser','compliancePercentage','expiringPercentage','expiredPercentage','documentTypeData','company'
            ,'totalCompany','activeCompany','inactiveCompany'));
    }

      /**
     * Get a list of contractors.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function dashboardlist(Request $request)
    {
        return response()->json((new User())->dashboardList($request->all()));
    }


      /**
     * Get a list of expirationals document.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function expirationalList(Request $request)
    {
        return response()->json((new Document())->expirationalList($request->all()));
    }

    /**
     * Get user chart data based on the selected duration.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChartUser(Request $request)
    {
        $duration = $request->input('type');
        $userChartData = [];

        switch ($duration) {
            case 'day':
                $userChartData = (new GeneralService())->getUserLast7DaysChartData();
                break;
            case 'month':
                $userChartData = (new GeneralService())->getUserLast6MonthsChartData();
                break;
            default:    
                $userChartData = (new GeneralService())->getUserMonthlyChartData();
                break;
        }

        return response()->json($userChartData);
    }

    /**
     * Display the user chart view.
     *
     * @return \Illuminate\View\View
     */
    public function getChartUser2()
    {
        return view("admin.site.userchart");
    }
    
    public function page(Request $request)
    {
        $page = Page::where('slug', $request->slug)->firstOrFail();
        return view('front.page', compact('page'));
    }
    

    public function getTotalBalance() {
    try {
        $stripeSecretKey = config('setting.stripe_secret_key');
        \Stripe\Stripe::setApiKey($stripeSecretKey);

        $stripe = new \Stripe\StripeClient($stripeSecretKey);

        // Keep your balance retrieval code
        $balance = $stripe->balance->retrieve();

        $result = ['available' => [], 'pending' => []];

        foreach ($balance->available as $available) {
            $result['available'][] = [
                'amount' => $available->amount / 100,
                'currency' => strtoupper($available->currency),
            ];
        }

        foreach ($balance->pending as $pending) {
            $result['pending'][] = [
                'amount' => $pending->amount / 100,
                'currency' => strtoupper($pending->currency),
            ];
        }

        $total = 0;
        $lastChargeId = null;
        $hasMore = true;

        while ($hasMore) {
            $params = ['limit' => 100];
            if ($lastChargeId) {
                $params['starting_after'] = $lastChargeId;
            }

            $charges = $stripe->charges->all($params);

            foreach ($charges->data as $charge) {
                if ($charge->paid && !$charge->refunded) {
                    $total += $charge->amount;
                }
            }

            $hasMore = $charges->has_more;

            if ($hasMore) {
                $lastChargeId = end($charges->data)->id;
            }
        }

        // Return the total amount received (in dollars)
        return $total / 100;

    } catch (\Exception $e) {
        \Log::error('Stripe Balance API Error: ' . $e->getMessage());
        return 0;
    }
}

public function getTotalReceivedPayment()
{
    try {
        $total = $this->getTotalBalance(); 
        return response()->json(['success' => true,'amount' => $total,]);
        
    } catch (\Exception $e) {
        
        \Log::error('Stripe Payment Fetch Error: ' . $e->getMessage());
        return response()->json(['success' => false,'message' => 'Failed to fetch payment',
        ]);
    }
}



}
