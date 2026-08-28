<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Import Permit Application Summary</title>
    <style>
        @page { margin: 1.4cm 1.6cm; }
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.35;
            color: #222;
        }
        .header-title {
            text-align: center;
            font-weight: bold;
            font-size: 15pt;
            margin-bottom: 2px;
        }
        .header-subtitle {
            text-align: center;
            font-size: 9pt;
            color: #555;
            margin-bottom: 16px;
        }
        .app-meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .app-meta-table td {
            padding: 3px 6px;
            font-size: 9.5pt;
        }
        .app-meta-table .label {
            width: 32%;
            color: #555;
        }
        .app-meta-table .value {
            font-weight: bold;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 10px;
            background: #eef2ff;
            border: 1px solid #c7d2fe;
            font-weight: bold;
        }
        .section-title {
            font-weight: bold;
            font-size: 11pt;
            margin: 16px 0 6px 0;
            padding-bottom: 3px;
            border-bottom: 2px solid #333;
        }
        .party-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        .party-table td {
            width: 50%;
            vertical-align: top;
            padding: 8px;
            border: 1px solid #ddd;
        }
        .party-table .party-heading {
            font-weight: bold;
            margin-bottom: 4px;
            text-transform: uppercase;
            font-size: 9pt;
            color: #555;
        }
        .party-table .party-name {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 4px;
        }
        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        .item-table th, .item-table td {
            border: 1px solid #ccc;
            padding: 5px 6px;
            font-size: 9pt;
        }
        .item-table th {
            background: #f3f4f6;
            text-align: left;
        }
        .item-table td.num {
            text-align: right;
        }
        .item-status {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 8px;
            font-size: 8pt;
            background: #f3f4f6;
        }
        .total-row td {
            font-weight: bold;
            background: #f9fafb;
        }
        .footer-note {
            margin-top: 24px;
            font-size: 8pt;
            color: #888;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="header-title">Import Permit Application Summary</div>
    <div class="header-subtitle">Application ID: {{ $application->application_id }}</div>

    {{-- ================= APPLICATION META ================= --}}
    <table class="app-meta-table">
        <tr>
            <td class="label">Application ID</td>
            <td class="value">{{ $application->application_id }}</td>
            <td class="label">Status</td>
            <td class="value"><span class="status-badge">{{ $application->status ?? '-' }}</span></td>
        </tr>
        <tr>
            <td class="label">ETA</td>
            <td class="value">{{ optional($application->eta)->format('d/m/Y') ?? '-' }}</td>
            <td class="label">Transport Type</td>
            <td class="value">{{ $application->transport_type ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Entry Point</td>
            <td class="value">{{ optional($application->entryPoint)->entry_name ?? '-' }}</td>
            <td class="label">Importer Verify</td>
            <td class="value">{{ $application->importer_verify ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Category</td>
            <td class="value">
                {{ (string) $application->category_application === '0' ? 'Self Application' : 'Application on Behalf of Others' }}
            </td>
            <td class="label">Verify Date</td>
            <td class="value">{{ optional($application->date_importer_verify)->format('d/m/Y') ?? '-' }}</td>
        </tr>
    </table>

    {{-- ================= EXPORTER & IMPORTER ================= --}}
    <div class="section-title">Exporter &amp; Importer</div>
    <table class="party-table">
        <tr>
            <td>
                <div class="party-heading">Exporter</div>
                <div class="party-name">{{ optional($application->exporter)->fullname ?? '-' }}</div>
                <div>Phone: {{ optional($application->exporter)->phone_number ?? '-' }}</div>
                <div>Email: {{ optional($application->exporter)->email ?? '-' }}</div>
                <div>
                    Address:
                    {{ trim(collect([
                        optional($application->exporter)->address_1,
                        optional($application->exporter)->address_2,
                        optional($application->exporter)->postcode,
                        optional($application->exporter)->district,
                    ])->filter()->implode(', ')) ?: '-' }}
                </div>
            </td>
            <td>
                <div class="party-heading">Importer (Consignee)</div>
                @php
                    $importerDetail = $application->importer_detail ?? [];
                    $importer = $application->importer;
                @endphp
                <div class="party-name">{{ $importerDetail['name'] ?? optional($importer)->name ?? '-' }}</div>
                <div>Phone: {{ $importerDetail['phone_no'] ?? optional($importer)->phone_no ?? '-' }}</div>
                <div>Country: {{ $importerDetail['country'] ?? optional($importer)->country ?? '-' }}</div>
                <div>Address: {{ $importerDetail['address'] ?? optional($importer)->address ?? '-' }}</div>
            </td>
        </tr>
    </table>

    {{-- ================= ITEMS ================= --}}
    <div class="section-title">Consignment Items</div>
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
                    <td>{{ $detail['item_name'] ?? '-' }}</td>
                    <td>{{ $detail['category'] ?? '-' }}</td>
                    <td>{{ $permit->purpose ?? '-' }}</td>
                    <td class="num">{{ number_format($permit->quantity ?? 0, 2) }}</td>
                    <td>{{ $permit->unit_measurement ?? '-' }}</td>
                    <td class="num">{{ number_format($permit->value ?? 0, 2) }}</td>
                    <td><span class="item-status">{{ $permit->status ?? '-' }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center; color:#888;">No items found for this application.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($application->consignmentPermits->count())
            <tfoot>
                <tr class="total-row">
                    <td colspan="5" style="text-align:right;">Total Value:</td>
                    <td class="num">RM {{ number_format($application->consignmentPermits->sum('value'), 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="footer-note">
        Generated on {{ now()->format('d/m/Y H:i') }} — Application {{ $application->application_id }}
    </div>

</body>
</html>