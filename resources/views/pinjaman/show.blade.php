@extends('layouts.admin.tabler')
@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        <a href="/pinjaman">&laquo; Kembali ke Daftar Pinjaman</a>
                    </div>
                    <h2 class="page-title">Detail Pinjaman {{ $pinjaman->nomor_pinjaman }} — {{ $pinjaman->nama_lengkap }}</h2>
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

            <div class="row row-cards">
                <!-- Info Pinjaman & Form Catat Angsuran -->
                <div class="col-md-5">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Ringkasan Pinjaman / Kasbon</h3>
                            <div class="card-actions">
                                @if ($pinjaman->status === 'berjalan')
                                    <span class="badge bg-warning text-dark">Berjalan</span>
                                @elseif ($pinjaman->status === 'lunas')
                                    <span class="badge bg-success">Lunas</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($pinjaman->status) }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted">Nama Guru/Karyawan</td>
                                    <td class="fw-bold">{{ $pinjaman->nama_lengkap }} ({{ $pinjaman->nik }})</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Cabang / Jabatan</td>
                                    <td>{{ $pinjaman->nama_cabang ?? '-' }} / {{ $pinjaman->jabatan ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Tanggal Pinjaman</td>
                                    <td>{{ date('d-m-Y', strtotime($pinjaman->tanggal_pinjaman)) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Jumlah Pinjaman</td>
                                    <td class="fw-bold">Rp {{ number_format($pinjaman->jumlah_pinjaman, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Tenor & Cicilan</td>
                                    <td class="text-primary fw-bold">{{ $pinjaman->tenor_bulan }} Bulan (@ Rp {{ number_format($pinjaman->nominal_angsuran_per_bulan, 0, ',', '.') }})</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Total Sudah Dibayar</td>
                                    <td class="text-success fw-bold">Rp {{ number_format($pinjaman->total_dibayar, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Sisa Pinjaman</td>
                                    <td class="text-danger fw-bold fs-3">Rp {{ number_format($pinjaman->sisa_pinjaman, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Keterangan</td>
                                    <td>{{ $pinjaman->keterangan ?: '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if ($pinjaman->sisa_pinjaman > 0)
                        <div class="card">
                            <div class="card-header bg-primary-lt">
                                <h3 class="card-title">Catat Pembayaran Angsuran / Cicilan</h3>
                            </div>
                            <form action="/pinjaman/{{ $pinjaman->id }}/angsuran" method="POST">
                                @csrf
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label class="form-label">Angsuran Ke-</label>
                                            <input type="number" name="angsuran_ke" class="form-control" value="{{ $angsuran->count() + 1 }}" min="1" required>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label class="form-label">Tanggal Bayar</label>
                                            <input type="date" name="tanggal_bayar" class="form-control" value="{{ date('Y-m-d') }}" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Nominal Bayar (Rp)</label>
                                        <input type="number" name="jumlah_bayar" class="form-control" value="{{ min($pinjaman->nominal_angsuran_per_bulan, $pinjaman->sisa_pinjaman) }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Metode Pembayaran</label>
                                        <select name="metode_pembayaran" class="form-select">
                                            <option value="potong_gaji">Potong Slip Gaji</option>
                                            <option value="tunai">Bayar Tunai</option>
                                            <option value="transfer">Transfer Bank</option>
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Catatan (Opsional)</label>
                                        <input type="text" name="keterangan" class="form-control" placeholder="Contoh: Potong gaji bulan berjalan">
                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <button type="submit" class="btn btn-success w-100">Simpan Pembayaran Angsuran</button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>

                <!-- Tabel Riwayat Angsuran -->
                <div class="col-md-7">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Riwayat Pembayaran Angsuran ({{ $angsuran->count() }}x)</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table table-striped">
                                <thead>
                                    <tr>
                                        <th>Ke-</th>
                                        <th>No. Transaksi</th>
                                        <th>Tanggal Bayar</th>
                                        <th>Metode</th>
                                        <th>Jumlah Bayar</th>
                                        <th>Keterangan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($angsuran as $ang)
                                        <tr>
                                            <td><span class="badge bg-blue">{{ $ang->angsuran_ke }}</span></td>
                                            <td class="text-muted">{{ $ang->nomor_transaksi }}</td>
                                            <td>{{ date('d/m/Y', strtotime($ang->tanggal_bayar)) }}</td>
                                            <td><span class="badge bg-azure-lt">{{ strtoupper(str_replace('_', ' ', $ang->metode_pembayaran)) }}</span></td>
                                            <td class="fw-bold text-success">Rp {{ number_format($ang->jumlah_bayar, 0, ',', '.') }}</td>
                                            <td>{{ $ang->keterangan ?: '-' }}</td>
                                            <td>
                                                <form action="/pinjaman/angsuran/{{ $ang->id }}/delete" method="POST" onsubmit="return confirm('Hapus catatan angsuran ini?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                Belum ada pembayaran angsuran yang tercatat untuk pinjaman ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
