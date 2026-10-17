@extends('layouts.presensi')
@section('header')
    <!-- App Header -->
    <div class="appHeader bg-primary text-light">
        <div class="left">
            <a href="/dashboard" class="headerButton">
                <ion-icon name="chevron-back-outline"></ion-icon>
            </a>
        </div>
        <div class="pageTitle">Pinjaman & Cicilan Kasbon</div>
        <div class="right">
            <a href="/presensi/slipgaji" class="headerButton text-light" title="Slip Gaji">
                <ion-icon name="receipt-outline"></ion-icon>
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="section" style="margin-top: 70px; padding-bottom: 85px;">
        @php
            $aktifLoans = $pinjaman->where('status', 'berjalan');
            $totalPinjamanAktif = $aktifLoans->sum('jumlah_pinjaman');
            $totalSisa = $aktifLoans->sum('sisa_pinjaman');
            $totalTerbayar = $aktifLoans->sum('total_dibayar');
        @endphp

        <!-- Summary Card -->
        <div class="card mb-2" style="border-radius: 14px; background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%); color: #fff; border: none;">
            <div class="card-body p-3">
                <div style="font-size: 0.75rem; opacity: 0.85;">RINGKASAN KASBON AKTIF</div>
                <div class="d-flex justify-content-between align-items-baseline mt-1">
                    <div>
                        <div style="font-size: 0.75rem; opacity: 0.9;">Sisa Pinjaman</div>
                        <div style="font-size: 1.25rem; font-weight: 800;">Rp {{ number_format($totalSisa, 0, ',', '.') }}</div>
                    </div>
                    <div class="text-right">
                        <div style="font-size: 0.75rem; opacity: 0.9;">Sudah Dicicil</div>
                        <div style="font-size: 0.95rem; font-weight: 700;">Rp {{ number_format($totalTerbayar, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if ($pinjaman->isEmpty())
            <div class="card" style="border-radius: 14px;">
                <div class="card-body text-center py-5">
                    <ion-icon name="checkmark-Done-circle-outline" style="font-size: 48px; color: #10b981;"></ion-icon>
                    <h4 class="mt-2 mb-1" style="font-weight: 600; color: #334155;">Tidak Ada Catatan Pinjaman</h4>
                    <p class="text-muted mb-0" style="font-size: 0.85rem;">
                        Anda tidak memiliki pinjaman/kasbon aktif saat ini.
                    </p>
                </div>
            </div>
        @else
            @foreach ($pinjaman as $item)
                @php
                    $persen = $item->jumlah_pinjaman > 0
                        ? min(100, round(($item->total_dibayar / $item->jumlah_pinjaman) * 100))
                        : 100;
                    $riwayat = $angsuranByPinjaman[$item->id] ?? [];
                @endphp
                <div class="card mb-2" style="border-radius: 14px; border: 1px solid #e2e8f0;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span style="font-weight: 700; color: #1e293b; font-size: 0.9rem;">{{ $item->nomor_pinjaman }}</span>
                            @if ($item->status === 'berjalan')
                                <span class="badge badge-warning">Berjalan</span>
                            @elseif ($item->status === 'lunas')
                                <span class="badge badge-success">Lunas</span>
                            @else
                                <span class="badge badge-secondary">{{ ucfirst($item->status) }}</span>
                            @endif
                        </div>

                        <div style="font-size: 0.76rem; color: #64748b;" class="mb-2">
                            Tanggal Pinjam: {{ date('d/m/Y', strtotime($item->tanggal_pinjaman)) }} &bull; Tenor: {{ $item->tenor_bulan }} Bulan
                        </div>

                        <div class="row text-center py-2 mb-2" style="background: #f8fafc; border-radius: 10px; margin: 0; font-size: 0.76rem;">
                            <div class="col-4 px-1">
                                <div class="text-muted">Total Pinjam</div>
                                <strong class="text-dark">Rp {{ number_format($item->jumlah_pinjaman, 0, ',', '.') }}</strong>
                            </div>
                            <div class="col-4 px-1" style="border-left: 1px solid #e2e8f0;">
                                <div class="text-muted">Cicilan/Bln</div>
                                <strong class="text-primary">Rp {{ number_format($item->nominal_angsuran_per_bulan, 0, ',', '.') }}</strong>
                            </div>
                            <div class="col-4 px-1" style="border-left: 1px solid #e2e8f0;">
                                <div class="text-muted">Sisa</div>
                                <strong class="text-danger">Rp {{ number_format($item->sisa_pinjaman, 0, ',', '.') }}</strong>
                            </div>
                        </div>

                        <div class="progress mb-1" style="height: 8px; border-radius: 6px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $persen }}%;"></div>
                        </div>
                        <div class="d-flex justify-content-between text-muted mb-2" style="font-size: 0.72rem;">
                            <span>Terbayar: Rp {{ number_format($item->total_dibayar, 0, ',', '.') }} ({{ $persen }}%)</span>
                            <span>{{ count($riwayat) }} / {{ $item->tenor_bulan }}x Angsuran</span>
                        </div>

                        @if (!empty($item->keterangan))
                            <div class="mb-2" style="font-size: 0.76rem; color: #475569;">
                                <strong>Keterangan:</strong> {{ $item->keterangan }}
                            </div>
                        @endif

                        @if (!empty($riwayat))
                            <div class="mt-2 pt-2" style="border-top: 1px dashed #cbd5e1;">
                                <div style="font-size: 0.75rem; font-weight: 700; color: #334155;" class="mb-1">
                                    Riwayat Pembayaran Angsuran:
                                </div>
                                @foreach ($riwayat as $ang)
                                    <div class="d-flex justify-content-between align-items-center py-1" style="font-size: 0.75rem; border-bottom: 1px solid #f1f5f9;">
                                        <div>
                                            <span class="badge badge-success mr-1">Ke-{{ $ang->angsuran_ke }}</span>
                                            <span>{{ date('d/m/Y', strtotime($ang->tanggal_bayar)) }}</span>
                                            <span class="text-muted">({{ str_replace('_', ' ', $ang->metode_pembayaran) }})</span>
                                        </div>
                                        <strong class="text-success">Rp {{ number_format($ang->jumlah_bayar, 0, ',', '.') }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif
    </div>
@endsection
