@extends('layouts.admin.tabler')
@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        <a href="/pengumuman">&laquo; Kembali ke Papan Pengumuman</a>
                    </div>
                    <h2 class="page-title">{{ $item->judul }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Isi Pengumuman</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <span class="badge bg-blue text-uppercase">{{ $item->kategori }}</span>
                                <span class="badge bg-azure-lt ms-1">Prioritas: {{ ucfirst($item->prioritas) }}</span>
                            </div>
                            <div class="text-muted small mb-3">
                                Tayang: {{ date('d-m-Y', strtotime($item->tanggal_tayang)) }}
                                @if ($item->tanggal_berakhir)
                                    s/d {{ date('d-m-Y', strtotime($item->tanggal_berakhir)) }}
                                @endif
                            </div>
                            <div class="p-3 bg-light rounded" style="white-space: pre-line;">{{ $item->isi }}</div>

                            @if (!empty($item->lampiran))
                                <div class="mt-3">
                                    <a href="/uploads/pengumuman/{{ $item->lampiran }}" target="_blank" class="btn btn-outline-primary w-100">
                                        Lihat / Unduh Lampiran
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-7">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Daftar Guru / Karyawan yang Sudah Membaca ({{ $pembaca->count() }} Orang)</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Guru / Karyawan</th>
                                        <th>Jabatan & Cabang</th>
                                        <th>Waktu Membaca</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($pembaca as $idx => $p)
                                        <tr>
                                            <td>{{ $idx + 1 }}</td>
                                            <td>
                                                <div class="fw-bold">{{ $p->nama_lengkap }}</div>
                                                <div class="text-muted small">NIK: {{ $p->nik }}</div>
                                            </td>
                                            <td>{{ $p->jabatan ?? '-' }} ({{ $p->nama_cabang ?? '-' }})</td>
                                            <td class="text-success fw-bold">
                                                {{ $p->dibaca_pada ? date('d/m/Y H:i', strtotime($p->dibaca_pada)) : '-' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                Belum ada guru/karyawan yang membuka pengumuman ini.
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
