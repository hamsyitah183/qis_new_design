<!DOCTYPE html>
<html>
<head>
    <title>Activity Log Report</title>
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
        <h2>Activity Log Report</h2>
        <p>Generated on: {{ now()->format('d M Y, h:i A') }}</p>
    </div>

    <div class="meta-info">
        @if(request('start_date'))
            <strong>Start Date:</strong> {{ request('start_date') }} {{ request('start_time') }}<br>
        @endif
        @if(request('end_date'))
            <strong>End Date:</strong> {{ request('end_date') }} {{ request('end_time') }}<br>
        @endif
        @if(request('causer_type'))
            <strong>User Type:</strong> {{ ucfirst(request('causer_type')) }}<br>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Date & Time</th>
                <th>User</th>
                <th>Description</th>
                <th>Changes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($activities as $activity)
                <tr>
                    <td>{{ $activity->created_at->format('d M Y, h:i A') }}</td>
                    <td>
                        @if($activity->causer)
                            {{ $activity->causer->fullname ?? $activity->causer->name ?? 'Unknown' }}<br>
                            <small>{{ $activity->causer->email ?? '' }}</small>
                        @else
                            System / Unknown
                        @endif
                    </td>
                    <td>{{ $activity->description }}</td>
                    <td>
                        @if($activity->properties && isset($activity->properties['attributes']))
                            @foreach($activity->properties['attributes'] as $key => $value)
                                <strong>{{ $key }}:</strong> {{ is_array($value) ? json_encode($value) : $value }}<br>
                            @endforeach
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center;">No records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
