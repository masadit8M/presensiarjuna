<?php

namespace App\Services;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FiturTambahanSchemaService
{
    private static bool $checked = false;

    /**
     * Auto-healing schema initializer for the 4 integrated Aku Hadir features.
     * Safe for live production even before `php artisan migrate` is executed.
     */
    public static function ensureTablesExist(): void
    {
        if (self::$checked) {
            return;
        }

        try {
            if (!Schema::hasTable('pengumuman')) {
                Schema::create('pengumuman', function (Blueprint $table) {
                    $table->id();
                    $table->string('judul', 180);
                    $table->text('isi');
                    $table->enum('kategori', ['umum', 'penting', 'kebijakan', 'agenda', 'darurat'])->default('umum');
                    $table->enum('prioritas', ['rendah', 'normal', 'tinggi'])->default('normal');
                    $table->string('lampiran', 255)->nullable();
                    $table->date('tanggal_tayang');
                    $table->date('tanggal_berakhir')->nullable();
                    $table->tinyInteger('status_aktif')->default(1);
                    $table->unsignedBigInteger('created_by')->nullable();
                    $table->timestamps();
                });
            }

            if (!Schema::hasTable('pengumuman_pegawai')) {
                Schema::create('pengumuman_pegawai', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('pengumuman_id')->index();
                    $table->string('nik', 30)->index();
                    $table->tinyInteger('is_dibaca')->default(0);
                    $table->dateTime('dibaca_pada')->nullable();
                    $table->timestamps();
                });
            }

            if (!Schema::hasTable('pinjaman_pegawai')) {
                Schema::create('pinjaman_pegawai', function (Blueprint $table) {
                    $table->id();
                    $table->string('nomor_pinjaman', 50)->unique();
                    $table->string('nik', 30)->index();
                    $table->date('tanggal_pinjaman');
                    $table->decimal('jumlah_pinjaman', 14, 2)->default(0);
                    $table->integer('tenor_bulan')->default(1);
                    $table->decimal('nominal_angsuran_per_bulan', 14, 2)->default(0);
                    $table->string('mulai_cicil_bulan', 7)->nullable(); // YYYY-MM
                    $table->tinyInteger('potong_gaji')->default(1);
                    $table->decimal('total_dibayar', 14, 2)->default(0);
                    $table->decimal('sisa_pinjaman', 14, 2)->default(0);
                    $table->enum('status', ['berjalan', 'lunas', 'dibatalkan'])->default('berjalan');
                    $table->text('keterangan')->nullable();
                    $table->unsignedBigInteger('created_by')->nullable();
                    $table->timestamps();
                });
            }

            if (!Schema::hasTable('pinjaman_angsuran')) {
                Schema::create('pinjaman_angsuran', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('pinjaman_pegawai_id')->index();
                    $table->string('nomor_transaksi', 50)->nullable();
                    $table->integer('angsuran_ke')->default(1);
                    $table->date('tanggal_bayar');
                    $table->decimal('jumlah_bayar', 14, 2)->default(0);
                    $table->enum('metode_pembayaran', ['potong_gaji', 'tunai', 'transfer'])->default('potong_gaji');
                    $table->unsignedBigInteger('periode_gaji_id')->nullable()->index();
                    $table->text('keterangan')->nullable();
                    $table->unsignedBigInteger('diterima_oleh')->nullable();
                    $table->timestamps();
                });
            }

            if (!Schema::hasTable('pengajuan_masalah')) {
                Schema::create('pengajuan_masalah', function (Blueprint $table) {
                    $table->id();
                    $table->string('nomor_tiket', 40)->unique();
                    $table->string('nik', 30)->index();
                    $table->enum('kategori_masalah', [
                        'lupa_absen_masuk',
                        'lupa_absen_pulang',
                        'aplikasi_error',
                        'gps_tidak_akurat',
                        'kamera_error',
                        'perubahan_jam_kerja',
                        'lainnya',
                    ])->default('lupa_absen_pulang');
                    $table->string('judul', 150);
                    $table->date('tanggal_kejadian');
                    $table->time('jam_kejadian')->nullable();
                    $table->text('deskripsi');
                    $table->string('foto_bukti', 255)->nullable();
                    $table->enum('status', ['diajukan', 'diproses', 'selesai', 'ditolak'])->default('diajukan');
                    $table->enum('tindakan_admin', [
                        'koreksi_absen_masuk',
                        'koreksi_absen_pulang',
                        'koreksi_penuh',
                        'tanpa_perubahan_data',
                    ])->nullable();
                    $table->text('catatan_admin')->nullable();
                    $table->unsignedBigInteger('diproses_oleh_user_id')->nullable();
                    $table->dateTime('tanggal_diproses')->nullable();
                    $table->timestamps();
                });
            }

            if (!Schema::hasTable('revisi_absensi')) {
                Schema::create('revisi_absensi', function (Blueprint $table) {
                    $table->id();
                    $table->string('nik', 30)->index();
                    $table->date('tgl_presensi')->index();
                    $table->enum('jenis_tindakan', ['insert_manual', 'update_data', 'tiket_kendala', 'hapus_data'])->default('update_data');
                    $table->unsignedBigInteger('user_id_eksekutor')->nullable();
                    $table->string('nama_eksekutor', 120)->nullable();
                    $table->text('alasan')->nullable();
                    $table->text('data_sebelumnya')->nullable();
                    $table->text('data_perubahan')->nullable();
                    $table->timestamps();
                });
            }

            self::$checked = true;
        } catch (\Throwable $e) {
            // Never crash live requests if schema check encounters transient lock
        }
    }

    /**
     * Resolve all NIKs belonging to the same employee (multi-branch consolidation by nama_lengkap).
     */
    public static function getAllNiksForKaryawan(?string $nik): array
    {
        if (empty($nik)) {
            return [];
        }

        try {
            $karyawan = DB::table('karyawan')->where('nik', $nik)->first();
            if ($karyawan && !empty($karyawan->nama_lengkap)) {
                $normName = trim(strtoupper($karyawan->nama_lengkap));
                $allNiks = DB::table('karyawan')
                    ->whereRaw('TRIM(UPPER(nama_lengkap)) = ?', [$normName])
                    ->pluck('nik')
                    ->toArray();
                if (!empty($allNiks)) {
                    return array_values(array_unique(array_merge([$nik], $allNiks)));
                }
            }
        } catch (\Throwable $e) {
        }

        return [$nik];
    }

    /**
     * Calculate active kasbon installment for an employee (across all their branch NIKs).
     */
    public static function getActiveKasbonInstallment(array $niks, ?string $periodeYyyyMm = null): array
    {
        self::ensureTablesExist();

        $result = [
            'nominal' => 0,
            'keterangan' => null,
            'loans' => [],
        ];

        if (empty($niks)) {
            return $result;
        }

        try {
            $loans = DB::table('pinjaman_pegawai')
                ->whereIn('nik', $niks)
                ->where('status', 'berjalan')
                ->where('potong_gaji', 1)
                ->where('sisa_pinjaman', '>', 0)
                ->orderBy('tanggal_pinjaman', 'asc')
                ->get();

            if ($loans->isEmpty()) {
                return $result;
            }

            $totalCicilan = 0;
            $ketParts = [];

            foreach ($loans as $loan) {
                if (!empty($periodeYyyyMm) && !empty($loan->mulai_cicil_bulan)) {
                    if ($periodeYyyyMm < $loan->mulai_cicil_bulan) {
                        continue;
                    }
                }

                $cicilan = min((float) $loan->nominal_angsuran_per_bulan, (float) $loan->sisa_pinjaman);
                if ($cicilan <= 0) {
                    continue;
                }

                $paidCount = DB::table('pinjaman_angsuran')
                    ->where('pinjaman_pegawai_id', $loan->id)
                    ->count();
                $angsuranKe = $paidCount + 1;

                $totalCicilan += $cicilan;
                $ketParts[] = 'Cicilan ke-' . $angsuranKe . '/' . $loan->tenor_bulan . ' (' . $loan->nomor_pinjaman . ')';
                $result['loans'][] = [
                    'loan' => $loan,
                    'cicilan' => $cicilan,
                    'angsuran_ke' => $angsuranKe,
                ];
            }

            $result['nominal'] = round($totalCicilan, 0);
            $result['keterangan'] = !empty($ketParts) ? implode(', ', $ketParts) : null;
        } catch (\Throwable $e) {
        }

        return $result;
    }
}
