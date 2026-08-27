@extends('admin.layouts.main')
@section('title')
Seo Meta Update
@endsection
@section('content')

<div class="page-title">
    <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
            <h3>Seo Meta</h3>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="admin/dashboard" class="pjax">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="admin/seo/meta" class="pjax">Seo Meta</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Seo Meta Update</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="section">
    <div class="card ">
        <div class="card-header ">
            <h4 class="acard-title">Seo Meta Update</h4>
        </div>
        <div class="card-body">
            <?= view('admin/seo/_form', compact('model')) ?>
        </div>
    </div>
</div>

@endsection