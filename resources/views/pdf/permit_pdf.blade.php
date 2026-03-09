<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Import Permit</title>
    <style>
        @page {
            margin: 1.54cm 1.54cm; /* A4 standard margin */
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.3;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin-bottom: 15px;
        }
        .header-table td {
            vertical-align: middle;
            text-align: center;
        }
        .logo {
            width: 80px;
            height: auto;
        }
        .title-text {
            text-align: center;
            font-weight: bold;
        }
        .permit-no {
            text-align: right;
            font-weight: bold;
            margin-bottom: 12px;
        }
        .content-section {
            margin-bottom: 8px;
            text-align: justify;
        }
        .underline-dots {
            border-bottom: 1px dotted #000;
            display: inline-block;
            min-width: 200px;
        }
        .conditions-list {
            margin-top: 10px;
            list-style-type: decimal;
            margin-left: 20px;
        }
        .conditions-list li {
            margin-bottom: 3px;
            text-align: justify;
        }
        .sub-list {
            list-style-type: lower-alpha;
            margin-left: 20px;
            margin-top: 5px;
        }
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            margin-top: 0; /* remove any margin above the table */
        }

        .schedule-table th, .schedule-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        .footer {
            margin-top: 30px;
            width: 100%;
        }
        .director-sign {
            float: right;
            text-align: center;
            width: 250px;
        }
        .page-break {
            page-break-after: always;
        }

        .variable-value {
            font-family: "Courier New", monospace;
            font-size: 9pt;
            word-break: break-word; /* allow breaking long text */
        }


    </style>
</head>
<body>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="width: 15%;">
                <img src="{{ public_path('asset/jata-svg.jpg') }}" class="logo" alt="Logo">
            </td>
            <td style="width: 70%;">
                <div>PLANT BIOSECURITY AND QUARANTINE DIVISION,</div>
                <div>DEPARTMENT OF AGRICULTURE, SABAH, MALAYSIA</div>
                <br>
                <div style="font-size: 14pt; font-weight: bold;">PERMIT TO IMPORT</div>
                <div style="font-size: 14pt; font-weight: bold;">REGULATED ARTICLES</div>
                <div>SIXTH / EIGHTH SCHEDULE</div>
                <div>Regulations 3, 5(1) and 5(4)</div>
            </td>
            <td style="width: 15%;">
                <img src="{{ public_path('asset/sabah-svg.jpg') }}" class="logo" alt="Sabah Logo" style="width: 80px;">
            </td>
        </tr>
    </table>

    <div class="permit-no">
        Permit No.: <span class="variable-value">{{ $permits->permit_number ?? '-' }}</span>
    </div>

    <!-- Content -->
    <table style="width:100%; border-collapse:collapse; margin-bottom:5px; table-layout: fixed; word-wrap: break-word;">

    <tr>
        <td style="white-space:nowrap; padding:2px 5px; width:25%;">
            Name of consignee
        </td>
        <td style="padding:2px 5px; border-bottom:1px dotted #000; width:75%;">
            <span class="variable-value" style="display:block; font-size:9pt; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                {{ strtoupper($importer['fullname'] ?? '-') }}
            </span>
        </td>
    </tr>

<tr>
    <td style="white-space:nowrap; padding:2px 5px; width:25%;">
        And address
    </td>
    <td style="padding:2px 5px; border-bottom:1px dotted #000; width:75%;">
        <span class="variable-value" style="display:block; font-size:8pt; line-height:1.1; white-space:normal; word-wrap:break-word;">
            {{ strtoupper(
                ($importer['address_1'] ?? '') . ', ' .
                ($importer['address_2'] ?? '') . ', ' .
                ($importer['postcode'] ?? '') . ' ' .
                ($importer['district'] ?? '') . ', ' .
                ($importer['state'] ?? '')
            ) }}
        </span>
    </td>
</tr>


    <tr>
        <td style="white-space:nowrap; padding:2px 5px;">
            Name of consignor
        </td>
        <td style="padding:2px 5px; border-bottom:1px dotted #000;">
            <span class="variable-value" style="display:block; font-size:9pt; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                {{ strtoupper($exporter['name'] ?? '-') }}
            </span>
        </td>
    </tr>

    <tr>
        <td style="white-space:nowrap; padding:2px 5px;">
            And address
        </td>
        <td style="padding:2px 5px; border-bottom:1px dotted #000; width:75%;">
        <span class="variable-value" style="display:block; font-size:8pt; line-height:1.1; white-space:normal; word-wrap:break-word;">
            {{ strtoupper($exporter['address'] ?? '-') }}
        </span>
    </td>
    </tr>

    <tr>
        <td style="white-space:nowrap; padding:2px 5px; vertical-align:top;">
            Entry Point
        </td>
        <td style="padding:2px 5px; border-bottom:1px dotted #000;">
            <span class="variable-value" style="display:block; font-size:9pt; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                {{ strtoupper($application->entryPoint->entry_name ?? '-') }}
            </span>
        </td>
    </tr>
</table>


    <div class="content-section" style="font-weight: bold;">
        This permit is issued subject to the following conditions:
    </div>

    <ol class="conditions-list">
        <li>Import license must be obtained from the relevant Ministry.</li>
        <li>A copy of this Import Permit must accompany the consignment.</li>
        <li>The regulated articles are subject to inspection prior to clearance.</li>
        <li>
            This permit is valid until <span class="underline-dots" style="width: 150px;"> <span class="variable-value">{{ $validUntil ?? '' }}</span></span> for one consignment only.
        </li>
        <li>
    The consignment must be accompanied by a Phytosanitary Certificate or a statement from the official Plant Protection Service of the country of origin bearing the following certificate:
    <ol class="sub-list">
        <li>
            (a) Treatment
            <table style="width:100%; border-collapse:collapse; margin-top:3px; margin-bottom:5px;">
                <tr>
                    <td style="border-bottom:1px dotted #000; height:16px;"></td>
                </tr>
                <tr>
                    <td style="border-bottom:1px dotted #000; height:16px;"></td>
                </tr>
            </table>
        </li>
        <li>
            (b) Other declaration
            <table style="width:100%; border-collapse:collapse; margin-top:3px; margin-bottom:5px;">
                <tr>
                    <td style="border-bottom:1px dotted #000; height:16px;"></td>
                </tr>
                <tr>
                    <td style="border-bottom:1px dotted #000; height:16px;"></td>
                </tr>
            </table>
        </li>
    </ol>
</li>

       <li style="margin-bottom:100px;">Further conditions</li>

    </ol>

    <br>

    <div class="content-section" style="font-weight: bold;">
        Schedule:
    </div>

    <table class="schedule-table">
    <thead>
        <tr>
            <th style="width: 50%;">Descriptions</th>
            <th style="width: 20%;">Quantity</th>
            <th style="width: 30%;">Country of Origin</th>
        </tr>
    </thead>
    <tbody>
        @php
            $itemName = $detail['item_name'] ?? '-';
            $parts = explode('-', $itemName);
            $afterDash = isset($parts[1]) ? trim($parts[1]) : $itemName;
            
            $country = \App\Models\Country::select('name')->where('code', $exporter['country'])->first();
        @endphp
        <tr>
            <td style="height:70px;"><span class="variable-value">{{ $afterDash }}</span></td>
            <td style="height:70px;"><span class="variable-value">{{ ($permits['quantity'] ?? '-') . ' ' . ($permits['unit_measurement'] ?? '') }}</span></td>
            <td style="height:70px;"><span class="variable-value">{{ $country->name ?? '-' }}</span></td>
        </tr>
    </tbody>
</table>


    <div class="footer" style="padding-top:30px;">
    <div style="float: left;">
        Date of Issue: <span class="variable-value">{{ now()->format('d/M/Y') }}</span>
    </div>
    <div class="director-sign">
        <div style="font-weight: bold;">Director of Agriculture</div>
        <div>Sabah, Malaysia</div>
    </div>
    <div style="clear: both;"></div>
</div>


</body>
</html>
