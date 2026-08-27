
@extends('admin.layouts.main')
@section('title')
{{ $page->title }}
@endsection
@section('content')
<div class="container-fluid">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light"></span>{{ $page->title }}</h4>
    <div class="row mb-5">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-body">
                    {!! $page->body !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection