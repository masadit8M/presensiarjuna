@extends('layouts.admin.tabler')
@section('content')
    @php
        $jmlHadir = (int) ($rekappresensi->jmlhadir ?? 0);
        $jmlIzin = (int) ($rekappresensi->jmlizin ?? 0);
        $jmlSakit = (int) ($rekappresensi->jmlsakit ?? 0);
        $jmlCuti = (int) ($rekappresensi->jmlcuti ?? 0);
        $jmlTerlambat = (int) ($rekappresensi->jmlterlambat ?? 0);
        $jmlBelumAbsen = max(0, ($totalKaryawan ?? 0) - ($jmlHadir + $jmlIzin + $jmlSakit + $jmlCuti));
    @endphp
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        Overview & Live Monitoring Presensi Selfie
                    </div>
                    <h2 class="page-title">
                        Dashboard Admin ({{ date('d-m-Y', strtotime($hariini ?? date('Y-m-d'))) }})
                    </h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        @if (!empty($pendingIzinCount) && $pendingIzinCount > 0)
                            <a href="{{ url('/presensi/izinsakit?status_approved=0') }}" class="btn btn-warning">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-bell-ringing" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                    <path d="M10 5a2 2 0 0 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6"></path>
                                    <path d="M9 17v1a3 3 0 0 0 6 0v-1"></path>
                                </svg>
                                {{ $pendingIzinCount }} Pengajuan Izin Menunggu Persetujuan
                            </a>
                        @endif
                        <a href="{{ url('/presensi/monitoring') }}" class="btn btn-outline-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-device-desktop" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M3 4m0 1a1 1 0 0 1 1 -1h16a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-16a1 1 0 0 1 -1 -1z"></path>
                                <path d="M7 20l10 0"></path>
                                <path d="M9 16l0 4"></path>
                                <path d="M15 16l0 4"></path>
                            </svg>
                            Layar Penuh Monitoring
                        </a>
                        <a href="{{ url('/gaji') }}" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-cash" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M7 9m0 2a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2z"></path>
                                <path d="M14 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                <path d="M17 9v-2a2 2 0 0 0 -2 -2h-10a2 2 0 0 0 -2 2v6a2 2 0 0 0 2 2h2"></path>
                            </svg>
                            Sistem Penggajian
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            @if (Session::get('success'))
                <div class="alert alert-success alert-dismissible mb-3" role="alert">
                    <div>{{ Session::get('success') }}</div>
                    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                </div>
            @endif
            @if (Session::get('warning'))
                <div class="alert alert-warning alert-dismissible mb-3" role="alert">
                    <div>{{ Session::get('warning') }}</div>
                    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                </div>
            @endif

            {{-- Summary Cards --}}
            <div class="row row-cards mb-3">
                <div class="col-sm-6 col-lg-2">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="bg-blue text-white avatar">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-users" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                            <path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"></path>
                                            <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"></path>
                                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                            <path d="M21 21v-2a4 4 0 0 0 -3 -3.85"></path>
                                        </svg>
                                    </span>
                                </div>
                                <div class="col">
                                    <div class="font-weight-medium">{{ $totalKaryawan ?? 0 }}</div>
                                    <div class="text-secondary small">Total Karyawan</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="bg-success text-white avatar">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-fingerprint" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                            <path d="M18.9 7a8 8 0 0 1 1.1 5v1a6 6 0 0 0 .8 3"></path>
                                            <path d="M8 11a4 4 0 0 1 8 0v1a10 10 0 0 0 2 6"></path>
                                            <path d="M12 11v2a14 14 0 0 0 2.5 8"></path>
                                            <path d="M8 15a18 18 0 0 0 1.8 6"></path>
                                            <path d="M4.9 19a22 22 0 0 1 -.9 -7v-1a8 8 0 0 1 12 -6.95"></path>
                                        </svg>
                                    </span>
                                </div>
                                <div class="col">
                                    <div class="font-weight-medium">{{ $jmlHadir }}</div>
                                    <div class="text-secondary small">Karyawan Hadir</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="bg-info text-white avatar">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file-text" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                            <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                                            <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path>
                                            <path d="M9 9l1 0"></path>
                                            <path d="M9 13l6 0"></path>
                                            <path d="M9 17l6 0"></path>
                                        </svg>
                                    </span>
                                </div>
                                <div class="col">
                                    <div class="font-weight-medium">{{ $jmlIzin + $jmlCuti }}</div>
                                    <div class="text-secondary small">Izin / Cuti</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="bg-warning text-white avatar">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-mood-sick" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                            <path d="M12 21a9 9 0 1 1 0 -18a9 9 0 0 1 0 18z"></path>
                                            <path d="M9 10h-.01"></path>
                                            <path d="M15 10h-.01"></path>
                                            <path d="M8 16l1 -1l1.5 1l1.5 -1l1.5 1l1.5 -1l1 1"></path>
                                        </svg>
                                    </span>
                                </div>
                                <div class="col">
                                    <div class="font-weight-medium">{{ $jmlSakit }}</div>
                                    <div class="text-secondary small">Karyawan Sakit</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="bg-danger text-white avatar">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-alarm" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                            <path d="M12 13m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"></path>
                                            <path d="M12 10l0 3l2 0"></path>
                                            <path d="M7 4l-2.75 2"></path>
                                            <path d="M17 4l2.75 2"></path>
                                        </svg>
                                    </span>
                                </div>
                                <div class="col">
                                    <div class="font-weight-medium">{{ $jmlTerlambat }}</div>
                                    <div class="text-secondary small">Terlambat</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="bg-secondary text-white avatar">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-user-x" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                            <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"></path>
                                            <path d="M6 21v-2a4 4 0 0 1 4 -4h3.5"></path>
                                            <path d="M22 22l-5 -5"></path>
                                            <path d="M17 22l5 -5"></path>
                                        </svg>
                                    </span>
                                </div>
                                <div class="col">
                                    <div class="font-weight-medium">{{ $jmlBelumAbsen }}</div>
                                    <div class="text-secondary small">Belum Absen</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Live Monitoring Foto Selfie & Presensi Card --}}
            <div class="card">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <h3 class="card-title mb-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-camera me-1 text-primary" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M5 7h1a2 2 0 0 0 2 -2a1 1 0 0 1 1 -1h6a1 1 0 0 1 1 1a2 2 0 0 0 2 2h1a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-9a2 2 0 0 1 2 -2"></path>
                                <path d="M9 13a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"></path>
                            </svg>
                            Live Monitoring Presensi & Foto Hasil Selfie Karyawan
                        </h3>
                        <small class="text-muted">Klik pada foto selfie masuk/pulang untuk memperbesar tampilan foto secara penuh.</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-green-lt" id="badge-count-shown">Menampilkan {{ isset($presensiHariIni) ? count($presensiHariIni) : 0 }} Karyawan</span>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ url('/panel/dashboardadmin') }}" method="GET" id="frmFilterDashboard" class="mb-3">
                        <div class="row g-2">
                            <div class="col-md-2">
                                <label class="form-label small text-muted mb-1">Tanggal Presensi</label>
                                <input type="text" name="tanggal" id="tanggal_dashboard" value="{{ $hariini ?? date('Y-m-d') }}" class="form-control" autocomplete="off">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted mb-1">Cabang</label>
                                <select name="kode_cabang" id="kode_cabang_dashboard" class="form-select" onchange="this.form.submit()">
                                    <option value="">Semua Cabang</option>
                                    @if (isset($cabang))
                                        @foreach ($cabang as $c)
                                            <option value="{{ $c->kode_cabang }}" {{ Request('kode_cabang') == $c->kode_cabang ? 'selected' : '' }}>
                                                {{ strtoupper($c->nama_cabang) }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small text-muted mb-1">Departemen</label>
                                <select name="kode_dept" id="kode_dept_dashboard" class="form-select" onchange="this.form.submit()">
                                    <option value="">Semua Dept</option>
                                    @if (isset($departemen))
                                        @foreach ($departemen as $dept)
                                            <option value="{{ $dept->kode_dept }}" {{ Request('kode_dept') == $dept->kode_dept ? 'selected' : '' }}>
                                                {{ strtoupper($dept->nama_dept) }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small text-muted mb-1">Filter Status</label>
                                <select id="filter_status_dashboard" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="hadir">Sudah Hadir (Selfie)</option>
                                    <option value="terlambat">Terlambat</option>
                                    <option value="disiplin">Disiplin Pagi (&le; 06:30)</option>
                                    <option value="izin">Izin / Sakit / Cuti</option>
                                    <option value="belum">Belum Absen</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted mb-1">Cari Nama / NIK Karyawan</label>
                                <div class="input-group">
                                    <input type="text" name="cari" id="cari_karyawan_dashboard" value="{{ Request('cari') }}" class="form-control" placeholder="Ketik nama atau NIK...">
                                    <button class="btn btn-primary" type="submit" title="Cari">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-search m-0" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                            <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"></path>
                                            <path d="M21 21l-6 -6"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-vcenter table-striped table-hover table-bordered align-middle" id="tabelPresensiDashboard">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 45px">No</th>
                                    <th>Karyawan & NIK</th>
                                    <th>Cabang / Dept</th>
                                    <th>Jadwal Shift</th>
                                    <th class="text-center">Jam Masuk</th>
                                    <th class="text-center">Foto Selfie Masuk</th>
                                    <th class="text-center">Jam Pulang</th>
                                    <th class="text-center">Foto Selfie Pulang</th>
                                    <th class="text-center">Status</th>
                                    <th>Keterangan</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (isset($presensiHariIni) && count($presensiHariIni) > 0)
                                    @foreach ($presensiHariIni as $d)
                                        @php
                                            $foto_in_url = !empty($d->foto_in) ? url('/storage/uploads/absensi/' . $d->foto_in) : '';
                                            $foto_in_alt = !empty($d->foto_in) ? asset('uploads/absensi/' . $d->foto_in) : '';
                                            $foto_out_url = !empty($d->foto_out) ? url('/storage/uploads/absensi/' . $d->foto_out) : '';
                                            $foto_out_alt = !empty($d->foto_out) ? asset('uploads/absensi/' . $d->foto_out) : '';

                                            $isHadir = ($d->status == 'h' || !empty($d->jam_in));
                                            $isTerlambat = ($isHadir && !empty($d->jam_in) && !empty($d->jam_masuk) && $d->jam_in > $d->jam_masuk);
                                            $isDisiplinPagi = ($isHadir && !empty($d->jam_in) && date('H:i', strtotime($d->jam_in)) <= '06:30');
                                            $rowStatusFilter = 'belum';
                                            if ($isHadir) {
                                                $rowStatusFilter = 'hadir';
                                            } elseif (in_array($d->status, ['i', 's', 'c'])) {
                                                $rowStatusFilter = 'izin';
                                            }
                                        @endphp
                                        <tr class="row-presensi-item"
                                            data-search="{{ strtolower($d->nama_lengkap . ' ' . $d->nik . ' ' . ($d->jabatan ?? '') . ' ' . ($d->kode_cabang ?? '') . ' ' . ($d->kode_dept ?? '')) }}"
                                            data-status="{{ $rowStatusFilter }}"
                                            data-terlambat="{{ $isTerlambat ? '1' : '0' }}"
                                            data-disiplin="{{ $isDisiplinPagi ? '1' : '0' }}">
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $d->nama_lengkap }}</div>
                                                <div class="text-muted small">NIK: {{ $d->nik }} {{ !empty($d->jabatan) ? '• ' . $d->jabatan : '' }}</div>
                                            </td>
                                            <td>
                                                <span class="badge bg-blue-lt">{{ strtoupper($d->kode_cabang ?: '-') }}</span>
                                                <span class="badge bg-azure-lt">{{ strtoupper($d->kode_dept ?: '-') }}</span>
                                            </td>
                                            <td>
                                                @if (!empty($d->nama_jam_kerja))
                                                    <div class="fw-medium">{{ $d->nama_jam_kerja }}</div>
                                                    <small class="text-muted">{{ $d->jam_masuk }} - {{ $d->jam_pulang }}</small>
                                                @else
                                                    <span class="text-muted small">Reguler (07:00)</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($isHadir)
                                                    <span class="badge {{ $isTerlambat ? 'bg-danger' : 'bg-success' }}">{{ $d->jam_in }}</span>
                                                    @if ($isDisiplinPagi)
                                                        <div class="mt-1"><span class="badge bg-teal-lt" style="font-size:10px;">&le; 06:30 Pagi</span></div>
                                                    @endif
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($isHadir && !empty($d->foto_in))
                                                    <a href="javascript:void(0)" class="preview-foto d-inline-block"
                                                       data-src="{{ $foto_in_url }}"
                                                       data-alt-src="{{ $foto_in_alt }}"
                                                       data-title="Selfie Masuk - {{ $d->nama_lengkap }} ({{ $hariini }} {{ $d->jam_in }})"
                                                       title="Klik untuk memperbesar Foto Selfie Masuk">
                                                        <img src="{{ $foto_in_url }}"
                                                             onerror="if(this.src !== '{{ $foto_in_alt }}'){this.src='{{ $foto_in_alt }}';}else{this.onerror=null;this.src='{{ asset('assets/img/camera.jpg') }}';}"
                                                             class="avatar avatar-md rounded border shadow-sm"
                                                             style="object-fit: cover; cursor: zoom-in; width: 48px; height: 48px;"
                                                             alt="Selfie Masuk">
                                                    </a>
                                                @elseif ($isHadir)
                                                    <span class="badge bg-secondary-lt">Tanpa Foto</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($isHadir)
                                                    @if (!empty($d->jam_out))
                                                        <span class="badge bg-primary">{{ $d->jam_out }}</span>
                                                    @else
                                                        <span class="badge bg-warning-lt">Belum Pulang</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($isHadir && !empty($d->foto_out))
                                                    <a href="javascript:void(0)" class="preview-foto d-inline-block"
                                                       data-src="{{ $foto_out_url }}"
                                                       data-alt-src="{{ $foto_out_alt }}"
                                                       data-title="Selfie Pulang - {{ $d->nama_lengkap }} ({{ $hariini }} {{ $d->jam_out }})"
                                                       title="Klik untuk memperbesar Foto Selfie Pulang">
                                                        <img src="{{ $foto_out_url }}"
                                                             onerror="if(this.src !== '{{ $foto_out_alt }}'){this.src='{{ $foto_out_alt }}';}else{this.onerror=null;this.src='{{ asset('assets/img/camera.jpg') }}';}"
                                                             class="avatar avatar-md rounded border shadow-sm"
                                                             style="object-fit: cover; cursor: zoom-in; width: 48px; height: 48px;"
                                                             alt="Selfie Pulang">
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($isHadir)
                                                    <span class="badge bg-success">Hadir</span>
                                                @elseif ($d->status == 'i')
                                                    <span class="badge bg-info">Izin</span>
                                                @elseif ($d->status == 's')
                                                    <span class="badge bg-warning">Sakit</span>
                                                @elseif ($d->status == 'c')
                                                    <span class="badge bg-purple text-white">Cuti</span>
                                                @elseif ($d->status == 'a')
                                                    <span class="badge bg-danger">Alpha</span>
                                                @else
                                                    <span class="badge bg-secondary-lt">Belum Absen</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($isHadir)
                                                    @if ($isTerlambat)
                                                        @php
                                                            $jamterlambat = hitungjamterlambat($d->jam_masuk, $d->jam_in);
                                                        @endphp
                                                        <span class="badge bg-danger">Terlambat {{ $jamterlambat }}</span>
                                                    @else
                                                        <span class="badge bg-success">Tepat Waktu</span>
                                                    @endif
                                                @elseif (in_array($d->status, ['i', 's', 'c']))
                                                    <span class="text-muted small">{{ $d->keterangan ?: '-' }}</span>
                                                @else
                                                    <span class="text-muted small">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-list flex-nowrap justify-content-center">
                                                    @if ($isHadir && !empty($d->id))
                                                        <a href="#" class="btn btn-sm btn-primary tampilkanpeta" id="{{ $d->id }}" title="Lihat Titik Lokasi GPS">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-map-pin m-0" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                                <path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"></path>
                                                                <path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z"></path>
                                                            </svg>
                                                        </a>
                                                    @endif
                                                    <a href="#" class="btn btn-sm btn-success koreksipresensi" nik="{{ $d->nik }}" title="Koreksi Presensi">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-edit m-0" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                            <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"></path>
                                                            <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z"></path>
                                                            <path d="M16 5l3 3"></path>
                                                        </svg>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="11" class="text-center text-muted py-4">Tidak ada data karyawan yang sesuai filter.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Lokasi Peta --}}
    <div class="modal modal-blur fade" id="modal-tampilkanpeta" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Lokasi Presensi Karyawan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="loadmap"></div>
            </div>
        </div>
    </div>

    {{-- Modal Koreksi Presensi --}}
    <div class="modal modal-blur fade" id="modal-koreksipresensi" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Koreksi Presensi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="loadkoreksipresensi"></div>
            </div>
        </div>
    </div>
@endsection

@push('myscript')
    <script>
        $(function() {
            $("#tanggal_dashboard").datepicker({
                autoclose: true,
                todayHighlight: true,
                format: 'yyyy-mm-dd'
            }).on('changeDate', function() {
                $("#frmFilterDashboard").submit();
            });

            function filterDashboardRows() {
                var keyword = ($("#cari_karyawan_dashboard").val() || "").toLowerCase().trim();
                var statusFilter = $("#filter_status_dashboard").val() || "";
                var visibleCount = 0;

                $("#tabelPresensiDashboard tbody tr.row-presensi-item").each(function() {
                    var row = $(this);
                    var textMatch = keyword === "" || (row.attr("data-search") || "").indexOf(keyword) !== -1;
                    var statusMatch = true;

                    if (statusFilter === "hadir") {
                        statusMatch = row.attr("data-status") === "hadir";
                    } else if (statusFilter === "terlambat") {
                        statusMatch = row.attr("data-terlambat") === "1";
                    } else if (statusFilter === "disiplin") {
                        statusMatch = row.attr("data-disiplin") === "1";
                    } else if (statusFilter === "izin") {
                        statusMatch = row.attr("data-status") === "izin";
                    } else if (statusFilter === "belum") {
                        statusMatch = row.attr("data-status") === "belum";
                    }

                    if (textMatch && statusMatch) {
                        row.show();
                        visibleCount++;
                    } else {
                        row.hide();
                    }
                });

                $("#badge-count-shown").text("Menampilkan " + visibleCount + " Karyawan");
            }

            $("#cari_karyawan_dashboard").on("keyup input", function() {
                filterDashboardRows();
            });

            $("#filter_status_dashboard").on("change", function() {
                filterDashboardRows();
            });

            $(document).on("click", ".tampilkanpeta", function(e) {
                e.preventDefault();
                var id = $(this).attr("id");
                $.ajax({
                    type: 'POST',
                    url: '{{ url('/tampilkanpeta') }}',
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id
                    },
                    cache: false,
                    success: function(respond) {
                        $("#loadmap").html(respond);
                    }
                });
                $("#modal-tampilkanpeta").modal("show");
            });

            $(document).on("click", ".koreksipresensi", function(e) {
                e.preventDefault();
                var nik = $(this).attr("nik");
                var tanggal = "{{ $hariini ?? date('Y-m-d') }}";
                $.ajax({
                    type: 'POST',
                    url: '{{ url('/koreksipresensi') }}',
                    data: {
                        _token: "{{ csrf_token() }}",
                        nik: nik,
                        tanggal: tanggal
                    },
                    cache: false,
                    success: function(respond) {
                        $("#loadkoreksipresensi").html(respond);
                    }
                });
                $("#modal-koreksipresensi").modal("show");
            });
        });
    </script>
@endpush
