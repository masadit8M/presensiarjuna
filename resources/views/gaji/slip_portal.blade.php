<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unduh Dokumen Slip Gaji - PAUD Arjuna Cendekia</title>
    <link rel="stylesheet" href="{{ asset('tabler/dist/css/tabler.min.css') }}">
    <style>
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 15px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        .portal-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 12px 35px rgba(15, 23, 42, 0.1);
            border: 1px solid #cbd5e1;
            max-width: 520px;
            width: 100%;
            overflow: hidden;
        }
        .portal-header {
            background: #ffffff;
            padding: 24px 20px 16px 20px;
            text-align: center;
            border-bottom: 1px solid #f1f5f9;
        }
        .portal-body {
            padding: 24px;
        }
        .security-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fef3c7;
            color: #92400e;
            font-size: 0.8rem;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 50px;
            margin-bottom: 12px;
            border: 1px solid #fde68a;
        }
        .employee-summary {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
        }
        .employee-summary table {
            width: 100%;
            font-size: 0.92rem;
        }
        .employee-summary td {
            padding: 5px 4px;
            vertical-align: top;
        }
        .employee-summary td.lbl {
            color: #64748b;
            width: 120px;
        }
        .employee-summary td.val {
            color: #0f172a;
            font-weight: 600;
        }
        .warning-box {
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
            border-radius: 8px;
            padding: 14px;
            margin-bottom: 22px;
            font-size: 0.875rem;
            color: #78350f;
            line-height: 1.5;
        }
        .btn-download-main {
            background: #0284c7;
            border-color: #0284c7;
            color: #ffffff;
            font-size: 1.05rem;
            padding: 14px 20px;
            border-radius: 10px;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);
            transition: all 0.2s ease;
        }
        .btn-download-main:hover {
            background: #0369a1;
            border-color: #0369a1;
            color: #ffffff;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="portal-card">
        <div class="portal-header">
            <img src="{{ asset('assets/img/logo_yayasan.png') }}" alt="Logo Yayasan" style="height: 52px; width: auto; margin-bottom: 8px;" onerror="this.style.display='none'">
            <div class="fw-bold text-dark" style="font-size: 1.15rem; letter-spacing: 0.5px;">YAYASAN ARJUNA CENDEKIA</div>
            <div class="text-muted small">Portal Pengunduhan Slip Gaji Elektronik</div>
        </div>

        <div class="portal-body">
            <div class="text-center">
                <div class="security-badge">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <circle cx="12" cy="12" r="9" />
                        <line x1="12" y1="8" x2="12.01" y2="8" />
                        <polyline points="11 12 12 12 12 16 13 16" />
                    </svg>
                    KEAMANAN: 1X AKSES AKTIF
                </div>
            </div>

            <div class="employee-summary">
                <table>
                    <tr>
                        <td class="lbl">Nama Karyawan</td>
                        <td class="val">: {{ $detail->nama_lengkap }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">NIK</td>
                        <td class="val">: {{ $detail->nik }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Jabatan</td>
                        <td class="val">: {{ $detail->jabatan ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Periode Gaji</td>
                        <td class="val">: {{ $detail->nama_periode }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Cabang</td>
                        <td class="val">: {{ $detail->nama_cabang ?? $detail->kode_cabang }}</td>
                    </tr>
                </table>
            </div>

            <div class="warning-box">
                <div class="fw-bold d-flex align-items-center mb-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M12 9v2m0 4v.01"/>
                        <path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"/>
                    </svg>
                    Perhatian Penting (Akses 1x):
                </div>
                <div>
                    Demi menjaga kerahasiaan nominal gaji, link ini disetel <strong>hanya dapat diunduh 1 (satu) kali</strong>. Setelah Anda menekan tombol unduh di bawah, tautan ini akan langsung <strong>NONAKTIF</strong>. Mohon pastikan file PDF langsung disimpan di folder unduhan perangkat HP/Laptop Anda.
                </div>
            </div>

            <div id="downloadArea">
                <a href="/slip-gaji/download/{{ $detail->id }}/{{ $token }}" id="btnDownload" class="btn btn-download-main w-100 d-flex align-items-center justify-content-center" onclick="handleDownloadStart()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" class="me-2">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" />
                        <polyline points="7 11 12 16 17 11" />
                        <line x1="12" y1="4" x2="12" y2="16" />
                    </svg>
                    UNDUH SLIP GAJI SEKARANG (PDF)
                </a>

                <a href="/slip-gaji/download/{{ $detail->id }}/{{ $token }}?inline=1" target="_blank" id="btnInline" class="btn btn-outline-secondary w-100 mt-2" onclick="handleDownloadStart()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <circle cx="12" cy="12" r="2" />
                        <path d="M22 12c-2.667 4.667 -6 7 -10 7s-7.333 -2.333 -10 -7c2.667 -4.667 6 -7 10 -7s7.333 2.333 10 7" />
                    </svg>
                    Buka Dokumen di Layar (1x Akses)
                </a>
            </div>

            <div id="downloadedNotice" class="alert alert-success mt-3 d-none text-start" role="alert">
                <div class="fw-bold mb-1 d-flex align-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" class="me-1 text-success">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M5 12l5 5l10 -10"/>
                    </svg>
                    Dokumen Slip Gaji Sedang Diunduh!
                </div>
                <div class="small">
                    File PDF sedang dikirim ke folder Download perangkat Anda. Silakan simpan file tersebut dengan aman. Sesuai sistem keamanan privasi, tautan ini kini telah <strong>dinonaktifkan</strong>.
                </div>
                <div class="small text-muted mt-2">
                    Jika di kemudian hari memerlukan salinan ulang, silakan melapor ke Superadmin: <strong>masaditfb@gmail.com</strong>
                </div>
            </div>

            <div class="text-center mt-4 text-muted" style="font-size: 0.75rem;">
                &copy; {{ date('Y') }} Yayasan Arjuna Cendekia &bull; Dokumen Rahasia Karyawan
            </div>
        </div>
    </div>

    <script>
        function handleDownloadStart() {
            setTimeout(function() {
                var btn = document.getElementById('btnDownload');
                var btnInline = document.getElementById('btnInline');
                var notice = document.getElementById('downloadedNotice');
                
                if (btn) {
                    btn.classList.add('disabled');
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Sedang Mengunduh Dokumen...';
                }
                if (btnInline) {
                    btnInline.classList.add('disabled');
                }
                if (notice) {
                    notice.classList.remove('d-none');
                }
            }, 300);
        }
    </script>
</body>
</html>
