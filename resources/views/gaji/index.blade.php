@extends('layouts.admin.tabler')
@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Modul Penggajian</div>
                <h2 class="page-title">
                    Daftar Periode Penggajian & Slip Gaji
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="/gaji/master" class="btn btn-outline-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-settings" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z"/><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"/></svg>
                        Master Gaji Karyawan
                    </a>
                    <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalGenerateGaji">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-calculator" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 3m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v14a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z"/><path d="M8 7h8"/><path d="M8 11h2"/><path d="M8 15h2"/><path d="M14 11h2"/><path d="M14 15h2"/></svg>
                        Generate Gaji Periode Baru
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row">
            <div class="col-12">
                @if (Session::get('success'))
                    <div class="alert alert-success alert-dismissible" role="alert">
                        <div class="d-flex">
                            <div>{{ Session::get('success') }}</div>
                        </div>
                        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif
                @if (Session::get('warning'))
                    <div class="alert alert-warning alert-dismissible" role="alert">
                        <div class="d-flex">
                            <div>{{ Session::get('warning') }}</div>
                        </div>
                        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Riwayat Periode Penggajian</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table table-striped">
                            <thead>
                                <tr>
                                    <th>Kode Periode</th>
                                    <th>Nama Periode</th>
                                    <th>Rentang Tanggal</th>
                                    <th>Standar HK</th>
                                    <th>Total Karyawan</th>
                                    <th>Total Gaji (THP)</th>
                                    <th>Status WA</th>
                                    <th>Status</th>
                                    <th class="w-1">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($periode as $p)
                                <tr>
                                    <td><span class="badge bg-blue-lt font-monospace">{{ $p->kode_periode }}</span></td>
                                    <td>
                                        <a href="/gaji/periode/{{ $p->id }}" class="text-reset fw-bold">
                                            {{ $p->nama_periode }}
                                        </a>
                                    </td>
                                    <td class="text-muted">
                                        {{ date('d/m/Y', strtotime($p->tgl_mulai)) }} s/d {{ date('d/m/Y', strtotime($p->tgl_selesai)) }}
                                    </td>
                                    <td><span class="badge bg-secondary-lt">{{ $p->hk_standar }} Hari</span></td>
                                    <td><span class="badge bg-primary">{{ $p->total_karyawan }} Orang</span></td>
                                    <td class="fw-bold text-success">
                                        Rp {{ number_format($p->total_gaji, 0, ',', '.') }}
                                    </td>
                                    <td>
                                        @if ($p->total_wa_terkirim > 0)
                                            <span class="badge bg-success-lt">{{ $p->total_wa_terkirim }}/{{ $p->total_karyawan }} Terkirim</span>
                                        @else
                                            <span class="badge bg-secondary-lt">Belum Dikirim</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($p->status == 'final')
                                            <span class="badge bg-success">Final</span>
                                        @elseif ($p->status == 'terbayar')
                                            <span class="badge bg-primary">Terbayar</span>
                                        @else
                                            <span class="badge bg-warning">Draft</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="/gaji/periode/{{ $p->id }}" class="btn btn-sm btn-info" title="Lihat Detail Slip">
                                                Buka Slip
                                            </a>
                                            <form action="/gaji/periode/{{ $p->id }}/delete" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus seluruh data gaji periode ini?')">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger ms-1" title="Hapus Periode">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        Belum ada data periode penggajian. Klik tombol <strong>"Generate Gaji Periode Baru"</strong> untuk memulai.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer d-flex align-items-center">
                        {{ $periode->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL GENERATE GAJI --}}
<div class="modal modal-blur fade" id="modalGenerateGaji" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="/gaji/storeperiode" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Generate Penggajian & Slip Gaji Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Ketentuan Otomatis:</strong>
                        <ul class="mb-0 ps-3">
                            <li>Standar <strong>26 Hari Kerja (HK)</strong> per bulan.</li>
                            <li>Gaji Harian = Gaji Pokok / 26 HK. Potongan Absen = (Izin + Alpha) × Gaji Harian.</li>
                            <li>Potongan Keterlambatan dihitung otomatis dari data presensi.</li>
                            <li>Bunda TPA: Remunerasi Gate 41 Pool diaktifkan jika total siswa TPA &ge; 41.</li>
                            <li>Setelah digenerate, seluruh komponen tetap dapat diedit oleh Superadmin.</li>
                        </ul>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Bulan Penggajian</label>
                            <select name="bulan" class="form-select" required>
                                @for ($b = 1; $b <= 12; $b++)
                                    <option value="{{ $b }}" {{ date('m') == $b ? 'selected' : '' }}>
                                        {{ $namabulan[$b] }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Tahun</label>
                            <select name="tahun" class="form-select" required>
                                @for ($y = date('Y') - 1; $y <= date('Y') + 2; $y++)
                                    <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Tanggal Mulai Absensi</label>
                            <input type="date" name="tgl_mulai" class="form-control" value="{{ date('Y-m-01') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Tanggal Selesai Absensi</label>
                            <input type="date" name="tgl_selesai" class="form-control" value="{{ date('Y-m-t') }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Standar Hari Kerja (HK)</label>
                            <input type="number" name="hk_standar" class="form-control" value="26" min="1" max="31" required>
                            <small class="text-muted">Standar yayasan: 26 hari kerja.</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Total Siswa TPA (Bulan Ini)</label>
                            <input type="number" name="total_siswa_tpa" class="form-control" value="41" placeholder="Contoh: 42">
                            <small class="text-muted">Jika &ge; 41 siswa, pool insentif TPA otomatis aktif.</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Keterangan / Catatan Periode</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan opsional untuk periode ini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary ms-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-calculator" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 3m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v14a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z"/><path d="M8 7h8"/><path d="M8 11h2"/><path d="M8 15h2"/><path d="M14 11h2"/><path d="M14 15h2"/></svg>
                        Hitung & Generate Gaji Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
