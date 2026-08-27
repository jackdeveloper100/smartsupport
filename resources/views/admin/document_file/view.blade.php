@extends('admin.layouts.main')
@section('title')
Document File
@endsection
@section('content')

<h1>View File</h1>
<div class="col-lg-6 col-md-6 col-12 mt-2 mt-md-0 mb-md-0 mb-2">
    <div class="view-img">
<img class="w-100 active" src="{{ $general->getFileUrl($model->filename,'document') }}"  data-bs-toggle="modal" data-bs-target="#imageModal" data-bs-slide-to="0" alt="Image Preview">

@endsection