@extends('company.layouts.main')
@section('title')
Team Member Update
@endsection
@section('content')

<div class="page-title">
    <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
            <h3>Team Members</h3>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-sm-end">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="company/dashboard" class="pjax">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="company/team-members" class="pjax">Team Members</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Team Member Update</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="card card-default color-palette-box">
    <div class="card-header justify-content-between">
        <h4 class="align-middle d-sm-inline-block">Team Member Update</h4>
    </div>
    <div class="card-body">
        <?= view('company/team_member/_form', compact('model')) ?>
    </div>
</div>

@endsection
