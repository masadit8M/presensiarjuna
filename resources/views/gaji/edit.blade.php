@extends('layouts.admin.tabler')
@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/gaji">Penggajian</a></li>
                        <li class="breadcrumb-item"><a href="/gaji/periode/{{ $detail->periode_id }}">{{ $detail->nama_periode }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Slip: {{ $detail->nama_lengkap }}</li>
                    </ol>
                </nav>
                <h2 class="page-title">
                    Edit Komponen Slip Gaji: {{ $detail->nama_lengkap }}
                </h2>
                <div class="text-muted">
                    NIK: <strong>{{ $detail->nik }}</strong> | Jabatan: <strong>{{ $detail->jabatan ?? '-' }}</strong> | Periode: <strong>{{ $detail->nama_periode }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <form action="/gaji/update/{{ $detail->id }}" method="POST">
            @csrf
            <div class="row">
                <!-- Data Kehadiran -->
                <div class="col-12 mb-3">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h3 class="card-title">1. Data Kehadiran Presensi (Standar: {{ $detail->hk_standar }} Hari Kerja)</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2 mb-2">
                                    <label class="form-label">Hari Hadir</label>
                                    <input type="number" name="hadir" class="form-control" value="{{ $detail->hadir }}">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="form-label">Izin</label>
                                    <input type="number" name="izin" class="form-control" value="{{ $detail->izin }}">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="form-label">Sakit</label>
                                    <input type="number" name="sakit" class="form-control" value="{{ $detail->sakit }}">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="form-label">Alpha</label>
                                    <input type="number" name="alpha" class="form-control" value="{{ $detail->alpha }}">
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">Total Terlambat (Jam Desimal)</label>
                                    <input type="number" step="0.01" name="terlambat_jam" class="form-control" value="{{ $detail->terlambat_jam }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Penghasilan / Penerimaan -->
                <div class="col-md-6 mb-3">
                    <div class="card h-100 border-success">
                        <div class="card-header bg-success-lt">
                            <h3 class="card-title text-success">2. Komponen Penerimaan / Penghasilan</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label required">Gaji Pokok</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="gaji_pokok" id="gaji_pokok" class="form-control" value="{{ round($detail->gaji_pokok) }}" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tunjangan Transportasi</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="tunjangan_transportasi" class="form-control" value="{{ round($detail->tunjangan_transportasi) }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tunjangan Jabatan</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="tunjangan_jabatan" class="form-control" value="{{ round($detail->tunjangan_jabatan) }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tunjangan Konsumsi</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="tunjangan_konsumsi" class="form-control" value="{{ round($detail->tunjangan_konsumsi) }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tunjangan Kehadiran</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="tunjangan_kehadiran" class="form-control" value="{{ round($detail->tunjangan_kehadiran) }}">
                                </div>
                            </div>

                            <div class="hr-text text-muted">Komponen Khusus Guru KB / TK</div>

                            <div class="mb-3">
                                <label class="form-label">Uang / Honor Kegiatan</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="honor_kegiatan" class="form-control" value="{{ round($detail->honor_kegiatan) }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Uang Ekstra / Ekstrakurikuler</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="honor_ekskul" class="form-control" value="{{ round($detail->honor_ekskul) }}">
                                </div>
                            </div>

                            <div class="hr-text text-muted">Komponen Khusus Bunda TPA (Daycare)</div>

                            <div class="mb-3">
                                <label class="form-label">Uang Lembur (Overtime Penjemputan)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="upah_lembur" class="form-control" value="{{ round($detail->upah_lembur) }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Insentif Pool TPA (Gate &ge; 41 Siswa)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="insentif_pool_tpa" class="form-control" value="{{ round($detail->insentif_pool_tpa) }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Reward Disiplin Pagi (06.30)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="reward_disiplin" class="form-control" value="{{ round($detail->reward_disiplin) }}">
                                </div>
                            </div>

                            <div class="hr-text text-muted">Penyesuaian Tambahan</div>

                            <div class="mb-3">
                                <label class="form-label">Bonus Tambahan / THR</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="bonus_tambahan" class="form-control" value="{{ round($detail->bonus_tambahan) }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Penyesuaian Penerimaan Lainnya</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="tunjangan_lainnya" class="form-control" value="{{ round($detail->tunjangan_lainnya) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Potongan -->
                <div class="col-md-6 mb-3">
                    <div class="card h-100 border-danger">
                        <div class="card-header bg-danger-lt">
                            <h3 class="card-title text-danger">3. Komponen Potongan</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Potongan Absensi (Izin + Alpha)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="potongan_absen" class="form-control" value="{{ round($detail->potongan_absen) }}">
                                </div>
                                <small class="text-muted">Formula default: (Izin + Alpha) × (Gaji Pokok / 26 HK)</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Potongan Keterlambatan</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="potongan_terlambat" class="form-control" value="{{ round($detail->potongan_terlambat) }}">
                                </div>
                                <small class="text-muted">Formula: Jam Telat × (Gaji Pokok / 26 / 10 Jam)</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Pinjaman / Kas Bon</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="potongan_kasbon" class="form-control" value="{{ round($detail->potongan_kasbon) }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Potongan BPJS</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="potongan_bpjs" class="form-control" value="{{ round($detail->potongan_bpjs) }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Sanksi Disiplin / Potongan Lainnya</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="potongan_lainnya" class="form-control" value="{{ round($detail->potongan_lainnya) }}">
                                </div>
                            </div>

                            <div class="hr-text text-muted">Catatan Slip</div>

                            <div class="mb-3">
                                <label class="form-label">Keterangan / Catatan Khusus di Slip</label>
                                <textarea name="catatan" class="form-control" rows="4" placeholder="Contoh: gantikan tk(1hr), pinj 1x(2jt), dll.">{{ $detail->catatan }}</textarea>
                            </div>

                            <div class="alert alert-warning mt-4">
                                <strong>Pemberitahuan:</strong>
                                Mengubah nominal di form ini akan secara otomatis memperbarui kalkulasi Total Penghasilan, Total Potongan, Gaji Bersih (THP), dan meregenerasi dokumen PDF resmi slip gaji.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 text-end mb-4">
                    <a href="/gaji/periode/{{ $detail->periode_id }}" class="btn btn-outline-secondary me-2">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-device-floppy" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2"/><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M14 4l0 4l-6 0l0 -4"/></svg>
                        Simpan Perubahan Slip Gaji
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
