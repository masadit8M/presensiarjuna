@extends('layouts.presensi')
@section('header')
    <!-- App Header -->
    <div class="appHeader bg-primary text-light">
        <div class="left">
            <a href="/presensi/pengumuman" class="headerButton">
                <ion-icon name="chevron-back-outline"></ion-icon>
            </a>
        </div>
        <div class="pageTitle">Detail Pengumuman</div>
        <div class="right"></div>
    </div>
@endsection

@section('content')
    <div class="section" style="margin-top: 70px; padding-bottom: 85px;">
        <div class="card" style="border-radius: 14px; border: 1px solid #e2e8f0;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge badge-primary text-uppercase">{{ $item->kategori }}</span>
                    <span class="text-muted" style="font-size: 0.76rem;">
                        <ion-icon name="calendar-outline" style="vertical-align: middle;"></ion-icon>
                        {{ date('d M Y', strtotime($item->tanggal_tayang)) }}
                    </span>
                </div>

                <h3 style="font-size: 1.1rem; font-weight: 700; color: #0f172a;" class="mb-2">
                    {{ $item->judul }}
                </h3>

                <hr class="my-2">

                <div style="font-size: 0.88rem; color: #334155; line-height: 1.65; white-space: pre-line;">{{ $item->isi }}</div>

                @if (!empty($item->lampiran))
                    <div class="mt-3 pt-3" style="border-top: 1px dashed #cbd5e1;">
                        <a href="/uploads/pengumuman/{{ $item->lampiran }}" target="_blank" class="btn btn-outline-primary btn-block" style="border-radius: 10px;">
                            <ion-icon name="attach-outline" style="vertical-align: middle;"></ion-icon>
                            Lihat / Unduh Lampiran Pengumuman
                        </a>
                    </div>
                @endif

                <div class="mt-3 pt-2 text-right" style="border-top: 1px solid #f1f5f9; font-size: 0.74rem; color: #16a34a;">
                    <ion-icon name="checkmark-done-circle" style="vertical-align: middle;"></ion-icon>
                    Tercatat sudah dibaca oleh {{ $user->nama_lengkap }}
                </div>
            </div>
        </div>
    </div>
@endsection
