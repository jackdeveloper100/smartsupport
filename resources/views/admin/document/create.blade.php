@extends('admin.layouts.main')
@section('title')
Document Create
@endsection
@section('content')

<div class="page-title">
    <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
            <h3>Document</h3>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="admin/dashboard" class="pjax">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="admin/contractors" class="pjax">Document</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Document Create</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="section">
    <div class="card ">
        <div class="card-header ">
            <h4 class="acard-title">Document Create</h4>
        </div>
        <div class="card-body">
        <?= view('admin/document/_form',['userId' => $userId]) ?>
        </div>
    </div>
</div>



@endsection