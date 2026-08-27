<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

class AdminAuth
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
         $auth =Auth::guard();
         if ($auth->guest()) {
             if ($request->ajax()) {
                 return Response::make("unauthorized");
             } else {
                 $request->session()->put('auth_redirect_url', url()->full());
                 return redirect('admin/login')->with('error', 'You are not authorized');
             }
         }else{
             $user=Auth::user();
             if(!$user->isAdmin()){
                 if ($request->ajax()) {
                     return Response::make("unauthorized");
                 } else {
                     return redirect('/')->with('error', 'You are not authorized');
                 }
             }
             if(session('tfa_verify')){
                 if ($request->ajax()) {
                     return Response::make("unauthorized");
                 } else {
                    // dd(22);
                     return redirect('admin/auth/tfa-verify?type=tfa');
                 }
             }
             if(!$user->hasPermission()){
                 if ($request->ajax()) {
                     return Response::make("unauthorized");
                 } else {
                     return redirect('admin/dashboard')->with('error', 'You are not authorized');
                 }    
             }
         }
         return $next($request);
     }
}