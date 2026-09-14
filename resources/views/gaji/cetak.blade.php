<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Slip Gaji - {{ $detail->nama_lengkap }} ({{ $detail->nama_periode }})</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/7.0.0/normalize.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/paper-css/0.4.1/paper.css">
    <style>
        @page { size: A4 portrait; margin: 0; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #111; background-color: #f5f5f5; }
        .sheet { background: white; margin: 0 auto; box-shadow: 0 .5mm 2mm rgba(0,0,0,.3); }
        .slip-box {
            padding: 12px 18px;
            box-sizing: border-box;
            height: 140mm;
            position: relative;
        }
        .cut-line {
            border-top: 1px dashed #999;
            margin: 0;
            padding: 0;
            position: relative;
        }
        .cut-label {
            position: absolute;
            top: -7px;
            right: 20px;
            background: #fff;
            padding: 0 6px;
            font-size: 9px;
            color: #888;
        }
        .kop-title { font-size: 13px; font-weight: bold; text-align: center; margin: 0; }
        .kop-lembaga { font-size: 12px; font-weight: bold; text-align: center; margin: 2px 0; }
        .kop-alamat { font-size: 8.5px; text-align: center; color: #444; margin: 0; }
        .double-divider {
            border-top: 2px solid #000;
            border-bottom: 1px solid #000;
            height: 2px;
            margin: 6px 0 8px 0;
        }
        .title-slip {
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }
        table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 1.5px 0; font-size: 9.5px; }
        .comp-table { width: 100%; margin-top: 4px; }
        .comp-table td { padding: 2.5px 0; font-size: 9.5px; vertical-align: top; }
        .comp-header { font-weight: bold; font-size: 9.5px; padding-bottom: 3px; border-bottom: 1px solid #ddd; }
        .subtotal-line td { border-top: 1px solid #aaa; font-weight: bold; padding-top: 3px; }
        .thp-box {
            margin-top: 6px;
            border-top: 1.5px solid #222;
            border-bottom: 1.5px solid #222;
            padding: 4px 0;
            font-weight: bold;
            font-size: 11px;
        }
        .sig-table { width: 100%; margin-top: 12px; text-align: center; font-size: 9px; }
        .sig-space { height: 35px; }
        @media print {
            body { background: transparent; }
            .no-print { display: none !important; }
            .sheet { box-shadow: none; }
        }
        .print-btn-bar {
            position: fixed;
            top: 15px;
            right: 20px;
            z-index: 9999;
            background: #fff;
            padding: 10px 16px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .btn-act {
            background: #0054a6;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-close-window {
            background: #6c757d;
            margin-left: 6px;
        }
    </style>
</head>
<body class="A4">
    <div class="print-btn-bar no-print">
        <button onclick="window.print()" class="btn-act">🖨️ Cetak / Simpan ke PDF</button>
        <a href="/gaji/download-pdf/{{ $detail->id }}" class="btn-act" style="background:#2fb344; margin-left:6px;">📥 Download PDF Asli</a>
        <button onclick="window.close()" class="btn-act btn-close-window">Tutup</button>
    </div>

    @php
        $cabang = strtoupper($detail->kode_cabang ?? 'CTD');
        $isLangsep = str_contains($cabang, 'LSP') || str_contains(strtoupper($detail->nama_cabang ?? ''), 'LANGSEP');
        $lembagaName = $isLangsep ? 'TPA - KB - TK ALAM ARJUNA' : 'KB TK ISLAM PLUS ARJUNA';
        $subHeader = $isLangsep ? 'KEL. BARENG KEC. KLOJEN KOTA MALANG' : 'KEC. BLIMBING KEL. PURWANTORO KOTA MALANG';
        $alamat = $isLangsep 
            ? 'Sekretariat: Jl. Raya Langsep 23 B Telp. (0341) 567723 Malang 65112' 
            : 'Sekretariat: Jl. Citandui 15 B Malang Kodepos 65116 Telp. (0341) 4371932';
    @endphp

    <section class="sheet">
        {{-- SLIP BAGIAN ATAS --}}
        <div class="slip-box">
            <p class="kop-title">YAYASAN ARJUNA CENDEKIA</p>
            <p class="kop-lembaga">{{ $lembagaName }}</p>
            <p class="kop-alamat">{{ $subHeader }}</p>
            <p class="kop-alamat">{{ $alamat }}</p>
            <div class="double-divider"></div>

            <div class="title-slip">SLIP GAJI</div>

            <table class="info-table">
                <tr>
                    <td style="width: 14%;">Tanggal</td>
                    <td style="width: 2%;">:</td>
                    <td style="width: 38%;">{{ date('d-M-Y', strtotime($detail->tgl_selesai ?? date('Y-m-d'))) }}</td>
                    <td style="width: 16%;">Tgl Masuk Kerja</td>
                    <td style="width: 2%;">:</td>
                    <td style="width: 28%;">-</td>
                </tr>
                <tr>
                    <td>Nama</td>
                    <td>:</td>
                    <td><strong>{{ strtoupper($detail->nama_lengkap) }}</strong></td>
                    <td>Periode</td>
                    <td>:</td>
                    <td><strong>{{ strtoupper($detail->nama_periode) }}</strong></td>
                </tr>
                <tr>
                    <td>Jabatan</td>
                    <td>:</td>
                    <td>{{ strtoupper($detail->jabatan ?? '-') }}</td>
                    <td>Hari Kerja (HK)</td>
                    <td>:</td>
                    <td>{{ $detail->hadir }} / {{ $detail->hk_standar }} HK (Izin: {{ $detail->izin }}, Sakit: {{ $detail->sakit }}, Alpha: {{ $detail->alpha }})</td>
                </tr>
            </table>

            <table class="comp-table">
                <tr>
                    <td style="width: 48%;">
                        <div class="comp-header">(PENGHASILAN / PENERIMAAN)</div>
                        <table style="margin-top: 3px;">
                            <tr>
                                <td>GAJI POKOK</td>
                                <td style="text-align: right;">Rp {{ number_format($detail->gaji_pokok, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>TUNJ. TRANSPORTASI</td>
                                <td style="text-align: right;">Rp {{ number_format($detail->tunjangan_transportasi, 0, ',', '.') }}</td>
                            </tr>
                            @if ($detail->tunjangan_jabatan > 0)
                            <tr>
                                <td>TUNJ. JABATAN</td>
                                <td style="text-align: right;">Rp {{ number_format($detail->tunjangan_jabatan, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            @if ($detail->honor_kegiatan > 0)
                            <tr>
                                <td>UANG KEGIATAN</td>
                                <td style="text-align: right;">Rp {{ number_format($detail->honor_kegiatan, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            @if ($detail->honor_ekskul > 0)
                            <tr>
                                <td>UANG EKSTRA / EKSKUL</td>
                                <td style="text-align: right;">Rp {{ number_format($detail->honor_ekskul, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            @if ($detail->upah_lembur > 0)
                            <tr>
                                <td>UANG LEMBUR</td>
                                <td style="text-align: right;">Rp {{ number_format($detail->upah_lembur, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            @if ($detail->insentif_pool_tpa > 0)
                            <tr>
                                <td>INSENTIF POOL TPA (GATE 41)</td>
                                <td style="text-align: right;">Rp {{ number_format($detail->insentif_pool_tpa, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            @if ($detail->reward_disiplin > 0)
                            <tr>
                                <td>REWARD DISIPLIN 06.30</td>
                                <td style="text-align: right;">Rp {{ number_format($detail->reward_disiplin, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            @if ($detail->bonus_tambahan > 0)
                            <tr>
                                <td>BONUS / THR</td>
                                <td style="text-align: right;">Rp {{ number_format($detail->bonus_tambahan, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            @if ($detail->tunjangan_lainnya > 0)
                            <tr>
                                <td>PENYESUAIAN / LAINNYA</td>
                                <td style="text-align: right;">Rp {{ number_format($detail->tunjangan_lainnya, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            <tr class="subtotal-line">
                                <td>SUBTOTAL PENGHASILAN</td>
                                <td style="text-align: right;">Rp {{ number_format($detail->total_penghasilan, 0, ',', '.') }}</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 4%;"></td>
                    <td style="width: 48%;">
                        <div class="comp-header">(POTONGAN)</div>
                        <table style="margin-top: 3px;">
                            @if ($detail->potongan_absen > 0)
                            <tr>
                                <td>POT. ABSENSI (IZIN/ALPHA)</td>
                                <td style="text-align: right; color:#c00;">Rp {{ number_format($detail->potongan_absen, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            @if ($detail->potongan_terlambat > 0)
                            <tr>
                                <td>POT. TERLAMBAT ({{ $detail->terlambat_jam }} Jam)</td>
                                <td style="text-align: right; color:#c00;">Rp {{ number_format($detail->potongan_terlambat, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            @if ($detail->potongan_kasbon > 0)
                            <tr>
                                <td>PINJAMAN / KAS BON</td>
                                <td style="text-align: right; color:#c00;">Rp {{ number_format($detail->potongan_kasbon, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            @if ($detail->potongan_bpjs > 0)
                            <tr>
                                <td>BPJS</td>
                                <td style="text-align: right; color:#c00;">Rp {{ number_format($detail->potongan_bpjs, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            @if ($detail->potongan_lainnya > 0)
                            <tr>
                                <td>SANKSI / POT. LAIN</td>
                                <td style="text-align: right; color:#c00;">Rp {{ number_format($detail->potongan_lainnya, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            @if ($detail->total_potongan == 0)
                            <tr>
                                <td style="color:#888;">Tidak ada potongan</td>
                                <td style="text-align: right; color:#888;">Rp 0</td>
                            </tr>
                            @endif
                            <tr class="subtotal-line">
                                <td>SUBTOTAL POTONGAN</td>
                                <td style="text-align: right; color:#c00;">Rp {{ number_format($detail->total_potongan, 0, ',', '.') }}</td>
                            </tr>
                        </table>

                        @if(!empty($detail->catatan))
                            <div style="margin-top: 6px; font-size: 8.5px; color: #555; background: #fdfdfd; border-left: 2px solid #0054a6; padding-left: 4px;">
                                <em>Catatan: {{ $detail->catatan }}</em>
                            </div>
                        @endif
                    </td>
                </tr>
            </table>

            <div class="thp-box">
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 70%;">TOTAL GAJI BERSIH (TAKE HOME PAY)</td>
                        <td style="width: 30%; text-align: right;">Rp {{ number_format($detail->gaji_bersih, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>

            <table class="sig-table">
                <tr>
                    <td style="width: 50%;">Dibuat Oleh,</td>
                    <td style="width: 50%;">MALANG, {{ strtoupper(date('d F Y')) }}<br>Penerima,</td>
                </tr>
                <tr>
                    <td class="sig-space"></td>
                    <td class="sig-space"></td>
                </tr>
                <tr>
                    <td><strong>(Kartika P.)</strong><br>Bendahara Yayasan</td>
                    <td><strong>( {{ strtoupper($detail->nama_lengkap) }} )</strong><br>Staff / Guru</td>
                </tr>
            </table>
        </div>

        {{-- GARIS POTONG LEMBAR 2-IN-1 --}}
        <div class="cut-line">
            <span class="cut-label">✂ Potong di sini (Salinan Karyawan & Arsip Yayasan)</span>
        </div>

        {{-- SLIP BAGIAN BAWAH (DUPLIKAT PERSIS UNTUK CETAK HEMAT KERTAS) --}}
        <div class="slip-box">
            <p class="kop-title">YAYASAN ARJUNA CENDEKIA</p>
            <p class="kop-lembaga">{{ $lembagaName }}</p>
            <p class="kop-alamat">{{ $subHeader }}</p>
            <p class="kop-alamat">{{ $alamat }}</p>
            <div class="double-divider"></div>

            <div class="title-slip">SLIP GAJI (ARSIP YAYASAN)</div>

            <table class="info-table">
                <tr>
                    <td style="width: 14%;">Tanggal</td>
                    <td style="width: 2%;">:</td>
                    <td style="width: 38%;">{{ date('d-M-Y', strtotime($detail->tgl_selesai ?? date('Y-m-d'))) }}</td>
                    <td style="width: 16%;">Tgl Masuk Kerja</td>
                    <td style="width: 2%;">:</td>
                    <td style="width: 28%;">-</td>
                </tr>
                <tr>
                    <td>Nama</td>
                    <td>:</td>
                    <td><strong>{{ strtoupper($detail->nama_lengkap) }}</strong></td>
                    <td>Periode</td>
                    <td>:</td>
                    <td><strong>{{ strtoupper($detail->nama_periode) }}</strong></td>
                </tr>
                <tr>
                    <td>Jabatan</td>
                    <td>:</td>
                    <td>{{ strtoupper($detail->jabatan ?? '-') }}</td>
                    <td>Hari Kerja (HK)</td>
                    <td>:</td>
                    <td>{{ $detail->hadir }} / {{ $detail->hk_standar }} HK (Izin: {{ $detail->izin }}, Sakit: {{ $detail->sakit }}, Alpha: {{ $detail->alpha }})</td>
                </tr>
            </table>

            <table class="comp-table">
                <tr>
                    <td style="width: 48%;">
                        <div class="comp-header">(PENGHASILAN / PENERIMAAN)</div>
                        <table style="margin-top: 3px;">
                            <tr>
                                <td>GAJI POKOK</td>
                                <td style="text-align: right;">Rp {{ number_format($detail->gaji_pokok, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>TUNJ. TRANSPORTASI</td>
                                <td style="text-align: right;">Rp {{ number_format($detail->tunjangan_transportasi, 0, ',', '.') }}</td>
                            </tr>
                            @if ($detail->tunjangan_jabatan > 0)
                            <tr>
                                <td>TUNJ. JABATAN</td>
                                <td style="text-align: right;">Rp {{ number_format($detail->tunjangan_jabatan, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            @if ($detail->honor_kegiatan > 0)
                            <tr>
                                <td>UANG KEGIATAN</td>
                                <td style="text-align: right;">Rp {{ number_format($detail->honor_kegiatan, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            @if ($detail->honor_ekskul > 0)
                            <tr>
                                <td>UANG EKSTRA / EKSKUL</td>
                                <td style="text-align: right;">Rp {{ number_format($detail->honor_ekskul, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            @if ($detail->upah_lembur > 0)
                            <tr>
                                <td>UANG LEMBUR</td>
                                <td style="text-align: right;">Rp {{ number_format($detail->upah_lembur, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            @if ($detail->insentif_pool_tpa > 0)
                            <tr>
                                <td>INSENTIF POOL TPA (GATE 41)</td>
                                <td style="text-align: right;">Rp {{ number_format($detail->insentif_pool_tpa, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            @if ($detail->reward_disiplin > 0)
                            <tr>
                                <td>REWARD DISIPLIN 06.30</td>
                                <td style="text-align: right;">Rp {{ number_format($detail->reward_disiplin, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            @if ($detail->bonus_tambahan > 0)
                            <tr>
                                <td>BONUS / THR</td>
                                <td style="text-align: right;">Rp {{ number_format($detail->bonus_tambahan, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            @if ($detail->tunjangan_lainnya > 0)
                            <tr>
                                <td>PENYESUAIAN / LAINNYA</td>
                                <td style="text-align: right;">Rp {{ number_format($detail->tunjangan_lainnya, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            <tr class="subtotal-line">
                                <td>SUBTOTAL PENGHASILAN</td>
                                <td style="text-align: right;">Rp {{ number_format($detail->total_penghasilan, 0, ',', '.') }}</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 4%;"></td>
                    <td style="width: 48%;">
                        <div class="comp-header">(POTONGAN)</div>
                        <table style="margin-top: 3px;">
                            @if ($detail->potongan_absen > 0)
                            <tr>
                                <td>POT. ABSENSI (IZIN/ALPHA)</td>
                                <td style="text-align: right; color:#c00;">Rp {{ number_format($detail->potongan_absen, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            @if ($detail->potongan_terlambat > 0)
                            <tr>
                                <td>POT. TERLAMBAT ({{ $detail->terlambat_jam }} Jam)</td>
                                <td style="text-align: right; color:#c00;">Rp {{ number_format($detail->potongan_terlambat, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            @if ($detail->potongan_kasbon > 0)
                            <tr>
                                <td>PINJAMAN / KAS BON</td>
                                <td style="text-align: right; color:#c00;">Rp {{ number_format($detail->potongan_kasbon, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            @if ($detail->potongan_bpjs > 0)
                            <tr>
                                <td>BPJS</td>
                                <td style="text-align: right; color:#c00;">Rp {{ number_format($detail->potongan_bpjs, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            @if ($detail->potongan_lainnya > 0)
                            <tr>
                                <td>SANKSI / POT. LAIN</td>
                                <td style="text-align: right; color:#c00;">Rp {{ number_format($detail->potongan_lainnya, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            @if ($detail->total_potongan == 0)
                            <tr>
                                <td style="color:#888;">Tidak ada potongan</td>
                                <td style="text-align: right; color:#888;">Rp 0</td>
                            </tr>
                            @endif
                            <tr class="subtotal-line">
                                <td>SUBTOTAL POTONGAN</td>
                                <td style="text-align: right; color:#c00;">Rp {{ number_format($detail->total_potongan, 0, ',', '.') }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <div class="thp-box">
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 70%;">TOTAL GAJI BERSIH (TAKE HOME PAY)</td>
                        <td style="width: 30%; text-align: right;">Rp {{ number_format($detail->gaji_bersih, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>

            <table class="sig-table">
                <tr>
                    <td style="width: 50%;">Dibuat Oleh,</td>
                    <td style="width: 50%;">MALANG, {{ strtoupper(date('d F Y')) }}<br>Penerima,</td>
                </tr>
                <tr>
                    <td class="sig-space"></td>
                    <td class="sig-space"></td>
                </tr>
                <tr>
                    <td><strong>(Kartika P.)</strong><br>Bendahara Yayasan</td>
                    <td><strong>( {{ strtoupper($detail->nama_lengkap) }} )</strong><br>Staff / Guru</td>
                </tr>
            </table>
        </div>
    </section>
</body>
</html>
