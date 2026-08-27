@extends('layouts.main')
@section('title')
Contact
@endsection
@section('content')

<div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Help</h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-sm-end ">
                    <ol class="breadcrumb ">
                        <li class="breadcrumb-item pjax"><a href="contractor/document" class="pjax">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Help</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

<div class="col-12 info-container">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">{{ @$page->title }}</h5>
                </div>
                <div class="card-body">
                    <div class="row" >
                        <div class="col-md-12">
                           {!! @$page->body !!}

                        </div>
                        
                </div>
            </div>
        </div>
</div>

<!--Bootstrap Tables-->
@endsection
@push('scripts')
<script type="text/javascript">
    documentReady(function() {
        $('.ajax-contact-form').validate({
            submitHandler: function(form) {
                app.ajaxForm(form);
            }
        })
    });
</script>
@endpush