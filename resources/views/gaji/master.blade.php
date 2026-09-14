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
                <a href="/gaji" class="btn btn-outline-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-left" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0"/><path d="M5 12l6 6"/><path d="M5 12l6 -6"/></svg>
                    Kembali ke Periode Gaji
                </a>
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
                <table class="table table-vcenter card-table table-striped">
                    <thead>
                        <tr>
                            <th>NIK & Nama Karyawan</th>
                            <th>Jabatan</th>
                            <th>Cabang / Dept</th>
                            <th>Gaji Pokok</th>
                            <th>Tunj. Transport</th>
                            <th>Tunj. Jabatan</th>
                            <th>Honor Kegiatan / Ekskul</th>
                            <th>Tarif Lembur (TPA)</th>
                            <th class="w-1">Aksi</th>
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
                            <td class="fw-bold">
                                Rp {{ number_format($k->gaji_pokok ?? 1200000, 0, ',', '.') }}
                            </td>
                            <td>Rp {{ number_format($k->tunjangan_transportasi ?? 150000, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($k->tunjangan_jabatan ?? 0, 0, ',', '.') }}</td>
                            <td>
                                <div class="small">Kegiatan: Rp {{ number_format($k->tarif_honor_kegiatan ?? 0, 0, ',', '.') }}</div>
                                <div class="small text-muted">Ekskul: Rp {{ number_format($k->tarif_ekskul ?? 0, 0, ',', '.') }}</div>
                            </td>
                            <td>Rp {{ number_format($k->tarif_lembur ?? 0, 0, ',', '.') }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalMaster{{ $k->nik }}">
                                    Edit Gaji
                                </button>
                            </td>
                        </tr>

                        {{-- MODAL EDIT MASTER --}}
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
                                            <div class="mb-3">
                                                <label class="form-label required">Gaji Pokok</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">Rp</span>
                                                    <input type="number" name="gaji_pokok" class="form-control" value="{{ round($k->gaji_pokok ?? 1200000) }}" required>
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
                                                <label class="form-label">Tunjangan Jabatan</label>
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
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary ms-auto">Simpan Standar Gaji</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
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
    </div>
</div>
@endsection
