@extends('company.layouts.main')
@section('title')
Contact
@endsection
@section('content')

<div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Support</h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-sm-end ">
                    <ol class="breadcrumb ">
                        <li class="breadcrumb-item pjax"><a href="company/dashboard" class="pjax">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Support</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

<div class="col-12 info-container">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Support Information</h5>
                </div>
                <div class="card-body">
                    <div class="row" >
                        <div class="col-md-12">
                            @if($user->unlimited_conractors == 0)
                                  @if($companyPlanId == 1 || is_null($companyPlanId))
                                        <h5>Contact Email : {{ config('setting.admin_email') }}</h5>
                                        <p>If you need any support, have queries, feel free to reach out to us at the email above. We’ll respond as soon as possible.</p>
                                    @else
                                        <h5>Contact Email : {{ config('setting.admin_email') }}</h5>
                                        <h5>Contact Number : {{ config('setting.admin_phone') }}</h5>
                                        <p>If you need support or have any questions, feel free to contact us via email or phone. We will get back to you as quickly as possible.</p>
                                    @endif
                            @else
                            <h5>Contact Email : {{ config('setting.admin_email') }}</h5>
                            <h5>Contact Number : {{ config('setting.admin_phone') }}</h5>
                            <p>If you need support or have any questions, feel free to contact us via email or phone. We will get back to you as quickly as possible.</p>
                            @endif
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