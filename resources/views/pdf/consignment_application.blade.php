@extends('pdf.layout')

@section('title', 'Consignment Application Summary')
@section('doc-title', 'Consignment Application Summary')
@section('doc-subtitle', 'Application ID: ' . $application->application_id)

@php
    $docRef = $application->application_id;
    $totalValue = $application->consignmentPermits->sum(fn ($p) => $p->value ?? 0);
    $totalQty = $application->consignmentPermits->sum(fn ($p) => $p->quantity ?? 0);
@endphp

@section('extra-style')
    <style>
        /* ---- Info grid (replaces the old plain meta table) ---- */
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

        /* ---- Party cards ---- */
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

        /* ---- Item table ---- */
        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2px;
        }
        .item-table th, .item-table td {
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

        /* ---- Totals summary box ---- */
        .totals-box {
            width: 260px;
            margin: 10px 0 0 auto;
            border: 1px solid #eceff1;
        }
        .totals-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-box td {
            padding: 7px 12px;
            font-size: 9pt;
        }
        .totals-box .totals-label {
            color: #6b7280;
        }
        .totals-box .totals-value {
            text-align: right;
            font-weight: bold;
        }
        .totals-box .grand-total td {
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

    {{-- ================= APPLICATION OVERVIEW ================= --}}
    <div class="section-block">
        <div class="section-title"><span class="section-icon"></span>Application Overview</div>
        <table class="info-grid">
            <tr>
                <td>
                    <div class="info-label">Reference No.</div>
                    <div class="info-value">{{ $application->reference_no ?? '—' }}</div>
                </td>
                <td>
                    <div class="info-label">Status</div>
                    <div class="info-value"><span class="status-badge">{{ $application->status ?? '—' }}</span></div>
                </td>
                <td>
                    <div class="info-label">ETA</div>
                    <div class="info-value">{{ optional($application->eta)->format('d M Y') ?? '—' }}</div>
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
                    <div class="info-label">PTN Number</div>
                    <div class="info-value">{{ $application->ptn_number ?? '—' }}</div>
                </td>
                <td>
                    <div class="info-label">Category</div>
                    <div class="info-value">
                        {{ (string) $application->category_application === '0' ? 'Self Application' : 'On Behalf of Others' }}
                    </div>
                </td>
                <td>
                    <div class="info-label">Importer Verify</div>
                    <div class="info-value">{{ $application->importer_verify ?? '—' }}</div>
                </td>
            </tr>
            @if (!empty($application->vehicle_ids))
                <tr>
                    <td colspan="4">
                        <div class="info-label">Vehicle ID(s)</div>
                        <div class="info-value">{{ implode(', ', $application->vehicle_ids) }}</div>
                    </td>
                </tr>
            @endif
        </table>
    </div>

    {{-- ================= EXPORTER & IMPORTER ================= --}}
    <div class="section-block">
        <div class="section-title"><span class="section-icon"></span>Exporter &amp; Importer</div>
        <table class="party-table">
            <tr>
                <td>
                    <div class="party-heading">Exporter (Applicant)</div>
                    <div class="party-name">{{ optional($application->exporter)->fullname ?? '—' }}</div>
                    <div class="party-row"><span class="party-row-label">Phone</span>{{ optional($application->exporter)->phone_number ?? '—' }}</div>
                    <div class="party-row"><span class="party-row-label">Email</span>{{ optional($application->exporter)->email ?? '—' }}</div>
                    <div class="party-row"><span class="party-row-label">Address</span>{{ trim(collect([
                        optional($application->exporter)->address_1,
                        optional($application->exporter)->address_2,
                        optional($application->exporter)->postcode,
                        optional($application->exporter)->district,
                    ])->filter()->implode(', ')) ?: '—' }}</div>
                </td>
                <td>
                    <div class="party-heading">Importer (Consignee)</div>
                    @php
                        $importerDetail = $application->importer_detail ?? [];
                        $importer = $application->importer;

                    

                        $country = \App\Models\Country::where('code', $importer['country'] )->first();
                    @endphp
                    <div class="party-name">{{ $importerDetail['name'] ?? optional($importer)->name ?? '—' }}</div>
                    <div class="party-row"><span class="party-row-label">Phone</span>{{ $importerDetail['phone_no'] ?? optional($importer)->phone_no ?? '—' }}</div>
                    <div class="party-row"><span class="party-row-label">Country</span>{{ $country->name ?? '—' }}</div>
                    <div class="party-row"><span class="party-row-label">Address</span>{{ $importerDetail['address'] ?? optional($importer)->address ?? '—' }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ================= ITEMS ================= --}}
    <div class="section-block">
        <div class="section-title"><span class="section-icon"></span>Consignment Items</div>
        <table class="item-table">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Purpose</th>
                    <th class="num">Quantity</th>
                    <th>Unit</th>
                    <th class="num">Value (RM)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($application->consignmentPermits as $permit)
                    @php
                        $detail = $permit->consignment_detail ?? [];
                    @endphp
                    <tr>
                        <td>{{ $detail['item_name'] ?? '—' }}</td>
                        <td>{{ $detail['category'] ?? '—' }}</td>
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
        </table>

        @if ($application->consignmentPermits->count())
            <div class="totals-box">
                <table>
                    <tr>
                        <td class="totals-label">Total Items</td>
                        <td class="totals-value">{{ $application->consignmentPermits->count() }}</td>
                    </tr>
                    <tr>
                        <td class="totals-label">Total Quantity</td>
                        <td class="totals-value">{{ number_format($totalQty, 2) }}</td>
                    </tr>
                    <tr class="grand-total">
                        <td>Total Value</td>
                        <td class="totals-value">RM {{ number_format($totalValue, 2) }}</td>
                    </tr>
                </table>
            </div>
        @endif
    </div>

@endsection