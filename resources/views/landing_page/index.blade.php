@extends('layouts.front')
@section('title')
Home Page
@endsection
@section('content')

<style>
        .star{
        color: red;
    }

    body .bi:before,
    [class^="bi-"]:before,
    [class*=" bi-"]:before {
        vertical-align: baseline !important;
    }
    
</style>
<?php
    $sessionUser = false;
    if (!auth()->guest()) {
        $sessionUser = auth()->user();
        
    }
?>
  
<div id="main" class="layout-horizontal">
    <header class="mb-0">
        <div class="header-top">
            <div class="container">
                <div class="logo">
                    <a>
                        <img src="{{$general->getFileUrl(config('setting.app_logo'),'setting')}}" alt="{{ Config::get('setting.app_name') }}" /> </a>
                </div>
                <div class="header-top-right gap-3">
                    @if($sessionUser)
                        <div class="dropdown">
                            <a href="#" id="topbarUserDropdown" class="user-dropdown d-flex align-items-center dropend dropdown-toggle " data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="avatar avatar-md2">
                                    <img src="{{ $general->getFileUrl($sessionUser->image,'profile') }}" alt="Avatar">
                                </div>
                                <div class="text">
                                    <h6 class="user-dropdown-name">{{$sessionUser->first_name }} {{$sessionUser->last_name}}</h6>
                                    <p class="user-dropdown-status text-sm text-muted d-sm-block d-none">{{$sessionUser->email}}</p>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg" aria-labelledby="topbarUserDropdown">
                                <li> <p class="user-dropdown-status text-sm text-muted d-sm-none d-block px-4 py-2">{{$sessionUser->email}}</p></li>
                                <li><a class="dropdown-item" href="{{ $sessionUser->type == 0 ? 'admin/account/update' : 'account/update' }}">My Account</a></li>

                                <li><a class="dropdown-item" href="{{ $sessionUser->type == 0 ? 'admin/account/password-change' : 'account/password-change' }} ">Settings</a></li>

                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="{{ $sessionUser->type == 0 ? 'admin/auth/logout' : 'logout' }}">Logout</a></li>
                            </ul>
                        </div>
                    @else
                        <div class="login">
                            <a href="login" class="btn btn-outline-primary">Login</a>
                            <a href="account/register" class="btn btn-primary">Sign Up</a>
                        </div>
                    @endif
                    <!-- Burger button responsive -->
                    <a href="#" class="burger-btn d-block d-xl-none">
                        <i class="bi bi-justify fs-3 mt-sm-2 mt-1"></i>
                    </a>
                </div>
            </div>
        </div>

    </header>



    </div>
    @endsection


