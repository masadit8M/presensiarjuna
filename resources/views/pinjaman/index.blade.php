@extends('layouts.admin.tabler')
@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Keuangan & Penggajian</div>
                    <h2 class="page-title">Pinjaman / Kasbon & Cicilan Guru</h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="/gaji/master" class="btn btn-outline-primary">
                            Master Gaji
                        </a>
                        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-tambah-pinjaman">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24"
                                stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M12 5l0 14" />
                                <path d="M5 12l14 0" />
                            </svg>
                            Tambah Pinjaman / Kasbon
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
            @if (Session::get('warning'))
                <div class="alert alert-warning alert-dismissible" role="alert">
                    <div>{{ Session::get('warning') }}</div>
                    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                </div>
            @endif

            <!-- Summary Cards -->
            <div class="row row-cards mb-3">
                <div class="col-sm-6 col-lg-3">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="bg-warning text-white avatar">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>
                                    </span>
                                </div>
                                <div class="col">
                                    <div class="font-weight-medium">{{ $stats['aktif_count'] }} Pinjaman Aktif</div>
                                    <div class="text-muted">Sedang berjalan</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="bg-primary text-white avatar">Rp</span>
                                </div>
                                <div class="col">
                                    <div class="font-weight-medium">Rp {{ number_format($stats['total_pinjaman_aktif'], 0, ',', '.') }}</div>
                                    <div class="text-muted">Total Plafon Aktif</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="bg-danger text-white avatar">Rp</span>
                                </div>
                                <div class="col">
                                    <div class="font-weight-medium">Rp {{ number_format($stats['total_sisa_piutang'], 0, ',', '.') }}</div>
                                    <div class="text-muted">Total Sisa Piutang</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="bg-success text-white avatar">Rp</span>
                                </div>
                                <div class="col">
                                    <div class="font-weight-medium">Rp {{ number_format($stats['total_terbayar'], 0, ',', '.') }}</div>
                                    <div class="text-muted">Total Angsuran Masuk</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter & Table -->
            <div class="card">
                <div class="card-body">
                    <form action="/pinjaman" method="GET" class="mb-3">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <input type="text" name="nama_karyawan" value="{{ Request('nama_karyawan') }}" class="form-control" placeholder="Cari Nama Guru / NIK / Nomor Pinjaman...">
                            </div>
                            <div class="col-md-3">
                                <select name="kode_cabang" class="form-select">
                                    <option value="">Semua Cabang</option>
                                    @foreach ($cabang as $c)
                                        <option value="{{ $c->kode_cabang }}" {{ Request('kode_cabang') == $c->kode_cabang ? 'selected' : '' }}>
                                            {{ $c->nama_cabang }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="berjalan" {{ Request('status') == 'berjalan' ? 'selected' : '' }}>Berjalan (Belum Lunas)</option>
                                    <option value="lunas" {{ Request('status') == 'lunas' ? 'selected' : '' }}>Sudah Lunas</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-vcenter card-table table-striped">
                            <thead>
                                <tr>
                                    <th>No. Pinjaman</th>
                                    <th>Guru / Karyawan</th>
                                    <th>Tgl Pinjam</th>
                                    <th>Total Pinjaman</th>
                                    <th>Tenor & Cicilan/Bln</th>
                                    <th>Terbayar</th>
                                    <th>Sisa Pinjaman</th>
                                    <th>Status</th>
                                    <th class="w-1">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pinjaman as $item)
                                    <tr>
                                        <td>
                                            <a href="/pinjaman/{{ $item->id }}" class="fw-bold">{{ $item->nomor_pinjaman }}</a>
                                            @if ($item->potong_gaji)
                                                <div><span class="badge bg-blue-lt" style="font-size: 0.68rem;">Auto Potong Gaji</span></div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-bold">{{ $item->nama_lengkap }}</div>
                                            <div class="text-muted small">{{ $item->nik }} &bull; {{ $item->nama_cabang ?? $item->kode_cabang }}</div>
                                        </td>
                                        <td>{{ date('d/m/Y', strtotime($item->tanggal_pinjaman)) }}</td>
                                        <td class="fw-bold">Rp {{ number_format($item->jumlah_pinjaman, 0, ',', '.') }}</td>
                                        <td>
                                            <div>{{ $item->tenor_bulan }}x Cicilan</div>
                                            <div class="text-primary small fw-bold">@ Rp {{ number_format($item->nominal_angsuran_per_bulan, 0, ',', '.') }}/bln</div>
                                        </td>
                                        <td class="text-success fw-bold">Rp {{ number_format($item->total_dibayar, 0, ',', '.') }}</td>
                                        <td class="text-danger fw-bold">Rp {{ number_format($item->sisa_pinjaman, 0, ',', '.') }}</td>
                                        <td>
                                            @if ($item->status === 'berjalan')
                                                <span class="badge bg-warning text-dark">Berjalan</span>
                                            @elseif ($item->status === 'lunas')
                                                <span class="badge bg-success">Lunas</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($item->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-list flex-nowrap">
                                                <a href="/pinjaman/{{ $item->id }}" class="btn btn-sm btn-primary">
                                                    Detail / Cicilan
                                                </a>
                                                <form action="/pinjaman/{{ $item->id }}/delete" method="POST" onsubmit="return confirm('Hapus data pinjaman {{ $item->nomor_pinjaman }} ini?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            Belum ada data pinjaman / kasbon karyawan. Klik tombol <strong>Tambah Pinjaman / Kasbon</strong> di kanan atas untuk mencatat pinjaman baru.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $pinjaman->links('vendor.pagination.bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Pinjaman -->
    <div class="modal modal-blur fade" id="modal-tambah-pinjaman" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pinjaman / Kasbon Guru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/pinjaman/store" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">Pilih Guru / Karyawan</label>
                            <select name="nik" class="form-select" required>
                                <option value="">-- Pilih Karyawan --</option>
                                @foreach ($karyawanList as $k)
                                    <option value="{{ $k->nik }}">{{ $k->nama_lengkap }} ({{ $k->nik }} - {{ $k->nama_cabang ?? '-' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Tanggal Pinjaman</label>
                                <input type="date" name="tanggal_pinjaman" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mulai Cicil Bulan</label>
                                <input type="month" name="mulai_cicil_bulan" class="form-control" value="{{ date('Y-m') }}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-7 mb-3">
                                <label class="form-label required">Jumlah Pinjaman (Rp)</label>
                                <input type="number" name="jumlah_pinjaman" id="inp_jumlah_pinjaman" class="form-control" placeholder="Contoh: 1000000" required>
                            </div>
                            <div class="col-md-5 mb-3">
                                <label class="form-label required">Tenor (Bulan)</label>
                                <input type="number" name="tenor_bulan" id="inp_tenor_bulan" class="form-control" value="1" min="1" max="60" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nominal Cicilan per Bulan (Rp)</label>
                            <input type="number" name="nominal_angsuran_per_bulan" id="inp_angsuran_bulan" class="form-control" placeholder="Otomatis dihitung (Jumlah / Tenor)">
                            <small class="form-hint">Otomatis tersinkron ke kolom Potongan Kasbon di Master Gaji & Slip Gaji.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="potong_gaji" value="1" checked>
                                <span class="form-check-label">Otomatis Potong di Slip Gaji Bulanan</span>
                            </label>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Keterangan / Keperluan Pinjaman</label>
                            <textarea name="keterangan" rows="2" class="form-control" placeholder="Contoh: Kasbon keperluan mendadak, dicicil 4x"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn me-auto" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Pinjaman</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('myscript')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var jml = document.getElementById('inp_jumlah_pinjaman');
                var tenor = document.getElementById('inp_tenor_bulan');
                var angsuran = document.getElementById('inp_angsuran_bulan');

                function calcCicilan() {
                    var vJml = parseFloat(jml.value || 0);
                    var vTenor = parseInt(tenor.value || 1);
                    if (vJml > 0 && vTenor > 0) {
                        angsuran.value = Math.round(vJml / vTenor);
                    }
                }
                if (jml && tenor) {
                    jml.addEventListener('input', calcCicilan);
                    tenor.addEventListener('input', calcCicilan);
                }
            });
        </script>
    @endpush
@endsection
