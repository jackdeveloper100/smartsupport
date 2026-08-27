<!DOCTYPE html>
<html>
<head>
    <style>
    @page {
    size: A4 landscape;
}
     * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        h1 {
            text-align: center;
            margin-top: 20px;
            font-size: 24px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            border: 1px solid grey;
           
        }
        /*th, td {*/
        /*    padding: 6px 9px;*/
        /*    text-align: left;*/
        /*    border: 1px solid #ddd;*/
        /*}*/
      th, td {
    padding: 6px;
    font-size: 12px;
    border: 1px solid #ddd;
    white-space: normal;
    word-wrap: break-word;
}
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #ddd;
        }
        td {
            font-size: 12px;
        }
        tr {
            page-break-inside: avoid;
        }
        .header {
            background-color: #4CAF50;
            color: white;
        }
        .notes-column {
            max-width: 300px;
             white-space: normal;
            word-wrap: break-word;
            overflow-wrap: break-word;
             font-size: 10px;
        }
        .small-column {
            width: 80px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
        }
        @media print {
        body {
            zoom: 75%;
        }
    }
    </style>
</head>
<body>
    
    <h1>Contractors @if($docStatus !== null) {!! (new \App\Models\Document())->getStatusBadge($docStatus) !!} @endif Documents Report</h1>
    
    <table>
        <thead>
            <tr class="header">
                <th  class="small-column">Id</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Business Name</th>
                <th>Contractor Status</th>
                <th>Country</th>
                <th>Email Verified</th>
                <th>Document Name</th>
                <th>Document Status</th>
                <th style="width: 50px;">Compliance Status</th>
                <th style="width: 50px;">Time Zone</th>
                <th style="width: 500px;">Notes</th>
                <th>Expired Date</th>
                <th>Created Date</th>
                <!-- <th>Updated Date</th> -->
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->first_name }}</td>
                <td>{{ $user->last_name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->business_name }}</td>
                <td>{{ $user->status == 1 ? 'ACtive' : 'Inactive'}}</td>
                <td>{{ $user->country }}</td>
                <td>{{ $user->email_verified ? 'Yes' : 'No' }}</td>
                <td>{{ (new \App\Models\Document())->getDocumentName($user->doctype) }}</td>
                <td>{!! (new \App\Models\Document())->getStatusBadge($user->docstatus) !!}</td>
                <td style="width: 50px;">{{ (new \App\Models\User())->getComplianceStatus($user->id)}}</td>
                <td style="width: 50px;">{{ $user->timezone }}</td>
                <td class="notes-column">{{ \Illuminate\Support\Str::limit($user->contractor_internal_note, 200) }}</td>
                <td>{{ $user->expired_at }}</td>
                <td>{{ $user->created_at->format('Y-m-d H:i:s') }}</td>
                <!-- <td>{{ $user->updated_at->format('Y-m-d H:i:s') }}</td> -->
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Generated on {{ \Carbon\Carbon::now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>
