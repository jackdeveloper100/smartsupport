@extends('layouts.blank')
@section('title')
    {{ $email->title }}
@endsection

@section('content')
<div class="card">
  <div class="card-header">
    <h5 class="card-title text-center "> {{ $email->title }} </h5>
  </div>

  <div class="card-body d-flex justify-space-between">
    <p class="card-text"> {!! $email->body !!} </p>
</div>
</div>
@endsection