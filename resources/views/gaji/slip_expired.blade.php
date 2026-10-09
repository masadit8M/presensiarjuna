<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Slip Gaji Sudah Tidak Aktif - PAUD Arjuna Cendekia</title>
    <link rel="stylesheet" href="{{ asset('tabler/dist/css/tabler.min.css') }}">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fb 0%, #e8edf5 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 15px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        .expired-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(30, 41, 59, 0.08);
            border: 1px solid #e2e8f0;
            max-width: 520px;
            width: 100%;
            overflow: hidden;
        }
        .expired-header {
            background: #fff;
            padding: 28px 24px 16px 24px;
            text-align: center;
            border-bottom: 1px solid #f1f5f9;
        }
        .expired-body {
            padding: 24px;
        }
        .icon-circle {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: #fef2f2;
            color: #dc2626;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px auto;
            border: 2px solid #fee2e2;
        }
        .info-table {
            width: 100%;
            background: #f8fafc;
            border-radius: 10px;
            padding: 12px 16px;
            margin: 16px 0;
            border: 1px solid #e2e8f0;
            font-size: 0.9rem;
        }
        .info-table td {
            padding: 6px 4px;
            vertical-align: top;
        }
        .info-table td.label {
            color: #64748b;
            width: 130px;
        }
        .info-table td.value {
            color: #1e293b;
            font-weight: 600;
        }
        .admin-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            padding: 16px;
            margin-top: 18px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="expired-card">
        <div class="expired-header">
            <img src="{{ asset('assets/img/logo_yayasan.png') }}" alt="Logo Yayasan" style="height: 48px; width: auto; margin-bottom: 8px;" onerror="this.style.display='none'">
            <div class="fw-bold text-dark" style="font-size: 1.1rem; letter-spacing: 0.5px;">YAYASAN ARJUNA CENDEKIA</div>
            <div class="text-muted small">Sistem Dokumen Slip Gaji Karyawan</div>
        </div>

        <div class="expired-body">
            <div class="icon-circle">
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <rect x="5" y="11" width="14" height="10" rx="2" />
                    <circle cx="12" cy="16" r="1" />
                    <path d="M8 11v-4a4 4 0 0 1 8 0v4" />
                </svg>
            </div>

            <h3 class="text-center text-danger mb-1 fw-bold">Link Sudah Tidak Aktif</h3>
            <p class="text-center text-muted small mb-3">
                Dokumen slip gaji hanya dapat dibuka / diunduh <strong>1 (satu) kali</strong> demi keamanan dan kerahasiaan data penghasilan.
            </p>

            @if (!empty($detail))
                <div class="info-table">
                    <table>
                        <tr>
                            <td class="label">Nama Karyawan</td>
                            <td class="value">: {{ $detail->nama_lengkap }}</td>
                        </tr>
                        <tr>
                            <td class="label">NIK</td>
                            <td class="value">: {{ $detail->nik }}</td>
                        </tr>
                        <tr>
                            <td class="label">Periode Gaji</td>
                            <td class="value">: {{ $detail->nama_periode }}</td>
                        </tr>
                        <tr>
                            <td class="label">Status Akses</td>
                            <td class="value text-danger">
                                : Telah diunduh
                                @if (!empty($detail->download_at))
                                    <div class="text-muted fw-normal small">Pada {{ date('d/m/Y H:i', strtotime($detail->download_at)) }} WIB</div>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            @endif

            <div class="admin-box">
                <div class="fw-bold text-primary mb-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <circle cx="12" cy="12" r="9" />
                        <line x1="12" y1="8" x2="12.01" y2="8" />
                        <polyline points="11 12 12 12 12 16 13 16" />
                    </svg>
                    Butuh Mengunduh Ulang?
                </div>
                <div class="text-muted small mb-3">
                    Jika Anda belum sempat menyimpan file PDF ke HP/perangkat Anda, silakan lapor ke <strong>Superadmin</strong> agar tautan dapat di-reset kembali.
                </div>

                @php
                    $namaKaryawan = $detail->nama_lengkap ?? 'Karyawan';
                    $nikKaryawan = $detail->nik ?? '-';
                    $periodeKaryawan = $detail->nama_periode ?? '-';
                    $mailSubject = "Permohonan Reset Link Slip Gaji - {$namaKaryawan} ({$nikKaryawan})";
                    $mailBody = "Assalamu'alaikum Mas Adit / Superadmin,\n\nSaya memohon bantuan untuk me-reset link slip gaji saya karena file PDF belum sempat tersimpan di perangkat saya.\n\nData Karyawan:\n- Nama: {$namaKaryawan}\n- NIK: {$nikKaryawan}\n- Periode: {$periodeKaryawan}\n\nMohon bantuannya untuk mengaktifkan kembali link slip gaji saya.\n\nTerima kasih.";
                    $mailHref = "mailto:masaditfb@gmail.com?subject=" . rawurlencode($mailSubject) . "&body=" . rawurlencode($mailBody);
                @endphp

                <a href="{{ $mailHref }}" class="btn btn-primary w-100 fw-bold mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <rect x="3" y="5" width="18" height="14" rx="2" />
                        <polyline points="3 7 12 13 21 7" />
                    </svg>
                    Lapor ke Superadmin: masaditfb@gmail.com
                </a>
                <div class="text-muted" style="font-size: 0.78rem;">
                    Klik tombol di atas untuk membuka email permohonan otomatis ke <strong>masaditfb@gmail.com</strong>
                </div>
            </div>

            <div class="text-center mt-3 text-muted" style="font-size: 0.75rem;">
                &copy; {{ date('Y') }} PAUD Arjuna Cendekia &bull; Perlindungan Privasi & Keamanan Slip Gaji
            </div>
        </div>
    </div>
</body>
</html>
