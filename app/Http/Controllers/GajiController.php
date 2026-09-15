<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\User;
use App\Services\SlipPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class GajiController extends Controller
{
    /**
     * Auto check and initialize database tables if they do not exist.
     * Guarantees zero downtime/crashes even without running manual artisan migrate.
     */
    private function ensureTablesExist()
    {
        if (!Schema::hasTable('gaji_master')) {
            Schema::create('gaji_master', function (Blueprint $table) {
                $table->id();
                $table->string('nik', 30)->unique();
                $table->decimal('gaji_pokok', 12, 2)->default(0);
                $table->decimal('tunjangan_transportasi', 12, 2)->default(150000);
                $table->decimal('tunjangan_jabatan', 12, 2)->default(0);
                $table->decimal('tunjangan_konsumsi', 12, 2)->default(0);
                $table->decimal('tunjangan_kehadiran', 12, 2)->default(0);
                $table->decimal('tunjangan_beras', 12, 2)->default(0);
                $table->decimal('tunjangan_fungsional', 12, 2)->default(0);
                $table->decimal('tunjangan_wali_kelas', 12, 2)->default(0);
                $table->decimal('tunjangan_masa_kerja', 12, 2)->default(0);
                $table->decimal('tarif_honor_kegiatan', 12, 2)->default(0);
                $table->decimal('tarif_ekskul', 12, 2)->default(0);
                $table->decimal('tarif_lembur', 12, 2)->default(0);
                $table->decimal('bpjs_kesehatan', 12, 2)->default(0);
                $table->decimal('bpjs_ketenagakerjaan', 12, 2)->default(0);
                $table->decimal('potongan_kasbon', 12, 2)->default(0);
                $table->decimal('potongan_lainnya', 12, 2)->default(0);
                $table->timestamps();
            });
        } else {
            if (!Schema::hasColumn('gaji_master', 'potongan_kasbon')) {
                Schema::table('gaji_master', function (Blueprint $table) {
                    $table->decimal('potongan_kasbon', 12, 2)->default(0)->after('bpjs_ketenagakerjaan');
                });
            }
            if (!Schema::hasColumn('gaji_master', 'potongan_lainnya')) {
                Schema::table('gaji_master', function (Blueprint $table) {
                    $table->decimal('potongan_lainnya', 12, 2)->default(0)->after('potongan_kasbon');
                });
            }
        }

        if (!Schema::hasTable('penggajian_periode')) {
            Schema::create('penggajian_periode', function (Blueprint $table) {
                $table->id();
                $table->string('kode_periode', 50)->unique();
                $table->string('nama_periode', 100);
                $table->integer('bulan');
                $table->integer('tahun');
                $table->date('tgl_mulai');
                $table->date('tgl_selesai');
                $table->integer('hk_standar')->default(26);
                $table->integer('total_siswa_tpa')->default(0);
                $table->enum('status', ['draft', 'final', 'terbayar'])->default('draft');
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('penggajian_detail')) {
            Schema::create('penggajian_detail', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('periode_id')->index();
                $table->string('nik', 30)->index();
                $table->string('kode_cabang', 20)->nullable();
                $table->string('kode_dept', 20)->nullable();
                $table->string('jabatan', 100)->nullable();
                $table->integer('hk_standar')->default(26);
                $table->integer('hadir')->default(0);
                $table->integer('izin')->default(0);
                $table->integer('sakit')->default(0);
                $table->integer('alpha')->default(0);
                $table->decimal('terlambat_jam', 6, 2)->default(0);
                $table->decimal('gaji_pokok', 12, 2)->default(0);
                $table->decimal('tunjangan_transportasi', 12, 2)->default(0);
                $table->decimal('tunjangan_jabatan', 12, 2)->default(0);
                $table->decimal('tunjangan_konsumsi', 12, 2)->default(0);
                $table->decimal('tunjangan_kehadiran', 12, 2)->default(0);
                $table->decimal('tunjangan_lainnya', 12, 2)->default(0);
                $table->decimal('honor_kegiatan', 12, 2)->default(0);
                $table->decimal('honor_ekskul', 12, 2)->default(0);
                $table->decimal('upah_lembur', 12, 2)->default(0);
                $table->decimal('insentif_pool_tpa', 12, 2)->default(0);
                $table->decimal('reward_disiplin', 12, 2)->default(0);
                $table->decimal('bonus_tambahan', 12, 2)->default(0);
                $table->decimal('potongan_absen', 12, 2)->default(0);
                $table->decimal('potongan_terlambat', 12, 2)->default(0);
                $table->decimal('potongan_kasbon', 12, 2)->default(0);
                $table->decimal('potongan_bpjs', 12, 2)->default(0);
                $table->decimal('potongan_lainnya', 12, 2)->default(0);
                $table->decimal('total_penghasilan', 12, 2)->default(0);
                $table->decimal('total_potongan', 12, 2)->default(0);
                $table->decimal('gaji_bersih', 12, 2)->default(0);
                $table->text('catatan')->nullable();
                $table->tinyInteger('status_kirim_wa')->default(0);
                $table->dateTime('waktu_kirim_wa')->nullable();
                $table->string('pdf_path', 255)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Display list of payroll periods.
     */
    public function index(Request $request)
    {
        $this->ensureTablesExist();

        $query = DB::table('penggajian_periode')
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc');

        if ($request->tahun) {
            $query->where('tahun', $request->tahun);
        }

        $periode = $query->paginate(12);

        // Attach counts & sums for each period
        foreach ($periode as $p) {
            $p->total_karyawan = DB::table('penggajian_detail')->where('periode_id', $p->id)->count();
            $p->total_gaji = DB::table('penggajian_detail')->where('periode_id', $p->id)->sum('gaji_bersih');
            $p->total_wa_terkirim = DB::table('penggajian_detail')->where('periode_id', $p->id)->where('status_kirim_wa', 1)->count();
        }

        $namabulan = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

        return view('gaji.index', compact('periode', 'namabulan'));
    }

    /**
     * Generate payroll for a new period based on attendance.
     */
    public function storePeriode(Request $request)
    {
        $this->ensureTablesExist();

        $request->validate([
            'bulan' => 'required|numeric|min:1|max:12',
            'tahun' => 'required|numeric|min:2020',
            'tgl_mulai' => 'required|date',
            'tgl_selesai' => 'required|date|after_or_equal:tgl_mulai',
            'hk_standar' => 'required|numeric|min:1',
        ]);

        $namabulan = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        $bulanNama = $namabulan[$request->bulan];
        $namaPeriode = "Gaji " . $bulanNama . " " . $request->tahun;
        $kodePeriode = "GAJI-" . $request->tahun . "-" . str_pad($request->bulan, 2, "0", STR_PAD_LEFT);

        // Check if period already exists
        $existing = DB::table('penggajian_periode')->where('kode_periode', $kodePeriode)->first();
        if ($existing) {
            return Redirect::back()->with(['warning' => 'Periode penggajian ' . $namaPeriode . ' sudah pernah digenerate! Anda dapat melihat atau mengeditnya di daftar.']);
        }

        $periodeId = DB::table('penggajian_periode')->insertGetId([
            'kode_periode' => $kodePeriode,
            'nama_periode' => $namaPeriode,
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
            'tgl_mulai' => $request->tgl_mulai,
            'tgl_selesai' => $request->tgl_selesai,
            'hk_standar' => $request->hk_standar ?? 26,
            'total_siswa_tpa' => $request->total_siswa_tpa ?? 0,
            'status' => 'draft',
            'keterangan' => $request->keterangan,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Pull active karyawan
        $karyawanList = DB::table('karyawan')->get();
        $hkStandar = $request->hk_standar ?? 26;

        foreach ($karyawanList as $k) {
            // Get master salary if configured
            $master = DB::table('gaji_master')->where('nik', $k->nik)->first();

            $gapok = $master ? $master->gaji_pokok : 1200000;
            $tunjTransport = $master ? $master->tunjangan_transportasi : 150000;
            $tunjJabatan = $master ? $master->tunjangan_jabatan : 0;
            $tunjKonsumsi = $master ? $master->tunjangan_konsumsi : 0;
            $tunjKehadiran = $master ? $master->tunjangan_kehadiran : 0;
            $honorKegiatan = $master ? $master->tarif_honor_kegiatan : 0;
            $honorEkskul = $master ? $master->tarif_ekskul : 0;
            $upahLembur = $master ? $master->tarif_lembur : 0;

            // Attendance calculation from presensi table
            $presensiData = DB::table('presensi')
                ->where('nik', $k->nik)
                ->whereBetween('tgl_presensi', [$request->tgl_mulai, $request->tgl_selesai])
                ->get();

            $hadir = 0;
            $izin = 0;
            $sakit = 0;
            $alpha = 0;
            $totalJamTerlambat = 0;
            $hadirPagiDisiplin = 0; // Datang sebelum 06.30 untuk Bunda TPA

            foreach ($presensiData as $p) {
                if ($p->status == 'h' || !empty($p->jam_in)) {
                    $hadir++;

                    // Disiplin pagi check (06:30)
                    if (!empty($p->jam_in) && date('H:i', strtotime($p->jam_in)) <= '06:30') {
                        $hadirPagiDisiplin++;
                    }

                    // Terlambat check
                    // Ambil jam masuk standar jika ada
                    $jamMasukJadwal = "07:00:00";
                    if (!empty($p->kode_jam_kerja)) {
                        $jk = DB::table('jam_kerja')->where('kode_jam_kerja', $p->kode_jam_kerja)->first();
                        if ($jk && !empty($jk->jam_masuk)) {
                            $jamMasukJadwal = $jk->jam_masuk;
                        }
                    }

                    if (!empty($p->jam_in) && strtotime($p->jam_in) > strtotime($jamMasukJadwal)) {
                        $diff = strtotime($p->jam_in) - strtotime($jamMasukJadwal);
                        $jamTelat = floor($diff / 3600);
                        $menitTelat = floor(($diff % 3600) / 60);
                        $totalJamTerlambat += ($jamTelat + round($menitTelat / 60, 2));
                    }
                } elseif ($p->status == 'i') {
                    $izin++;
                } elseif ($p->status == 's') {
                    $sakit++;
                } elseif ($p->status == 'a') {
                    $alpha++;
                }
            }

            // Calculate alpha if total presence records is less than HK and no explicit permission
            $totalDicatat = $hadir + $izin + $sakit + $alpha;
            if ($totalDicatat < $hkStandar) {
                // If it's a new or mid-month calculation, difference can be treated as alpha or unrecorded
                $selisih = $hkStandar - $totalDicatat;
                // Only count as alpha if requested date range has passed
                if (strtotime($request->tgl_selesai) <= time()) {
                    $alpha += $selisih;
                }
            }

            $potonganKasbon = $master ? ($master->potongan_kasbon ?? 0) : 0;
            $potonganBpjs = $master ? (($master->bpjs_kesehatan ?? 0) + ($master->bpjs_ketenagakerjaan ?? 0)) : 0;
            $potonganLainnya = $master ? ($master->potongan_lainnya ?? 0) : 0;

            // FORMULA STANDAR 26 HARI KERJA (SESUAI EXCEL ARJUNA)
            // Gaji Basis Harian = Gaji Pokok + Tunjangan Transportasi
            // Gaji Harian = Gaji Basis Harian / 26 HK
            // Gaji Per Jam = Gaji Harian / 10 Jam
            $gajiBasisHarian = ($gapok + $tunjTransport);
            $gajiHarian = $hkStandar > 0 ? ($gajiBasisHarian / $hkStandar) : 0;
            $gajiPerJam = $gajiHarian > 0 ? ($gajiHarian / 10) : 0;

            $potonganAbsen = round(($izin + $alpha) * $gajiHarian, 0);
            $potonganTerlambat = round($totalJamTerlambat * $gajiPerJam, 0);

            // Cabang & Jabatan Rules
            $insentifPoolTpa = 0;
            $rewardDisiplin = 0;

            // Jika TPA dan siswa >= 41 (Pool Gate 41)
            $isTpa = str_contains(strtoupper($k->jabatan ?? ''), 'TPA') || str_contains(strtoupper($k->kode_dept ?? ''), 'TPA');
            if ($isTpa) {
                if (($request->total_siswa_tpa ?? 0) >= 41) {
                    $insentifPoolTpa = 150000; // Bonus pool gate 41
                }
                if ($hadirPagiDisiplin >= 15) {
                    $rewardDisiplin = 50000; // Reward konsistensi disiplin 06.30
                }
            }

            $totalPenghasilan = $gapok + $tunjTransport + $tunjJabatan + $tunjKonsumsi + $tunjKehadiran + $honorKegiatan + $honorEkskul + $upahLembur + $insentifPoolTpa + $rewardDisiplin;
            $totalPotongan = $potonganAbsen + $potonganTerlambat + $potonganKasbon + $potonganBpjs + $potonganLainnya;
            $gajiBersih = max(0, $totalPenghasilan - $totalPotongan);

            DB::table('penggajian_detail')->insert([
                'periode_id' => $periodeId,
                'nik' => $k->nik,
                'kode_cabang' => $k->kode_cabang,
                'kode_dept' => $k->kode_dept,
                'jabatan' => $k->jabatan,
                'hk_standar' => $hkStandar,
                'hadir' => $hadir,
                'izin' => $izin,
                'sakit' => $sakit,
                'alpha' => $alpha,
                'terlambat_jam' => $totalJamTerlambat,
                'gaji_pokok' => $gapok,
                'tunjangan_transportasi' => $tunjTransport,
                'tunjangan_jabatan' => $tunjJabatan,
                'tunjangan_konsumsi' => $tunjKonsumsi,
                'tunjangan_kehadiran' => $tunjKehadiran,
                'tunjangan_lainnya' => 0,
                'honor_kegiatan' => $honorKegiatan,
                'honor_ekskul' => $honorEkskul,
                'upah_lembur' => $upahLembur,
                'insentif_pool_tpa' => $insentifPoolTpa,
                'reward_disiplin' => $rewardDisiplin,
                'bonus_tambahan' => 0,
                'potongan_absen' => $potonganAbsen,
                'potongan_terlambat' => $potonganTerlambat,
                'potongan_kasbon' => $potonganKasbon,
                'potongan_bpjs' => $potonganBpjs,
                'potongan_lainnya' => $potonganLainnya,
                'total_penghasilan' => $totalPenghasilan,
                'total_potongan' => $totalPotongan,
                'gaji_bersih' => $gajiBersih,
                'status_kirim_wa' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect('/gaji/periode/' . $periodeId)->with(['success' => 'Penggajian periode ' . $namaPeriode . ' berhasil digenerate untuk ' . count($karyawanList) . ' karyawan!']);
    }

    /**
     * View details of all employee slips in a payroll period.
     */
    public function showPeriode($id, Request $request)
    {
        $this->ensureTablesExist();

        $periode = DB::table('penggajian_periode')->where('id', $id)->first();
        if (!$periode) {
            return redirect('/gaji')->with(['warning' => 'Periode penggajian tidak ditemukan']);
        }

        $query = DB::table('penggajian_detail')
            ->join('karyawan', 'penggajian_detail.nik', '=', 'karyawan.nik')
            ->leftJoin('cabang', 'penggajian_detail.kode_cabang', '=', 'cabang.kode_cabang')
            ->leftJoin('departemen', 'penggajian_detail.kode_dept', '=', 'departemen.kode_dept')
            ->where('periode_id', $id)
            ->select('penggajian_detail.*', 'karyawan.nama_lengkap', 'karyawan.no_hp', 'cabang.nama_cabang', 'departemen.nama_dept')
            ->orderBy('karyawan.nama_lengkap', 'asc');

        if ($request->kode_cabang) {
            $query->where('penggajian_detail.kode_cabang', $request->kode_cabang);
        }

        if ($request->kode_dept) {
            $query->where('penggajian_detail.kode_dept', $request->kode_dept);
        }

        if ($request->nama_karyawan) {
            $query->where('karyawan.nama_lengkap', 'like', '%' . $request->nama_karyawan . '%');
        }

        $details = $query->get();

        $cabang = DB::table('cabang')->orderBy('nama_cabang')->get();
        $departemen = DB::table('departemen')->orderBy('nama_dept')->get();

        // Summary stats
        $stats = [
            'total_karyawan' => $details->count(),
            'total_gaji_bersih' => $details->sum('gaji_bersih'),
            'total_potongan' => $details->sum('total_potongan'),
            'total_wa_terkirim' => $details->where('status_kirim_wa', 1)->count(),
        ];

        return view('gaji.show', compact('periode', 'details', 'cabang', 'departemen', 'stats'));
    }

    /**
     * Edit a specific employee salary slip (Superadmin).
     */
    public function editDetail($id)
    {
        $this->ensureTablesExist();

        $detail = DB::table('penggajian_detail')
            ->join('karyawan', 'penggajian_detail.nik', '=', 'karyawan.nik')
            ->join('penggajian_periode', 'penggajian_detail.periode_id', '=', 'penggajian_periode.id')
            ->where('penggajian_detail.id', $id)
            ->select('penggajian_detail.*', 'karyawan.nama_lengkap', 'penggajian_periode.nama_periode')
            ->first();

        if (!$detail) {
            return redirect('/gaji')->with(['warning' => 'Data slip gaji tidak ditemukan']);
        }

        return view('gaji.edit', compact('detail'));
    }

    /**
     * Update employee salary slip values (Superadmin).
     */
    public function updateDetail(Request $request, $id)
    {
        $this->ensureTablesExist();

        $gapok = str_replace(['.', ','], '', $request->gaji_pokok ?? 0);
        $tunjTransport = str_replace(['.', ','], '', $request->tunjangan_transportasi ?? 0);
        $tunjJabatan = str_replace(['.', ','], '', $request->tunjangan_jabatan ?? 0);
        $tunjKonsumsi = str_replace(['.', ','], '', $request->tunjangan_konsumsi ?? 0);
        $tunjKehadiran = str_replace(['.', ','], '', $request->tunjangan_kehadiran ?? 0);
        $honorKegiatan = str_replace(['.', ','], '', $request->honor_kegiatan ?? 0);
        $honorEkskul = str_replace(['.', ','], '', $request->honor_ekskul ?? 0);
        $upahLembur = str_replace(['.', ','], '', $request->upah_lembur ?? 0);
        $insentifPool = str_replace(['.', ','], '', $request->insentif_pool_tpa ?? 0);
        $rewardDisiplin = str_replace(['.', ','], '', $request->reward_disiplin ?? 0);
        $bonus = str_replace(['.', ','], '', $request->bonus_tambahan ?? 0);
        $tunjLain = str_replace(['.', ','], '', $request->tunjangan_lainnya ?? 0);

        $potAbsen = str_replace(['.', ','], '', $request->potongan_absen ?? 0);
        $potTerlambat = str_replace(['.', ','], '', $request->potongan_terlambat ?? 0);
        $potKasbon = str_replace(['.', ','], '', $request->potongan_kasbon ?? 0);
        $potBpjs = str_replace(['.', ','], '', $request->potongan_bpjs ?? 0);
        $potLain = str_replace(['.', ','], '', $request->potongan_lainnya ?? 0);

        $totalPenghasilan = $gapok + $tunjTransport + $tunjJabatan + $tunjKonsumsi + $tunjKehadiran + $honorKegiatan + $honorEkskul + $upahLembur + $insentifPool + $rewardDisiplin + $bonus + $tunjLain;
        $totalPotongan = $potAbsen + $potTerlambat + $potKasbon + $potBpjs + $potLain;
        $gajiBersih = max(0, $totalPenghasilan - $totalPotongan);

        DB::table('penggajian_detail')->where('id', $id)->update([
            'hadir' => $request->hadir ?? 0,
            'izin' => $request->izin ?? 0,
            'sakit' => $request->sakit ?? 0,
            'alpha' => $request->alpha ?? 0,
            'terlambat_jam' => $request->terlambat_jam ?? 0,
            'gaji_pokok' => $gapok,
            'tunjangan_transportasi' => $tunjTransport,
            'tunjangan_jabatan' => $tunjJabatan,
            'tunjangan_konsumsi' => $tunjKonsumsi,
            'tunjangan_kehadiran' => $tunjKehadiran,
            'tunjangan_lainnya' => $tunjLain,
            'honor_kegiatan' => $honorKegiatan,
            'honor_ekskul' => $honorEkskul,
            'upah_lembur' => $upahLembur,
            'insentif_pool_tpa' => $insentifPool,
            'reward_disiplin' => $rewardDisiplin,
            'bonus_tambahan' => $bonus,
            'potongan_absen' => $potAbsen,
            'potongan_terlambat' => $potTerlambat,
            'potongan_kasbon' => $potKasbon,
            'potongan_bpjs' => $potBpjs,
            'potongan_lainnya' => $potLain,
            'total_penghasilan' => $totalPenghasilan,
            'total_potongan' => $totalPotongan,
            'gaji_bersih' => $gajiBersih,
            'catatan' => $request->catatan,
            'updated_at' => now(),
        ]);

        // Re-generate PDF if it exists
        $detail = DB::table('penggajian_detail')
            ->join('karyawan', 'penggajian_detail.nik', '=', 'karyawan.nik')
            ->join('penggajian_periode', 'penggajian_detail.periode_id', '=', 'penggajian_periode.id')
            ->leftJoin('cabang', 'penggajian_detail.kode_cabang', '=', 'cabang.kode_cabang')
            ->where('penggajian_detail.id', $id)
            ->select('penggajian_detail.*', 'karyawan.nama_lengkap', 'penggajian_periode.nama_periode', 'penggajian_periode.tgl_selesai', 'cabang.nama_cabang')
            ->first();

        if ($detail) {
            $pdfRelPath = SlipPdfService::generatePdf($detail);
            DB::table('penggajian_detail')->where('id', $id)->update(['pdf_path' => $pdfRelPath]);
        }

        return redirect('/gaji/periode/' . $detail->periode_id)->with(['success' => 'Slip gaji ' . $detail->nama_lengkap . ' berhasil diperbarui!']);
    }

    /**
     * Print slip in browser (paperless 2-in-1 format).
     */
    public function cetakSlip($id)
    {
        $this->ensureTablesExist();

        $detail = DB::table('penggajian_detail')
            ->join('karyawan', 'penggajian_detail.nik', '=', 'karyawan.nik')
            ->join('penggajian_periode', 'penggajian_detail.periode_id', '=', 'penggajian_periode.id')
            ->leftJoin('cabang', 'penggajian_detail.kode_cabang', '=', 'cabang.kode_cabang')
            ->where('penggajian_detail.id', $id)
            ->select('penggajian_detail.*', 'karyawan.nama_lengkap', 'penggajian_periode.nama_periode', 'penggajian_periode.tgl_selesai', 'cabang.nama_cabang')
            ->first();

        if (!$detail) {
            return "Data slip gaji tidak ditemukan";
        }

        return view('gaji.cetak', compact('detail'));
    }

    /**
     * Download native PDF document of the slip.
     */
    public function downloadPdf($id)
    {
        $this->ensureTablesExist();

        $detail = DB::table('penggajian_detail')
            ->join('karyawan', 'penggajian_detail.nik', '=', 'karyawan.nik')
            ->join('penggajian_periode', 'penggajian_detail.periode_id', '=', 'penggajian_periode.id')
            ->leftJoin('cabang', 'penggajian_detail.kode_cabang', '=', 'cabang.kode_cabang')
            ->where('penggajian_detail.id', $id)
            ->select('penggajian_detail.*', 'karyawan.nama_lengkap', 'penggajian_periode.nama_periode', 'penggajian_periode.tgl_selesai', 'cabang.nama_cabang')
            ->first();

        if (!$detail) {
            return redirect()->back()->with(['warning' => 'Data slip gaji tidak ditemukan']);
        }

        $pdfRelPath = SlipPdfService::generatePdf($detail);
        DB::table('penggajian_detail')->where('id', $id)->update(['pdf_path' => $pdfRelPath]);

        $fullPath = public_path($pdfRelPath);
        $cleanName = 'Slip_Gaji_' . preg_replace('/[^A-Za-z0-9]/', '_', $detail->nama_lengkap) . '_' . $detail->nik . '.pdf';

        return response()->download($fullPath, $cleanName);
    }

    /**
     * Send slip document (.pdf) via WhatsApp Gateway.
     */
    public function kirimWa($id)
    {
        $this->ensureTablesExist();

        $detail = DB::table('penggajian_detail')
            ->join('karyawan', 'penggajian_detail.nik', '=', 'karyawan.nik')
            ->join('penggajian_periode', 'penggajian_detail.periode_id', '=', 'penggajian_periode.id')
            ->leftJoin('cabang', 'penggajian_detail.kode_cabang', '=', 'cabang.kode_cabang')
            ->where('penggajian_detail.id', $id)
            ->select('penggajian_detail.*', 'karyawan.nama_lengkap', 'karyawan.no_hp', 'penggajian_periode.nama_periode', 'penggajian_periode.tgl_selesai', 'cabang.nama_cabang')
            ->first();

        if (!$detail) {
            return redirect()->back()->with(['warning' => 'Data slip gaji tidak ditemukan']);
        }

        if (empty($detail->no_hp)) {
            return redirect()->back()->with(['warning' => 'Nomor WhatsApp karyawan ' . $detail->nama_lengkap . ' belum diisi di Data Karyawan!']);
        }

        // Format phone number to 628xxx
        $phone = preg_replace('/[^0-9]/', '', $detail->no_hp);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        // Generate PDF
        $pdfRelPath = SlipPdfService::generatePdf($detail);
        DB::table('penggajian_detail')->where('id', $id)->update(['pdf_path' => $pdfRelPath]);
        $publicPdfUrl = url($pdfRelPath);

        // Prepare professional WhatsApp message text
        $msg = "Assalamu'alaikum Wr. Wb.\n";
        $msg .= "Yth. *" . strtoupper($detail->nama_lengkap) . "*,\n\n";
        $msg .= "Berikut kami lampirkan dokumen resmi *SLIP GAJI* Anda:\n";
        $msg .= "📋 *Periode:* " . $detail->nama_periode . "\n";
        $msg .= "💼 *Jabatan:* " . strtoupper($detail->jabatan ?? '-') . "\n";
        $msg .= "🏢 *Cabang:* " . strtoupper($detail->nama_cabang ?? $detail->kode_cabang) . "\n";
        $msg .= "------------------------------------\n";
        $msg .= "💵 *Gaji Bersih (THP):* Rp " . number_format($detail->gaji_bersih, 0, ',', '.') . "\n";
        $msg .= "------------------------------------\n";
        $msg .= "File dokumen resmi .pdf terlampir pada pesan ini.\n\n";
        $msg .= "_Dibuat oleh: Kartika P. (Bendahara Yayasan)_\n";
        $msg .= "_PAUD Arjuna Cendekia_";

        $gatewayUrl = env('WA_GATEWAY_URL', 'https://wagateway.pedasalami.com/send-message');

        try {
            $response = Http::timeout(15)->post($gatewayUrl, [
                'number' => $phone,
                'message' => $msg,
                'file_dikirim' => $publicPdfUrl,
            ]);

            if ($response->successful()) {
                DB::table('penggajian_detail')->where('id', $id)->update([
                    'status_kirim_wa' => 1,
                    'waktu_kirim_wa' => now(),
                ]);

                return redirect()->back()->with(['success' => 'Dokumen PDF Slip Gaji berhasil dikirim ke WhatsApp ' . $detail->nama_lengkap . ' (' . $phone . ')']);
            } else {
                return redirect()->back()->with(['warning' => 'Gateway merespons: ' . $response->body() . '. Silakan periksa koneksi WhatsApp Gateway.']);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with(['warning' => 'Gagal menghubungi WhatsApp Gateway (' . $e->getMessage() . '). Anda dapat mendownload PDF atau mengirim manual.']);
        }
    }

    /**
     * Send all slips in a period via WhatsApp.
     */
    public function kirimWaSemua($periode_id)
    {
        $this->ensureTablesExist();

        $details = DB::table('penggajian_detail')
            ->join('karyawan', 'penggajian_detail.nik', '=', 'karyawan.nik')
            ->where('periode_id', $periode_id)
            ->select('penggajian_detail.id')
            ->get();

        $successCount = 0;
        foreach ($details as $d) {
            // Send each
            $detail = DB::table('penggajian_detail')
                ->join('karyawan', 'penggajian_detail.nik', '=', 'karyawan.nik')
                ->join('penggajian_periode', 'penggajian_detail.periode_id', '=', 'penggajian_periode.id')
                ->leftJoin('cabang', 'penggajian_detail.kode_cabang', '=', 'cabang.kode_cabang')
                ->where('penggajian_detail.id', $d->id)
                ->select('penggajian_detail.*', 'karyawan.nama_lengkap', 'karyawan.no_hp', 'penggajian_periode.nama_periode', 'penggajian_periode.tgl_selesai', 'cabang.nama_cabang')
                ->first();

            if ($detail && !empty($detail->no_hp)) {
                $phone = preg_replace('/[^0-9]/', '', $detail->no_hp);
                if (str_starts_with($phone, '0')) {
                    $phone = '62' . substr($phone, 1);
                }

                $pdfRelPath = SlipPdfService::generatePdf($detail);
                DB::table('penggajian_detail')->where('id', $d->id)->update(['pdf_path' => $pdfRelPath]);
                $publicPdfUrl = url($pdfRelPath);

                $msg = "Assalamu'alaikum Wr. Wb.\n";
                $msg .= "Yth. *" . strtoupper($detail->nama_lengkap) . "*,\n\n";
                $msg .= "Berikut kami lampirkan dokumen resmi *SLIP GAJI* Anda:\n";
                $msg .= "📋 *Periode:* " . $detail->nama_periode . "\n";
                $msg .= "💵 *Gaji Bersih (THP):* Rp " . number_format($detail->gaji_bersih, 0, ',', '.') . "\n";
                $msg .= "------------------------------------\n";
                $msg .= "_Dibuat oleh: Kartika P. (Bendahara Yayasan)_\n";
                $msg .= "_PAUD Arjuna Cendekia_";

                $gatewayUrl = env('WA_GATEWAY_URL', 'https://wagateway.pedasalami.com/send-message');

                try {
                    $res = Http::timeout(10)->post($gatewayUrl, [
                        'number' => $phone,
                        'message' => $msg,
                        'file_dikirim' => $publicPdfUrl,
                    ]);
                    if ($res->successful()) {
                        DB::table('penggajian_detail')->where('id', $d->id)->update([
                            'status_kirim_wa' => 1,
                            'waktu_kirim_wa' => now(),
                        ]);
                        $successCount++;
                    }
                } catch (\Exception $e) {
                    // continue next
                }
            }
        }

        return redirect()->back()->with(['success' => 'Proses kirim WA massal selesai. ' . $successCount . ' slip berhasil terkirim.']);
    }

    /**
     * Delete a period and all its slip details.
     */
    public function deletePeriode($id)
    {
        $this->ensureTablesExist();

        DB::table('penggajian_detail')->where('periode_id', $id)->delete();
        DB::table('penggajian_periode')->where('id', $id)->delete();

        return redirect('/gaji')->with(['success' => 'Periode penggajian dan seluruh data slip berhasil dihapus']);
    }

    /**
     * Standards extracted directly from official documents:
     * 1. LAP. KB PAUD ARJUNA 2026-2027.xls (KB & TK Citandui & Langsep)
     * 2. LAP.KEU 2026.xlsx (TPA / Daycare Citandui & Langsep)
     */
    private function getExcelSalaryStandards()
    {
        return [
            // --- GURU KB & TK CABANG CITANDUI & LANGSEP ---
            'CLARISTA' => [
                'gaji_pokok' => 689000,
                'tunjangan_transportasi' => 150000,
                'tunjangan_jabatan' => 0,
                'potongan_kasbon' => 0,
            ],
            'DWI RETNO' => [
                'gaji_pokok' => 1011240,
                'tunjangan_transportasi' => 150000,
                'tunjangan_jabatan' => 0,
                'potongan_kasbon' => 400000, // Dari Excel: PINJAMAN 4X(2JT)
            ],
            'MERINDA' => [
                'gaji_pokok' => 1004880,
                'tunjangan_transportasi' => 150000,
                'tunjangan_jabatan' => 0,
                'potongan_kasbon' => 0,
            ],
            'TITIN HAMIDAH' => [
                'gaji_pokok' => 1067420,
                'tunjangan_transportasi' => 150000,
                'tunjangan_jabatan' => 200000, // Dari Excel: Uang Ekstra/Tunj. KB
                'potongan_kasbon' => 0,
            ],
            'ULUM KHUSNATIN' => [
                'gaji_pokok' => 898880,
                'tunjangan_transportasi' => 150000,
                'tunjangan_jabatan' => 0,
                'potongan_kasbon' => 0,
            ],
            'ERIN' => [ // Cocok dengan ERIN WIDAYANTI & ERIN WIDAYATI
                'gaji_pokok' => 730340,
                'tunjangan_transportasi' => 150000,
                'tunjangan_jabatan' => 0,
                'potongan_kasbon' => 0,
            ],
            'RIZKA ALFIANTI' => [
                'gaji_pokok' => 1067420,
                'tunjangan_transportasi' => 150000,
                'tunjangan_jabatan' => 0,
                'potongan_kasbon' => 0,
            ],
            'ROSHELLA' => [
                'gaji_pokok' => 1573040,
                'tunjangan_transportasi' => 150000,
                'tunjangan_jabatan' => 0,
                'potongan_kasbon' => 0,
            ],
            'ALEXANDRA' => [
                'gaji_pokok' => 400000,
                'tunjangan_transportasi' => 150000,
                'tunjangan_jabatan' => 0,
                'potongan_kasbon' => 0,
            ],
            'ANANDA LAILA' => [
                'gaji_pokok' => 400000,
                'tunjangan_transportasi' => 150000,
                'tunjangan_jabatan' => 0,
                'potongan_kasbon' => 0,
            ],
            'KARTIKA' => [
                'gaji_pokok' => 1700000,
                'tunjangan_transportasi' => 150000,
                'tunjangan_jabatan' => 0,
                'potongan_kasbon' => 0,
            ],

            // --- STAFF TPA / DAYCARE ---
            'CINDY NOVALITA' => [
                'gaji_pokok' => 2650000,
                'tunjangan_transportasi' => 150000,
                'tunjangan_jabatan' => 0,
                'potongan_kasbon' => 200000, // Dari Excel: Pinjaman 1x(2jt)
            ],
            'MAHDALENA' => [
                'gaji_pokok' => 2173000,
                'tunjangan_transportasi' => 150000,
                'tunjangan_jabatan' => 0,
                'potongan_kasbon' => 0,
            ],
            'KASYATI' => [
                'gaji_pokok' => 1700000,
                'tunjangan_transportasi' => 150000,
                'tunjangan_jabatan' => 0,
                'potongan_kasbon' => 0,
            ],
            'MILYAS' => [
                'gaji_pokok' => 1378000,
                'tunjangan_transportasi' => 200000,
                'tunjangan_jabatan' => 0,
                'potongan_kasbon' => 0,
            ],
            'ANGGITA DARA' => [
                'gaji_pokok' => 1350000,
                'tunjangan_transportasi' => 150000,
                'tunjangan_jabatan' => 0,
                'potongan_kasbon' => 0,
            ],
            'YUYUS' => [
                'gaji_pokok' => 1350000,
                'tunjangan_transportasi' => 150000,
                'tunjangan_jabatan' => 0,
                'potongan_kasbon' => 0,
            ],
            'ATIK CAHYANINGRUM' => [
                'gaji_pokok' => 1219000,
                'tunjangan_transportasi' => 150000,
                'tunjangan_jabatan' => 0,
                'potongan_kasbon' => 0,
            ],
            'SETYO ARINI' => [
                'gaji_pokok' => 1166000,
                'tunjangan_transportasi' => 150000,
                'tunjangan_jabatan' => 0,
                'potongan_kasbon' => 0,
            ],
            'NOVI RAHMA' => [
                'gaji_pokok' => 1160000,
                'tunjangan_transportasi' => 150000,
                'tunjangan_jabatan' => 0,
                'potongan_kasbon' => 0,
            ],
            'CICIK TRIYA' => [
                'gaji_pokok' => 1050000,
                'tunjangan_transportasi' => 150000,
                'tunjangan_jabatan' => 0,
                'potongan_kasbon' => 0,
            ],
            'FEBRIANA' => [
                'gaji_pokok' => 1050000,
                'tunjangan_transportasi' => 150000,
                'tunjangan_jabatan' => 0,
                'potongan_kasbon' => 0,
            ],
            'METY FARIDA' => [
                'gaji_pokok' => 1000000,
                'tunjangan_transportasi' => 150000,
                'tunjangan_jabatan' => 0,
                'potongan_kasbon' => 0,
            ],
            'NAILA DHINI' => [
                'gaji_pokok' => 901000,
                'tunjangan_transportasi' => 150000,
                'tunjangan_jabatan' => 0,
                'potongan_kasbon' => 0,
            ],
            'FITRI FADILATUL' => [
                'gaji_pokok' => 901000,
                'tunjangan_transportasi' => 150000,
                'tunjangan_jabatan' => 0,
                'potongan_kasbon' => 0,
            ],
            'MARISA DINADA' => [
                'gaji_pokok' => 901000,
                'tunjangan_transportasi' => 150000,
                'tunjangan_jabatan' => 0,
                'potongan_kasbon' => 0,
            ],
            'HENI RAHMAWATI' => [
                'gaji_pokok' => 901000,
                'tunjangan_transportasi' => 150000,
                'tunjangan_jabatan' => 0,
                'potongan_kasbon' => 0,
            ],
        ];
    }

    /**
     * Synchronize Master Gaji standards from Excel dictionary.
     */
    private function syncMasterFromExcelStandards($force = false)
    {
        $standards = $this->getExcelSalaryStandards();
        $karyawanList = DB::table('karyawan')->get();

        foreach ($karyawanList as $k) {
            $namaUpper = strtoupper($k->nama_lengkap ?? '');
            $matched = null;

            foreach ($standards as $key => $vals) {
                if (str_contains($namaUpper, $key)) {
                    $matched = $vals;
                    break;
                }
            }

            if ($matched) {
                $master = DB::table('gaji_master')->where('nik', $k->nik)->first();
                if (!$master) {
                    DB::table('gaji_master')->insert([
                        'nik' => $k->nik,
                        'gaji_pokok' => $matched['gaji_pokok'],
                        'tunjangan_transportasi' => $matched['tunjangan_transportasi'],
                        'tunjangan_jabatan' => $matched['tunjangan_jabatan'] ?? 0,
                        'potongan_kasbon' => $matched['potongan_kasbon'] ?? 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } elseif ($force || $master->gaji_pokok <= 0 || $master->gaji_pokok == 1200000) {
                    DB::table('gaji_master')->where('nik', $k->nik)->update([
                        'gaji_pokok' => $matched['gaji_pokok'],
                        'tunjangan_transportasi' => $matched['tunjangan_transportasi'],
                        'tunjangan_jabatan' => $matched['tunjangan_jabatan'] ?? 0,
                        'potongan_kasbon' => $matched['potongan_kasbon'] ?? 0,
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * User-triggered action to synchronize master standards from Excel.
     */
    public function syncMasterExcel()
    {
        $this->ensureTablesExist();
        $this->syncMasterFromExcelStandards(true);
        return redirect('/gaji/master')->with(['success' => 'Standar Gaji Pokok, Tunjangan, & Potongan Kasbon seluruh Guru KB/TK dan Staff TPA berhasil disinkronkan dari Excel!']);
    }

    /**
     * Manage Master Gaji per employee.
     */
    public function master(Request $request)
    {
        $this->ensureTablesExist();

        // Auto sync if any master record is missing or zero
        $this->syncMasterFromExcelStandards(false);

        $query = DB::table('karyawan')
            ->leftJoin('gaji_master', 'karyawan.nik', '=', 'gaji_master.nik')
            ->leftJoin('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->leftJoin('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->select(
                'karyawan.nik',
                'karyawan.nama_lengkap',
                'karyawan.jabatan',
                'karyawan.kode_cabang',
                'karyawan.kode_dept',
                'cabang.nama_cabang',
                'departemen.nama_dept',
                'gaji_master.id as master_id',
                'gaji_master.gaji_pokok',
                'gaji_master.tunjangan_transportasi',
                'gaji_master.tunjangan_jabatan',
                'gaji_master.tunjangan_konsumsi',
                'gaji_master.tarif_honor_kegiatan',
                'gaji_master.tarif_ekskul',
                'gaji_master.tarif_lembur',
                'gaji_master.potongan_kasbon',
                'gaji_master.bpjs_kesehatan',
                'gaji_master.potongan_lainnya'
            )
            ->orderBy('karyawan.nama_lengkap', 'asc');

        if ($request->kode_cabang) {
            $query->where('karyawan.kode_cabang', $request->kode_cabang);
        }

        if ($request->kode_dept) {
            $query->where('karyawan.kode_dept', $request->kode_dept);
        }

        if ($request->nama_karyawan) {
            $query->where('karyawan.nama_lengkap', 'like', '%' . $request->nama_karyawan . '%');
        }

        $karyawan = $query->paginate(15);
        $cabang = DB::table('cabang')->orderBy('nama_cabang')->get();
        $departemen = DB::table('departemen')->orderBy('nama_dept')->get();

        return view('gaji.master', compact('karyawan', 'cabang', 'departemen'));
    }

    /**
     * Update Master Gaji for a specific employee.
     */
    public function updateMaster(Request $request, $nik)
    {
        $this->ensureTablesExist();

        $gapok = str_replace(['.', ','], '', $request->gaji_pokok ?? 0);
        $tunjTransport = str_replace(['.', ','], '', $request->tunjangan_transportasi ?? 0);
        $tunjJabatan = str_replace(['.', ','], '', $request->tunjangan_jabatan ?? 0);
        $tunjKonsumsi = str_replace(['.', ','], '', $request->tunjangan_konsumsi ?? 0);
        $honorKegiatan = str_replace(['.', ','], '', $request->tarif_honor_kegiatan ?? 0);
        $honorEkskul = str_replace(['.', ','], '', $request->tarif_ekskul ?? 0);
        $upahLembur = str_replace(['.', ','], '', $request->tarif_lembur ?? 0);
        $potonganKasbon = str_replace(['.', ','], '', $request->potongan_kasbon ?? 0);
        $bpjsKesehatan = str_replace(['.', ','], '', $request->bpjs_kesehatan ?? 0);
        $potonganLainnya = str_replace(['.', ','], '', $request->potongan_lainnya ?? 0);

        $exists = DB::table('gaji_master')->where('nik', $nik)->first();
        if ($exists) {
            DB::table('gaji_master')->where('nik', $nik)->update([
                'gaji_pokok' => $gapok,
                'tunjangan_transportasi' => $tunjTransport,
                'tunjangan_jabatan' => $tunjJabatan,
                'tunjangan_konsumsi' => $tunjKonsumsi,
                'tarif_honor_kegiatan' => $honorKegiatan,
                'tarif_ekskul' => $honorEkskul,
                'tarif_lembur' => $upahLembur,
                'potongan_kasbon' => $potonganKasbon,
                'bpjs_kesehatan' => $bpjsKesehatan,
                'potongan_lainnya' => $potonganLainnya,
                'updated_at' => now(),
            ]);
        } else {
            DB::table('gaji_master')->insert([
                'nik' => $nik,
                'gaji_pokok' => $gapok,
                'tunjangan_transportasi' => $tunjTransport,
                'tunjangan_jabatan' => $tunjJabatan,
                'tunjangan_konsumsi' => $tunjKonsumsi,
                'tarif_honor_kegiatan' => $honorKegiatan,
                'tarif_ekskul' => $honorEkskul,
                'tarif_lembur' => $upahLembur,
                'potongan_kasbon' => $potonganKasbon,
                'bpjs_kesehatan' => $bpjsKesehatan,
                'potongan_lainnya' => $potonganLainnya,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->back()->with(['success' => 'Master standar gaji & potongan karyawan ' . $nik . ' berhasil diperbarui!']);
    }
}
