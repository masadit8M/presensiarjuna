@extends('layouts.admin.tabler')
@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Monitoring & Bantuan Presensi</div>
                    <h2 class="page-title">Tiket Laporan Kendala Absen Guru / Karyawan</h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <a href="/presensi/log-koreksi" class="btn btn-outline-primary">
                        Lihat Log Audit Koreksi Presensi
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
                            <div class="font-weight-medium text-warning fw-bold fs-3">{{ $stats['diajukan'] }}</div>
                            <div class="text-muted">Menunggu Diproses (Diajukan)</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="font-weight-medium text-info fw-bold fs-3">{{ $stats['diproses'] }}</div>
                            <div class="text-muted">Sedang Diproses</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="font-weight-medium text-success fw-bold fs-3">{{ $stats['selesai'] }}</div>
                            <div class="text-muted">Selesai Ditindaklanjuti</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="font-weight-medium text-danger fw-bold fs-3">{{ $stats['ditolak'] }}</div>
                            <div class="text-muted">Ditolak</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="/presensi/kendala-absen" method="GET" class="mb-3">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <input type="text" name="cari" value="{{ Request('cari') }}" class="form-control" placeholder="Cari Nama Guru / NIK / No. Tiket...">
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
                                    <option value="diajukan" {{ Request('status') == 'diajukan' ? 'selected' : '' }}>Diajukan (Baru)</option>
                                    <option value="diproses" {{ Request('status') == 'diproses' ? 'selected' : '' }}>Diproses</option>
                                    <option value="selesai" {{ Request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                    <option value="ditolak" {{ Request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
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
                                    <th>No. Tiket</th>
                                    <th>Guru / Karyawan</th>
                                    <th>Tgl & Jam Kejadian</th>
                                    <th>Kategori & Kronologi</th>
                                    <th>Bukti</th>
                                    <th>Status & Tanggapan</th>
                                    <th class="w-1">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tiket as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $item->nomor_tiket }}</div>
                                            <div class="text-muted small">{{ date('d/m/Y H:i', strtotime($item->created_at)) }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-bold">{{ $item->nama_lengkap }}</div>
                                            <div class="text-muted small">{{ $item->nik }} &bull; {{ $item->nama_cabang ?? $item->kode_cabang }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-bold">{{ date('d-m-Y', strtotime($item->tanggal_kejadian)) }}</div>
                                            <div class="text-muted small">Jam: {{ $item->jam_kejadian ? substr($item->jam_kejadian, 0, 5) : '-' }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-blue-lt">{{ $item->judul }}</span>
                                            <div class="small mt-1">{{ $item->deskripsi }}</div>
                                        </td>
                                        <td>
                                            @if (!empty($item->foto_bukti))
                                                <a href="/uploads/kendala/{{ $item->foto_bukti }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                    Lihat Foto
                                                </a>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($item->status === 'diajukan')
                                                <span class="badge bg-warning text-dark">Diajukan</span>
                                            @elseif ($item->status === 'diproses')
                                                <span class="badge bg-info">Diproses</span>
                                            @elseif ($item->status === 'selesai')
                                                <span class="badge bg-success">Selesai</span>
                                            @else
                                                <span class="badge bg-danger">Ditolak</span>
                                            @endif
                                            @if (!empty($item->catatan_admin))
                                                <div class="small text-muted mt-1">{{ $item->catatan_admin }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal-proses-{{ $item->id }}">
                                                Proses / Koreksi
                                            </button>

                                            <!-- Modal Proses Tiket -->
                                            <div class="modal modal-blur fade" id="modal-proses-{{ $item->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Proses Tiket {{ $item->nomor_tiket }} — {{ $item->nama_lengkap }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <form action="/presensi/kendala-absen/{{ $item->id }}/proses" method="POST">
                                                            @csrf
                                                            <div class="modal-body">
                                                                <div class="p-2 mb-3 bg-light rounded">
                                                                    <div><strong>Tgl Kejadian:</strong> {{ date('d-m-Y', strtotime($item->tanggal_kejadian)) }} ({{ $item->jam_kejadian ? substr($item->jam_kejadian, 0, 5) : '-' }})</div>
                                                                    <div><strong>Keluhan:</strong> {{ $item->judul }} — {{ $item->deskripsi }}</div>
                                                                </div>

                                                                <div class="row">
                                                                    <div class="col-md-6 mb-3">
                                                                        <label class="form-label required">Status Tiket</label>
                                                                        <select name="status" class="form-select" required>
                                                                            <option value="selesai" {{ $item->status == 'selesai' ? 'selected' : '' }}>Selesai (Disetujui)</option>
                                                                            <option value="diproses" {{ $item->status == 'diproses' ? 'selected' : '' }}>Sedang Diproses</option>
                                                                            <option value="ditolak" {{ $item->status == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-6 mb-3">
                                                                        <label class="form-label required">Tindakan Admin</label>
                                                                        <select name="tindakan_admin" class="form-select" required>
                                                                            <option value="koreksi_absen_pulang" {{ $item->kategori_masalah == 'lupa_absen_pulang' ? 'selected' : '' }}>Update Jam Absen Pulang Otomatis</option>
                                                                            <option value="koreksi_absen_masuk" {{ $item->kategori_masalah == 'lupa_absen_masuk' ? 'selected' : '' }}>Update Jam Absen Masuk Otomatis</option>
                                                                            <option value="koreksi_penuh">Update Jam Masuk & Pulang Otomatis</option>
                                                                            <option value="tanpa_perubahan_data">Catatan Saja (Tanpa Ubah Presensi)</option>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="row">
                                                                    <div class="col-md-4 mb-3">
                                                                        <label class="form-label">Jam Masuk (HH:MM:SS)</label>
                                                                        <input type="text" name="jam_in_koreksi" class="form-control" value="{{ $item->kategori_masalah == 'lupa_absen_masuk' && $item->jam_kejadian ? $item->jam_kejadian : '07:15:00' }}">
                                                                    </div>
                                                                    <div class="col-md-4 mb-3">
                                                                        <label class="form-label">Jam Pulang (HH:MM:SS)</label>
                                                                        <input type="text" name="jam_out_koreksi" class="form-control" value="{{ $item->kategori_masalah == 'lupa_absen_pulang' && $item->jam_kejadian ? $item->jam_kejadian : '15:30:00' }}">
                                                                    </div>
                                                                    <div class="col-md-4 mb-3">
                                                                        <label class="form-label">Shift Jam Kerja</label>
                                                                        <select name="kode_jam_kerja" class="form-select">
                                                                            @foreach ($jamkerja as $jk)
                                                                                <option value="{{ $jk->kode_jam_kerja }}">{{ $jk->nama_jam_kerja }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="mb-2">
                                                                    <label class="form-label">Catatan Balasan Admin</label>
                                                                    <textarea name="catatan_admin" rows="2" class="form-control" placeholder="Contoh: Sudah dikoreksi jam pulang sesuai laporan">{{ $item->catatan_admin }}</textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn me-auto" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-primary">Simpan & Proses Tiket</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            Belum ada tiket laporan kendala absen dari guru/karyawan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $tiket->links('vendor.pagination.bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
