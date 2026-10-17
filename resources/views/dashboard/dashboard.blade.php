@extends('layouts.presensi')
@section('content')
    <style>
        .logout {
            position: absolute;
            color: white;
            font-size: 30px;
            text-decoration: none;
            right: 8px;
        }

        .logout:hover {
            color: white;

        }
    </style>
    <div class="section" id="user-section">
        <a href="/proseslogout" class="logout">
            <ion-icon name="exit-outline"></ion-icon>
        </a>
        <div id="user-detail">
            <div class="avatar">
                @if (!empty(Auth::guard('karyawan')->user()->foto))
                    @php
                        $path = Storage::url('uploads/karyawan/' . Auth::guard('karyawan')->user()->foto);
                    @endphp
                    <img src="{{ url($path) }}" alt="avatar" class="imaged w64" style="height:60px">
                @else
                    <img src="assets/img/sample/avatar/avatar1.jpg" alt="avatar" class="imaged w64 rounded">
                @endif
            </div>
            <div id="user-info">
                <h3 id="user-name">{{ Auth::guard('karyawan')->user()->nama_lengkap }}</h3>
                <span id="user-role">{{ Auth::guard('karyawan')->user()->jabatan }}</span>
                <span id="user-role">({{ $cabang->nama_cabang }})</span>
                <p style="margin-top: 15px">
                    <span id="user-role">({{ $departemen->nama_dept }})</span>
                </p>
            </div>
        </div>
    </div>

    <div class="section" id="menu-section">
        <div class="card">
            <div class="card-body text-center">
                <div class="list-menu">
                    <div class="item-menu text-center">
                        <div class="menu-icon">
                            <a href="/editprofile" class="green" style="font-size: 40px;">
                                <ion-icon name="person-sharp"></ion-icon>
                            </a>
                        </div>
                        <div class="menu-name">
                            <span class="text-center">Profil</span>
                        </div>
                    </div>
                    <div class="item-menu text-center">
                        <div class="menu-icon">
                            <a href="/presensi/izin" class="danger" style="font-size: 40px;">
                                <ion-icon name="calendar-number"></ion-icon>
                            </a>
                        </div>
                        <div class="menu-name">
                            <span class="text-center">Cuti</span>
                        </div>
                    </div>
                    <div class="item-menu text-center">
                        <div class="menu-icon">
                            <a href="/presensi/histori" class="warning" style="font-size: 40px;">
                                <ion-icon name="document-text"></ion-icon>
                            </a>
                        </div>
                        <div class="menu-name">
                            <span class="text-center">Histori</span>
                        </div>
                    </div>
                    <div class="item-menu text-center">
                        <div class="menu-icon">
                            @php
                                $lokCabang = !empty($cabang->lokasi_cabang) ? 'https://www.google.com/maps?q=' . urlencode($cabang->lokasi_cabang) : url('/presensi/create');
                            @endphp
                            <a href="{{ $lokCabang }}" target="_blank" class="orange" style="font-size: 40px;" title="Lokasi Cabang {{ $cabang->nama_cabang ?? '' }} (Radius {{ $cabang->radius_cabang ?? 0 }}m)">
                                <ion-icon name="location"></ion-icon>
                            </a>
                        </div>
                        <div class="menu-name">
                            Lokasi
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="section mt-2" id="presence-section">
        <div class="todaypresence">
            <div class="row">

                <div class="col-6">
                    <div class="card gradasigreen">
                        <div class="card-body">
                            <div class="presencecontent">
                                <div class="iconpresence">
                                    @if ($presensihariini != null)
                                        @if ($presensihariini->foto_in != null)
                                            @php
                                                $path = Storage::url('uploads/absensi/' . $presensihariini->foto_in);
                                            @endphp
                                            <img src="{{ url($path) }}" alt="" class="imaged w48">
                                        @else
                                            <ion-icon name="camera"></ion-icon>
                                        @endif
                                    @else
                                        <ion-icon name="camera"></ion-icon>
                                    @endif
                                </div>
                                <div class="presencedetail">
                                    <h4 class="presencetitle">Masuk</h4>
                                    <span>{{ $presensihariini != null ? $presensihariini->jam_in : 'Belum Absen' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card gradasired">
                        <div class="card-body">
                            <div class="presencecontent">
                                <div class="iconpresence">
                                    @if ($presensihariini != null && $presensihariini->jam_out != null)
                                        @if ($presensihariini->foto_out != null)
                                            @php
                                                $path = Storage::url('uploads/absensi/' . $presensihariini->foto_out);
                                            @endphp
                                            <img src="{{ url($path) }}" alt="" class="imaged w48">
                                        @else
                                            <ion-icon name="camera"></ion-icon>
                                        @endif
                                    @else
                                        <ion-icon name="camera"></ion-icon>
                                    @endif
                                </div>
                                <div class="presencedetail">
                                    <h4 class="presencetitle">Pulang</h4>
                                    <span>{{ $presensihariini != null && $presensihariini->jam_out != null ? $presensihariini->jam_out : 'Belum Absen' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Layanan Mandiri Guru & Karyawan (Integrasi Fitur Aku Hadir) -->
        <div class="card mt-2 mb-2" style="border-radius: 14px; border: 1px solid rgba(37,99,235,0.15); box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
            <div class="card-body py-2 px-2">
                <div class="row text-center">
                    <div class="col-3 px-1">
                        <a href="/presensi/slipgaji" style="text-decoration: none; color: #1e293b; display: block; padding: 6px 2px;">
                            <div style="width: 42px; height: 42px; margin: 0 auto 4px; border-radius: 12px; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                                <ion-icon name="receipt-outline"></ion-icon>
                            </div>
                            <span style="font-size: 0.72rem; font-weight: 600; display: block; line-height: 1.1;">Slip Gaji</span>
                        </a>
                    </div>
                    <div class="col-3 px-1">
                        <a href="/presensi/pinjaman" style="text-decoration: none; color: #1e293b; display: block; padding: 6px 2px;">
                            <div style="width: 42px; height: 42px; margin: 0 auto 4px; border-radius: 12px; background: #ccfbf1; color: #0d9488; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                                <ion-icon name="wallet-outline"></ion-icon>
                            </div>
                            <span style="font-size: 0.72rem; font-weight: 600; display: block; line-height: 1.1;">Kasbon</span>
                        </a>
                    </div>
                    <div class="col-3 px-1">
                        <a href="/presensi/pengumuman" style="text-decoration: none; color: #1e293b; display: block; padding: 6px 2px; position: relative;">
                            @if (isset($belumDibacaCount) && $belumDibacaCount > 0)
                                <span class="badge badge-danger" style="position: absolute; top: 2px; right: 12px; font-size: 0.6rem; z-index: 5;">{{ $belumDibacaCount }}</span>
                            @endif
                            <div style="width: 42px; height: 42px; margin: 0 auto 4px; border-radius: 12px; background: #dbeafe; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                                <ion-icon name="megaphone-outline"></ion-icon>
                            </div>
                            <span style="font-size: 0.72rem; font-weight: 600; display: block; line-height: 1.1;">Pengumuman</span>
                        </a>
                    </div>
                    <div class="col-3 px-1">
                        <a href="/presensi/kendala" style="text-decoration: none; color: #1e293b; display: block; padding: 6px 2px;">
                            <div style="width: 42px; height: 42px; margin: 0 auto 4px; border-radius: 12px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                                <ion-icon name="help-buoy-outline"></ion-icon>
                            </div>
                            <span style="font-size: 0.72rem; font-weight: 600; display: block; line-height: 1.1;">Lapor Absen</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if (isset($pengumumanTerbaru) && $pengumumanTerbaru->isNotEmpty())
            <div class="card mb-2" style="border-radius: 12px; border-left: 4px solid #2563eb; background: #eff6ff;">
                <div class="card-body py-2 px-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span style="font-size: 0.75rem; font-weight: 700; color: #1d4ed8;">
                            <ion-icon name="megaphone" style="vertical-align: middle;"></ion-icon> INFO YAYASAN TERBARU
                        </span>
                        <a href="/presensi/pengumuman" style="font-size: 0.72rem; font-weight: 600; color: #2563eb;">Semua &raquo;</a>
                    </div>
                    @foreach ($pengumumanTerbaru as $info)
                        <a href="/presensi/pengumuman/{{ $info->id }}" style="display: block; text-decoration: none; color: #1e293b; padding: 3px 0; border-top: {{ !$loop->first ? '1px dashed #bfdbfe' : 'none' }};">
                            <div style="font-size: 0.8rem; font-weight: 600;">{{ $info->judul }}</div>
                            <div style="font-size: 0.72rem; color: #475569;">{{ \Illuminate\Support\Str::limit(strip_tags($info->isi), 75) }}</div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div id="rekappresensi">
            <h3>Rekap Presensi Bulan {{ $namabulan[$bulanini] }} Tahun {{ $tahunini }}</h3>
            <div class="row">
                <div class="col-3">
                    <div class="card">
                        <div class="card-body text-center" style="padding: 12px 12px !important; line-height:0.8rem">
                            <span class="badge bg-danger"
                                style="position: absolute; top:3px; right:10px; font-size:0.6rem; z-index:999">{{ $rekappresensi->jmlhadir }}</span>
                            <ion-icon name="accessibility-outline" style="font-size: 1.6rem;"
                                class="text-primary mb-1"></ion-icon>
                            <br>
                            <span style="font-size: 0.8rem; font-weight:500">Hadir</span>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="card">
                        <div class="card-body text-center" style="padding: 12px 12px !important; line-height:0.8rem">
                            <span class="badge bg-danger"
                                style="position: absolute; top:3px; right:10px; font-size:0.6rem; z-index:999">
                                {{ $rekappresensi->jmlizin }}
                            </span>
                            <ion-icon name="newspaper-outline" style="font-size: 1.6rem;"
                                class="text-success mb-1"></ion-icon>
                            <br>
                            <span style="font-size: 0.8rem; font-weight:500">Izin</span>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="card">
                        <div class="card-body text-center" style="padding: 12px 12px !important; line-height:0.8rem">
                            <span class="badge bg-danger"
                                style="position: absolute; top:3px; right:10px; font-size:0.6rem; z-index:999">
                                {{ $rekappresensi->jmlsakit }}</span>
                            <ion-icon name="medkit-outline" style="font-size: 1.6rem;"
                                class="text-warning mb-1"></ion-icon>
                            <br>
                            <span style="font-size: 0.8rem; font-weight:500">Sakit</span>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="card">
                        <div class="card-body text-center" style="padding: 12px 12px !important; line-height:0.8rem">
                            <span class="badge bg-danger"
                                style="position: absolute; top:3px; right:10px; font-size:0.6rem; z-index:999">
                                {{ $rekappresensi->jmlcuti }}
                            </span>
                            <ion-icon name="document-outline" style="font-size: 1.6rem;"
                                class="text-danger mb-1"></ion-icon>
                            <br>
                            <span style="font-size: 0.8rem; font-weight:500">Cuti</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="presencetab mt-2">
            <div class="tab-pane fade show active" id="pilled" role="tabpanel">
                <ul class="nav nav-tabs style1" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#home" role="tab">
                            Bulan Ini
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#profile" role="tab">
                            Leaderboard
                        </a>
                    </li>
                </ul>
            </div>
            <div class="tab-content mt-2" style="margin-bottom:100px;">
                <div class="tab-pane fade show active" id="home" role="tabpanel">
                    <!--
                                                                                                                                                                                                                    <ul class="listview image-listview">
                                                                                                                                                                                                                        @foreach ($historibulanini as $d)
    @php
        $path = Storage::url('uploads/absensi/' . $d->foto_in);
    @endphp
                                                                                                                                                                                                                        <li>
                                                                                                                                                                                                                            <div class="item">
                                                                                                                                                                                                                                <div class="icon-box bg-primary">
                                                                                                                                                                                                                                    <ion-icon name="finger-print-outline"></ion-icon>
                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                <div class="in">
                                                                                                                                                                                                                                    <div>{{ date('d-m-Y', strtotime($d->tgl_presensi)) }}</div>
                                                                                                                                                                                                                                    <span class="badge badge-success">{{ $d->jam_in }}</span>
                                                                                                                                                                                                                                    <span class="badge badge-danger">{{ $presensihariini != null && $d->jam_out != null ? $d->jam_out : 'Belum Absen' }}</span>
                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                        </li>
    @endforeach
                                                                                                                                                                                                                    </ul>
                                                                                                                                                                                                                -->

                    @foreach ($historibulanini as $d)
                        @if ($d->status == 'h')
                            <div class="card mb-1" style="border : 1px solid blue">
                                <div class="card-body">
                                    <div class="historicontent">
                                        <div class="iconpresensi">
                                            <ion-icon name="finger-print-outline" style="font-size: 48px;"
                                                class="text-success"></ion-icon>
                                        </div>
                                        <div class="datapresensi">
                                            <h3 style="line-height: 3px">{{ $d->nama_jam_kerja }}</h3>
                                            <h4 style="margin:0px !important">
                                                {{ date('d-m-Y', strtotime($d->tgl_presensi)) }}</h4>
                                            <span style="color:green">{{ date('H:i', strtotime($d->jam_masuk)) }} -
                                                {{ date('H:i', strtotime($d->jam_pulang)) }}</span>
                                            <br>
                                            <span>
                                                {!! $d->jam_in != null ? date('H:i', strtotime($d->jam_in)) : '<span class="text-danger">Belum Scan</span>' !!}
                                            </span>
                                            <span>
                                                {!! $d->jam_out != null
                                                    ? '-' . date('H:i', strtotime($d->jam_out))
                                                    : '<span class="text-danger">- Belum Scan</span>' !!}
                                            </span>
                                            <br>
                                            @php
                                                //Jam Ketika dia Absen
                                                $jam_in = date('H:i', strtotime($d->jam_in));

                                                //Jam Jadwal Masuk
                                                $jam_masuk = date('H:i', strtotime($d->jam_masuk));

                                                $jadwal_jam_masuk = $d->tgl_presensi . ' ' . $jam_masuk;
                                                $jam_presensi = $d->tgl_presensi . ' ' . $jam_in;
                                            @endphp
                                            @if ($jam_in > $jam_masuk)
                                                @php
                                                    $jmlterlambat = hitungjamterlambat(
                                                        $jadwal_jam_masuk,
                                                        $jam_presensi,
                                                    );
                                                    $jmlterlambatdesimal = hitungjamterlambatdesimal(
                                                        $jadwal_jam_masuk,
                                                        $jam_presensi,
                                                    );
                                                @endphp
                                                <span class="danger">Terlambat {{ $jmlterlambat }}
                                                    ({{ $jmlterlambatdesimal }} Jam)
                                                </span>
                                            @else
                                                <span style="color:green">Tepat Waktu</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif($d->status == 'i')
                            <div class="card mb-1">
                                <div class="card-body">
                                    <div class="historicontent">
                                        <div class="iconpresensi">
                                            <ion-icon name="document-outline" style="font-size: 48px;"
                                                class="text-warning"></ion-icon>
                                        </div>
                                        <div class="datapresensi">
                                            <h3 style="line-height: 3px">IZIN - {{ $d->kode_izin }}</h3>
                                            <h4 style="margin:0px !important">
                                                {{ date('d-m-Y', strtotime($d->tgl_presensi)) }}</h4>
                                            <span>
                                                {{ $d->keterangan }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif($d->status == 's')
                            <div class="card mb-1">
                                <div class="card-body">
                                    <div class="historicontent">
                                        <div class="iconpresensi">
                                            <ion-icon name="medkit-outline" style="font-size: 48px;"
                                                class="text-primary"></ion-icon>
                                        </div>
                                        <div class="datapresensi">
                                            <h3 style="line-height: 3px">SAKIT - {{ $d->kode_izin }}</h3>
                                            <h4 style="margin:0px !important">
                                                {{ date('d-m-Y', strtotime($d->tgl_presensi)) }}</h4>
                                            <span>
                                                {{ $d->keterangan }}
                                            </span>
                                            <br>
                                            @if (!empty($d->doc_sid))
                                                <span style="color: blue">
                                                    <ion-icon name="document-attach-outline"></ion-icon> SID
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif($d->status == 'c')
                            <div class="card mb-1">
                                <div class="card-body">
                                    <div class="historicontent">
                                        <div class="iconpresensi">
                                            <ion-icon name="document-outline" style="font-size: 48px;"
                                                class="text-info"></ion-icon>
                                        </div>
                                        <div class="datapresensi">
                                            <h3 style="line-height: 3px">CUTI - {{ $d->kode_izin }}</h3>
                                            <h4 style="margin:0px !important">
                                                {{ date('d-m-Y', strtotime($d->tgl_presensi)) }}</h4>
                                            <span class="text-info">
                                                {{ $d->nama_cuti }}
                                            </span>
                                            <br>
                                            <span>
                                                {{ $d->keterangan }}
                                            </span>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
                <div class="tab-pane fade" id="profile" role="tabpanel">
                    <ul class="listview image-listview">
                        @foreach ($leaderboard as $d)
                            <li>
                                <div class="item">
                                    <img src="assets/img/sample/avatar/avatar1.jpg" alt="image" class="image">
                                    <div class="in">
                                        <div>
                                            <b>{{ $d->nama_lengkap }}</b><br>
                                            <small class="text-muted">{{ $d->jabatan }}</small>
                                        </div>
                                        <span class="badge {{ $d->jam_in < '07:00' ? 'bg-success' : 'bg-danger' }}">
                                            {{ $d->jam_in }}
                                        </span>
                                    </div>
                                </div>
                            </li>
                        @endforeach

                    </ul>
                </div>

            </div>
        </div>
    </div>
@endsection
