<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class UserAuth
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
        $user = Auth::user();
        
        if (Auth::guest()) {
            if ($request->ajax()) {
                return Response::make("unauthorized");
            } 
            else
            {
                session('auth_redirect_url',url()->full());
                return redirect('login')->with('error', 'You are not authorized');
            }
        }else{
            if(session('tfa_verify')){
                return redirect('auth/verify?type=tfa');
            }elseif ($user->isAdmin()) {
                return redirect('/')->with('error', 'You are not authorized');
            }
        }

        return $next($request);
    }
}
