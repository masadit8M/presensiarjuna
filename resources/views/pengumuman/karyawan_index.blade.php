@extends('layouts.presensi')
@section('header')
    <!-- App Header -->
    <div class="appHeader bg-primary text-light">
        <div class="left">
            <a href="/dashboard" class="headerButton">
                <ion-icon name="chevron-back-outline"></ion-icon>
            </a>
        </div>
        <div class="pageTitle">Papan Pengumuman Yayasan</div>
        <div class="right"></div>
    </div>
@endsection

@section('content')
    <div class="section" style="margin-top: 70px; padding-bottom: 85px;">
        @if (Session::get('warning'))
            <div class="alert alert-warning mb-2">{{ Session::get('warning') }}</div>
        @endif

        @if ($pengumuman->isEmpty())
            <div class="card" style="border-radius: 14px;">
                <div class="card-body text-center py-5">
                    <ion-icon name="megaphone-outline" style="font-size: 48px; color: #94a3b8;"></ion-icon>
                    <h4 class="mt-2 mb-1" style="font-weight: 600; color: #334155;">Belum Ada Pengumuman</h4>
                    <p class="text-muted mb-0" style="font-size: 0.85rem;">
                        Saat ini belum ada pengumuman aktif dari Yayasan / Admin.
                    </p>
                </div>
            </div>
        @else
            @foreach ($pengumuman as $item)
                @php
                    $sudahBaca = in_array($item->id, $dibacaIds);
                    $badgeColor = match($item->kategori) {
                        'penting', 'darurat' => 'badge-danger',
                        'kebijakan' => 'badge-warning',
                        'agenda' => 'badge-info',
                        default => 'badge-primary',
                    };
                @endphp
                <a href="/presensi/pengumuman/{{ $item->id }}" style="text-decoration: none; color: inherit;">
                    <div class="card mb-2" style="border-radius: 14px; border-left: 4px solid {{ $sudahBaca ? '#cbd5e1' : '#2563eb' }}; box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div>
                                    <span class="badge {{ $badgeColor }} text-uppercase" style="font-size: 0.68rem;">
                                        {{ $item->kategori }}
                                    </span>
                                    @if ($item->prioritas === 'tinggi')
                                        <span class="badge badge-danger" style="font-size: 0.68rem;">Prioritas Tinggi</span>
                                    @endif
                                </div>
                                @if (!$sudahBaca)
                                    <span class="badge badge-primary" style="font-size: 0.68rem;">BARU</span>
                                @else
                                    <span class="text-muted" style="font-size: 0.7rem;">
                                        <ion-icon name="checkmark-done-outline" style="vertical-align: middle; color: #16a34a;"></ion-icon> Dibaca
                                    </span>
                                @endif
                            </div>

                            <h4 class="mb-1" style="font-size: 0.95rem; font-weight: {{ $sudahBaca ? '600' : '700' }}; color: #1e293b;">
                                {{ $item->judul }}
                            </h4>

                            <p class="text-muted mb-2" style="font-size: 0.8rem; line-height: 1.4;">
                                {{ \Illuminate\Support\Str::limit(strip_tags($item->isi), 110) }}
                            </p>

                            <div class="d-flex justify-content-between align-items-center text-muted" style="font-size: 0.73rem;">
                                <span>
                                    <ion-icon name="calendar-outline" style="vertical-align: middle;"></ion-icon>
                                    {{ date('d M Y', strtotime($item->tanggal_tayang)) }}
                                </span>
                                <span class="text-primary font-weight-bold">
                                    Baca Selengkapnya <ion-icon name="chevron-forward-outline" style="vertical-align: middle;"></ion-icon>
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        @endif
    </div>
@endsection
