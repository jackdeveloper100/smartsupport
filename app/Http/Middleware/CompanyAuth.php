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
         $auth =Auth::guard();
         if ($auth->guest()) {
             if ($request->ajax()) {
                 return Response::make("unauthorized");
             } else {
                 $request->session()->put('auth_redirect_url', url()->full());
                 return redirect('login')->with('error', 'You are Not Authorized');
             }
         }else{
             $user=Auth::user();
             if(!$user->isCompany()){
                 if ($request->ajax()) {
                     return Response::make("unauthorized");
                 } else {
                     return redirect('/')->with('error', 'You are Not Authorized');
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
                 if ($request->ajax()) {
                     return Response::make("unauthorized");
                 } else {
                     return redirect('company/dashboard')->with('error', 'You are Not Authorized');
                 }    
             }
         }
         return $next($request);
     }
}
