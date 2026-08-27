@extends('admin.layouts.main')
@section('title')
Log
@endsection
@section('content')
<!-- Content -->

<div class="page-title">
    <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
            <h3>Log</h3>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="admin/dashboard" class="pjax">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Log</li>
                </ol>
            </nav>
        </div>
    </div>
</div>
<!-- Invoice List Table -->
 <div class="section">
    <div class="card">
        <div class="card-header ">
            <h5 class="card-title">Log </h5>
        </div>
        <div class="card-body">
            <div class="card-datatable table-responsive">
                <table class="datatable-list-table table border-top" id="data-table">
                    <thead>
                        <tr>
                            <th>Created At</th>
                            <th>Type</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Device</th>
                            <th>IP</th>
                            <th>Locaion</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- / Content -->
<!--Bootstrap Tables-->
@endsection
@push('scripts')
<script>
    // documentReady(function() {
    //     datatableObj = $('#data-table').DataTable({
    //         ajax: {
    //             url: '{{route("admin/log/list")}}',
    //             method: 'post',
    //             dataSrc: 'data',
    //             data: {
    //                 '_token': CSRF_TOKEN
    //             },
    //         },
    //         columns: [{
    //                 data: "created_at",
    //                 responsivePriority: 5
    //             },
    //             {
    //                 data: "type",
    //                 responsivePriority: 4
    //             },
    //             {
    //                 data: "first_name",
    //                 responsivePriority: 4
    //             },
    //             {
    //                 data: "email",
    //                 responsivePriority: 4
    //             },
    //             {
    //                 data: "device",
    //                 responsivePriority: 4,
    //                 sortable: false
    //             },
    //             {
    //                 data: "ip",
    //                 responsivePriority: 4,
    //                 sortable: false
    //             },
    //             {
    //                 data: "location",
    //                 responsivePriority: 3,
    //                 sortable: false
    //             }
    //         ],
    //         responsive: true,
    //         serverSide: true,
    //         "order": [
    //             [0, "desc"]
    //         ]
    //     });
        
    // });
    documentReady(function () {
         pageLength: 25, 
         lengthMenu: [25, 50, 100],
         stateSave :true,
    datatableObj = $('#data-table').DataTable({
        ajax: {
            url: '{{route("admin/log/list")}}',
            method: 'post',
            dataSrc: 'data',
            data: {
                '_token': CSRF_TOKEN
            },
        },
        columns: [
            { data: "created_at", responsivePriority: 5 },
            { data: "type", responsivePriority: 4 },
            { data: "first_name", responsivePriority: 4 },
            { data: "email", responsivePriority: 4 },
            { data: "device", responsivePriority: 4, sortable: false },
            { data: "ip", responsivePriority: 4, sortable: false },
            { data: "location", responsivePriority: 3, sortable: false }
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

            // Previous
            paginationHTML += `<li class="paginate_button page-item previous ${currentPage === 0 ? 'disabled' : ''}">
                <a href="#" class="page-link" data-dt-idx="previous">Previous</a></li>`;

            // Current and next page
            if (currentPage < totalPages) {
                paginationHTML += `<li class="paginate_button page-item ${currentPage === 0 ? 'active' : ''}">
                    <a href="#" class="page-link" data-dt-idx="${currentPage}">${currentPage + 1}</a></li>`;
            }

            if (currentPage + 1 < totalPages) {
                paginationHTML += `<li class="paginate_button page-item">
                    <a href="#" class="page-link" data-dt-idx="${currentPage + 1}">${currentPage + 2}</a></li>`;
            }

            // Ellipsis + Last
            if (totalPages > currentPage + 2) {
                paginationHTML += `<li class="paginate_button page-item disabled">
                    <a href="#" class="page-link">…</a></li>`;

                paginationHTML += `<li class="paginate_button page-item ${currentPage === totalPages - 1 ? 'active' : ''}">
                    <a href="#" class="page-link" data-dt-idx="${totalPages - 1}">${totalPages}</a></li>`;
            }

            // Next
            paginationHTML += `<li class="paginate_button page-item next ${currentPage === totalPages - 1 ? 'disabled' : ''}">
                <a href="#" class="page-link" data-dt-idx="next">Next</a></li>`;

            paginate.html(paginationHTML);

            // Handle clicks
            paginate.find('a').on('click', function (e) {
                e.preventDefault();
                const dtIdx = $(this).data('dt-idx');

                if (dtIdx === 'previous' && currentPage > 0) {
                    api.page(currentPage - 1).draw('page');
                } else if (dtIdx === 'next' && currentPage < totalPages - 1) {
                    api.page(currentPage + 1).draw('page');
                } else if (!isNaN(dtIdx)) {
                    api.page(parseInt(dtIdx)).draw('page');
                }
            });
        }
    });
});

</script>
@endpush