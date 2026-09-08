@extends('company.layouts.main')
@section('title')
Contractors
@endsection
@section('content')

<?php $sessionUser = auth()->user(); ?>
<div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Contractors</h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-sm-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('company/dashboard') }}" class="pjax">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Contractors</li>
                    </ol>
                </nav>
            </div>
        </div>
</div>
<!-- Content -->
<section class="section">
        <div class="card">
            <div class="card-header justify-content-between d-flex align-items-center flex-wrap gap-2 pb-3">
                <h5 class="card-title">
                Contractors
                </h5>
                <div class="d-flex flex-wrap flex-wrap gap-2 align-items-end flex-wrap">
                    
                      @if($sessionUser->hasPermission('company/contractors/export-data'))
                      <a class="btn btn-success d-none" href="#" id="exportData" type="button" data-id="">Export Data</a>
                      @endif
              
                      <a href="javascript:void(0)" class="btn btn-secondary d-none" type="button" id="sendReminderMail">Send Reminder Email</a>
                 
                      <button class="btn btn-danger d-none" type="button" onclick="app.confirmAction(this);" data-action="company/contractor/delete_multiple" data-id="' . selected_id[] . '"  id="deleteEntreeBtn">Delete Selected</button>
                  
                      @if($sessionUser->hasPermission('company/contractor/create'))
                      <a href="company/contractor/create" class="btn btn-primary d-sm-inline-block pjax" style="float: inline-end;">Create</a>
                      @endif

                  </div>
                </div>
                <div class="d-flex align-items-center flex-wrap gap-2 justify-content-end px-4 mb-4">
                    <div class="form-group mb-0">    
                        <label class="body" for="startingDate">Select Date</label>
                    <div class="form-group mb-0 position-relative has-icon-right">
                                 <input type="date" class="form-control flatpickr-range" id="startingDate" onchange="tableFilterExpire()" name="startingDate"  value="">
                                <div class="form-control-icon">
                                 <i class="bi bi-calendar-event"></i>
                               </div>
                    </div>
                </div>

                <div class="form-group mb-0">
                        <label class="body">Status</label>
                        <select class="form-select"
                           name="status" onchange="tableFilterExpire();" id="status">
                            <option value="">All</option>
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                </div>
            </div>
            
            <div class="card-body dataTable-container">
                    <table class="datatable-list-table table border-top" id="data-table">
                        <thead>
                            <tr>
                            <th><input type="checkbox" id="selected_id" name="selected_id[]" class="form-check-input"></th>
                            <th>Id</th>
                            <th>Name</th>
                            <th>Business Name</th>
                            <!--<th>Email</th>-->
                            <th>Contractor Status</th>
                            <th>Status</th>
                            <th>Total Documents</th>
                            <th>Compliance Status</th>
                            <th>Last Activity Date</th>
                            <th>Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
        </div>
</section>

@endsection
@push('scripts')
<script>
function tableFilterExpire(){
     tableDataExp.status = $('#status').val();
     tableDataExp.startingDate = $('#startingDate').val();
     datatableObj.ajax.reload();
    }
    var tableDataExp = {
          '_token': CSRF_TOKEN,
          'status': $('#status').val(),
          'startingDate' : $('#startingDate').val(),
        };

        $(document).ready(function () {
    datatableObj = $('#data-table').DataTable({
         pageLength: 25, 
         lengthMenu: [25, 50, 100],
         stateSave :true,
        ajax: {
            url: '{{ route("company/contractor/list") }}',
            method: 'post',
            dataSrc: 'data',
            data: function (d) {
                return $.extend({}, d, tableDataExp);
            }
        },
        columns: [
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'select-checkbox',
                render: function (data, type, row, meta) {
                    return '<input type="checkbox" class="row-select form-check-input" data-id="' + row.user_id + '">';
                }
            },
            { data: "id", responsivePriority: 4 },
            { data: "first_name", responsivePriority: 4 },
            { data: "business_name", responsivePriority: 2 },
            // { data: "email", responsivePriority: 2 },
            { data: "status", responsivePriority: 2 },
            { data: "company_approved_status", responsivePriority: 2 },
            { data: "document_count", responsivePriority: 4, bSortable: false },
            { data: "compliance_status", responsivePriority: 4, bSortable: false },
            { data: "latest_document_updated", responsivePriority: 3, bSortable: false },
            {
          data: "action",
          bSortable: false,
          responsivePriority: 1
        },
        ],
        responsive: true,
        serverSide: true,
        order: [[0, "desc"]],
        pagingType: "simple_numbers",

        drawCallback: function (settings) {
            const api = this.api();
            const pageInfo = api.page.info();
            const currentPage = pageInfo.page;
            const totalPages = pageInfo.pages;
            const paginate = $('#data-table_paginate ul');

            let paginationHTML = '';

            // Previous button
            paginationHTML += `<li class="paginate_button page-item previous ${currentPage === 0 ? 'disabled' : ''}">
                <a href="#" class="page-link" data-dt-idx="previous">Previous</a></li>`;

            // First page number
            if (currentPage < totalPages) {
                paginationHTML += `<li class="paginate_button page-item ${currentPage === 0 ? 'active' : ''}">
                    <a href="#" class="page-link" data-dt-idx="${currentPage}">${currentPage + 1}</a></li>`;
            }

            // Second page number (next page)
            if (currentPage + 1 < totalPages) {
                paginationHTML += `<li class="paginate_button page-item ${currentPage === (currentPage + 1) ? 'active' : ''}">
                    <a href="#" class="page-link" data-dt-idx="${currentPage + 1}">${currentPage + 2}</a></li>`;
            }

            // Ellipsis + Last page
            if (totalPages > currentPage + 2) {
                paginationHTML += `<li class="paginate_button page-item disabled">
                    <a href="#" class="page-link">…</a></li>`;

                paginationHTML += `<li class="paginate_button page-item ${currentPage === (totalPages - 1) ? 'active' : ''}">
                    <a href="#" class="page-link" data-dt-idx="${totalPages - 1}">${totalPages}</a></li>`;
            }

            // Next button
            paginationHTML += `<li class="paginate_button page-item next ${currentPage === (totalPages - 1) ? 'disabled' : ''}">
                <a href="#" class="page-link" data-dt-idx="next">Next</a></li>`;

            paginate.html(paginationHTML);

            // Event handlers for pagination links
            paginate.find('a').on('click', function (e) {
                e.preventDefault();
                const dtIdx = $(this).data('dt-idx');

                if (dtIdx === 'previous') {
                    if (currentPage > 0) api.page(currentPage - 1).draw('page');
                } else if (dtIdx === 'next') {
                    if (currentPage < totalPages - 1) api.page(currentPage + 1).draw('page');
                } else {
                    api.page(parseInt(dtIdx)).draw('page');
                }
            });
        }
    });
});

$('#selected_id').on('click', function() {
    var isChecked = $(this).prop('checked'); 
    $('#data-table .row-select').prop('checked', isChecked); 
    toggleDeleteButton(); 
});
    
$('#data-table').on('change', '.row-select', function() {
    var totalCheckboxes = $('#data-table .row-select').length;
    var checkedCheckboxes = $('#data-table .row-select:checked').length;
    $('#selected_id').prop('checked', totalCheckboxes === checkedCheckboxes);
    toggleDeleteButton(); 
});

function toggleDeleteButton() {
    var selectedIds = [];
    $('#data-table .row-select:checked').each(function() {
        selectedIds.push($(this).data('id'));  
    });
         updateExportLink(selectedIds);
         updateReminderLink(selectedIds);

    if (selectedIds.length > 0) {
        $('#deleteEntreeBtn').removeClass('d-none');    
        $('#deleteEntreeBtn').data('id', selectedIds.join(','));
        $('#sendReminderMail').removeClass('d-none');  
        $('#sendReminderMail').data('id', selectedIds.join(','));
        $('#exportData').removeClass('d-none');  
        $('#exportData').data('id', selectedIds.join(','));
    } else {
        $('#deleteEntreeBtn').addClass('d-none');  
        $('#deleteEntreeBtn').data('id', '');  
        $('#sendReminderMail').addClass('d-none');  
        $('#sendReminderMail').data('id', '');  
        $('#exportData').addClass('d-none');  
        $('#exportData').data('id', '');  
    }
}

function updateExportLink(selectedIds) {
    const ids = selectedIds.join(','); 
    $('#exportData').attr('data-id', ids);
    $('#exportData').attr('href', '{{ route('company/contractor/export-data', ['id' => 'REPLACE_ID']) }}'.replace('REPLACE_ID', ids));
    $('#exportData').removeClass('d-none');
}

 function updateReminderLink(selectedIds) {
        const ids = selectedIds.join(',');
        $('#sendReminderMail').attr('data-id', ids);
        const url = '{{ route('company/contractor/send-remindermail', ['id' => 'REPLACE_ID']) }}'.replace('REPLACE_ID', ids);
        $('#sendReminderMail').attr('onclick', `app.showModalView('${url}')`);
        $('#sendReminderMail').removeClass('d-none');
    }

</script>
@endpush