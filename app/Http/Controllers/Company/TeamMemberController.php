<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\CompanyPermissionService;
use App\Services\TeamMemberService;
use Illuminate\Support\Facades\Auth;

class TeamMemberController extends Controller
{
    /**
     * @var TeamMemberService
     */
    protected $teamMemberService;

    public function __construct(TeamMemberService $teamMemberService)
    {
        parent::__construct();
        $this->teamMemberService = $teamMemberService;
    }

    /**
     * Display the Team Members index page.
     */
    public function index()
    {
        $redirect = Auth::user() ? Auth::user()->checkCompanyPlanAccess() : redirect('login');
        if ($redirect) {
            return $redirect;
        }

        if (!Auth::user()->hasPermission('company/team-members')) {
            return redirect('company/dashboard')->with('error', 'This feature isn’t available on your current plan. Please upgrade to access it.');
        }
        return view('company/team_member/index');
    }

    /**
     * Get Datatable JSON list of company team members.
     */
    public function list(Request $request)
    {
        return response()->json($this->teamMemberService->getTeamMemberList(Auth::user(), $request->all()));
    }

    /**
     * Show form for creating a new team member.
     */
    public function create()
    {
        $redirect = Auth::user() ? Auth::user()->checkCompanyPlanAccess() : redirect('login');
        if ($redirect) {
            return $redirect;
        }

        if (!Auth::user()->hasPermission('company/team-member/create')) {
            return redirect('company/team-members')->with('error', 'This feature isn’t available on your current plan. Please upgrade to access it.');
        }

        if (!$this->teamMemberService->canCreateTeamMember(Auth::user())) {
            return redirect('company/team-members')->with('error', 'You have reached the maximum limit of 3 team members for your company account.');
        }

        $model = new User();
        $permissionServiceModel = new CompanyPermissionService();
        return view('company/team_member/create', compact('model', 'permissionServiceModel'));
    }

    /**
     * Show form for updating an existing team member.
     */
    public function update(Request $request)
    {
        $redirect = Auth::user() ? Auth::user()->checkCompanyPlanAccess() : redirect('login');
        if ($redirect) {
            return $redirect;
        }

        if (!Auth::user()->hasPermission('company/team-member/update')) {
            return redirect('company/team-members')->with('error', 'This feature isn’t available on your current plan. Please upgrade to access it.');
        }

        $companyId = Auth::user()->getCompanyOwnerId();
        $model = User::where('id', $request->input('id'))
            ->where('company_id', $companyId)
            ->where('type', 2)
            ->first();

        if (!$model) {
            return redirect('company/team-members')->with('error', 'Team member not found.');
        }

        $permissionServiceModel = new CompanyPermissionService();
        return view('company/team_member/update', compact('model', 'permissionServiceModel'));
    }

    /**
     * Save a new team member or update an existing team member.
     */
    public function save(Request $request)
    {
        return response()->json($this->teamMemberService->saveTeamMember(Auth::user(), $request->all()));
    }

    /**
     * Toggle active/inactive status of a team member.
     */
    public function statusSave(Request $request)
    {
        return response()->json($this->teamMemberService->toggleTeamMemberStatus(Auth::user(), $request->input('id')));
    }

    /**
     * Delete a team member.
     */
    public function delete(Request $request)
    {
        return response()->json($this->teamMemberService->deleteTeamMember(Auth::user(), $request->input('id')));
    }
}
