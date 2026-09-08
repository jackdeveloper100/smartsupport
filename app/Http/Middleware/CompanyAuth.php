<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

class CompanyAuth
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string|null $guard
     * @return mixed
     */
    
     public function handle($request, Closure $next, $guard = null)
     {
         $auth = Auth::guard();
         if ($auth->guest()) {
             if ($request->pjax() || $request->ajax() || $request->wantsJson()) {
                 return response()->json([
                     'status' => 0,
                     'message' => 'You are Not Authorized',
                     'redirect' => url('login')
                 ], 401)->header('X-PJAX-URL', url('login'));
             } else {
                 $request->session()->put('auth_redirect_url', url()->full());
                 return redirect('login')->with('error', 'You are Not Authorized');
             }
         } else {
             $user = Auth::user();
             if (!$user->isCompany() || $user->status == 0) {
                 Auth::logout();
                 session()->flash('error', 'Your account has been deactivated. Please contact your company administrator.');
                 
                 if ($request->pjax() || $request->ajax() || $request->wantsJson()) {
                     return response()->json([
                         'status' => 0,
                         'message' => 'Your account has been deactivated. Please contact your company administrator.',
                         'redirect' => url('login')
                     ], 401)->header('X-PJAX-URL', url('login'));
                 } else {
                     return redirect('login');
                 }
             }
             if(session('tfa_verify')){
                 if ($request->ajax()) {
                     return Response::make("unauthorized");
                 } else {
                     return redirect('company/auth/verify?type=tfa');
                 }
             }
             if(!$user->hasPermission()){
                 if ($request->ajax() || $request->wantsJson()) {
                     return response()->json([
                         'draw' => (int)$request->input('draw', 1),
                         'recordsTotal' => 0,
                         'recordsFiltered' => 0,
                         'data' => [],
                         'status' => 0,
                         'message' => 'This feature isn’t available on your current plan. Please upgrade to access it.'
                     ], 200);
                 } else {
                     return redirect('company/dashboard')->with('error', 'You are Not Authorized');
                 }    
             }
         }
         return $next($request);
     }
}
