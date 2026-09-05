<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sahkan E-mel Anda</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f8f9fa;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f8f9fa; min-height: 100vh;">
    <tr>
        <td align="center" style="padding: 40px 20px;">
            <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; width: 100%;">

                {{-- Logo --}}
                <tr>
                    <td align="center" style="padding-bottom: 30px;">
                        @if(file_exists(public_path('images/Logo-DOA.png')))
                            <img src="{{ $message->embed(public_path('images/Logo-DOA.png')) }}" alt="DOA Logo" style="height: 60px; display: block;">
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

                                    {{-- Label --}}
                                    <table cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 12px;">
                                        <tr>
                                            <td style="background-color: #f3e5f5; border-radius: 4px; padding: 4px 10px;">
                                                <span style="font-size: 11px; font-weight: 700; color: #7b1fa2; text-transform: uppercase; letter-spacing: 1px;">
                                                    &#9679; PENGESAHAN
                                                </span>
                                            </td>
                                        </tr>
                                    </table>

                                    {{-- Title --}}
                                    <h1 style="margin: 0 0 20px 0; font-size: 22px; font-weight: 700; color: #1a1a1a;">
                                        Sahkan Alamat E-mel Anda
                                    </h1>

                                    {{-- Divider --}}
                                    <hr style="border: none; border-top: 1px solid #eeeeee; margin: 0 0 20px 0;">

                                    {{-- Content --}}
                                    <div style="font-size: 14px; line-height: 1.8; color: #444444;">
                                        <p style="margin-top: 0;">Helo{{ isset($user) && $user->fullname ? ' ' . $user->fullname : '' }},</p>
                                        <p>Terima kasih kerana mendaftar akaun dengan Sistem QIS. Untuk melengkapkan pendaftaran dan mengamankan akaun anda, sila sahkan alamat e-mel anda dengan menekan butang di bawah.</p>

                                        <div style="margin-top: 30px; margin-bottom: 30px; text-align: center;">
                                            <a href="{{ $url ?? '#' }}" style="display: inline-block; background-color: #2e7d32; color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 6px; font-weight: 600;">Sahkan Alamat E-mel</a>
                                        </div>
                                        
                                        <p style="color: #6c757d; font-size: 13px;">Jika anda tidak mendaftar akaun ini, tiada tindakan lanjut diperlukan.</p>
                                    </div>

                                    {{-- Signature --}}
                                    <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eeeeee;">
                                        <p style="margin: 0; font-size: 14px; color: #6c757d;">Yang Ikhlas,</p>
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
                            &#169; {{ date('Y') }} QIS. Hak cipta terpelihara.<br>
                            E-mel ini dihantar kerana anda adalah pengguna berdaftar sistem QIS.
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>