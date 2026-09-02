@extends('pdf.layout')

@section('title', 'Activity Log Report')
@section('doc-title', 'Activity Log Report')
@section('doc-subtitle', 'Generated on: ' . now()->format('d M Y, h:i A'))

@section('extra-style')
    <style>
        .meta-info {
            margin-bottom: 15px;
            font-size: 9pt;
            color: #4b5563;
        }

        .meta-info strong {
            color: #1a1a1a;
        }

        /* Table overrides for activity log */
        .activity-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .activity-table th,
        .activity-table td {
            border: 1px solid #eceff1;
            padding: 6px 8px;
            font-size: 8.5pt;
            vertical-align: top;
        }

        .activity-table th {
            background: #f4f7f5;
            color: #4b5563;
            text-align: left;
            font-size: 7.5pt;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-weight: bold;
        }

        .activity-table tr:nth-child(even) td {
            background: #fafbfc;
        }

        .activity-table .changes-list {
            font-size: 8pt;
            margin: 0;
            padding-left: 0;
            list-style: none;
        }

        .activity-table .changes-list li {
            padding: 1px 0;
        }

        .activity-table .changes-list strong {
            color: #1a1a1a;
        }

        .empty-state {
            text-align: center;
            color: #9ca3af;
            padding: 24px 0;
            font-size: 9pt;
        }
    </style>
@endsection

@section('content')
    {{-- ================= META INFO ================= --}}
    <div class="section-block">
        <div class="section-title"><span class="section-icon"></span>Report Parameters</div>
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
    </div>

    {{-- ================= ACTIVITY TABLE ================= --}}
    <div class="section-block">
        <div class="section-title"><span class="section-icon"></span>Activity Logs</div>
        <table class="activity-table">
            <thead>
                <tr>
                    <th style="width:15%;">Date &amp; Time</th>
                    <th style="width:20%;">User</th>
                    <th style="width:25%;">Description</th>
                    <th style="width:40%;">Changes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activities as $activity)
                    <tr>
                        <td>{{ $activity->created_at->format('d M Y, h:i A') }}</td>
                        <td>
                            @if($activity->causer)
                                {{ $activity->causer->fullname ?? $activity->causer->name ?? 'Unknown' }}
                                @if($activity->causer->email)
                                    <br><small style="color:#6b7280;">{{ $activity->causer->email }}</small>
                                @endif
                            @else
                                System / Unknown
                            @endif
                        </td>
                        <td>{{ $activity->description }}</td>
                        <td>
                            @if($activity->properties && isset($activity->properties['attributes']))
                                <ul class="changes-list">
                                    @foreach($activity->properties['attributes'] as $key => $value)
                                        <li>
                                            <strong>{{ $key }}:</strong>
                                            {{ is_array($value) ? json_encode($value) : $value }}
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="empty-state">No records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection