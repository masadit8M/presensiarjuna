@extends('layouts.presensi')
@section('header')
    <!-- App Header -->
    <div class="appHeader bg-primary text-light">
        <div class="left">
            <a href="/dashboard" class="headerButton">
                <ion-icon name="chevron-back-outline"></ion-icon>
            </a>
        </div>
        <div class="pageTitle">Slip Gaji Saya</div>
        <div class="right">
            <a href="/presensi/pinjaman" class="headerButton text-light" title="Info Kasbon">
                <ion-icon name="wallet-outline"></ion-icon>
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="section" style="margin-top: 70px; padding-bottom: 85px;">
        @if (Session::get('success'))
            <div class="alert alert-success mb-2">{{ Session::get('success') }}</div>
        @endif
        @if (Session::get('warning'))
            <div class="alert alert-warning mb-2">{{ Session::get('warning') }}</div>
        @endif

        <!-- Banner Ringkasan Pegawai & Shortcut Kasbon -->
        <div class="card mb-2" style="border-radius: 14px; background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%); color: #fff; border: none; box-shadow: 0 4px 12px rgba(37,99,235,0.22);">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div style="font-size: 0.75rem; opacity: 0.85;">GURU / KARYAWAN</div>
                        <div style="font-size: 1.05rem; font-weight: 700;">{{ $user->nama_lengkap }}</div>
                        <div style="font-size: 0.78rem; opacity: 0.9;">{{ $user->jabatan }} &bull; NIK: {{ $user->nik }}</div>
                    </div>
                    <div class="text-right">
                        <a href="/presensi/pinjaman" class="btn btn-sm btn-light" style="border-radius: 10px; font-weight: 600; color: #1e3a8a;">
                            <ion-icon name="wallet-outline" style="vertical-align: middle;"></ion-icon>
                            Info Kasbon
                            @if ($activeLoansCount > 0)
                                <span class="badge badge-danger ml-1">{{ $activeLoansCount }}</span>
                            @endif
                        </a>
                    </div>
                </div>
                @if ($totalSisaPinjaman > 0)
                    <div class="mt-2 pt-2" style="border-top: 1px solid rgba(255,255,255,0.2); font-size: 0.8rem; display: flex; justify-content: space-between;">
                        <span>Sisa Pinjaman / Kasbon Aktif:</span>
                        <strong>Rp {{ number_format($totalSisaPinjaman, 0, ',', '.') }}</strong>
                    </div>
                @endif
            </div>
        </div>

        @if ($slips->isEmpty())
            <div class="card" style="border-radius: 14px;">
                <div class="card-body text-center py-5">
                    <ion-icon name="document-text-outline" style="font-size: 48px; color: #94a3b8;"></ion-icon>
                    <h4 class="mt-2 mb-1" style="font-weight: 600; color: #334155;">Belum Ada Slip Gaji</h4>
                    <p class="text-muted mb-0" style="font-size: 0.85rem;">
                        Data slip gaji Anda akan tampil otomatis di sini setelah periode penggajian diterbitkan oleh Bendahara.
                    </p>
                </div>
            </div>
        @else
            @foreach ($slips as $slip)
                <div class="card mb-2" style="border-radius: 14px; border: 1px solid #e2e8f0; box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="badge badge-primary mb-1" style="font-size: 0.72rem;">
                                    <ion-icon name="calendar-outline" style="vertical-align: middle;"></ion-icon>
                                    {{ $slip->nama_periode }}
                                </span>
                                <div style="font-size: 0.76rem; color: #64748b;">
                                    Periode: {{ date('d/m/Y', strtotime($slip->tgl_mulai)) }} - {{ date('d/m/Y', strtotime($slip->tgl_selesai)) }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div style="font-size: 0.7rem; color: #64748b;">GAJI BERSIH (THP)</div>
                                <div style="font-size: 1.05rem; font-weight: 800; color: #16a34a;">
                                    Rp {{ number_format($slip->gaji_bersih, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>

                        <div class="row text-center py-2 my-2" style="background: #f8fafc; border-radius: 10px; margin-left: 0; margin-right: 0; font-size: 0.76rem;">
                            <div class="col-3 px-1">
                                <div class="text-muted">Hadir</div>
                                <strong class="text-success">{{ $slip->hadir }} hr</strong>
                            </div>
                            <div class="col-3 px-1" style="border-left: 1px solid #e2e8f0;">
                                <div class="text-muted">Izin/Sakit</div>
                                <strong class="text-info">{{ ($slip->izin ?? 0) + ($slip->sakit ?? 0) }} hr</strong>
                            </div>
                            <div class="col-3 px-1" style="border-left: 1px solid #e2e8f0;">
                                <div class="text-muted">Penghasilan</div>
                                <strong class="text-dark">Rp {{ number_format($slip->total_penghasilan, 0, ',', '.') }}</strong>
                            </div>
                            <div class="col-3 px-1" style="border-left: 1px solid #e2e8f0;">
                                <div class="text-muted">Potongan</div>
                                <strong class="text-danger">Rp {{ number_format($slip->total_potongan, 0, ',', '.') }}</strong>
                            </div>
                        </div>

                        @if (($slip->potongan_kasbon ?? 0) > 0)
                            <div class="mb-2" style="font-size: 0.75rem; color: #b45309; background: #fffbeb; padding: 5px 10px; border-radius: 8px;">
                                <ion-icon name="information-circle-outline" style="vertical-align: middle;"></ion-icon>
                                Termasuk Potongan Kasbon: <strong>Rp {{ number_format($slip->potongan_kasbon, 0, ',', '.') }}</strong>
                                @if (!empty($slip->ket_potongan_kasbon))
                                    ({{ $slip->ket_potongan_kasbon }})
                                @endif
                            </div>
                        @endif

                        <div class="d-flex" style="gap: 8px;">
                            <a href="/presensi/slipgaji/{{ $slip->id }}/cetak" target="_blank" class="btn btn-outline-primary btn-sm flex-fill" style="border-radius: 9px; font-weight: 600;">
                                <ion-icon name="eye-outline" style="vertical-align: middle;"></ion-icon> Lihat Rincian
                            </a>
                            <a href="/presensi/slipgaji/{{ $slip->id }}/download" class="btn btn-success btn-sm flex-fill" style="border-radius: 9px; font-weight: 600;">
                                <ion-icon name="download-outline" style="vertical-align: middle;"></ion-icon> Unduh PDF
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
@endsection
