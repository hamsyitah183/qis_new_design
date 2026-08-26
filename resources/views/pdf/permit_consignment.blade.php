<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
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
            font-weight: normal;
        }

        .field-value {
            border-bottom: 1px dotted #000;
            font-weight: bold;
            font-family: "Courier New", monospace;
            font-size: 9pt;
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
            font-family: "Courier New", monospace;
            font-size: 9pt;
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
            font-family: "Courier New", monospace;
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

        .variable-value {
            font-family: "Courier New", monospace;
            font-size: 9pt;
        }

        .bold-label {
            font-weight: bold;
        }

        .mono {
            font-family: "Courier New", monospace;
        }

        .print-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 7pt;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 4px;
            background: #fff;
        }

        /* ─── QR PAGE STYLES ────────────────────────────────────── */
        .page-break {
            page-break-after: always;
        }

        .qr-page {
            text-align: center;
            padding-top: 30px;
        }

        .qr-page-title {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .qr-frame {
            display: inline-block;
            border: 1px solid #000;
            padding: 16px;
        }

        .qr-permit-no {
            margin-top: 10px;
            font-weight: bold;
        }

        .qr-note {
            margin-top: 10px;
            font-size: 9pt;
            line-height: 1.4;
            display: inline-block;
            text-align: left;
        }

        .qr-note-verification {
            display: block;
            padding-left: 2.12rem;
        }
    </style>
</head>

<body>

    <div class="asal-label">ASAL</div>

    {{-- ================= HEADER ================= --}}
    <table class="header-table">
        <tr>
            <td class="header-left">
                Ruj.: <span class="mono">{{ $application->ptn_number ?? '-' }}</span><br>
                NO.KENDERAAN:
                @if (!empty($application->vehicle_ids))
                    @php
                        $vehicleNumbers = [];
                        if (is_array($application->vehicle_ids)) {
                            foreach ($application->vehicle_ids as $vid) {
                                $vehicle = \App\Models\UserVehicleList::find($vid);
                                if ($vehicle) {
                                    $vehicleNumbers[] = $vehicle->vehicle_number;
                                }
                            }
                        }
                    @endphp
                    <span
                        class="mono">{{ implode(', ', $vehicleNumbers) ?: implode(', ', $application->vehicle_ids) }}</span>
                @else
                    <span class="mono">-</span>
                @endif
                <br><br>
                Kepada:<br>
                Pertubuhan Perlindungan Tumbuhan<br><br>
                JABATAN PERTANIAN<br>
                @php
                    $consigneeState = \App\Models\Country::select('name')
                        ->where('code', $importer['country'] ?? null)
                        ->first();
                @endphp
                NEGERI <span class="mono">{{ strtoupper($consigneeState->name ?? ($importer['state'] ?? '-')) }}</span>
            </td>
            <td class="header-center">
                <img src="{{ public_path('asset/sabah-svg.jpg') }}" class="logo" alt="Sabah Logo">
                <div class="sabah-title">SABAH, MALAYSIA</div>
            </td>
            <td class="header-right">
                <img src="{{ public_path('asset/Logo-DOA.png') }}" class="logo" alt="Logo"><br>
                Jabatan Pertanian,<br>
                (Seksyen Biosekuriti &amp; Kuarantin Tumbuhan),<br><br>
                Daerah: <span class="mono"
                    style="border-bottom:1px dotted #000; display:inline-block; min-width:120px;">
                    SIPITANG
                </span><br>
                Tarikh: <span class="mono"
                    style="border-bottom:1px dotted #000; display:inline-block; min-width:120px;">{{ now()->format('d.m.Y') }}</span>
            </td>
        </tr>
    </table>

    <div class="no-line">No. <span class="mono">{{ $permitNumber ?? ($items->first()->permit_number ?? '-') }}</span>
    </div>
    <div class="form-title">PERIHAL KONSAIMEN</div>

    {{-- ================= MAIN FIELDS ================= --}}
    <table class="field-table">
        <tr>
            <td class="field-label">Nama dan Alamat Pengeksport:</td>
            <td class="field-value">
                {{ strtoupper($exporter['name'] ?? ($exporter['fullname'] ?? '-')) }},
                {{ strtoupper(
                    trim(
                        ($exporter['address'] ?? '') .
                            ' ' .
                            ($exporter['address_1'] ?? '') .
                            ' ' .
                            ($exporter['address_2'] ?? '') .
                            ' ' .
                            ($exporter['postcode'] ?? '') .
                            ' ' .
                            ($exporter['district'] ?? '') .
                            ', ' .
                            ($exporter['state'] ?? ''),
                    ),
                ) }}
            </td>
        </tr>
        <tr>
            <td class="field-label">Nama dan Alamat Konsigni (Penerima):</td>
            <td class="field-value">
                {{ strtoupper($importer['name'] ?? ($importer['fullname'] ?? '-')) }},
                {{ strtoupper(
                    trim(
                        ($importer['address'] ?? '') .
                            ' ' .
                            ($importer['address_1'] ?? '') .
                            ' ' .
                            ($importer['address_2'] ?? '') .
                            ' ' .
                            ($importer['postcode'] ?? '') .
                            ' ' .
                            ($importer['district'] ?? '') .
                            ', ' .
                            ($importer['state'] ?? ''),
                    ),
                ) }}
            </td>
        </tr>
        <tr>
            <td class="field-label">Bilangan dan Perihal Bungkusan:</td>
            <td class="field-value">
                @php
                    $totalsByUnit = collect($items)
                        ->groupBy('unit_measurement')
                        ->map(function ($group) {
                            return collect($group)->sum('quantity');
                        });
                @endphp
                {{ $totalsByUnit->map(fn($qty, $unit) => $qty . ' ' . strtoupper($unit ?? ''))->implode(', ') ?: '-' }}
            </td>
        </tr>
        <tr>
            <td class="field-label">Tempat Asal:</td>
            <td class="field-value">
                {{ strtoupper(trim(($exporter['district'] ?? '') . ', ' . ($exporter['state'] ?? ''))) ?: '-' }}
            </td>
        </tr>
        <tr>
            <td class="field-label">Cara Penghantaran Terisytihar:</td>
            <td class="field-value">
                @php
                    $transportMap = [
                        'Land' => 'MELALUI DARAT',
                        'Sea' => 'MELALUI LAUT',
                        'Air' => 'MELALUI UDARA',
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
            <td class="field-value">{{ strtoupper($entryPoint->entry_name ?? ($entryPoint['entry_name'] ?? '-')) }}
            </td>
        </tr>
        <tr>
            <td class="field-label">Tarikh Bertolak/Berlepas:</td>
            <td class="field-value">
                {{ optional($application->eta)->format('d.m.Y') ?? '-' }}
            </td>
        </tr>

        <tr>
            <td class="field-label">Nama Hasil dan Kuantiti Terisytihar:</td>
            <td class="field-value">
                <table style="width:100%; border-collapse:collapse;">
                    @foreach ($items as $detail)
                        @php
                            $itemName = data_get(
                                $detail,
                                'consignment_detail.item_name',
                                data_get($detail, 'item_name', '-'),
                            );
                            $parts = explode('-', $itemName);
                            $afterDash = isset($parts[1]) ? trim($parts[1]) : $itemName;
                            $qty = data_get($detail, 'quantity', data_get($detail, 'consignment_detail.quantity', '-'));
                        @endphp
                        <tr>
                            <td class="mono" style="border-bottom:1px dotted #000; padding:2px 4px; width:70%;">
                                {{ strtoupper($afterDash) }}
                            </td>
                            <td class="mono" style="border-bottom:1px dotted #000; padding:2px 4px; width:30%;">
                                {{ $qty }} KG
                            </td>
                        </tr>
                    @endforeach
                </table>
            </td>
        </tr>
    </table>

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
            <div class="officer-name">{{ $approvingOfficer['name'] ?? '_____________________________' }}</div>
            <div>{{ $approvingOfficer['title'] ?? 'Pegawai Pertanian Kanan (Pegawai Pertanian)' }}</div>
            <div>DAERAH {{ strtoupper($district ?? (optional($entryPoint)->district ?? '-')) }}</div>
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

    <div class="print-footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i') }}
    </div>

    <!-- ========== QR PAGE ========== -->
    <div class="page-break"></div>

    <div class="qr-page">
        <div class="qr-page-title">QR CODE</div>

        @if (!empty($qrDataUri))
            <div class="qr-frame">
                <img src="{{ $qrDataUri }}" alt="QR Code" style="width: 300px; height: 300px;">
            </div>
        @else
            <p>QR code is unavailable.</p>
        @endif

        <div class="qr-permit-no">
            Permit No.: <span class="variable-value">{{ $permitNumber ?? '-' }}</span>
        </div>

        <div class="qr-note">
            <strong>Note:</strong> Please ensure both pages of this permit (form details and QR code page) are printed and brought together for
            <span class="qr-note-verification">verification.</span>
        </div>
    </div>

</body>

</html>