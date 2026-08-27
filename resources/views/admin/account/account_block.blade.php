<?php
$model = auth()->user();

?>
<!-- Header -->

<div class="section">
    <div class="row">
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-center align-items-center flex-column">
                        <div class="avatar avatar-2xl">
                            <img src="{{ $general->getFileUrl($model->image,'profile') }}" alt="Avatar">
                            <fa-camera style="position: absolute;top: 4px;right: 0; left: 80px; background-color: #7367f0; padding: 1px 24px 5px 3px; border-radius: 70%; color: white;"><i class="fa fa-camera" onclick="app.showModalView('admin/account/image')">
                            </i> <fa-camera>
                        </div>

                        <h3 class="mt-3">{{ $model->first_name }} {{ $model->last_name }}</h3>
                        <p class="text-small">{{ $model->email }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-body">
                    <form action="admin/account/save" method="post" class="ajax-form">
                    {{ csrf_field() }}
                            <div class="form-group">
                                <label for="name" class="form-label">First Name</label>
                                <input type="text" name="first_name" id="first_name" class="form-control" placeholder="First Name" value="{{ $model->first_name }}">
                            </div>  
                            <div class="form-group">
                                <label for="name" class="form-label">First Name</label>
                                <input type="text" name="last_name" id="last_name" class="form-control" placeholder="Last Name" value="{{ $model->last_name }}">
                            </div>
                            <div class="form-group">
                                <label for="name" class="form-label">Email</label>
                                <input type="text" name="email" id="email" class="form-control" placeholder="Email" value="{{ $model->email }}">
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="user-profile-header d-flex flex-column flex-sm-row text-sm-start text-center mb-4">
                <div class="flex-shrink-0 mt-n2 mx-sm-0" style="position: relative;">
                    <img src="{{ $general->getFileUrl($model->image,'profile') }}" class="fas fa-edit" alt="image" height="100px" width="100px" style="border-radius: 100%;">
                    <fa-camera style="position: absolute;top: 4px;right: 0; left: 80px; background-color: #7367f0; padding: 1px 24px 5px 3px; border-radius: 70%; color: white;"><i class="fa fa-camera" onclick="app.showModalView('admin/account/image')">
                        </i> <fa-camera>
                </div>
                <div class="flex-grow-1 mt-3 mt-sm-5">
                    <div class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-4 flex-md-row flex-column gap-4">
                        <div class="user-profile-info">
                            <h4>{{ $model->first_name }} {{ $model->last_name }}</h4>
                            <ul class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-2">
                                <li class="list-inline-item"><i class="fa fa-mail"></i> {{ $model->email }}</li>
                            </ul>
                        </div>
                        <!-- <a onclick="app.showModalView('admin/account/image')" class=" noroute btn btn-primary text-white" style="background-color:#685dd8;">
                            <i class="fa fa-photo me-1"></i>Account
                        </a> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--/ Header -->

<!-- Navbar pills -->
<div class="row">
    <div class="col-md-12">
        <ul class="nav nav-pills flex-column flex-sm-row mb-4">
            <li class="nav-item">
                <a class="nav-link pjax {{ $general->routeMatchClass('admin/account/update')}}" href="{{ route('admin/account/update') }}"><i class="ti-xs fa fa-user-check me-1"></i> Account</a>
            </li>
            <li class="nav-item">
                <a class="nav-link pjax {{ $general->routeMatchClass('admin/account/password-change')}}" href="{{ route('admin/account/password-change') }}"><i class="ti-xs fa fa-key me-1"></i> Password Change</a>
            </li>
            <li class="nav-item">
                <a class="nav-link pjax {{ $general->routeMatchClass('admin/account/tfa')}}" href="{{ route('admin/account/tfa') }}"><i class="ti-xs fa fa-lock me-1"></i> Two Factor Authentication</a>
            </li>
            <li class="nav-item">
                <a class="nav-link pjax {{ $general->routeMatchClass('admin/account/device')}}" href="{{ route('admin/account/device') }}"><i class="ti-xs fa fa-bell me-1"></i> Device</a>
            </li>
            <li class="nav-item">
                <a class="nav-link pjax {{ $general->routeMatchClass('admin/account/log')}} " href="{{ route('admin/account/log') }}"><i class="ti-xs fa fa-link me-1"></i> Log</a>
            </li>
        </ul>
    </div>
</div>