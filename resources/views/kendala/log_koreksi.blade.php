@extends('layouts.admin.tabler')
@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Audit Trail & Keamanan Data</div>
                    <h2 class="page-title">Log Audit Koreksi Presensi (Revisi Absensi)</h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <a href="/presensi/kendala-absen" class="btn btn-outline-primary">
                        Tiket Kendala Absen
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="card">
                <div class="card-body">
                    <form action="/presensi/log-koreksi" method="GET" class="mb-3">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="text" name="cari" value="{{ Request('cari') }}" class="form-control" placeholder="Cari Nama Guru / NIK / Nama Admin Eksekutor / Alasan...">
                            </div>
                            <div class="col-md-4">
                                <select name="kode_cabang" class="form-select">
                                    <option value="">Semua Cabang</option>
                                    @foreach ($cabang as $c)
                                        <option value="{{ $c->kode_cabang }}" {{ Request('kode_cabang') == $c->kode_cabang ? 'selected' : '' }}>
                                            {{ $c->nama_cabang }}
                                        </option>
                                    @endforeach
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
                                    <th>Waktu Eksekusi</th>
                                    <th>Admin Eksekutor</th>
                                    <th>Guru / Karyawan</th>
                                    <th>Tgl Presensi</th>
                                    <th>Jenis Tindakan</th>
                                    <th>Data Sebelumnya</th>
                                    <th>Data Perubahan</th>
                                    <th>Alasan / Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($logs as $log)
                                    <tr>
                                        <td class="text-muted small">
                                            {{ $log->created_at ? date('d/m/Y H:i:s', strtotime($log->created_at)) : '-' }}
                                        </td>
                                        <td class="fw-bold">{{ $log->nama_eksekutor ?: 'Admin' }}</td>
                                        <td>
                                            <div class="fw-bold">{{ $log->nama_lengkap ?: $log->nik }}</div>
                                            <div class="text-muted small">{{ $log->nik }} &bull; {{ $log->nama_cabang ?? '-' }}</div>
                                        </td>
                                        <td class="fw-bold">{{ date('d-m-Y', strtotime($log->tgl_presensi)) }}</td>
                                        <td>
                                            <span class="badge bg-azure-lt text-uppercase">{{ str_replace('_', ' ', $log->jenis_tindakan) }}</span>
                                        </td>
                                        <td class="small font-monospace text-muted">{{ $log->data_sebelumnya }}</td>
                                        <td class="small font-monospace text-success fw-bold">{{ $log->data_perubahan }}</td>
                                        <td>{{ $log->alasan ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            Belum ada riwayat koreksi presensi yang tercatat di log audit. Setiap koreksi presensi dari menu Monitoring maupun Tiket Kendala akan otomatis tercatat di sini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $logs->links('vendor.pagination.bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
