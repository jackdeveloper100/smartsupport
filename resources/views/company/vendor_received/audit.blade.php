<style>
.modal-dialog{
max-width:700px !important;
}
</style>
<div class="modal-lg">
    <div class="modal-header">
        <h5 class="modal-title" id="myModalLabel1">
            {{ $w9->vendor_company_name ?: "Vendor" }}
        </h5>
        <button type="button" class="btn-close rounded-pill" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body p-4">

        @php
            $tinStatus = $tinStatus ?? 'Unknown';

            if ($age['ageYears'] < 1 && str_contains($tinStatus, 'ending')) {
                $statusColor = 'success'; 
                $statusText  = 'Green';
            } elseif (($age['ageYears'] >= 1 && $age['ageYears'] < 2) 
                    || ($tinStatus !== 'Provided' && $age['ageYears'] < 2)) {
                $statusColor = 'warning'; 
                $statusText  = 'Amber';
            } else {
                $statusColor = 'danger';  
                $statusText  = 'Red';
            }
        @endphp

        <div class="mb-3">
            <span class="badge bg-{{ $statusColor }} px-3 py-2">
                {{ $statusText }}
            </span>
        </div>

        <div class="card border mb-3">
            <div class="card-body p-3">
                <table class="table table-sm table-borderless mb-0">
                    <tbody>
                        <tr>
                            <th><strong>Signed Date</strong></th>
                            <td>{{ $signedDate }}</td>
                        </tr>

                        <tr>
                            <th><strong>Age</strong></th>
                            <td>{{ $age['ageDisplay'] }}</td>
                        </tr>

                        <tr>
                            <th><strong>TIN Status</strong></th>
                            <td>{{ $tinStatus }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <hr>

        <p>
            We color the audit trail icon based on W-9 age and TIN status:
            <span class="text-success fw-bold">Green</span> (&lt; 1 year with TIN),
            <span class="text-warning fw-bold">Amber</span> (1–2 years or no TIN and &lt; 2 years),
            <span class="text-danger fw-bold">Red</span> (≥ 2 years or no TIN and ≥ 2 years).
        </p>

        <div class="card border mt-4">
            <div class="card-body">
                <h6 class="fw-bold">Our Guidance</h6>

                <p>
                    Consider reconfirming or requesting an updated W-9 as part of annual vendor maintenance
                    (recommended enterprise control), especially before year-end 1099 processing.
                </p>

                <p>
                    Vendor indicated “Applied For” (no TIN yet).  
                    For interest/dividend and certain broker payments, you may treat the payee as having applied
                    for a TIN for up to 60 days after receiving a W-9 marked “Applied For.”  
                    If a valid TIN isn’t provided within 60 days, begin 24% backup withholding.
                    For most other reportable payments, backup withholding applies until a TIN is furnished.
                </p>

                <p class="small text-muted">
                    Authority/Context: IRS Instructions for the Requester of Form W-9 and backup withholding rules
                    (rate 24%) describe the “Applied For” TIN 60-day treatment and when backup withholding applies.
                </p>
            </div>
        </div>

        {{-- Close button --}}
        <div class="text-end mt-3">
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
        </div>
    </div>
</div>
