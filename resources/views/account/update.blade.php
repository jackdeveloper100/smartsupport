@extends('layouts.main')
@section('title')
Profile
@endsection
@section('content')
<style>
    .star{
        color: red;
    }
</style>

<div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Account Profile</h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-sm-end ">
                    <ol class="breadcrumb ">
                        <li class="breadcrumb-item"><a href="contractor/document" class="pjax">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Profile</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

<div class="section">
    <div class="row">
        <div class="col-12">
            <div class="card p-3 mb-4">
                    <div class="d-flex align-items-center profile-card">
                        <div class="avatar avatar-2xl">
                            <img src="{{ $general->getFileUrl($data->image,'profile') }}" alt="Avatar">
                            <fa-camera><i class="fa fa-camera" onclick="app.showModalView('account/image')">
                            </i> 
                        </div>
                            <div class="flex-column">
                                <h3 class="mt-3">{{ $data->first_name }} {{ $data->last_name }}</h3>
                                <p class="text-small">{{ $data->email }}</p>
                            </div>
                </div>
            </div>
        </div>
        </div>
        <div class="row">
        <div class="col-12 col-lg-12">
                <div class="card">
                    <div class="card-body">
                    <form action="account/save" method="post" class="ajax-form">
                    {{ csrf_field() }}
                    <div class="row">
      
                    <div class="col-lg-6 col-sm-6 col-12">
                        <div class="form-group">
                            <label for="name" class="form-label">First Name <span class="star">*</span></label>
                            <input type="text" name="first_name" id="first_name" class="form-control" placeholder="First Name" value="{{ $data->first_name }}">
                        </div>  
                    </div>
                    <div class="col-lg-6 col-sm-6 col-12">
                        <div class="form-group">
                            <label for="name" class="form-label">Last Name</label>
                            <input type="text" name="last_name" id="last_name" class="form-control" placeholder="Last Name" value="{{ $data->last_name }}">
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-12">
                        <div class="form-group">
                            <label for="name" class="form-label">Email <span class="star">*</span></label>
                            <input type="text" name="email" id="email" class="form-control" placeholder="Email" value="{{ $data->email }}">
                        </div>
                    </div>
                    </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
    </div>
    </div>

@endsection
@push('scripts')
<script type="text/javascript">
  documentReady(function() {
    $('.ajax-form').validate({
      submitHandler: function(form) {
        app.ajaxForm(form);
      }
    })
  });
</script>
@endpush