@extends('layouts.admin.tabler')
@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Komunikasi Internal</div>
                    <h2 class="page-title">Papan Pengumuman Yayasan</h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-buat-pengumuman">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24"
                            stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M12 5l0 14" />
                            <path d="M5 12l14 0" />
                        </svg>
                        Buat Pengumuman Baru
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

            <div class="card">
                <div class="card-body">
                    <form action="/pengumuman" method="GET" class="mb-3">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="text" name="cari" value="{{ Request('cari') }}" class="form-control" placeholder="Cari Judul / Isi Pengumuman...">
                            </div>
                            <div class="col-md-4">
                                <select name="kategori" class="form-select">
                                    <option value="">Semua Kategori</option>
                                    <option value="umum" {{ Request('kategori') == 'umum' ? 'selected' : '' }}>Umum</option>
                                    <option value="penting" {{ Request('kategori') == 'penting' ? 'selected' : '' }}>Penting</option>
                                    <option value="kebijakan" {{ Request('kategori') == 'kebijakan' ? 'selected' : '' }}>Kebijakan</option>
                                    <option value="agenda" {{ Request('kategori') == 'agenda' ? 'selected' : '' }}>Agenda Kegiatan</option>
                                    <option value="darurat" {{ Request('kategori') == 'darurat' ? 'selected' : '' }}>Darurat</option>
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
                                    <th>Judul & Isi Ringkas</th>
                                    <th>Kategori & Prioritas</th>
                                    <th>Periode Tayang</th>
                                    <th>Sudah Dibaca</th>
                                    <th>Status</th>
                                    <th class="w-1">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pengumuman as $item)
                                    <tr>
                                        <td>
                                            <a href="/pengumuman/{{ $item->id }}" class="fw-bold">{{ $item->judul }}</a>
                                            <div class="text-muted small">{{ \Illuminate\Support\Str::limit(strip_tags($item->isi), 90) }}</div>
                                            @if (!empty($item->lampiran))
                                                <div class="mt-1">
                                                    <a href="/uploads/pengumuman/{{ $item->lampiran }}" target="_blank" class="badge bg-azure-lt">Lampiran Tersedia</a>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-blue-lt text-uppercase">{{ $item->kategori }}</span>
                                            <div class="small text-muted mt-1">Prioritas: <strong>{{ ucfirst($item->prioritas) }}</strong></div>
                                        </td>
                                        <td>
                                            <div>Mulai: <strong>{{ date('d/m/Y', strtotime($item->tanggal_tayang)) }}</strong></div>
                                            <div class="text-muted small">
                                                Selesai: {{ $item->tanggal_berakhir ? date('d/m/Y', strtotime($item->tanggal_berakhir)) : 'Tidak dibatasi' }}
                                            </div>
                                        </td>
                                        <td>
                                            <a href="/pengumuman/{{ $item->id }}" class="badge bg-green text-white">
                                                {{ $item->jumlah_pembaca }} Guru Membaca
                                            </a>
                                        </td>
                                        <td>
                                            @if ($item->status_aktif)
                                                <span class="badge bg-success">Aktif Tayang</span>
                                            @else
                                                <span class="badge bg-secondary">Nonaktif</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-list flex-nowrap">
                                                <a href="/pengumuman/{{ $item->id }}" class="btn btn-sm btn-outline-primary">
                                                    Cek Pembaca
                                                </a>
                                                <form action="/pengumuman/{{ $item->id }}/update" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="toggle_status" value="1">
                                                    <button type="submit" class="btn btn-sm btn-outline-warning">
                                                        {{ $item->status_aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                                    </button>
                                                </form>
                                                <form action="/pengumuman/{{ $item->id }}/delete" method="POST" onsubmit="return confirm('Hapus pengumuman ini?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            Belum ada pengumuman yang diterbitkan. Klik tombol <strong>Buat Pengumuman Baru</strong> untuk mengirim informasi ke dashboard HP seluruh guru/karyawan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $pengumuman->links('vendor.pagination.bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Buat Pengumuman -->
    <div class="modal modal-blur fade" id="modal-buat-pengumuman" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Buat Pengumuman Yayasan Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/pengumuman/store" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">Judul Pengumuman</label>
                            <input type="text" name="judul" class="form-control" placeholder="Contoh: Jadwal Rapat Evaluasi Bulanan & Pembagian Rapor" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Kategori</label>
                                <select name="kategori" class="form-select" required>
                                    <option value="umum">Umum</option>
                                    <option value="penting">Penting</option>
                                    <option value="kebijakan">Kebijakan Yayasan</option>
                                    <option value="agenda">Agenda Kegiatan</option>
                                    <option value="darurat">Darurat</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Prioritas</label>
                                <select name="prioritas" class="form-select" required>
                                    <option value="normal">Normal</option>
                                    <option value="tinggi">Tinggi (Tampil Paling Atas)</option>
                                    <option value="rendah">Rendah</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Tanggal Mulai Tayang</label>
                                <input type="date" name="tanggal_tayang" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Berakhir (Opsional)</label>
                                <input type="date" name="tanggal_berakhir" class="form-control">
                                <small class="form-hint">Kosongkan jika ingin tetap tayang tanpa batas tanggal.</small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Isi Pengumuman</label>
                            <textarea name="isi" rows="5" class="form-control" placeholder="Tulis isi lengkap pengumuman di sini..." required></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Lampiran File / Foto / PDF (Opsional)</label>
                            <input type="file" name="lampiran" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn me-auto" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Terbitkan Pengumuman</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
