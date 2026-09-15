@extends('layouts.admin.tabler')
@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/gaji">Penggajian</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $periode->nama_periode }}</li>
                    </ol>
                </nav>
                <h2 class="page-title">
                    {{ $periode->nama_periode }}
                </h2>
                <div class="text-muted mt-1">
                    Rentang Absensi: <strong>{{ date('d F Y', strtotime($periode->tgl_mulai)) }} s/d {{ date('d F Y', strtotime($periode->tgl_selesai)) }}</strong> | Standar: <strong>{{ $periode->hk_standar }} Hari Kerja</strong>
                </div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="/gaji" class="btn btn-outline-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-left" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0"/><path d="M5 12l6 6"/><path d="M5 12l6 -6"/></svg>
                        Kembali
                    </a>
                    <form action="/gaji/kirimwa-semua/{{ $periode->id }}" method="POST" onsubmit="return confirm('Kirimkan dokumen PDF Slip Gaji ke SELURUH karyawan melalui WhatsApp?')">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-brand-whatsapp" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9"/><path d="M9 10a.5 .5 0 0 0 1 0v-1a.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a.5 .5 0 0 0 0 -1h-1a.5 .5 0 0 0 0 1"/></svg>
                            Kirim Dokumen PDF via WA (Semua)
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <!-- Summary Cards -->
        <div class="row row-deck row-cards mb-3">
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Total Karyawan</div>
                        </div>
                        <div class="h1 mb-0">{{ $stats['total_karyawan'] }} <span class="fs-4 text-muted">Orang</span></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Total Gaji Bersih (THP)</div>
                        </div>
                        <div class="h1 mb-0 text-success">Rp {{ number_format($stats['total_gaji_bersih'], 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Total Potongan</div>
                        </div>
                        <div class="h1 mb-0 text-danger">Rp {{ number_format($stats['total_potongan'], 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Pengiriman WA</div>
                        </div>
                        <div class="h1 mb-0 text-primary">{{ $stats['total_wa_terkirim'] }} <span class="fs-4 text-muted">/ {{ $stats['total_karyawan'] }} Terkirim</span></div>
                    </div>
                </div>
            </div>
        </div>

        @if (Session::get('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                <div>{{ Session::get('success') }}</div>
                <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
        @endif
        @if (Session::get('warning'))
            <div class="alert alert-warning alert-dismissible" role="alert">
                <div>{{ Session::get('warning') }}</div>
                <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
        @endif

        <!-- Filter Card -->
        <div class="card mb-3">
            <div class="card-body">
                <form action="/gaji/periode/{{ $periode->id }}" method="GET">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <select name="kode_cabang" class="form-select">
                                <option value="">- Semua Cabang (Citandui & Langsep) -</option>
                                @foreach ($cabang as $c)
                                    <option value="{{ $c->kode_cabang }}" {{ request('kode_cabang') == $c->kode_cabang ? 'selected' : '' }}>
                                        {{ strtoupper($c->nama_cabang) }} ({{ $c->kode_cabang }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="kode_dept" class="form-select">
                                <option value="">- Semua Departemen (KB, TK, TPA) -</option>
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
                            <button type="submit" class="btn btn-primary w-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-search" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/><path d="M21 21l-6 -6"/></svg>
                                Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Slip Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-hover" style="min-width: 1250px;">
                    <thead>
                        <tr>
                            <th style="min-width: 200px;">Karyawan & Jabatan</th>
                            <th style="min-width: 140px;">Cabang / Dept</th>
                            <th style="min-width: 150px;">Kehadiran (26 HK)</th>
                            <th style="min-width: 130px;">Gaji Pokok</th>
                            <th style="min-width: 160px;">Tunjangan & Bonus</th>
                            <th style="min-width: 120px;">Potongan</th>
                            <th style="min-width: 150px;">Gaji Bersih (THP)</th>
                            <th style="min-width: 110px;">Status WA</th>
                            <th class="text-center" style="min-width: 220px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($details as $d)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $d->nama_lengkap }}</div>
                                <div class="text-muted small">NIK: {{ $d->nik }} | {{ $d->jabatan ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-blue-lt">{{ $d->nama_cabang ?? $d->kode_cabang }}</span>
                                <div class="text-muted small">{{ $d->nama_dept ?? $d->kode_dept }}</div>
                            </td>
                            <td>
                                <div><strong>{{ $d->hadir }}</strong> / {{ $d->hk_standar }} Hari</div>
                                <div class="text-muted small">
                                    I:{{ $d->izin }} | S:{{ $d->sakit }} | A:{{ $d->alpha }} | Telat:{{ $d->terlambat_jam }}j
                                </div>
                            </td>
                            <td class="text-nowrap">Rp {{ number_format($d->gaji_pokok, 0, ',', '.') }}</td>
                            <td class="text-nowrap">
                                @php
                                    $totalTunj = ($d->total_penghasilan - $d->gaji_pokok);
                                @endphp
                                <span class="text-info">+Rp {{ number_format($totalTunj, 0, ',', '.') }}</span>
                            </td>
                            <td class="text-nowrap">
                                @if ($d->total_potongan > 0)
                                    <span class="text-danger fw-bold">-Rp {{ number_format($d->total_potongan, 0, ',', '.') }}</span>
                                    @if (($d->potongan_kasbon ?? 0) > 0)
                                        <div class="text-muted small">Kasbon: Rp {{ number_format($d->potongan_kasbon, 0, ',', '.') }}</div>
                                    @endif
                                @else
                                    <span class="text-muted">Rp 0</span>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                <div class="fw-bold text-success fs-3">
                                    Rp {{ number_format($d->gaji_bersih, 0, ',', '.') }}
                                </div>
                            </td>
                            <td>
                                @if ($d->status_kirim_wa)
                                    <span class="badge bg-success" title="{{ $d->waktu_kirim_wa }}">
                                        Terkirim
                                    </span>
                                @else
                                    <span class="badge bg-secondary-lt">Belum</span>
                                @endif
                            </td>
                            <td class="text-center text-nowrap">
                                <div class="btn-list flex-nowrap justify-content-center">
                                    {{-- Edit Superadmin --}}
                                    <a href="/gaji/edit/{{ $d->id }}" class="btn btn-sm btn-outline-primary" title="Edit Komponen Slip">
                                        Edit
                                    </a>

                                    {{-- Cetak Slip 2-in-1 --}}
                                    <a href="/gaji/cetak/{{ $d->id }}" target="_blank" class="btn btn-sm btn-outline-info" title="Lihat & Cetak Slip Paperless">
                                        Cetak
                                    </a>

                                    {{-- Download PDF Document --}}
                                    <a href="/gaji/download-pdf/{{ $d->id }}" class="btn btn-sm btn-outline-secondary" title="Download File PDF Resmi">
                                        PDF
                                    </a>

                                    {{-- Kirim Dokumen PDF via WA --}}
                                    <form action="/gaji/kirimwa/{{ $d->id }}" method="POST" onsubmit="return confirm('Kirim dokumen PDF Slip Gaji ke {{ $d->nama_lengkap }} ({{ $d->no_hp }})?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Kirim Dokumen PDF via WhatsApp">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-brand-whatsapp m-0" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9"/><path d="M9 10a.5 .5 0 0 0 1 0v-1a.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a.5 .5 0 0 0 0 -1h-1a.5 .5 0 0 0 0 1"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                Tidak ada data slip gaji pada filter ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
