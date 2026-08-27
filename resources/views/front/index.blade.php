@extends('layouts.main')
@section('title')
Home
@endsection
@section('content')

<h4 class="fw-bold py-3 mb-4">Home</h4>
<div class="row mb-5">
    <div class="col-md">
        <div class="card mb-3">
            <div class="row g-0">
                <div class="col-md-4">
                    <img class="card-img card-img-left" src="theme/assets/img/elements/9.jpg" alt="Card image" />
                </div>
                <div class="col-md-8">
                    <div class="card-body">
                        <h5 class="card-title">Card title</h5>
                        <p class="card-text">
                            This is a wider card with supporting text below as a natural lead-in to additional content.
                            This content is a little bit longer.
                        </p>
                        <p class="card-text"><small class="text-muted">Last updated 3 mins ago</small></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md">
        <div class="card mb-3">
            <div class="row g-0">
                <div class="col-md-8">
                    <div class="card-body">
                        <h5 class="card-title">Card title</h5>
                        <p class="card-text">
                            This is a wider card with supporting text below as a natural lead-in to additional content.
                            This content is a little bit longer.
                        </p>
                        <p class="card-text"><small class="text-muted">Last updated 3 mins ago</small></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <img class="card-img card-img-right" src="theme/assets/img/elements/12.jpg" alt="Card image" />
                </div>
            </div>
        </div>
    </div>
</div>
@endsection