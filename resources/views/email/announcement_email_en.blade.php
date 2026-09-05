<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $announcement->title }}</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f8f9fa;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f8f9fa; min-height: 100vh;">
    <tr>
        <td align="center" style="padding: 40px 20px;">
            <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; width: 100%;">

                {{-- Logo (embedded as base64) --}}
                <tr>
                    <td align="center" style="padding-bottom: 30px;">
                        @if(file_exists(public_path('images/Logo-DOA.png')))
                            <img src="{{ $message->embed(public_path('images/Logo-DOA.png')) }}" alt="QIS Logo" style="height: 60px; display: block;">
                        @else
                            <p style="font-size: 18px; font-weight: bold; color: #2e7d32;">QIS System</p>
                        @endif
                    </td>
                </tr>

                {{-- Card --}}
                <tr>
                    <td>
                        <table width="100%" cellpadding="0" cellspacing="0" border="0"
                               style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                            <tr>
                                <td style="padding: 40px 50px;">

                                    {{-- Label (no emoji, pure CSS badge) --}}
                                    <table cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 12px;">
                                        <tr>
                                            <td style="background-color: #e8f5e9; border-radius: 4px; padding: 4px 10px;">
                                                <span style="font-size: 11px; font-weight: 700; color: #2e7d32; text-transform: uppercase; letter-spacing: 1px;">
                                                    &#9679; ANNOUNCEMENT
                                                </span>
                                            </td>
                                        </tr>
                                    </table>

                                    {{-- Title --}}
                                    <h1 style="margin: 0 0 20px 0; font-size: 22px; font-weight: 700; color: #1a1a1a;">
                                        {{ $announcement->title }}
                                    </h1>

                                    {{-- Divider --}}
                                    <hr style="border: none; border-top: 1px solid #eeeeee; margin: 0 0 20px 0;">

                                    {{-- Content --}}
                                    <div style="font-size: 14px; line-height: 1.8; color: #444444;">
                                        {!! $announcement->content !!}
                                    </div>

                                    {{-- Image (embedded as base64) --}}
                                    @if(isset($imagePath) && $imagePath)
                                        <div style="margin-top: 24px; text-align: center;">
                                            <img src="{{ $message->embed($imagePath) }}" alt="Announcement Image"
                                                 style="max-width: 100%; border-radius: 6px; border: 1px solid #eeeeee;">
                                        </div>
                                    @endif

                                    {{-- Valid dates --}}
                                    @if($announcement->valid_from || $announcement->valid_until)
                                        <p style="margin: 24px 0 0 0; font-size: 12px; color: #999999;">
                                            @if($announcement->valid_from && $announcement->valid_until)
                                                Valid: {{ \Carbon\Carbon::parse($announcement->valid_from)->format('d M Y') }} &#8211; {{ \Carbon\Carbon::parse($announcement->valid_until)->format('d M Y') }}
                                            @elseif($announcement->valid_from)
                                                Valid from: {{ \Carbon\Carbon::parse($announcement->valid_from)->format('d M Y') }}
                                            @elseif($announcement->valid_until)
                                                Valid until: {{ \Carbon\Carbon::parse($announcement->valid_until)->format('d M Y') }}
                                            @endif
                                        </p>
                                    @endif

                                    {{-- Signature --}}
                                    <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eeeeee;">
                                        <p style="margin: 0; font-size: 14px; color: #6c757d;">Regards,</p>
                                        <p style="margin: 4px 0 0 0; font-size: 14px; font-weight: 600; color: #333333;">QIS System &#8212; Jabatan Pertanian Sabah</p>
                                    </div>

                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td align="center" style="padding-top: 20px;">
                        <p style="margin: 0; font-size: 11px; color: #aaaaaa;">
                            &#169; {{ date('Y') }} QIS. All rights reserved.<br>
                            This email was sent because you are a registered user of the QIS system.
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
