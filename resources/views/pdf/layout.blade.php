<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>@yield('title', 'Document')</title>
    <style>
        @page {
            margin: 2.8cm 1.6cm 2cm 1.6cm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.45;
            color: #2b2b2b;
        }

        /* ================= LETTERHEAD ================= */
        .letterhead {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        .letterhead td {
            vertical-align: middle;
            padding: 0;
        }
        .letterhead .logo-cell {
            width: 64px;
        }
        .letterhead .logo-cell img {
            width: 56px;
            height: 56px;
            object-fit: contain;
        }
        .letterhead .org-cell {
            padding-left: 12px;
        }
        .letterhead .org-name {
            font-size: 13pt;
            font-weight: bold;
            color: #1a1a1a;
            margin: 0;
        }
        .letterhead .org-sub {
            font-size: 8.5pt;
            color: #6b7280;
            margin: 1px 0 0 0;
        }
        .letterhead .doc-meta-cell {
            text-align: right;
            font-size: 8pt;
            color: #6b7280;
            width: 170px;
        }
        .letterhead .doc-meta-cell .doc-ref-label {
            font-size: 7.5pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #9ca3af;
        }
        .letterhead .doc-meta-cell .doc-ref {
            font-weight: bold;
            font-size: 10.5pt;
            color: #2d8f4f;
            margin-bottom: 2px;
        }

        .letterhead-divider {
            height: 3px;
            background: #2d8f4f;
            margin: 10px 0 20px 0;
            width: 100%;
        }

        .doc-title-block {
            text-align: center;
            margin: 0 0 20px 0;
        }
        .doc-title-block .doc-title {
            font-weight: bold;
            font-size: 16pt;
            margin-bottom: 3px;
            color: #1a1a1a;
        }
        .doc-title-block .doc-subtitle {
            font-size: 9.5pt;
            color: #6b7280;
        }

        /* ================= SHARED CONTENT HELPERS ================= */
        .section-block {
            margin-bottom: 18px;
        }
        .section-title {
            font-weight: bold;
            font-size: 10.5pt;
            margin-bottom: 8px;
            color: #1a1a1a;
        }
        .section-title .section-icon {
            display: inline-block;
            width: 6px;
            height: 6px;
            background: #2d8f4f;
            margin-right: 6px;
            border-radius: 1px;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 11px;
            border-radius: 10px;
            background: #eaf6ee;
            border: 1px solid #b7e0c2;
            color: #226b3c;
            font-weight: bold;
            font-size: 9pt;
        }

        /* ================= FOOTER ================= */
        .doc-footer {
            position: fixed;
            bottom: -1.5cm;
            left: 0;
            right: 0;
            font-size: 7.5pt;
            color: #9ca3af;
            border-top: 1px solid #eceff1;
            padding-top: 6px;
        }
        .doc-footer table {
            width: 100%;
        }
        .doc-footer .footer-left {
            text-align: left;
        }
        .doc-footer .footer-right {
            text-align: right;
        }
        .doc-footer .footer-page:before {
            content: "Page " counter(page) " of " counter(pages);
        }
    </style>
    @yield('extra-style')
</head>
<body>

    {{-- ================= LETTERHEAD ================= --}}
    <table class="letterhead">
        <tr>
            <td class="logo-cell">
                <img src="{{ public_path('asset/Logo-DOA.png') }}" alt="DOA Logo">
            </td>
            <td class="org-cell">
                <p class="org-name">Department of Agriculture Sabah</p>
                <p class="org-sub">Jabatan Pertanian Sabah</p>
            </td>
            <td class="doc-meta-cell">
                <div class="doc-ref-label">Reference</div>
                <div class="doc-ref">{{ $docRef ?? '—' }}</div>
                <div>{{ now()->format('d M Y, h:i A') }}</div>
            </td>
        </tr>
    </table>
    <div class="letterhead-divider"></div>

    <div class="doc-title-block">
        <div class="doc-title">@yield('doc-title', 'Document')</div>
        @hasSection('doc-subtitle')
            <div class="doc-subtitle">@yield('doc-subtitle')</div>
        @endif
    </div>

    {{-- ================= PAGE CONTENT ================= --}}
    @yield('content')

    <div class="doc-footer">
        <table>
            <tr>
                <td class="footer-left">Official Document — Department of Agriculture Sabah</td>
                <td class="footer-right footer-page"></td>
            </tr>
        </table>
    </div>

</body>
</html>