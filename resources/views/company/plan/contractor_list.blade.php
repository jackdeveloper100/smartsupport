        
    <style>
        .modal-body{
            padding: 2rem;
        }
         
     .modal-dialog, html[data-bs-theme="dark"] .modal-dialog {
        max-width: 68%;
        height: 650px;
        overflow-y : auto;
    }
    .modal-success
    {
        max-width: 550px !important;
        width: 100% !important;
    }
    
    @media only screen and (max-width: 767px){
          .modal-dialog, html[data-bs-theme="dark"] .modal-dialog {
        max-width: 90%;
        height: 550px;
        
    }
    }
    </style>
    
     <form class="ajax-form" method="post" action="company/selected-contrators"   id="ajax-form">
        @csrf
        <div class="modal-header">
            <h5 class="modal-title" id="myModalLabel1">Select the contractors you'd like to keep.
</h5>
            <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        
        <input type="hidden" name="plan_id" id="modalPlanId" value="{{$planId}}">

    <div class="modal-body">
         <div class="col-md-12">
        <div class="form-group">
            <!--<label for="contractors" class="body">Select the contractors you'd like to keep. </label>-->
            <div class="mb-2 d-flex justify-content-end">
                <strong>Selected: <span id="selectedCount">0</span></strong>
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered ">
                    <thead>
                        <tr>
                            <th>Select</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Business Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contractorList as $contractor)
                            <tr>
                                <td>
                                    <input 
                                        type="checkbox" 
                                        class="form-check-input contractor-checkbox" 
                                        name="contractors[]" 
                                        value="{{ $contractor->id }}">
                                </td>
                                <td>{{ $contractor->first_name . ' ' .$contractor->last_name}}</td>
                                <td>{{ $contractor->email }}</td>
                                <td>{{ $contractor->business_name }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">No contractors found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
        
        <div class="modal-footer">
                <button type="button" class="btn" data-bs-dismiss="modal">
                    <span class="d-sm-block">Close</span>
                </button>
                <button type="submit" class="btn btn-primary ms-1" >
                    <span class="d-sm-block">Submit</span>
                </button>
            </div>
    </div>
    
    </form>
    
    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-success">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="successModalLabel">Success</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="successModalBody">
            Contractors selected successfully!
          </div>
          <div class="modal-footer">
            <button type="button" id="successModalOkBtn" class="btn btn-primary" >OK</button>
            <button type="button" id="successModalOkBtn1" class="btn btn-primary" data-bs-dismiss="modal" >OK</button>
          </div>
        </div>
      </div>
    </div>

        
<script type="text/javascript">
$(document).ready(function () {
    const contractorLimit = {{ $contractorLimit }};

    function updateModalButtons(isLimitExceeded) {
        if (isLimitExceeded) {
            $('#successModalOkBtn').hide();
            $('#successModalOkBtn1').show();
        } else {
            $('#successModalOkBtn').show();
            $('#successModalOkBtn1').hide();
        }
    }

    // Function to display messages in the success modal
    function showMessageModal(title, message, isError = false) {
        $('#successModalLabel').text(title);
        $('#successModalBody').text(message);
        // If it's an error, we want only the 'OK' button that closes the modal
        updateModalButtons(isError); 
        var successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();
    }

    $('.ajax-form').validate({
        submitHandler: function (form) {
            // Check if any contractor is selected before submitting
            if ($('.contractor-checkbox:checked').length === 0) {
                showMessageModal('Selection Required', 'Please select 1 to '+ contractorLimit  + ' contractor.', true);
                return false; // Prevent form submission
            }

            $.ajax({
                type: 'POST',
                url: $(form).attr('action'),
                data: $(form).serialize(),  
                success: function (res) {
                    if (res.status === 1 && res.plan_id) {
                        showMessageModal('Success', res.message || 'Contractors selected successfully!');
                        
                        $('#successModalOkBtn').off('click').on('click', function () {
                            $('#successModal').modal('hide'); 

                            const redirectForm = $('<form>', {
                                action: res.next,
                                method: 'POST'
                            });

                            redirectForm.append($('<input>', {
                                type: 'hidden',
                                name: '_token',
                                value: '{{ csrf_token() }}'
                            }));

                            redirectForm.append($('<input>', {
                                type: 'hidden',
                                name: 'plan_id',
                                value: res.plan_id
                            }));

                            $('body').append(redirectForm);
                            redirectForm.submit();
                        });
                    } else {
                        
                        showMessageModal('Error', res.message || 'An unexpected error occurred.', true);
                    }
                },
                error: function () {
                    showMessageModal('Error', 'Something went wrong. Please try again.', true);
                }
            });
        }
    });

    $(document).on('change', '.contractor-checkbox', function () {
        let checkedBoxes = $('.contractor-checkbox:checked');
        let count = checkedBoxes.length;

        if (count > contractorLimit) {
            this.checked = false; 
            showMessageModal('Limit Exceeded', 'Your current plan allows a maximum of ' + contractorLimit + ' contractors.', true);
            return;
        }

        $('#selectedCount').text(count); 
    });
});
</script>

