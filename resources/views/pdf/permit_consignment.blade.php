<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Perihal Konsaimen</title>
    <style>
        @page {
            margin: 1.4cm 1.6cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.35;
        }
        .asal-label {
            text-align: right;
            font-weight: bold;
            font-size: 9pt;
            margin-bottom: 6px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin-bottom: 10px;
        }
        .header-table td {
            vertical-align: top;
            font-size: 9pt;
        }
        .header-left {
            width: 33%;
        }
        .header-center {
            width: 34%;
            text-align: center;
        }
        .header-right {
            width: 33%;
            text-align: left;
        }
        .logo {
            width: 65px;
            height: auto;
        }
        .sabah-title {
            font-weight: bold;
            margin-top: 4px;
        }
        .no-line {
            text-align: center;
            font-weight: bold;
            font-size: 13pt;
            margin: 10px 0 4px 0;
        }
        .form-title {
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            text-decoration: underline;
            margin-bottom: 14px;
        }
        .field-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .field-table td {
            padding: 2px 4px;
            vertical-align: top;
        }
        .field-label {
            width: 34%;
            white-space: nowrap;
        }
        .field-value {
            border-bottom: 1px dotted #000;
            font-weight: bold;
        }
        .items-block {
            margin-left: 34%;
            margin-top: -2px;
            margin-bottom: 8px;
        }
        .items-block table {
            width: 100%;
            border-collapse: collapse;
        }
        .items-block td {
            padding: 1px 4px;
            border-bottom: 1px dotted #000;
        }
        .items-block .qty {
            width: 25%;
            text-align: left;
        }
        .declaration {
            text-align: justify;
            margin: 16px 0;
        }
        .validity-line {
            margin-bottom: 4px;
        }
        .sign-row {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        .sign-row td {
            vertical-align: top;
            padding: 2px 4px;
        }
        .sign-label {
            width: 45%;
        }
        .footer-block {
            margin-top: 40px;
        }
        .disahkan {
            width: 55%;
        }
        .officer-name {
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 30px;
        }
        .stamp-box {
            float: right;
            width: 140px;
            height: 100px;
            border: 1px dashed #999;
            text-align: center;
            font-size: 8pt;
            color: #888;
            padding-top: 40px;
        }
        .sk-block {
            margin-top: 20px;
            font-size: 9.5pt;
        }
        .form-code {
            margin-top: 30px;
            font-size: 8.5pt;
        }
        .underline-dots {
            border-bottom: 1px dotted #000;
            display: inline-block;
            min-width: 200px;
        }
    </style>
</head>
<body>

    <div class="asal-label">ASAL</div>

    {{-- ================= HEADER ================= --}}
    <table class="header-table">
        <tr>
            <td class="header-left">
                Ruj.: {{ $application->reference_no ?? '-' }}<br>
                {{-- TODO: no dedicated "vehicle number" relation yet.
                     vehicle_ids is a JSON array of IDs — resolve against your
                     Vehicle model once it exists. Falling back to the raw IDs. --}}
                NO.KENDERAAN: {{ is_array($application->vehicle_ids ?? null) ? implode(', ', $application->vehicle_ids) : '-' }}<br><br>
                Kepada:<br>
                Pertubuhan Perlindungan Tumbuhan<br><br>
                JABATAN PERTANIAN<br>
                {{-- Destination state derived from the consignee's "country" field,
                     which for domestic shipments actually stores the MY state code. --}}
                @php
                    $consigneeState = \App\Models\Country::select('name')->where('code', $importer['country'] ?? null)->first();
                @endphp
                NEGERI {{ strtoupper($consigneeState->name ?? '-') }}
            </td>
            <td class="header-center">
                <img src="{{ public_path('asset/sabah-svg.jpg') }}" class="logo" alt="Sabah Logo">
                <div class="sabah-title">SABAH, MALAYSIA</div>
            </td>
            <td class="header-right">
                <img src="{{ public_path('asset/jata-svg.jpg') }}" class="logo" alt="Logo"><br>
                Jabatan Pertanian,<br>
                (Seksyen Biosekuriti &amp; Kuarantin Tumbuhan),<br><br>
                {{-- TODO: $district — resolve entry point's district ID to a name
                     once a District model/relation is wired up. --}}
                Daerah: <span class="underline-dots" style="min-width:120px;">{{ $district ?? optional($entry)->district ?? '-' }}</span><br>
                Tarikh: <span class="underline-dots" style="min-width:120px;">{{ now()->format('d.m.Y') }}</span>
            </td>
        </tr>
    </table>

    <div class="no-line">No. {{ optional($items->first())->permit_number ?? '-' }}</div>
    <div class="form-title">PERIHAL KONSAIMEN</div>

    {{-- ================= MAIN FIELDS ================= --}}
    <table class="field-table">
        <tr>
            <td class="field-label">Nama dan Alamat Pengeksport:</td>
            <td class="field-value">
                {{ strtoupper($exporter['fullname'] ?? '-') }},
                {{ strtoupper(trim(
                    ($exporter['address_1'] ?? '') . ' ' .
                    ($exporter['address_2'] ?? '') . ' ' .
                    ($exporter['postcode'] ?? '') . ' ' .
                    ($exporter['district'] ?? '') . ', ' .
                    ($exporter['state'] ?? '')
                )) }}
            </td>
        </tr>
        <tr>
            <td class="field-label">Nama dan Alamat Konsigni (Penerima):</td>
            <td class="field-value">
                {{ strtoupper($importer['name'] ?? '-') }},
                {{ strtoupper($importer['address'] ?? '-') }}
            </td>
        </tr>
        <tr>
            <td class="field-label">Bilangan dan Perihal Bungkusan:</td>
            <td class="field-value">
                {{-- Aggregates quantity across all items. If units differ this just
                     concatenates them — flag to Ruby if mixed-unit consignments
                     need a smarter rollup than a straight sum. --}}
                @php
                    $totalsByUnit = collect($items)->groupBy('unit_measurement')->map(function ($group) {
                        return collect($group)->sum('quantity');
                    });
                @endphp
                {{ $totalsByUnit->map(fn($qty, $unit) => $qty . ' ' . strtoupper($unit ?? ''))->implode(', ') ?: '-' }}
            </td>
        </tr>
        <tr>
            <td class="field-label">Tempat Asal:</td>
            <td class="field-value">
                {{-- TODO: no "origin place" field on the model yet — this is
                     currently just falling back to the exporter's district/state.
                     Add an `origin_place` column on the application (or item) if
                     it needs to be entered separately, as in the sample photo. --}}
                {{ strtoupper(trim(($exporter['district'] ?? '') . ', ' . ($exporter['state'] ?? ''))) ?: '-' }}
            </td>
        </tr>
        <tr>
            <td class="field-label">Cara Penghantaran Terisytihar:</td>
            <td class="field-value">
                @php
                    $transportMap = [
                        'Land' => 'MELALUI DARAT',
                        'Sea'  => 'MELALUI LAUT',
                        'Air'  => 'MELALUI UDARA',
                    ];
                @endphp
                {{ $transportMap[$application->transport_type ?? ''] ?? strtoupper($application->transport_type ?? '-') }}
            </td>
        </tr>
        <tr>
            <td class="field-label">Nama Hasil Terisytihar:</td>
            <td class="field-value">SEPERTI DIBAWAH</td>
        </tr>
        <tr>
            <td class="field-label">Tempat Masuk/Terisytihar:</td>
            <td class="field-value">{{ strtoupper(optional($entry)->entry_name ?? '-') }}</td>
        </tr>
        <tr>
            <td class="field-label">Tarikh Bertolak/Berlepas:</td>
            <td class="field-value">
                {{ optional($application->eta)->format('d.m.Y') ?? '-' }}
            </td>
        </tr>
    </table>

    <div class="items-block">
        <div style="font-weight:bold; margin-bottom:2px;">Nama Hasil dan Kuantiti Terisytihar:</div>
        <table>
            @foreach ($items as $detail)
                @php
                    $itemName = data_get($detail, 'consignment_detail.item_name', '-');
                    $parts = explode('-', $itemName);
                    $afterDash = isset($parts[1]) ? trim($parts[1]) : $itemName;
                @endphp
                <tr>
                    <td>{{ strtoupper($afterDash) }}</td>
                    <td class="qty">{{ data_get($detail, 'quantity', '-') }}</td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="declaration">
        Dengan ini Jabatan Pertanian tidak ada halangan dan memaklumkan bahawa tumbuh-tumbuhan atau
        keluaran tumbuh-tumbuhan yang diperakui dan diperihalkan di atas telah diproses mengikut
        prosedur-prosedur di mana yang perlu dan adalah dianggap sebagai bebas daripada makhluk
        perosak berbahaya.
    </div>

    <div class="validity-line">
        Surat kebenaran ini sah hanya bagi satu konsaimen/pengeluaran
        dan mansuh pada: <span class="underline-dots">{{ $validUntil ?? '-' }}</span>
    </div>

    <table class="sign-row">
        <tr>
            <td class="sign-label">Nama Pegawai Pemeriksa dan Pelapor:
                <span class="underline-dots"></span>
            </td>
            <td>Tandatangan:
                <span class="underline-dots"></span>
            </td>
        </tr>
    </table>

    {{-- ================= AUTHORISING OFFICER / FOOTER ================= --}}
    <div class="footer-block">
        <div class="disahkan">
            Disahkan
            {{-- TODO: no "approving officer" relation exists yet. This block is
                 static per district in the sample (e.g. a district agriculture
                 officer's name/title/stamp). Wire this to whichever table stores
                 the officer assigned to $entry->district once it exists. --}}
            <div class="officer-name">{{ $approvingOfficer['name'] ?? '_____________________________' }}</div>
            <div>{{ $approvingOfficer['title'] ?? 'Pegawai Pertanian Kanan (Pegawai Pertanian)' }}</div>
            <div>DAERAH {{ strtoupper($district ?? optional($entry)->district ?? '-') }}</div>
        </div>

        <div class="stamp-box">Cap Jabatan Pertanian</div>
        <div style="clear:both;"></div>

        <div class="sk-block">
            s.k. Pengarah,<br>
            Jabatan Pertanian Sabah,<br>
            88632 Kota Kinabalu.<br><br>
            (U/P: Seksyen Biosekuriti &amp; Kuarantin Tumbuhan)
        </div>

        <div class="form-code">
            YKS/ldw/agc<br>
            P.K. 530 (L) - 2024
        </div>
    </div>

</body>
</html>