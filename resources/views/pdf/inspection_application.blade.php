@extends('pdf.layout')

@section('title', 'Inspection Certificate Summary')
@section('doc-title', 'Inspection Certificate Summary')
@section('doc-subtitle', 'Application ID: ' . $application->application_id)

@php
    $docRef = $application->application_id;
@endphp

@section('extra-style')
    <style>
        .info-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        .info-grid td {
            width: 25%;
            vertical-align: top;
            padding: 10px 12px;
            border: 1px solid #eceff1;
            background: #fafbfc;
        }

        .info-grid .info-label {
            font-size: 7.5pt;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #9ca3af;
            margin-bottom: 3px;
        }

        .info-grid .info-value {
            font-size: 9.5pt;
            font-weight: bold;
            color: #1a1a1a;
        }

        .party-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
            margin: 0 -10px 4px -10px;
        }

        .party-table td {
            width: 50%;
            vertical-align: top;
            padding: 12px 14px;
            border: 1px solid #eceff1;
        }

        .party-table .party-heading {
            font-weight: bold;
            margin-bottom: 6px;
            text-transform: uppercase;
            font-size: 7.5pt;
            letter-spacing: 0.5px;
            color: #2d8f4f;
        }

        .party-table .party-name {
            font-weight: bold;
            font-size: 11.5pt;
            margin-bottom: 6px;
            color: #1a1a1a;
        }

        .party-table .party-row {
            font-size: 9pt;
            color: #4b5563;
            margin-bottom: 3px;
        }

        .party-table .party-row .party-row-label {
            color: #9ca3af;
            display: inline-block;
            width: 52px;
        }

        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2px;
        }

        .item-table th,
        .item-table td {
            border: 1px solid #eceff1;
            padding: 7px 8px;
            font-size: 9pt;
        }

        .item-table th {
            background: #f4f7f5;
            color: #4b5563;
            text-align: left;
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-weight: bold;
        }

        .item-table td.num {
            text-align: right;
        }

        .item-table tr:nth-child(even) td {
            background: #fafbfc;
        }

        .item-status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 8px;
            font-size: 7.5pt;
            background: #f3f4f6;
            color: #4b5563;
            text-transform: capitalize;
        }

        .total-row td {
            background: #eaf6ee;
            color: #226b3c;
            font-weight: bold;
            font-size: 10pt;
            border-top: 2px solid #2d8f4f;
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

    {{-- ================= APPLICATION META ================= --}}
    <div class="section-block mb-3">
        <div class="section-title"><span class="section-icon"></span>Application Details</div>
        <table class="info-grid">
            <tr>
                <td>
                    <div class="info-label">Application ID</div>
                    <div class="info-value">{{ $application->application_id }}</div>
                </td>
                <td>
                    <div class="info-label">Status</div>
                    <div class="info-value"><span class="status-badge">{{ $application->status ?? '—' }}</span></div>
                </td>
                <td>
                    <div class="info-label">ETA</div>
                    <div class="info-value">{{ optional($application->eta)->format('d/m/Y') ?? '—' }}</div>
                </td>
                <td>
                    <div class="info-label">Transport Type</div>
                    <div class="info-value">{{ $application->transport_type ?? '—' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="info-label">Entry Point</div>
                    <div class="info-value">{{ optional($application->entryPoint)->entry_name ?? '—' }}</div>
                </td>
                <td>
                    <div class="info-label">Submitted By</div>
                    <div class="info-value">{{ $application->user->fullname ?? '—' }}</div>
                </td>
                <td>
                    <div class="info-label">Category</div>
                    <div class="info-value">
                        {{ (string) $application->category_application === '0' ? 'Self Application' : 'On Behalf of Others' }}
                    </div>
                </td>
                <td>
                    <div class="info-label">Time Submitted</div>
                    <div class="info-value">{{ optional($application->created_at)->format('d/m/Y') ?? '—' }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ================= EXPORTER & IMPORTER ================= --}}
    <div class="section-block mb-3">
        <div class="section-title"><span class="section-icon"></span>Exporter &amp; Importer</div>
        <table class="party-table">
            <tr>
                <td>
                    <div class="party-heading">Exporter</div>
                    <div class="party-name">{{ optional($application->exporter)->name ?? '—' }}</div>
                    <div class="party-row"><span class="party-row-label">Phone</span>{{ optional($application->exporter)->phone_no ?? '—' }}</div>
                    <div class="party-row"><span class="party-row-label">Country</span>{{ $application->exporter->countryInfo->name  }}</div>
                    <div class="party-row"><span class="party-row-label">Address</span>{{ optional($application->exporter)->address ?? '—' }}</div>
                </td>
                <td>
                    <div class="party-heading">Importer</div>
                    @php
                        $importerDetail = $application->importer_detail ?? [];
                        $importer = $application->importer;

                        $importerCountryCode = $importerDetail['country'] ?? null;
                        $importerCountry = $importerCountryCode
                            ? \App\Models\Country::where('code', $importerCountryCode)->first()
                            : null;
 
                    @endphp
                    <div class="party-name">{{ $importerDetail['name'] ?? (optional($importer)->fullname ?? '—') }}</div>
                    <div class="party-row"><span class="party-row-label">Phone</span>{{ $importerDetail['phone_no'] ?? (optional($importer)->phone_number ?? '—') }}</div>
                    <div class="party-row"><span class="party-row-label">Country</span>Malaysia</div>
                    <div class="party-row"><span class="party-row-label">Address</span>{{ $importerDetail['address'] ?? (optional($importer)->address_1 ?? '—') }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ================= ITEMS ================= --}}
    <div class="section-block mb-3">
        <div class="section-title"><span class="section-icon"></span>Consignment Items</div>
        <table class="item-table">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Uses</th>
                    <th>Purpose</th>
                    <th class="num">Quantity</th>
                    <th>Unit</th>
                    <th class="num">Value (RM)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($application->inspectionItems as $permit)
                    @php
                        $detail = $permit->consignment_detail ?? [];
                    @endphp
                    <tr>
                        <td>{{ $detail['item_name'] ?? '—' }}</td>
                        <td>{{ $detail['uses'] ?? '—' }}</td>
                        <td>{{ $permit->purpose ?? '—' }}</td>
                        <td class="num">{{ number_format($permit->quantity ?? 0, 2) }}</td>
                        <td>{{ $permit->unit_measurement ?? '—' }}</td>
                        <td class="num">{{ number_format($permit->value ?? 0, 2) }}</td>
                        <td><span class="item-status">{{ $permit->status ?? '—' }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-state">No items found for this application.</td>
                    </tr>
                @endforelse
            </tbody>
            @if ($application->inspectionItems->count())
                {{-- <tfoot>
                    <tr class="total-row">
                        <td colspan="5" style="text-align:right;">Total Value:</td>
                        <td class="num">RM {{ number_format($application->inspectionItems->sum('value'), 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot> --}}
            @endif
        </table>
    </div>

@endsection