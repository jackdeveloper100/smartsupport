<?php

namespace App\Services;

use App\Models\User;
use App\Models\EmailQueue;
use App\Models\EmailTemplate;
use App\Services\AuthService;
use App\Helpers\General;
use App\Helpers\Pagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TeamMemberService
{
    /**
     * Check if company owner can create an additional team member.
     * Enforces maximum 2 additional sub-users (total 3 including owner).
     */
    public function canCreateTeamMember(User $sessionUser): bool
    {
        $companyOwnerId = $sessionUser->getCompanyOwnerId();
        $currentSubUsersCount = User::where('company_id', $companyOwnerId)
            ->where('type', 2)
            ->where('id', '!=', $companyOwnerId)
            ->count();

        return $currentSubUsersCount < 2;
    }

    /**
     * Get DataTables JSON array format for team members.
     */
    public function getTeamMemberList(User $sessionUser, array $postData): array
    {
        if (!$sessionUser->hasPermission('company/team-members')) {
            return [
                'draw' => (int)($postData['draw'] ?? 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'status' => 0,
                'message' => 'This feature isn’t available on your current plan. Please upgrade to access it.'
            ];
        }

        $companyOwnerId = $sessionUser->getCompanyOwnerId();
        $query = DB::table('user')
            ->where('type', 2)
            ->where(function ($q) use ($companyOwnerId) {
                $q->where('company_id', $companyOwnerId)
                  ->orWhere('id', $companyOwnerId);
            });

        $searchText = $postData['search']['value'] ?? '';

        if (strlen($searchText) > 2) {
            $query->where(function ($q) use ($searchText) {
                $q->where('first_name', 'like', '%' . $searchText . '%')
                  ->orWhere('last_name', 'like', '%' . $searchText . '%')
                  ->orWhere('email', 'like', '%' . $searchText . '%');
            });
        }

        $result = (new Pagination())->getDataTable($query, $postData);

        foreach ($result['data'] as $key => $row) {
            $name = trim($row->first_name . ' ' . $row->last_name);
            $formattedName = $name ?: ($row->company_name ?: $row->email);
            $result['data'][$key]->first_name = $formattedName;
            $result['data'][$key]->name = $formattedName;

            $isOwner = ($row->id == $companyOwnerId);
            if ($isOwner) {
                $result['data'][$key]->status_badge = '<span class="badge bg-light">Main Account</span>';
                $result['data'][$key]->actions = '<span class="badge bg-light text-dark border">Owner</span>';
            } else {
                $result['data'][$key]->status_badge = ($row->status == 1)
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>';

                $actions = '<div class="act-btns d-flex align-items-center">';
                if ($sessionUser->hasPermission('company/team-member/update')) {
                    $actions .= '<a href="company/team-member/update?id=' . $row->id . '" class="text-body pjax tool-btn me-2"><i class="bi bi-pencil-square"><span class="tooltip-text">Edit</span></i></a>';
                }
                if ($sessionUser->hasPermission('company/team-member/status-save')) {
                    $statusText = ($row->status == 1) ? 'Deactivate' : 'Activate';
                    $toggleIcon = ($row->status == 1) ? 'bi-person-x-fill' : 'bi-person-check-fill';
                    $confirmTitle = ($row->status == 1) ? 'Deactivate Team Member?' : 'Activate Team Member?';
                    $confirmText = ($row->status == 1)
                        ? 'Are you sure you want to deactivate this team member?'
                        : 'Are you sure you want to activate this team member?';
                    $confirmBtn = ($row->status == 1) ? 'Yes, Deactivate' : 'Yes, Activate';

                    $actions .= '<button style="border:none; background:none;" onclick="app.confirmCustomAction(this);" data-action="company/team-member/status-save" data-id="' . $row->id . '" data-title="' . htmlspecialchars($confirmTitle, ENT_QUOTES) . '" data-text="' . htmlspecialchars($confirmText, ENT_QUOTES) . '" data-confirm-btn="' . htmlspecialchars($confirmBtn, ENT_QUOTES) . '" class="text-body tool-btn"><i class="bi ' . $toggleIcon . '"><span class="tooltip-text">' . $statusText . '</span></i></button>';
                }
                if ($sessionUser->hasPermission('company/team-member/delete')) {
                    $actions .= '<button style="border:none; background:none;" onclick="app.confirmCustomAction(this);" data-action="company/team-member/delete" data-id="' . $row->id . '" data-title="Delete Team Member?" data-text="Are you sure you want to delete this team member? This action cannot be undone." data-confirm-btn="Yes, Delete" class="text-body tool-btn me-2 dlt-lnk"><i class="bi bi-trash-fill"><span class="tooltip-text">Delete</span></i></button>';
                }
                $actions .= '</div>';

                $result['data'][$key]->actions = $actions;
            }
        }

        return $result;
    }

    /**
     * Create or update a team member record.
     */
    public function saveTeamMember(User $companyUser, array $postData): array
    {
        $id = $postData['id'] ?? null;
        $permKey = $id ? 'company/team-member/update' : 'company/team-member/create';

        if (!$companyUser->hasPermission($permKey)) {
            return [
                'status' => 0,
                'message' => 'This feature isn’t available on your current plan. Please upgrade to access it.'
            ];
        }

        $companyId = $companyUser->getCompanyOwnerId();

        // Enforce 3-user maximum limit on new team member creation
        if (!$id) {
            $totalUsers = User::where('company_id', $companyId)->where('type', 2)->count();
            if ($totalUsers >= 3) {
                return [
                    'status' => 0,
                    'message' => 'You have reached the maximum limit of 3 team members for your company account.'
                ];
            }
        }

        $companyOwnerId = $companyUser->getCompanyOwnerId();
        $isCompanyOwner = ($companyUser->id == $companyOwnerId);
        $isSelfEdit = ($id && $id == $companyUser->id);

        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:user,email,' . $id,
        ];
        if (!$isSelfEdit || $isCompanyOwner) {
            $rules['permission'] = 'required|array';
        }

        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) {
            return [
                'status' => 0,
                'message' => $validator->errors()->first()
            ];
        }

        $isNew = !$id;
        $user = $id
            ? User::where('id', $id)->where('company_id', $companyId)->first()
            : new User();

        if (!$user) {
            return ['status' => 0, 'message' => 'Team member not found.'];
        }

        $general = new General();

        // Handle profile image upload
        if (isset($postData['image']) && $postData['image']->isValid()) {
            $uploadResult = $general->uploadFile($postData['image'], 'profile');
            if (!$uploadResult['status']) {
                return $uploadResult;
            }
            $image = $uploadResult['file_name'];
            if ($image) {
                if ($user->image) {
                    $general->deleteFile($user->image, 'profile');
                }
                $user->image = $image;
            }
        }

        $user->first_name = $postData['first_name'];
        $user->last_name = $postData['last_name'] ?? '';
        $user->email = $postData['email'];
        $user->company_id = $companyId;
        $user->type = 2;
        $user->status = isset($postData['status']) ? (int)$postData['status'] : 1;

        if ($isSelfEdit && !$isCompanyOwner) {
            $permissionArray = explode(',', $user->permission);
        } else {
            $permissionArray = $postData['permission'] ?? [];
        }
        $user->permission = implode(',', array_filter($permissionArray));
        $user->registered_ip = $general->getClientIp();

        if ($isNew) {
            $token = Str::random(40);
            $user->invite_token = $token;
            $user->invite_token_created_at = time();
            $user->password = (new AuthService())->encryptPassword(Str::random(16));
            $user->save();

            $setupUrl = route('company/auth/setup-password', ['token' => $token]);
            $companyName = $companyUser->company_name ?: (trim($companyUser->first_name . ' ' . $companyUser->last_name) ?: 'Company Account');
            $userName = trim($user->first_name . ' ' . $user->last_name) ?: $user->email;

            $templateParams = [
                'name' => $userName,
                'email' => $user->email,
                'company_name' => $companyName,
                'password_setup_url' => $setupUrl,
                'url' => $setupUrl,
                'password' => 'Set up via link below'
            ];

            $templateData = (new EmailTemplate())->getEmailTemplate('team_invite', $templateParams);

            if (!empty($templateData) && is_array($templateData)) {
                $subject = $templateData['subject'];
                $emailBody = view('email/template', ['body' => $templateData['body'], 'company' => $companyName])->render();
            } else {
                $rawBody = '<p>Hello <strong>' . htmlspecialchars($userName) . '</strong>,</p>';
                $rawBody .= '<p>You have been invited to join <strong>' . htmlspecialchars($companyName) . '</strong> on Smart Support.</p>';
                $rawBody .= '<p>Please click the button below to set up your account password and log in:</p>';
                $rawBody .= '<p><a href="' . $setupUrl . '" style="display:inline-block; padding:10px 20px; background-color:#2563eb; color:#ffffff; text-decoration:none; border-radius:5px; font-weight:bold;">Set Up Password</a></p>';
                $rawBody .= '<p>Or copy and paste this link into your browser:<br><a href="' . $setupUrl . '">' . $setupUrl . '</a></p>';
                $subject = 'Invitation to join ' . $companyName;
                $emailBody = view('email/template', ['body' => $rawBody, 'company' => $companyName])->render();
            }

            $emailBody = str_replace(
                ['http://https://', 'https://https://', 'http://https//', 'https://https//', 'http://http://'],
                'https://',
                $emailBody
            );

            $emailQueue = new EmailQueue();
            $emailQueue->email_to = $user->email;
            $emailQueue->email_subject = $subject;
            $emailQueue->email_body = $emailBody;
            $emailQueue->is_sent = 0;
            $emailQueue->save();

            $message = 'Team member invited successfully';
        } else {
            $user->save();
            $message = 'Team member updated successfully.';
        }

        return [
            'status' => 1,
            'message' => $message,
            'next' => 'load',
            'url' => 'company/team-members'
        ];
    }

    /**
     * Toggle active/inactive status of a team member.
     */
    public function toggleTeamMemberStatus(User $sessionUser, $id): array
    {
        if (!$sessionUser->hasPermission('company/team-member/status-save')) {
            return [
                'status' => 0,
                'message' => 'This feature isn’t available on your current plan. Please upgrade to access it.'
            ];
        }

        if ($sessionUser->id == $id && $sessionUser->id != $sessionUser->getCompanyOwnerId()) {
            return ['status' => 0, 'message' => 'You cannot deactivate your own account.'];
        }

        $companyId = $sessionUser->getCompanyOwnerId();
        $user = User::where('id', $id)
            ->where('company_id', $companyId)
            ->where('type', 2)
            ->first();

        if (!$user) {
            return ['status' => 0, 'message' => 'Team member not found.'];
        }

        $user->status = $user->status == 1 ? 0 : 1;
        $user->save();

        $statusMessage = $user->status == 1 ? 'Team member activated successfully.' : 'Team member deactivated successfully.';

        return [
            'status' => 1,
            'message' => $statusMessage,
            'next' => 'load',
            'url' => 'company/team-members'
        ];
    }

    /**
     * Delete a team member.
     */
    public function deleteTeamMember(User $sessionUser, $id): array
    {
        if (!$sessionUser->hasPermission('company/team-member/delete')) {
            return [
                'status' => 0,
                'message' => 'This feature isn’t available on your current plan. Please upgrade to access it.'
            ];
        }

        if ($sessionUser->id == $id && $sessionUser->id != $sessionUser->getCompanyOwnerId()) {
            return ['status' => 0, 'message' => 'You cannot delete your own account.'];
        }

        $companyId = $sessionUser->getCompanyOwnerId();
        $user = User::where('id', $id)
            ->where('company_id', $companyId)
            ->where('type', 2)
            ->first();

        if (!$user) {
            return ['status' => 0, 'message' => 'Team member not found.'];
        }

        $user->delete();

        return [
            'status' => 1,
            'message' => 'Team member removed successfully.',
            'next' => 'load',
            'url' => 'company/team-members'
        ];
    }
}
