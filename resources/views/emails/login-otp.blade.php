<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kode OTP Masuk Newsroom</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; padding: 24px; color: #1e293b;">
    <div style="max-width: 520px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 32px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <div style="text-align: center; margin-bottom: 24px;">
            <div style="display: inline-block; background-color: #e11d48; color: #ffffff; font-weight: 900; font-size: 20px; padding: 8px 16px; border-radius: 8px;">
                PORTAL BERITA
            </div>
            <h2 style="font-size: 20px; font-weight: 800; color: #0f172a; margin-top: 16px; margin-bottom: 4px;">
                Verifikasi Keamanan Akun Redaksi
            </h2>
            <p style="font-size: 13px; color: #64748b; margin: 0;">
                Halo <strong>{{ $userName }}</strong>, gunakan kode di bawah ini untuk masuk ke Studio Newsroom.
            </p>
        </div>

        <div style="background-color: #fff1f2; border: 1px dashed #f43f5e; border-radius: 10px; padding: 20px; text-align: center; margin: 24px 0;">
            <span style="font-size: 11px; font-weight: 700; color: #be123c; text-transform: uppercase; letter-spacing: 0.1em; display: block; margin-bottom: 8px;">
                KODE VERIFIKASI OTP ANDA
            </span>
            <div style="font-family: 'Courier New', Courier, monospace; font-size: 36px; font-weight: 900; letter-spacing: 8px; color: #e11d48;">
                {{ $otpCode }}
            </div>
            <span style="font-size: 12px; color: #9f1239; display: block; margin-top: 8px;">
                Berlaku selama 5 menit. Jangan berikan kode ini kepada siapa pun!
            </span>
        </div>

        <p style="font-size: 12px; color: #64748b; line-height: 1.6; margin-bottom: 16px;">
            Sistem mendeteksi upaya masuk ke area sensitif redaksi (Super Admin / Pemred / Editor Desk). Jika Anda tidak merasa melakukan permintaan ini, segera hubungi tim IT Administrator.
        </p>

        <div style="border-top: 1px solid #e2e8f0; padding-top: 16px; text-align: center; font-size: 11px; color: #94a3b8;">
            &copy; {{ date('Y') }} Portal Berita Digital &bull; Sistem Autentikasi Redaksi
        </div>
    </div>
</body>
</html>
