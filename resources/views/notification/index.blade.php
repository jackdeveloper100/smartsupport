@extends('layouts.main')
@section('title')
Notification
@endsection
@section('content')

<?php
$userId = auth()->id();
$sessionUser = auth()->user();
?>
<!-- tempory take h4 -->
<h4>Notification Section</h4>
<div class="section">
    <div class="row">
        <div class="col-12">    
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pb-4">
                        <h4 class="card-title mb-0">Notifications</h4>
                        <p class="mb-0 badge bg-light-info">{{$newNotification}} New</p>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-lg">
                            <thead>
                                <!-- Table header if any -->
                            </thead>
                            <tbody id="notification-table-body">
                                @if($notificationData->isNotEmpty())
                                    @foreach($notificationData as $notification)    
                                    <tr>
                                        <td class="px-0 p-sm-3">
                                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-normal align-items-sm-center flex-wrap gap-sm-5 gap-1">
                                                <div class="d-flex gap-2 align-items-center">
                                                    {!! $notificationModel->getNotificationBadge($notification['document_status']) !!}
                                                    {{$notification->description}}
                                                </div>
                                                <p class="mb-0 text-end">{{ \Carbon\Carbon::createFromTimestamp($notification->created_at)->format('Y-m-d h:i A') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>

                    @if($totalNotification > 10)
                    <div class="w-100 text-center">
                        <button id="load-more-btn" class="btn btn-primary">Load More..</button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script type="text/javascript">
documentReady(function(){
    let limit = 10;  
    $('#load-more-btn').on('click', function() {
        let user_id = "{{ $userId }}"; 
        $.ajax({
            url: 'contractor/notifications/load-more', 
            method: 'GET',
            data: {
                user_id: user_id,
                limit: limit,
            },
            success: function(response) {
                if (response.notifications.length > 0) {
                    response.notifications.forEach(function(notification) {
                        let notificationRow = `
                            <tr>
                                <td class="px-0 p-sm-3">
                                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-normal align-items-sm-center flex-wrap gap-sm-5 gap-1">
                                        <div class="d-flex gap-2 align-items-center">
                                            ${notification.badge} <!-- Assuming the badge is returned in response -->
                                            ${notification.description}
                                        </div>
                                        <p class="mb-0 text-end">${notification.created_at}</p>
                                    </div>
                                </td>
                            </tr>
                        `;
                        $('#notification-table-body').append(notificationRow);
                    });
                    limit += 10;
                   if (response.notifications.length < 10) {
                                    $('#load-more-btn').hide();
                                }
                            } else {
                                // In case of an empty response, hide the button immediately
                                $('#load-more-btn').hide();
                            }
                        },
            error: function() {
                alert('Error loading notifications.');
            }
        });
    });
});
</script>
@endpush