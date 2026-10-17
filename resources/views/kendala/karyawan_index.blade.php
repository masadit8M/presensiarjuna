@extends('layouts.presensi')
@section('header')
    <!-- App Header -->
    <div class="appHeader bg-primary text-light">
        <div class="left">
            <a href="/dashboard" class="headerButton">
                <ion-icon name="chevron-back-outline"></ion-icon>
            </a>
        </div>
        <div class="pageTitle">Lapor Kendala Absen</div>
        <div class="right">
            <a href="/presensi/kendala/create" class="headerButton text-light">
                <ion-icon name="add-circle-outline"></ion-icon>
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="section" style="margin-top: 70px; padding-bottom: 90px;">
        @if (Session::get('success'))
            <div class="alert alert-success mb-2">{{ Session::get('success') }}</div>
        @endif
        @if (Session::get('warning'))
            <div class="alert alert-warning mb-2">{{ Session::get('warning') }}</div>
        @endif

        <div class="mb-2">
            <a href="/presensi/kendala/create" class="btn btn-primary btn-block" style="border-radius: 12px; font-weight: 600;">
                <ion-icon name="add-circle-outline" style="vertical-align: middle;"></ion-icon>
                Buat Laporan Kendala Baru (Lupa Absen / Error)
            </a>
        </div>

        @if ($tiket->isEmpty())
            <div class="card" style="border-radius: 14px;">
                <div class="card-body text-center py-5">
                    <ion-icon name="help-buoy-outline" style="font-size: 48px; color: #94a3b8;"></ion-icon>
                    <h4 class="mt-2 mb-1" style="font-weight: 600; color: #334155;">Belum Ada Laporan Kendala</h4>
                    <p class="text-muted mb-0" style="font-size: 0.82rem;">
                        Jika Anda mengalami kendala saat presensi (misal: lupa absen pulang, GPS tidak akurat, atau kamera gagal memuat), silakan kirim laporan di sini agar dikoreksi Admin.
                    </p>
                </div>
            </div>
        @else
            @foreach ($tiket as $item)
                @php
                    $statusBadge = match($item->status) {
                        'selesai' => 'badge-success',
                        'ditolak' => 'badge-danger',
                        'diproses' => 'badge-info',
                        default => 'badge-warning',
                    };
                @endphp
                <div class="card mb-2" style="border-radius: 14px; border: 1px solid #e2e8f0;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span style="font-size: 0.78rem; font-weight: 700; color: #475569;">{{ $item->nomor_tiket }}</span>
                            <span class="badge {{ $statusBadge }} text-uppercase">{{ $item->status }}</span>
                        </div>

                        <h4 style="font-size: 0.95rem; font-weight: 700; color: #1e293b;" class="mb-1">
                            {{ $item->judul }}
                        </h4>

                        <div class="text-muted mb-2" style="font-size: 0.76rem;">
                            <ion-icon name="calendar-outline" style="vertical-align: middle;"></ion-icon>
                            Tgl Kejadian: <strong>{{ date('d/m/Y', strtotime($item->tanggal_kejadian)) }}</strong>
                            @if (!empty($item->jam_kejadian))
                                &bull; Jam: {{ substr($item->jam_kejadian, 0, 5) }}
                            @endif
                        </div>

                        <div class="p-2 mb-2" style="background: #f8fafc; border-radius: 8px; font-size: 0.8rem; color: #334155;">
                            {{ $item->deskripsi }}
                        </div>

                        @if (!empty($item->foto_bukti))
                            <div class="mb-2">
                                <a href="/uploads/kendala/{{ $item->foto_bukti }}" target="_blank" class="btn btn-xs btn-outline-secondary" style="font-size: 0.73rem; border-radius: 8px;">
                                    <ion-icon name="image-outline" style="vertical-align: middle;"></ion-icon> Lihat Foto Bukti
                                </a>
                            </div>
                        @endif

                        @if (!empty($item->catatan_admin) || !empty($item->tindakan_admin))
                            <div class="mt-2 pt-2" style="border-top: 1px dashed #cbd5e1; font-size: 0.78rem;">
                                <div class="font-weight-bold text-primary">Tanggapan Admin:</div>
                                @if (!empty($item->tindakan_admin))
                                    <div>Tindakan: <strong>{{ ucwords(str_replace('_', ' ', $item->tindakan_admin)) }}</strong></div>
                                @endif
                                @if (!empty($item->catatan_admin))
                                    <div class="text-dark">{{ $item->catatan_admin }}</div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif
    </div>
@endsection
