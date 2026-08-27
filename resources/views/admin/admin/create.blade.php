@extends('admin.layouts.main')
@section('title')
User Create
@endsection
@section('content')


<div class="page-title">
    <div class="row">
        <div class="col-12 col-md-4 order-md-1 order-last">
            <h3>User Create</h3>
        </div>
        <div class="col-12 col-md-8 order-md-2 order-first">
            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-sm-end">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="admin/dashboard" class="pjax">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="admin/users" class="pjax">Users</a></li>
                    <li class="breadcrumb-item active" aria-current="page">User Create</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="card card-default color-palette-box" style="display: flow;">
<div class="card-header justify-content-between">
        <h4 class="align-middle d-sm-inline-block">User Create</h4>
    </div>
  <div class="card-body">
    <?= view('admin/admin/_form',compact('model','permissionServiceModel')) ?>
  </div>
</div>

@endsection