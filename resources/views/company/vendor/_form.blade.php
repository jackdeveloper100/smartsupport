<style>
    .star{
        color: red;
    }
</style>

<form class="ajax-form" method="POST" action="{{ route('company/request/store') }}" id="ajax-form">
    @csrf
    <input type="hidden" name="id" value="{{ @$model->id }}">

    <div class="container py-2">
        <div class="row">

            <div class="col-md-12 mb-3">

                <label class="form-label fw-semibold">Requesting on behalf of <strong>{{ $user->company_name }}</strong></label>
                <input type="hidden" name="representative_of" value="{{ $user->id  }}">
            </div>

            <div class="col-md-12 mb-3">
                <label class="form-label fw-semibold">Vendor Contact Person Name <span class="star">*</span></label>
                <select name="vendor_contact_person" id="vendor_contact_person" class="form-select">
                    <option value="">Select Contact Person</option>
                    @foreach($contractorList as $contractor)
                        <option 
                            value="{{ $contractor->id }}"
                            data-business="{{ $contractor->business_name ?? '' }}"
                            data-email="{{ $contractor->email ?? '' }}"
                            @selected(old('vendor_contact_person', $model->vendor_contact_person ?? '') == $contractor->id ) >
                            {{ $contractor->first_name }} {{ $contractor->last_name }}
                        </option>
                    @endforeach

                </select>
            </div>

            <div class="col-md-12 mb-3">
                <label class="form-label fw-semibold">Vendor Company/Business Name <span class="star">*</span></label>
                <input type="text" name="vendor_company_name" id="vendor_company_name"
                    class="form-control" placeholder="Vendor Company/Business Name"
                    value="{{ old('vendor_company_name', $model->vendor_company_name ?? '') }}" >
            </div>

            <div class="col-md-12 mb-3">
                <label class="form-label fw-semibold">Email Address <span class="star">*</span></label>
                <input type="email" name="vendor_email" id="vendor_email"
                    class="form-control" placeholder="Email Address"
                    value="{{ old('vendor_email', $model->vendor_email ?? '') }}" readonly>
            </div>

            <div class="col-md-12 mb-3">
                <label class="form-label fw-semibold">Phone</label> <span class="star">*</span>
                <div class="input-group">
                    <span class="input-group-text">+01</span>
                    <input type="text" name="vendor_phone" 
                        class="form-control" placeholder="Phone Number"
                        value="{{ old('vendor_phone', $model->vendor_phone ?? '') }}">
                </div>
            </div>

            <div class="col-md-12 mb-3">
                <label class="form-label fw-semibold">Account Number</label>
                <input type="text" name="vendor_account_number" class="form-control"
                    placeholder="Account Number"
                    value="{{ old('vendor_account_number', $model->vendor_account_number ?? '') }}">
            </div>

            <div class="col-md-12 mb-3">
                <label class="form-label fw-semibold">Notify</label>
                <div class="form-check mt-1">
                    <input class="form-check-input" type="checkbox" id="notify" name="notify_me"
                        @checked(old('notify_me', $model->notify_me ?? true))>
                    <label class="form-check-label" for="notify">
                        Send request to the vendor's email address
                    </label>
                </div>
            </div>

            @if(!isset($model->id))
            <div class="col-md-12 mb-4">
                <label class="form-label fw-semibold">Send additional requests?</label>
                <div class="form-check mt-1">
                    <input class="form-check-input" type="checkbox" name="send_additional" id="additional" value="1"
                        @checked(old('send_additional', false))>
                    <label class="form-check-label" for="additional">
                        Keep request window open to send an additional request
                    </label>
                </div>
            </div>
            @endif
            
            @if(isset($model->id))
            <div class="form-group row">
                <label class="col-sm-5 col-form-label">Request last sent at</label>
                <div class="col-sm-7">
                <input type="text" class="form-control text-white" style="background-color: #B1B1B1 !important;> id="email_last_at" 
                       value="{{ $model->updated_at ? $model->updated_at->format('D, M d, Y g:i A') : 'N/A' }}" disabled>
                </div>
            </div>
            @endif
        </div>
    </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Close </button>
            <button type="submit" class="btn btn-primary"> Save & Proceed </button> 
        </div>
</form>

<script>
$(document).ready(function() {

    $('#ajax-form').validate({
        rules: {
            vendor_contact_person: {
                required: true
            },
            vendor_email: {
                required: true,
                email: true
            },
            vendor_company_name: {
                required: true
            },
            vendor_phone: {
                required: true,
                digits: true,
                 minlength: 10,
                maxlength: 10
            },
            notify_me: {
                required: true 
            },
        },
        messages: {
            vendor_contact_person: {
                required: "Please select a contact person"
            },
            vendor_company_name: {
                required: "Please enter your company name"
            },
            vendor_email: {
                required: "Please enter your email",
                email: "Please enter a valid email"
            },
            vendor_phone: {
                required: "Please enter your number",
                digits: "Please enter only numbers",
                minlength: "Phone number must be 10 digits",
                maxlength: "Phone number must not exceed 10 digits"
            },
             notify_me: {
                required: "You must select the notify option"
            }
        },
        errorPlacement: function(error, element) {
            error.addClass('invalid-feedback');
            element.closest('.mb-3').append(error);
        },
        highlight: function(element) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function(element) {
            $(element).removeClass('is-invalid');
        },
        submitHandler: function(form) {
            var formData = new FormData(form);
            app.showProgressLoader("Your request is being processed...");
            $.ajax({
                url: $(form).attr('action'),
                type: $(form).attr('method'),
                data: formData,
                contentType: false,
                processData: false,
                success: function(res) {
                    app.hideProgressLoader();
                    if($('#additional').is(':checked')) {
                
                        Swal.fire({
                            icon: 'success',
                            title: 'Request Sent',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                
                        $('#ajax-form')[0].reset();

                        $('#vendor_contact_person').val('').trigger('change');
                
                        $('#vendor_company_name, #vendor_email, #vendor_phone, #vendor_account_number')
                            .val('')
                            .prop('readonly', false); 
                
                        $('#notify').prop('checked', false);
                
                        $('#additional').prop('checked', false);
                
                        $('#ajax-form')
                            .find('.is-invalid').removeClass('is-invalid');
                        $('#ajax-form')
                            .find('.invalid-feedback').remove();
                
                        $('html, body').animate({ scrollTop: 0 }, 300);
                
                        return; 
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Request Sent',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = res.url || "{{ route('company/request') }}";
                    });
                },

                error: function(xhr) {
                    app.hideProgressLoader();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Something went wrong'
                    });
                }
            });
        }
    });

    $('#vendor_contact_person').change(function() {
        var selected = $(this).find(':selected');

        if(selected.val() === 'other' || selected.val() === '') {
            $('#vendor_company_name').val('').prop('readonly', false);
            $('#vendor_email').val('').prop('readonly', false);
        } else {
            var businessName = selected.data('business') || '';
            var email = selected.data('email') || '';

            $('#vendor_company_name').val(businessName).prop('readonly', businessName !== '');
            $('#vendor_email').val(email).prop('readonly', email !== '');
        }

        $('#vendor_company_name').valid();
        $('#vendor_email').valid();
    });
});

</script>

