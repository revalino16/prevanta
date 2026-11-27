<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode OTP Reset Kata Sandi - Prevanta</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f7f0f2; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #2a2420;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f7f0f2; padding: 28px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 520px; background-color: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #f0dde3;">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #9b2c45 0%, #7a2137 100%); background-color: #9b2c45; padding: 26px 28px; text-align: center;">
                            <p style="margin: 0; font-size: 12px; letter-spacing: 1.2px; text-transform: uppercase; color: #f8d5df; font-weight: 600;">
                                Prevanta • Posyandu Jambu 77
                            </p>
                            <h1 style="margin: 8px 0 0; font-size: 22px; line-height: 1.3; color: #ffffff; font-weight: 700;">
                                Kode Verifikasi Reset Kata Sandi
                            </h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 28px;">
                            <p style="margin: 0 0 14px; font-size: 15px; line-height: 1.6; color: #2a2420;">
                                Halo, <strong>{{ $userName }}</strong>,
                            </p>
                            <p style="margin: 0 0 22px; font-size: 14px; line-height: 1.6; color: #5b524d;">
                                Kami menerima permintaan untuk mengatur ulang kata sandi akun Prevanta Anda. Silakan masukkan kode OTP 6 digit di bawah ini pada halaman verifikasi:
                            </p>

                            <!-- Kotak Kode OTP -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 0 0 22px;">
                                <tr>
                                    <td align="center" style="background-color: #fdf0f3; border: 1.5px dashed #c8647d; border-radius: 14px; padding: 20px 16px;">
                                        <p style="margin: 0 0 6px; font-size: 12px; color: #7a2137; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">
                                            Kode OTP Anda
                                        </p>
                                        <p style="margin: 0; font-family: 'Courier New', Courier, monospace; font-size: 34px; font-weight: 800; letter-spacing: 10px; color: #7a2137;">
                                            {{ $otpCode }}
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0 0 16px; font-size: 13px; line-height: 1.6; color: #5b524d;">
                                Kode ini hanya berlaku selama <strong>{{ $expiresInMinutes }} menit</strong> dan hanya dapat digunakan satu kali.
                            </p>

                            <!-- Peringatan Keamanan -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="background-color: #fdf6ea; border-left: 4px solid #d9822b; border-radius: 8px; padding: 12px 14px;">
                                        <p style="margin: 0; font-size: 12.5px; line-height: 1.55; color: #7a4b0b;">
                                            <strong>Demi keamanan akun Anda:</strong> Jangan bagikan kode OTP ini kepada siapa pun, termasuk pihak yang mengaku kader atau bidan. Jika Anda tidak merasa meminta reset kata sandi, abaikan email ini.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #fbf7f8; padding: 18px 28px; text-align: center; border-top: 1px solid #f0dde3;">
                            <p style="margin: 0; font-size: 12px; color: #8c7f7a;">
                                Email ini dikirim secara otomatis oleh sistem <strong>Prevanta - Posyandu Jambu 77</strong>.<br>
                                Mohon tidak membalas email ini.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>