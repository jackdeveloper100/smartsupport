<?php 
namespace App\Services;

use Illuminate\Support\Facades\Auth;

class PermissionService
{

    /**
     * Check if the user has a specific permission.
     *
     * @param string|array $permission
     * @return bool
     */

    public function hasPermission($permission='',$userPermission=''): bool
    {
        if($permission==''){
            $permission=\Route::getCurrentRoute()->uri;
        }

        if (is_array($permission)) {
            foreach ($permission as $p) {
                if ($this->checkPermission($p,$userPermission)) {
                    return true;
                }
            }
            return false;
        }

        return $this->checkPermission($permission,$userPermission);
    }

    /**
     * Check if the user has a specific permission in the permission list.
     *
     * @param string $permission
     * @return bool
     */
    public function checkPermission(string $permission,$userPermission): bool
    {

        if (in_array($permission, $this->getPermissionList())) {
            return in_array($permission, explode(',', $userPermission));
        }
        return true;
    }

    /**
     * Get the list of all permissions.
     *
     * @return array
     */
    public function getPermissionList(): array
    {
        $permissionList = [];
        foreach ($this->getPermissionListData() as $permissionL) {
            $permissionList[] = $permissionL['key'];
            if (isset($permissionL['list']) && $permissionL['list']) {
                foreach ($permissionL['list'] as $permission) {
                    $permissionList[] = $permission['key'];
                }
            }
        }
        return $permissionList;
    }

    /**
     * Get the permission data list.
     *
     * @return array
     */
    public function getPermissionListData(): array
    {
        return [
            [
                'title' => 'User',
                'key' => 'admin_admin',
                'list' => [
                    [
                        'title' => 'List',
                        'key' => 'admin/users',
                    ],
                    [
                        'title' => 'View',
                        'key' => 'admin/user/view',
                    ],
                    [
                        'title' => 'Create',
                        'key' => 'admin/user/create'
                    ],
                    [
                        'title' => 'Update',
                        'key' => 'admin/user/update'
                    ],
                    [
                        'title' => 'Delete',
                        'key' => 'admin/user/delete'
                    ],
                ]
            ],
            [
                'title' => 'Contractor',
                'key' => 'admin_contractor',
                'list' => [
                    [
                        'title' => 'List',
                        'key' => 'admin/contractors',
                    ],
                    [
                        'title' => 'View',
                        'key' => 'admin/contractor/view',
                         'list' => [
                                    [
                                        'title' => 'Login as Contractor',
                                        'key' => 'admin/login-as-contractor',
                                    ],
                                    [
                                        'title' => 'Send Mail',
                                        'key' => 'admin/contractor/document/send-remindermail',
                                    ],
                                ],
                    ],
                    [
                        'title' => 'Create',
                        'key' => 'admin/contractor/create'
                    ],
                    [
                        'title' => 'Update',
                        'key' => 'admin/contractor/update'
                    ],
                    [
                        'title' => 'Delete',
                        'key' => 'admin/contractor/delete'
                    ],
                    [
                        'title' => 'Multiple Delete',
                        'key' => 'admin/contractor/delete_multiple'
                    ],
                    // [
                    //     'title' => 'Export Data',
                    //     'key' => 'admin/contractor/export-data'
                    // ],
                    [
                        'title' => 'Remainder Mail',
                        'key' => 'admin/contractor/send-remindermail'
                    ],
                ]
            ],
            [
                'title' => 'Documents',
                'key' => 'admin_documents',
                'list' => [
                    [
                        'title' => 'List',
                        'key' => 'admin/contractor/document',
                    ],
                    [
                        'title' => 'View',
                        'key' => 'admin/document/view'
                    ],
                    [
                        'title' => 'Change Status',
                        'key' => 'admin/document/change_status'
                    ],
                    // [
                    //     'title' => 'Email',
                    //     'key' => 'admin/contractor/document/send-remindermail'
                    // ],
                ]
            ],
            [
                'title' => 'Company',
                'key' => 'admin_company',
                'list' => [
                    [
                        'title' => 'List',
                        'key' => 'admin/company',
                    ],
                    [
                        'title' => 'View',
                        'key' => 'admin/company/view',
                          'list' => [
                                    [
                                        'title' => 'Login as Company',
                                        'key' => 'company/login-as-company',
                                    ],
                                ],
                    ],
                    [
                        'title' => 'Create',
                        'key' => 'admin/company/create',
                    ],
                    [
                        'title' => 'Update',
                        'key' => 'admin/company/update',
                    ],
                    [
                        'title' => 'Delete',
                        'key' => 'admin/company/delete'
                    ],
                ]
            ],
            [
                'title' => 'Manage Documents',
                'key' => 'admin_documents_type',
                'list' => [
                    [
                        'title' => 'List',
                        'key' => 'admin/setting/document_type',
                    ],
                    [
                        'title' => 'Create',
                        'key' => 'admin/setting/document_type/create',
                    ],
                    [
                        'title' => 'Update',
                        'key' => 'admin/setting/document_type/update',
                    ],
                    [
                        'title' => 'Delete',
                        'key' => 'admin/setting/document_type/delete'
                    ],
                ]
            ],
            [
                'title' => 'Report',
                'key' => 'admin_report',
                'list' => [
                    [
                        'title' => 'Generate',
                        'key' => 'admin/contractor/report',
                    ],
                ]
            ],
            [
                'title' => 'Export Data',
                'key' => 'admin_export_data',
                'list' => [
                    [
                        'title' => 'Export Data',
                        'key' => 'admin/contractors/export-data',
                    ],
                ]
            ],
            [
                'title' => 'Email Template',
                'key' => 'admin_email_template',
                'list' => [
                    [
                        'title' => 'List',
                        'key' => 'admin/email-template',
                    ],
                    [
                        'title' => 'Update',
                        'key' => 'admin/email-template/update'
                    ],
                    // [
                    //     'title' => 'View',
                    //     'key' => 'page/'
                    // ],
                ]
            ],
             [
                'title' => 'Pages',
                'key' => 'admin_page',
                'list' => [
                    [
                        'title' => 'List',
                        'key' => 'admin/page',
                    ],
                    [
                        'title' => 'Update',
                        'key' => 'admin/page/update'
                    ],
                    // [
                    //     'title' => 'View',
                    //     'key' => 'page/'
                    // ],
                ]
            ],
              [
                'title' => 'Subscription',
                'key' => 'admin_company_subscription',
                'list' => [
                    [
                        'title' => 'List',
                        'key' => 'admin/company/subscription',
                    ],
                ]
            ],
            [
                'title' => 'Setting',
                'key' => 'admin_setting',
                'list' => [
                    [
                        'title' => 'Update',
                        'key' => 'admin/setting/update',
                    ],
                ]
            ],
            // [
            //     'title' => 'Home Page',
            //     'key' => 'admin_home_page',
            //     'list' => [
            //         [
            //             'title' => 'Update',
            //             'key' => 'admin/content/update',
            //         ],
            //     ]
            // ],
             [
                'title' => 'Dashboard',
                'key' => 'admin_dashboard',
                'list' => [
                    [
                        'title' => 'view',
                        'key' => 'admin_dashboard',
                    ],
                ]
            ],
        ];
    }
}