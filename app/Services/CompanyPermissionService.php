<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class CompanyPermissionService
{
    /**
     * Check if the authenticated company user or sub-user has a specific permission.
     *
     * @param string|array $permission
     * @param string $userPermission
     * @return bool
     */
    public function hasPermission($permission = '', $userPermission = ''): bool
    {
        // Company main owner (company_id matches user id or parent user) has full access
        $user = Auth::user();
        if ($user && empty($user->permission) && (int)$user->type === 2) {
            return true;
        }

        if (empty($userPermission) && $user) {
            $userPermission = $user->permission;
        }

        if ($permission == '') {
            $permission = Route::getCurrentRoute()->getName() ?? Route::getCurrentRoute()->uri;
        }

        if (is_array($permission)) {
            foreach ($permission as $p) {
                if ($this->checkPermission($p, $userPermission)) {
                    return true;
                }
            }
            return false;
        }

        return $this->checkPermission($permission, $userPermission);
    }

    /**
     * Check if a specific permission key exists in the granted user permissions list.
     *
     * @param string $permission
     * @param string|null $userPermission
     * @return bool
     */
    public function checkPermission(string $permission, ?string $userPermission): bool
    {
        if (empty($userPermission)) {
            return false;
        }

        if (in_array($permission, $this->getPermissionList())) {
            return in_array($permission, explode(',', $userPermission));
        }

        return true;
    }

    /**
     * Get flat array of all company permission keys.
     *
     * @return array
     */
    public function getPermissionList(): array
    {
        $permissionList = [];
        foreach ($this->getPermissionListData() as $group) {
            $permissionList[] = $group['key'];
            if (isset($group['list']) && is_array($group['list'])) {
                foreach ($group['list'] as $item) {
                    $permissionList[] = $item['key'];
                    if (isset($item['list']) && is_array($item['list'])) {
                        foreach ($item['list'] as $subItem) {
                            $permissionList[] = $subItem['key'];
                        }
                    }
                }
            }
        }
        return array_unique($permissionList);
    }

    /**
     * Get structured permission hierarchy for company sub-users.
     *
     * @return array
     */
    public function getPermissionListData(): array
    {
        return [
            [
                'title' => 'Contractors',
                'key' => 'company_contractors',
                'list' => [
                    ['title' => 'View List', 'key' => 'company/contractor'],
                    ['title' => 'View Details', 'key' => 'company/contractor/view'],
                    ['title' => 'Create Contractor', 'key' => 'company/contractor/create'],
                    ['title' => 'Edit Contractor', 'key' => 'company/contractor/update'],
                    ['title' => 'Delete Contractor', 'key' => 'company/contractor/delete'],
                    ['title' => 'Login As Contractor', 'key' => 'company/login-as-contractor'],
                    ['title' => 'Export Contractor Data', 'key' => 'company/contractors/export-data'],
                ]
            ],
            [
                'title' => 'Documents',
                'key' => 'company_documents',
                'list' => [
                    ['title' => 'View Documents List', 'key' => 'company/contractor/document'],
                    ['title' => 'View Document Details', 'key' => 'company/document/view'],
                    ['title' => 'Approve / Reject Status', 'key' => 'company/document/change_status'],
                    ['title' => 'Send Expiration Reminder', 'key' => 'company/contractor/document/send-remindermail'],
                ]
            ],
            [
                'title' => 'Vendor W-9 Requests',
                'key' => 'company_vendors',
                'list' => [
                    ['title' => 'View Vendor W-9 Requests', 'key' => 'company/request'],
                    ['title' => 'View Vendor W-9s Received', 'key' => 'company/w9/request/received'],
                    ['title' => 'Send W-9 Request Email', 'key' => 'company/vendor/send_mail'],
                ]
            ],
            [
                'title' => 'Reports',
                'key' => 'company_reports',
                'list' => [
                    ['title' => 'Generate & Download Reports', 'key' => 'company/report'],
                ]
            ],
            [
                'title' => 'Team Members',
                'key' => 'company_team_members',
                'list' => [
                    ['title' => 'View Team Members', 'key' => 'company/team-members'],
                    ['title' => 'Invite Team Member', 'key' => 'company/team-member/create'],
                    ['title' => 'Edit Team Member', 'key' => 'company/team-member/update'],
                    ['title' => 'Toggle Active Status', 'key' => 'company/team-member/status-save'],
                    ['title' => 'Delete Team Member', 'key' => 'company/team-member/delete'],
                ]
            ],
            [
                'title' => 'Plans',
                'key' => 'company_plans',
                'list' => [
                    ['title' => 'View & Select Plans', 'key' => 'company/plan'],
                ]
            ],
        ];
    }
}
