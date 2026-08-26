<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Inspection Certificate</title>
    <style>
        @page {
            margin: 1.5cm 1.5cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 9.5pt;
            line-height: 1.35;
            color: #000;
        }
        .original-box {
            display: inline-block;
            border: 1px solid #000;
            padding: 3px 14px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .ref-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .ref-table td {
            vertical-align: top;
            padding: 0;
        }
        .ref-lines td {
            padding: 1px 0;
            white-space: nowrap;
        }
        .dotted {
            border-bottom: 1px dotted #000;
            display: inline-block;
            min-width: 180px;
        }
        .header-logos {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0 8px 0;
        }
        .header-logos td {
            text-align: center;
            vertical-align: middle;
        }
        .logo {
            width: 65px;
            height: auto;
        }
        .logo-caption {
            font-weight: bold;
            font-size: 9pt;
            margin-top: 2px;
        }
        .dept-name {
            text-align: left;
            font-weight: bold;
            font-size: 10pt;
            padding-left: 10px;
        }
        .kepada-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .kepada-table td {
            vertical-align: top;
        }
        .kepada-lines {
            border-bottom: 1px dotted #000;
            height: 14px;
            display: block;
        }
        .serial-box {
            border: 1px solid #000;
            padding: 6px 10px;
            text-align: center;
            width: 160px;
            float: right;
        }
        .serial-box .no {
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: 2px;
        }
        .title-block {
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            margin: 10px 0 12px 0;
        }
        .intro-text {
            text-align: justify;
            margin-bottom: 12px;
        }
        .intro-text .en {
            font-style: italic;
        }
        .particulars-title {
            font-weight: bold;
            margin-bottom: 6px;
        }
        .particulars-table {
            width: 100%;
            border-collapse: collapse;
        }
        .particulars-table td {
            padding: 3px 0;
            vertical-align: top;
        }
        .p-label {
            width: 30%;
            white-space: nowrap;
            padding-right: 6px;
        }
        .p-label .en {
            font-style: italic;
        }
        .p-value {
            border-bottom: 1px dotted #000;
        }
        .p-value.blank {
            color: transparent;
        }
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        .footer-table td {
            vertical-align: top;
            width: 50%;
        }
        .sign-line {
            border-bottom: 1px dotted #000;
            width: 220px;
            height: 30px;
            display: block;
            margin-bottom: 4px;
        }
        .verify-box {
            border: 1px solid #000;
            padding: 8px;
        }
        .verify-title {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 4px;
        }
        .verify-title .en {
            font-weight: normal;
            font-style: italic;
            text-decoration: none;
        }
        .verify-text {
            margin-bottom: 10px;
        }
        .verify-text .en {
            font-style: italic;
        }
        .officer-line {
            border-bottom: 1px dotted #000;
            width: 100%;
            height: 26px;
            display: block;
            margin-bottom: 2px;
        }
        .center-caption {
            text-align: center;
            font-size: 8.5pt;
        }
        .doc-code {
            font-size: 8pt;
            margin-top: 20px;
        }
        .note {
            font-size: 8pt;
            font-style: italic;
        }
    </style>
</head>
<body>

    <div class="original-box">ORIGINAL</div>

    <table class="ref-table">
        <tr>
            <td style="width: 55%;">
                <table class="ref-lines">
                    <tr><td>Ruj.: <span class="dotted">{{ $application->reference_no ?? '' }}</span></td></tr>
                    <tr><td>Tarikh: <span class="dotted">{{ optional($application->created_at)->format('d/m/Y') ?? '' }}</span></td></tr>
                    <tr><td>Daerah: <span class="dotted">{{ $entry->district ?? '' }}</span></td></tr>
                </table>
            </td>
            <td style="width: 45%;"></td>
        </tr>
    </table>

    <table class="header-logos">
        <tr>
            <td style="width: 45%;">
                <img src="{{ public_path('asset/sabah-svg.jpg') }}" class="logo" alt="Sabah Logo"><br>
                <div class="logo-caption">SABAH, MALAYSIA</div>
            </td>
            <td style="width: 15%;">
                <img src="{{ public_path('asset/jata-svg.jpg') }}" class="logo" alt="JPS Logo">
            </td>
            <td class="dept-name" style="width: 40%;">
                Jabatan Pertanian Sabah<br>
                (Seksyen Biosekuriti dan<br>
                Kuarantin Tumbuhan)
            </td>
        </tr>
    </table>

    <table class="kepada-table">
        <tr>
            <td style="width: 65%;">
                Kepada: Pegawai Penguasa,<br>
                <span style="margin-left: 40px;">(To)</span> Jabatan Kastam DiRaja Malaysia,<br>
                <span class="kepada-lines">&nbsp;</span>
                <span class="kepada-lines">&nbsp;</span>
            </td>
            <td style="width: 35%;">
                <div class="serial-box">
                    No. Siri:<br>
                    <span class="no">{{ $application->serial_number ?? $application->application_id }}</span><br>
                    <span style="font-style: italic; font-weight: normal; font-size: 8.5pt;">(Serial No.)</span>
                </div>
            </td>
        </tr>
    </table>

    <div class="title-block">
        SIJIL PEMERIKSAAN <span style="font-style: italic;">(INSPECTION CERTIFICATE)</span>
    </div>

    <div class="intro-text">
        Sijil Pemeriksaan ini adalah sah sebagai ganti kepada Permit Import bagi pengimportan tumbuhan/produk
        tumbuhan yang dikecualikan daripada keperluan Permit selaras dengan sub-peraturan 4(1)(a) dan 4(1)(b).
        Konsainan import adalah tertakluk kepada pemeriksaan Kuarantin selaras dengan Peraturan No. 10 dan
        tertakluk kepada tindakan-tindakan Kuarantin sekiranya didapati berpenyakit atau melanggar mana-mana
        peruntukan dalam Peraturan-Peraturan Kuarantin Tumbuhan 1981; Akta Kuarantin Tumbuhan 1976.
        <span class="en">
            (This Inspection Certificate is substitute to an Import Permit for importation of plants/plant
            products which is exempted under sub-regulation No. 4(1)(a) and 4(1)(b). Imported consignment
            are subjected to Quarantine inspection in accordance to Regulation No. 10 and such items are
            liable to Quarantine actions if found to be diseased, or harbouring pests or in contravention
            with the current Plant Quarantine Regulations 1981; Plant Quarantine Act 1976.)
        </span>
    </div>

    <div class="particulars-title">Butir-butir <span style="font-weight: normal; font-style: italic;">(Particulars)</span>:</div>

    <table class="particulars-table">
        <tr>
            <td class="p-label">1. Tumbuhan/Produk Tumbuhan:<br><span class="en">(Plants/Plant Products)</span></td>
            <td class="p-value">
                @forelse($items as $detail)
                    @php
                        $itemName = $detail['consignment_detail']['item_name'] ?? '-';
                        $parts = explode('-', $itemName);
                        $afterDash = isset($parts[1]) ? trim($parts[1]) : $itemName;
                    @endphp
                    {{ $afterDash }}@if(!$loop->last), @endif
                @empty
                    &nbsp;
                @endforelse
            </td>
        </tr>
        <tr><td class="p-label">&nbsp;</td><td class="p-value">&nbsp;</td></tr>
        <tr><td class="p-label">&nbsp;</td><td class="p-value">&nbsp;</td></tr>

        <tr>
            <td class="p-label">2. Kuantiti <span class="en">(Quantity)</span>:</td>
            <td class="p-value">
                @forelse($items as $detail)
                    {{ ($detail['quantity'] ?? '-') . ' ' . ($detail['unit_measurement'] ?? '') }}@if(!$loop->last), @endif
                @empty
                    &nbsp;
                @endforelse
            </td>
        </tr>

        <tr>
            <td class="p-label">3. Negara Asal <span class="en">(Place of Origin)</span>:</td>
            <td class="p-value">
                @php
                    $country = \App\Models\Country::select('name')->where('code', $exporter['country'] ?? null)->first();
                @endphp
                {{ $country->name ?? '-' }}
            </td>
        </tr>

        <tr>
            <td class="p-label">4. Nama & Alamat Pengeksport:<br><span class="en">(Exporter's Name & Address)</span></td>
            <td class="p-value">{{ strtoupper($exporter['name'] ?? '-') }}</td>
        </tr>
        <tr>
            <td class="p-label">&nbsp;</td>
            <td class="p-value">{{ strtoupper($exporter['address'] ?? '-') }}</td>
        </tr>
        <tr><td class="p-label">&nbsp;</td><td class="p-value">&nbsp;</td></tr>

        <tr>
            <td class="p-label">5. Nama & Alamat Pengimport:<br><span class="en">(Importer's Name & Address)</span></td>
            <td class="p-value">{{ strtoupper($importer['fullname'] ?? '-') }}</td>
        </tr>
        <tr>
            <td class="p-label">&nbsp;</td>
            <td class="p-value">
                {{ strtoupper(
                    ($importer['address_1'] ?? '') . ', ' .
                    ($importer['address_2'] ?? '') . ', ' .
                    ($importer['postcode'] ?? '') . ' ' .
                    ($importer['district'] ?? '') . ', ' .
                    ($importer['state'] ?? '')
                ) }}
            </td>
        </tr>
        <tr><td class="p-label">&nbsp;</td><td class="p-value">&nbsp;</td></tr>

        <tr>
            <td class="p-label">6. Kaedah Pengangkutan <span class="en">(Means of Conveyance)</span>:</td>
            <td class="p-value">{{ $application->mode_of_transport ?? '-' }}</td>
        </tr>

        <tr>
            <td class="p-label">7. Tarikh & Masa Ketibaan <span class="en">(Date & Time of Arrival)</span>:</td>
            <td class="p-value">{{ $application->arrival_date ?? '-' }} {{ $application->arrival_time ?? '' }}</td>
        </tr>

        <tr>
            <td class="p-label">8. Sijil ini adalah sah sehingga:<br><span class="en">(This Certificate is valid until)</span></td>
            <td class="p-value">{{ $application->valid_until ?? ($validUntil ?? '-') }}</td>
        </tr>
    </table>

    <table class="footer-table">
        <tr>
            <td>
                <span class="sign-line">&nbsp;</span>
                (ADQ/SAO/AO)<br>
                Seksyen Biosekuriti dan Kuarantin Tumbuhan,<br>
                <span class="en">(Plant Biosecurity and Quarantine Section),</span><br>
                b/p PENGARAH PERTANIAN.<br>
                <span class="en">(for DIRECTOR OF AGRICULTURE).</span>
            </td>
            <td>
                <div class="verify-box">
                    <div class="verify-title">
                        Pengesahan Pemeriksaan <span class="en">(Verification of Inspection)</span>
                    </div>
                    <div class="verify-text">
                        <strong>Saya telah memeriksa dan mengesahkan</strong> bahawa konsainan ini
                        *bebas perosak/berperosak dan boleh *dilepaskan/disita untuk tindakan lanjut.<br>
                        <span class="en">
                            (I have inspected and verified that the items are *free from disease/diseased
                            and bound for *released/to be detained for further action).
                        </span>
                    </div>
                    <span class="officer-line">&nbsp;</span>
                    <div class="center-caption">(Inspecting Officer)</div>
                    <br>
                    Tarikh <span class="en">(Date)</span>: <span class="dotted">&nbsp;</span>
                    <br><br>
                    <span class="note">*Sila potong yang tidak berkenaan <span class="en">(strikethrough if not applicable)</span></span>
                </div>
            </td>
        </tr>
    </table>

    <div class="doc-code">P.K. 628 (L) - 2026</div>

</body>
</html>