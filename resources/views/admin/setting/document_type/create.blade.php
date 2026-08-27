@extends('admin.layouts.main')
@section('title')
Documents Type Create
@endsection
@section('content')
<div class="page-title">
    <div class="row">
        <div class="col-12 col-md-4 order-md-1 order-last">
            <h3>Documents Type</h3>
        </div>
        <div class="col-12 col-md-8 order-md-2 order-first">
            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-sm-end">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="admin/dashboard" class="pjax">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="admin/setting/document_type" class="pjax">Documents Type</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Documents Type Create</li>
                </ol>
            </nav>
        </div>
    </div>
</div>


<div class="card card-default color-palette-box">
    <div class="card-header justify-content-between">
        <h4 class="align-middle d-sm-inline-block">Documents Type Create</h4>
    </div>
    <div class="card-body">
        <?= view('admin/setting/document_type/_form') ?>
    </div>
</div>

@endsection