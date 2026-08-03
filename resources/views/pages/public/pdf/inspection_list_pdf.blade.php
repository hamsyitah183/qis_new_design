<!DOCTYPE html>
<html>
<head>
    <title>Inspection Certificate List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .meta-info {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Inspection Certificate List</h2>
        <p>Generated on: {{ now()->format('d M Y, h:i A') }}</p>
    </div>

    <div class="meta-info">
        <strong>Status:</strong> {{ request('status') ? ucfirst(request('status')) : 'All Statuses' }}
        @if($exporterName)
            , <strong>Exporter:</strong> {{ $exporterName }}
        @endif
        @if($importerName)
            , <strong>Importer:</strong> {{ $importerName }}
        @endif
        @if($publicUserName)
            , <strong>Public User:</strong> {{ $publicUserName }}
        @endif
        @if(request('username'))
            , <strong data-bm="Dihantar Oleh:" data-en="Submitted By:">Submitted By:</strong> {{ request('username') }}
        @endif
        @if(request('start_date') || request('end_date'))
            <br>
            <strong data-bm="Tempoh:" data-en="Period:">Period:</strong> {{ request('start_date') ?? 'Start' }} <span data-bm="hingga" data-en="to">to</span> {{ request('end_date') ?? 'End' }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th data-bm="ID Permohonan" data-en="Application ID">Application ID</th>
                <th data-bm="Tarikh" data-en="Date">Date</th>
                <th data-bm="Pengimport" data-en="Importer">Importer</th>
                <th data-bm="Pengeksport" data-en="Exporter">Exporter</th>
                <th data-bm="Status" data-en="Status">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($applications as $app)
                <tr>
                    <td>{{ $app->application_id }}</td>
                    <td>{{ $app->created_at->format('d M Y, h:i A') }}</td>
                    <td>{{ $app->importer->fullname ?? '-' }}</td>
                    <td>{{ $app->exporter->name ?? '-' }}</td>
                    <td>{{ strtoupper($app->status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;" data-bm="Tiada rekod dijumpai." data-en="No records found.">No records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
