@extends('layouts.presensi')
@section('header')
    <!-- App Header -->
    <div class="appHeader bg-primary text-light">
        <div class="left">
            <a href="/presensi/kendala" class="headerButton">
                <ion-icon name="chevron-back-outline"></ion-icon>
            </a>
        </div>
        <div class="pageTitle">Kirim Laporan Kendala</div>
        <div class="right"></div>
    </div>
@endsection

@section('content')
    <div class="section" style="margin-top: 70px; padding-bottom: 90px;">
        <div class="card" style="border-radius: 14px; border: 1px solid #e2e8f0;">
            <div class="card-body p-3">
                <form action="/presensi/kendala/store" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group mb-2">
                        <label style="font-weight: 600; font-size: 0.82rem;">Kategori Kendala</label>
                        <select name="kategori_masalah" class="form-control" required>
                            <option value="lupa_absen_pulang">Lupa Absen Pulang</option>
                            <option value="lupa_absen_masuk">Lupa Absen Masuk</option>
                            <option value="gps_tidak_akurat">Titik GPS / Lokasi Tidak Akurat</option>
                            <option value="kamera_error">Kamera Selfie Gagal Memuat</option>
                            <option value="aplikasi_error">Aplikasi / Koneksi Error</option>
                            <option value="perubahan_jam_kerja">Perubahan / Tukar Jam Kerja</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-7">
                            <div class="form-group mb-2">
                                <label style="font-weight: 600; font-size: 0.82rem;">Tanggal Kejadian</label>
                                <input type="date" name="tanggal_kejadian" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-5">
                            <div class="form-group mb-2">
                                <label style="font-weight: 600; font-size: 0.82rem;">Perkiraan Jam</label>
                                <input type="time" name="jam_kejadian" class="form-control" value="{{ date('H:i') }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-2">
                        <label style="font-weight: 600; font-size: 0.82rem;">Keterangan / Kronologi Singkat</label>
                        <textarea name="deskripsi" rows="3" class="form-control" placeholder="Contoh: Tadi sore sudah mengajar sampai pukul 15.30 tapi lupa klik absen pulang..." required></textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label style="font-weight: 600; font-size: 0.82rem;">Foto Bukti / Screenshot (Opsional)</label>
                        <input type="file" name="foto_bukti" class="form-control" accept="image/*">
                        <small class="text-muted">Bisa dikosongkan atau lampirkan foto kegiatan/screenshot jika ada.</small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block" style="border-radius: 12px; font-weight: 600;">
                        <ion-icon name="send-outline" style="vertical-align: middle;"></ion-icon> Kirim Laporan ke Admin
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
