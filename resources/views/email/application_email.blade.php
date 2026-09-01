@extends('pages.front', ['title' => 'reset_password'])

@section('content')
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f8f9fa; min-height: 100vh;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; width: 100%;">
                    <!-- Logo -->
                    <tr>
                        <td align="center" style="padding-bottom: 30px;">
                        @if(file_exists(public_path('images/Logo-DOA.png')))
                            <img src="{{ $message->embed(public_path('images/Logo-DOA.png')) }}" alt="DOA Logo" style="height: 60px; display: block;">
                        @else
                            <p style="font-size: 18px; font-weight: bold; color: #2e7d32;">QIS System</p>
                        @endif
                        </td>
                    </tr>

                    <!-- Card -->
                    <tr>
                        <td>
                            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                                style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                                <tr>
                                    <td style="padding: 50px;">
                                        <!-- Title -->
                                        <h1 style="margin: 0 0 15px 0; font-size: 24px; font-weight: 600; color: #333333;">
                                            QIS Application Update</h1>

                                        <!-- Message -->
                                        <p style="margin: 0 0 20px 0; font-size: 14px; line-height: 1.6; color: #6c757d;">
                                            {!! $news !!}
                                        </p>

                                       


                                        <!-- Signature -->
                                        <div style="margin-bottom: 40px;">
                                            <p style="margin: 0; font-size: 14px; color: #6c757d;">Regards,</p>
                                            <p
                                                style="margin: 5px 0 0 0; font-size: 14px; font-weight: 600; color: #6c757d;">
                                                QIS System</p>
                                        </div>

                                        
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding-top: 20px;">
                            <p style="margin: 0; font-size: 11px; color: #6c757d;">© {{ date('Y') }} QIS. All rights
                                reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
@endsection