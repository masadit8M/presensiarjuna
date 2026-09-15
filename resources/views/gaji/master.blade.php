@extends('layouts.admin.tabler')
@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/gaji">Penggajian</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Master Gaji Karyawan</li>
                    </ol>
                </nav>
                <h2 class="page-title">
                    Master Standar Gaji Karyawan
                </h2>
                <div class="text-muted">
                    Atur standar Gaji Pokok, Tunjangan Transportasi, dan Tarif Lembur/Kegiatan per karyawan untuk otomasi perhitungan gaji bulanan.
                </div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="/gaji/master/sync-excel" class="btn btn-outline-success" onclick="return confirm('Perbarui dan sinkronkan standar Gaji Pokok & Potongan Kasbon seluruh Guru KB/TK dan Staff TPA sesuai data resmi Excel?')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-refresh" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4"/><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4"/></svg>
                        Sinkron Standar Excel
                    </a>
                    <a href="/gaji" class="btn btn-outline-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-left" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0"/><path d="M5 12l6 6"/><path d="M5 12l6 -6"/></svg>
                        Kembali ke Periode Gaji
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @if (Session::get('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                <div>{{ Session::get('success') }}</div>
                <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
        @endif

        <div class="card mb-3">
            <div class="card-body">
                <form action="/gaji/master" method="GET">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <select name="kode_cabang" class="form-select">
                                <option value="">- Semua Cabang -</option>
                                @foreach ($cabang as $c)
                                    <option value="{{ $c->kode_cabang }}" {{ request('kode_cabang') == $c->kode_cabang ? 'selected' : '' }}>
                                        {{ strtoupper($c->nama_cabang) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="kode_dept" class="form-select">
                                <option value="">- Semua Departemen -</option>
                                @foreach ($departemen as $d)
                                    <option value="{{ $d->kode_dept }}" {{ request('kode_dept') == $d->kode_dept ? 'selected' : '' }}>
                                        {{ strtoupper($d->nama_dept) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="nama_karyawan" class="form-control" placeholder="Cari nama karyawan..." value="{{ request('nama_karyawan') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-striped" style="min-width: 1400px;">
                    <thead>
                        <tr>
                            <th style="min-width: 220px;">NIK & Nama Karyawan</th>
                            <th style="min-width: 140px;">Jabatan</th>
                            <th style="min-width: 130px;">Cabang / Dept</th>
                            <th style="min-width: 130px;">Gaji Pokok</th>
                            <th style="min-width: 130px;">Tunj. Transport</th>
                            <th style="min-width: 150px;">Gaji Harian (26 HK)</th>
                            <th style="min-width: 120px;">Tunj. Jabatan</th>
                            <th style="min-width: 160px;">Honor Kegiatan / Ekskul</th>
                            <th style="min-width: 140px;">Tarif Lembur (TPA)</th>
                            <th style="min-width: 150px;">Insentif Pagi ≤06:30</th>
                            <th style="min-width: 150px;">Pool SPP &gt;40 Siswa</th>
                            <th style="min-width: 150px;">Potongan Kasbon / Rutin</th>
                            <th class="text-center" style="min-width: 130px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($karyawan as $k)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $k->nama_lengkap }}</div>
                                <div class="text-muted small">
                                    NIK: {{ $k->nik }}
                                    @if (!empty($k->alt_niks) && count($k->alt_niks) > 0)
                                        <span class="badge bg-secondary-lt ms-1" title="NIK Cabang Lain">Alt: {{ implode(', ', $k->alt_niks) }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $k->jabatan ?? '-' }}</td>
                            <td>
                                @if (!empty($k->all_cabang) && count($k->all_cabang) > 1)
                                    @foreach ($k->all_cabang as $cb)
                                        <span class="badge bg-blue-lt me-1">{{ $cb }}</span>
                                    @endforeach
                                @else
                                    <span class="badge bg-blue-lt">{{ $k->nama_cabang ?? $k->kode_cabang }}</span>
                                @endif
                                <div class="text-muted small">{{ $k->nama_dept ?? $k->kode_dept }}</div>
                            </td>
                            <td class="fw-bold text-nowrap text-primary">
                                Rp {{ number_format($k->gaji_pokok ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="text-nowrap">Rp {{ number_format($k->tunjangan_transportasi ?? 150000, 0, ',', '.') }}</td>
                            <td class="text-nowrap">
                                <div class="fw-bold text-success">
                                    Rp {{ number_format($k->gaji_harian ?? 0, 0, ',', '.') }}
                                    <span class="text-muted fw-normal small">/hari</span>
                                </div>
                                <div class="text-muted small">
                                    Per jam: Rp {{ number_format($k->gaji_per_jam ?? 0, 0, ',', '.') }}
                                </div>
                            </td>
                            <td class="text-nowrap">Rp {{ number_format($k->tunjangan_jabatan ?? 0, 0, ',', '.') }}</td>
                            <td>
                                <div class="small text-nowrap">Kegiatan: Rp {{ number_format($k->tarif_honor_kegiatan ?? 0, 0, ',', '.') }}</div>
                                <div class="small text-muted text-nowrap">Ekskul: Rp {{ number_format($k->tarif_ekskul ?? 0, 0, ',', '.') }}</div>
                            </td>
                            <td class="text-nowrap">Rp {{ number_format($k->tarif_lembur ?? 0, 0, ',', '.') }}</td>
                            <td class="text-nowrap">
                                @if (($k->insentif_pagi ?? 0) > 0)
                                    <span class="badge bg-green-lt fw-bold">Rp {{ number_format($k->insentif_pagi, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                @if (($k->hak_pool_spp ?? 0) > 0)
                                    <span class="badge bg-teal-lt fw-bold">Rp {{ number_format($k->hak_pool_spp, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                @if (($k->potongan_kasbon ?? 0) > 0)
                                    <span class="badge bg-danger-lt fw-bold">Kasbon: Rp {{ number_format($k->potongan_kasbon, 0, ',', '.') }}</span>
                                @elseif (($k->bpjs_kesehatan ?? 0) > 0)
                                    <span class="badge bg-warning-lt">BPJS: Rp {{ number_format($k->bpjs_kesehatan, 0, ',', '.') }}</span>
                                @elseif (($k->potongan_lainnya ?? 0) > 0)
                                    <span class="badge bg-secondary-lt">Rp {{ number_format($k->potongan_lainnya, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-muted">Rp 0</span>
                                @endif
                            </td>
                            <td class="text-center text-nowrap">
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalMaster{{ $k->nik }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-edit" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3"/><path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3"/></svg>
                                    Edit Gaji
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="13" class="text-center py-4 text-muted">
                                Tidak ada data karyawan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex align-items-center">
                {{ $karyawan->links() }}
            </div>
        </div>

        {{-- MODAL EDIT MASTER DI LUAR TABEL --}}
        @foreach ($karyawan as $k)
        <div class="modal modal-blur fade" id="modalMaster{{ $k->nik }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <form action="/gaji/master/{{ $k->nik }}/update" method="POST">
                        @csrf
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title mb-0">Setting Master Gaji: {{ $k->nama_lengkap }}</h5>
                                <div class="text-muted small">NIK Utama: {{ $k->nik }} @if(!empty($k->alt_niks)) | NIK Lain: {{ implode(', ', $k->alt_niks) }} @endif</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="hr-text text-primary fw-bold">Komponen Penghasilan / Tunjangan</div>

                            <div class="mb-3">
                                <label class="form-label required">Gaji Pokok</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="gaji_pokok" id="gapok_{{ $k->nik }}" class="form-control" value="{{ round($k->gaji_pokok ?? 0) }}" oninput="hitungHarian('{{ $k->nik }}')" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label required">Tunjangan Transportasi</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="tunjangan_transportasi" id="transp_{{ $k->nik }}" class="form-control" value="{{ round($k->tunjangan_transportasi ?? 150000) }}" oninput="hitungHarian('{{ $k->nik }}')" required>
                                </div>
                            </div>

                            <div class="card bg-azure-lt mb-3 border-0">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong class="text-primary">Gaji Harian Standar (26 HK):</strong>
                                            <div class="small text-muted">Rumus: (Gaji Pokok + Tunj. Transport) &divide; 26 HK</div>
                                        </div>
                                        <div class="text-end">
                                            <div class="h3 mb-0 text-primary fw-bold" id="labelHarian{{ $k->nik }}">
                                                Rp {{ number_format($k->gaji_harian ?? 0, 0, ',', '.') }}
                                            </div>
                                            <div class="small text-muted">Dasar potongan ketidakhadiran /hari</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tunjangan Jabatan / Uang Ekstra</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="tunjangan_jabatan" class="form-control" value="{{ round($k->tunjangan_jabatan ?? 0) }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tunjangan Konsumsi</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="tunjangan_konsumsi" class="form-control" value="{{ round($k->tunjangan_konsumsi ?? 0) }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Honor Kegiatan (Guru)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="tarif_honor_kegiatan" class="form-control" value="{{ round($k->tarif_honor_kegiatan ?? 0) }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Honor Ekstrakurikuler (Guru)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="tarif_ekskul" class="form-control" value="{{ round($k->tarif_ekskul ?? 0) }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tarif Lembur (Bunda TPA)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="tarif_lembur" class="form-control" value="{{ round($k->tarif_lembur ?? 0) }}">
                                </div>
                            </div>

                            <div class="card border-success mb-3">
                                <div class="card-header bg-success-lt py-2">
                                    <strong class="text-success">🎯 Insentif Khusus Bunda TPA / Daycare</strong>
                                    <div class="small text-muted mt-1">Berdasarkan Pedoman Dasar Perhitungan Gaji & Insentif Arjuna. Isi Rp 0 untuk Guru KB/TK.</div>
                                </div>
                                <div class="card-body pb-1">
                                    <div class="mb-3">
                                        <label class="form-label">Plafon Insentif Disiplin Pagi (≤ 06:30)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" name="insentif_pagi" class="form-control" value="{{ round($k->insentif_pagi ?? 0) }}" placeholder="50000">
                                        </div>
                                        <small class="text-muted">Cair penuh jika <strong>semua hari kerja</strong> check-in ≤ 06:30. Gugur total (Rp 0) jika ada 1 hari terlambat.</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Plafon Pool Insentif SPP Lunas (&gt; 40 Siswa)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" name="hak_pool_spp" class="form-control" value="{{ round($k->hak_pool_spp ?? 0) }}" placeholder="100000">
                                        </div>
                                        <small class="text-muted">Cair jika total siswa TPA lunas SPP ≥ 41 siswa pada periode yang digenerate.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="hr-text text-danger fw-bold">Komponen Potongan Gaji Rutin</div>

                            <div class="mb-3">
                                <label class="form-label text-danger">Potongan Pinjaman / Kas Bon Rutin</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="potongan_kasbon" class="form-control" value="{{ round($k->potongan_kasbon ?? 0) }}" placeholder="Contoh: 400000">
                                </div>
                                <small class="text-muted">Potongan angsuran kasbon/pinjaman tetap per bulan (contoh di Excel: Dwi Retno Rp 400.000, Cindy Novalita Rp 200.000).</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Potongan BPJS</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="bpjs_kesehatan" class="form-control" value="{{ round($k->bpjs_kesehatan ?? 0) }}" placeholder="Contoh: 50000">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Penyesuaian / Potongan Rutin Lainnya</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="potongan_lainnya" class="form-control" value="{{ round($k->potongan_lainnya ?? 0) }}" placeholder="0">
                                </div>
                            </div>

                            <div class="alert alert-info py-2 small mb-0 mt-3">
                                <strong>Info Konsolidasi:</strong> Standar gaji ini otomatis diterapkan untuk seluruh cabang penugasan karyawan ini (@if(!empty($k->all_cabang)){{ implode(', ', $k->all_cabang) }}@endif).
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary ms-auto">Simpan Standar Gaji</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection

@push('myscript')
<script>
function hitungHarian(nik) {
    var gapokEl = document.getElementById('gapok_' + nik);
    var transpEl = document.getElementById('transp_' + nik);
    var labelEl = document.getElementById('labelHarian' + nik);
    if (gapokEl && transpEl && labelEl) {
        var gapok = parseFloat(gapokEl.value) || 0;
        var transp = parseFloat(transpEl.value) || 0;
        var harian = Math.round((gapok + transp) / 26);
        labelEl.innerText = 'Rp ' + harian.toLocaleString('id-ID');
    }
}
</script>
@endpush
