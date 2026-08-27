@extends('company.layouts.main')

@section('title')
Plans
@endsection

@section('content')
<?php
    $sessionUser = auth()->user();
    $userModel = new \App\Models\User();
    $totalContractor = $userModel->where('company_id', $sessionUser->id)->where('company_approved_status', 1)->count();
    $stripeEnable = config('setting.stripe_enable');

?>

        
    <style>
        .modal-body{
            padding: 2rem;
        }
    </style>
<div class="page-title">
    <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
            <h3>Plans</h3>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-sm-end">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="company/dashboard" class="pjax">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Plans</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<section class="price-list" id="pricing">
    <div class="container">
        <div class="row">
            <div class="col-12  m-auto">
                <div class="pricing">
                    <div class="row align-items-center">
                        @foreach($plans as $plan)
                            @php
                                $isHighlighted = $loop->index === 1;
                                $isActive = @$subscriptionData->plan_id == $plan->id && @$subscriptionData->status == 'active';
                            @endphp

                            <div class="col-md-4 px-sm-0 my-3">
                                <div class="card {{ $isHighlighted ? 'bg-primary text-white shadow-lg' : '' }}">
                                    <div class="card-header text-center {{ $isHighlighted ? 'bg-primary text-white' : $plan->status }}">
                                        <h4 class="card-title">{{ $plan->title }}</h4>
                                    </div>

                                    <h1 class="price text-center {{ $isHighlighted ? 'text-white' : '' }}">
                                        ${{ number_format($plan->amount, 2) }}
                                        <small class="{{ !$isHighlighted ? 'text-muted' : '' }} fs-4">/ {{ $plan->duration }}</small>
                                    </h1>

                                    <ul class="px-3 {{ $isHighlighted ? 'text-white' : '' }}">
                                        @foreach(explode("\n", $plan->description) as $line)
                                            @if(trim($line) !== '')
                                                <li>
                                                    <i class="bi bi-check-circle {{ $isHighlighted ? 'greenlight' : '' }}"></i>
                                                    {{ trim($line) }}
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>

                                    <div class="card-footer bg-transparent">
                                        <form
                                            action="{{ route('plan-select') }}"
                                            method="POST"
                                            class="plan-form"
                                            data-contractor-limit="{{ $plan->contractor_limit }}"
                                        >
                                            @csrf
                                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                            <input type="hidden" name="userEmailToken" value="{{ $token }}">

                                            @if(@$subscriptionData->plan_id !== null)
                                                <button type="submit"
                                                    class="btn btn-block
                                                        @if($isActive && $isHighlighted)
                                                            btn btn-light
                                                        @elseif(!$isHighlighted)
                                                            btn btn-primary
                                                        @elseif($isHighlighted)
                                                            btn btn-outline-white
                                                        @else
                                                            btn-light
                                                        @endif"
                                                    @if($isActive) disabled @endif>
                                                    {{ $isActive ? 'Active Plan' : 'Upgrade Plan' }}
                                                </button>
                                            @else
                                                <button type="submit" class="@if($isHighlighted) btn btn-outline-white @else btn btn-primary @endif btn-block">
                                                    Sign Up
                                                </button>
                                            @endif
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div> </div> </div> </div> </div> </section>

        <div class="modal fade" id="contractorLimitModal" tabindex="-1" role="dialog" aria-labelledby="contractorLimitModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="contractorLimitModalLabel">Contractor Limit Exceeded</h5>
                        <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        </button>
                    </div>
                    <div class="modal-body">
                       You have more contractors than this plan allows. Please choose another plan or select which contractors you want to keep.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary"
                            id="confirmContinueBtn1">
                            Continue
                        </button>
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal"
                            id="confirmOkBtn2">
                            Ok
                        </button>
                    </div>
                </div>
            </div>
        </div>

@endsection

@push('scripts')
<script>
    function initializePlanPageScripts() {
        var totalContractor = {{ $totalContractor }};
        var stripeEnable = {{ $stripeEnable ?? 0 }};
        var unlimitedContractors = {{ $sessionUser->unlimited_conractors ? 'true' : 'false' }};
        
        let selectedForm = null;
        let selectedPlanId = null; // This is the selectedPlanId you want to use

        $('.plan-form').off('submit').on('submit', function (e) {
            e.preventDefault();

            if (stripeEnable === 0) {
                $('#contractorLimitModalLabel').text('Payment Unavailable');
                $('.modal-body').text("We can't accept payments right now. Please try again later.");
                $('#confirmContinueBtn1').hide();
                $('#confirmOkBtn2').show();
                $('#contractorLimitModal').modal('show');
                return;
            } else {
                let contractorLimit = parseInt($(this).data('contractor-limit'));
                let planId = parseInt($(this).find('input[name="plan_id"]').val());
                let planTitle = $(this).closest('.card').find('.card-title').text().trim();
                
                // if ((planId === 3 || planTitle.toLowerCase() === 'premium')) {
                //     this.submit(); // submit the form directly
                //     return;
                // }
                if (!unlimitedContractors && totalContractor > contractorLimit) {
                    selectedForm = $(this);
                    selectedPlanId = $(this).find('input[name="plan_id"]').val(); 

                    $('#contractorLimitModalLabel').text('Contractor Limit Exceeded');
                    $('.modal-body').text('You have more contractors than this plan allows. Please choose another plan or select which contractors you want to keep.');
                    $('#confirmContinueBtn1').show();
                    $('#confirmOkBtn2').hide();
                    $('#contractorLimitModal').modal('show');
                    return;
                }
            }

            this.submit();
        });

        // This is the correct listener that has access to selectedPlanId
        $('#confirmContinueBtn1').off('click').on('click', function () {
            if (selectedPlanId) { // selectedPlanId is defined in this scope/closure
                $('#contractorLimitModal').modal('hide');
                app.showModalView(`company/contractor_list/${selectedPlanId}`);
            }
        });
    }

    $(document).ready(function() {
        initializePlanPageScripts();
    });

    $(document).on('pjax:complete', function() {
        initializePlanPageScripts();
    });
</script>
@endpush