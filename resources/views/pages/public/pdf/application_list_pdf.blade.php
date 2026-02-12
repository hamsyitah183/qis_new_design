<!DOCTYPE html>
<html>
<head>
    <title>Import Permit Application List</title>
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
        <h2>Import Permit Application List</h2>
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
            , <strong>Submitted By:</strong> {{ request('username') }}
        @endif
        @if(request('start_date') || request('end_date'))
            <br>
            <strong>Period:</strong> {{ request('start_date') ?? 'Start' }} to {{ request('end_date') ?? 'End' }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>App ID</th>
                <th>Date</th>
                <th>Importer</th>
                <th>Exporter</th>
                <th>Status</th>
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
                    <td colspan="5" style="text-align: center;">No records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
