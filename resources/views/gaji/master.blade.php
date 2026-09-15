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
                <table class="table table-vcenter card-table table-striped" style="min-width: 1250px;">
                    <thead>
                        <tr>
                            <th style="min-width: 220px;">NIK & Nama Karyawan</th>
                            <th style="min-width: 140px;">Jabatan</th>
                            <th style="min-width: 130px;">Cabang / Dept</th>
                            <th style="min-width: 130px;">Gaji Pokok</th>
                            <th style="min-width: 130px;">Tunj. Transport</th>
                            <th style="min-width: 120px;">Tunj. Jabatan</th>
                            <th style="min-width: 160px;">Honor Kegiatan / Ekskul</th>
                            <th style="min-width: 140px;">Tarif Lembur (TPA)</th>
                            <th style="min-width: 150px;">Potongan Kasbon / Rutin</th>
                            <th class="text-center" style="min-width: 130px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($karyawan as $k)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $k->nama_lengkap }}</div>
                                <div class="text-muted small">NIK: {{ $k->nik }}</div>
                            </td>
                            <td>{{ $k->jabatan ?? '-' }}</td>
                            <td>
                                <span class="badge bg-blue-lt">{{ $k->nama_cabang ?? $k->kode_cabang }}</span>
                                <div class="text-muted small">{{ $k->nama_dept ?? $k->kode_dept }}</div>
                            </td>
                            <td class="fw-bold text-nowrap text-primary">
                                Rp {{ number_format($k->gaji_pokok ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="text-nowrap">Rp {{ number_format($k->tunjangan_transportasi ?? 150000, 0, ',', '.') }}</td>
                            <td class="text-nowrap">Rp {{ number_format($k->tunjangan_jabatan ?? 0, 0, ',', '.') }}</td>
                            <td>
                                <div class="small text-nowrap">Kegiatan: Rp {{ number_format($k->tarif_honor_kegiatan ?? 0, 0, ',', '.') }}</div>
                                <div class="small text-muted text-nowrap">Ekskul: Rp {{ number_format($k->tarif_ekskul ?? 0, 0, ',', '.') }}</div>
                            </td>
                            <td class="text-nowrap">Rp {{ number_format($k->tarif_lembur ?? 0, 0, ',', '.') }}</td>
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
                            <td colspan="10" class="text-center py-4 text-muted">
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
                            <h5 class="modal-title">Setting Master Gaji: {{ $k->nama_lengkap }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="hr-text text-primary fw-bold">Komponen Penghasilan / Tunjangan</div>

                            <div class="mb-3">
                                <label class="form-label required">Gaji Pokok</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="gaji_pokok" class="form-control" value="{{ round($k->gaji_pokok ?? 0) }}" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label required">Tunjangan Transportasi</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="tunjangan_transportasi" class="form-control" value="{{ round($k->tunjangan_transportasi ?? 150000) }}" required>
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
