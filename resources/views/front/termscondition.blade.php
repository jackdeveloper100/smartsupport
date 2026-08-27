@extends('layouts.main')
@section('title')
Terms & Condition
@endsection
@section('content')
<style>
    p a {
        color: #6516c2;
        text-decoration: underline;
    }
</style>

<div class="card card-default color-palette-box">
    <div class="card-header">
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-lg-12 ">
                <section class="">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-12">
                                <h2>
                                    <center>{{$page->title}}</center>   
                                </h2>
                                <br>
                                <br>
                                {!! $page->body !!}
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection