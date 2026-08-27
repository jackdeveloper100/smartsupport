<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Log;
use App\Services\PermissionService;
use App\Models\Device;


class AdminController extends Controller
{
    /**
     * Display the admin dashboard index page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin/admin/index');
    }

    /**
     * List the admins based on the request data.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        return response()->json((new User())->listAdmin($request->all()));
    }

    /**
     * Show the form for creating a new admin.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $model = new User();
        $permissionServiceModel = new PermissionService();
        return view('admin/admin/create', compact('model','permissionServiceModel'));
    }

    /**
     * Show the form for updating an existing admin.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function update(Request $request)
    {
        $model = User::find($request->input('id'));
        $permissionServiceModel = new PermissionService();
        if (!$model) {
            return redirect('admin/admin')->withError('error', 'No data found');
        }
        return view('admin/admin/update', compact('model','permissionServiceModel'));
    }

    /**
     * Save a new admin or update an existing admin.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request)
    {
        return response()->json((new User())->storeAdmin($request->all()));
    }

    /**
     * Delete an existing admin.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function delete(Request $request)
    {
        $model = User::find($request->input('id'));
        if (!$model) {
            if ($request->ajax()) {
                return response()->json(['status' => 0, 'message' => 'No data found']);
            } else {
                return redirect('admin/contractor')->withError('error', 'No data found');
            }
        }

        $model->delete();

        if ($request->ajax()) {
            return response()->json(['status' => 1, 'message' => 'User Removed successfully.', 'next' => 'load', 'url' => 'admin/users']);
        } else {
            return redirect()->route('admin/users')->with('success', 'User Remove Successfully.');
        }
    }

    /**
     * View details of a specific admin.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function view(Request $request)
    {
        $id = $request->input('id');
        $logData = Log::where('user_id', $id)
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();
        $deviceData = Device::where('user_id', $id)
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();
        $model = User::where('id', $id)->first();

        return view('admin/admin/view', compact('model', 'logData', 'deviceData'));
    }
}
    