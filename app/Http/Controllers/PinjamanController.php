<?php

namespace App\Http\Controllers;

use App\Services\FiturTambahanSchemaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PinjamanController extends Controller
{
    /**
     * Sync an employee's active kasbon installment to `gaji_master` so payroll stays in sync.
     */
    private function syncKasbonToGajiMaster(string $nik): void
    {
        try {
            if (!Schema::hasTable('gaji_master')) {
                return;
            }
            $allNiks = FiturTambahanSchemaService::getAllNiksForKaryawan($nik);
            $kasbonInfo = FiturTambahanSchemaService::getActiveKasbonInstallment($allNiks);

            foreach ($allNiks as $targetNik) {
                $exists = DB::table('gaji_master')->where('nik', $targetNik)->first();
                if ($exists) {
                    DB::table('gaji_master')->where('nik', $targetNik)->update([
                        'potongan_kasbon' => $kasbonInfo['nominal'],
                        'ket_potongan_kasbon' => $kasbonInfo['keterangan'],
                        'updated_at' => now(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
        }
    }

    /**
     * Admin: List all employee loans / kasbon.
     */
    public function index(Request $request)
    {
        FiturTambahanSchemaService::ensureTablesExist();

        $query = DB::table('pinjaman_pegawai')
            ->join('karyawan', 'pinjaman_pegawai.nik', '=', 'karyawan.nik')
            ->leftJoin('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->leftJoin('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->select(
                'pinjaman_pegawai.*',
                'karyawan.nama_lengkap',
                'karyawan.jabatan',
                'karyawan.kode_cabang',
                'cabang.nama_cabang',
                'departemen.nama_dept'
            )
            ->orderByRaw("CASE WHEN pinjaman_pegawai.status = 'berjalan' THEN 0 WHEN pinjaman_pegawai.status = 'lunas' THEN 1 ELSE 2 END")
            ->orderBy('pinjaman_pegawai.tanggal_pinjaman', 'desc')
            ->orderBy('pinjaman_pegawai.id', 'desc');

        if ($request->filled('nama_karyawan')) {
            $search = $request->nama_karyawan;
            $query->where(function ($q) use ($search) {
                $q->where('karyawan.nama_lengkap', 'like', '%' . $search . '%')
                    ->orWhere('pinjaman_pegawai.nik', 'like', '%' . $search . '%')
                    ->orWhere('pinjaman_pegawai.nomor_pinjaman', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('kode_cabang')) {
            $query->where('karyawan.kode_cabang', $request->kode_cabang);
        }

        if ($request->filled('status')) {
            $query->where('pinjaman_pegawai.status', $request->status);
        }

        $pinjaman = $query->paginate(20)->withQueryString();

        // Stats summary
        $allActive = DB::table('pinjaman_pegawai')->where('status', 'berjalan')->get();
        $stats = [
            'aktif_count' => $allActive->count(),
            'total_pinjaman_aktif' => $allActive->sum('jumlah_pinjaman'),
            'total_sisa_piutang' => $allActive->sum('sisa_pinjaman'),
            'total_terbayar' => DB::table('pinjaman_pegawai')->whereIn('status', ['berjalan', 'lunas'])->sum('total_dibayar'),
        ];

        $cabang = DB::table('cabang')->orderBy('nama_cabang')->get();
        $karyawanList = DB::table('karyawan')
            ->leftJoin('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->select('karyawan.nik', 'karyawan.nama_lengkap', 'karyawan.jabatan', 'cabang.nama_cabang')
            ->orderBy('karyawan.nama_lengkap')
            ->get();

        return view('pinjaman.index', compact('pinjaman', 'stats', 'cabang', 'karyawanList'));
    }

    /**
     * Admin: Store a new employee loan / kasbon.
     */
    public function store(Request $request)
    {
        FiturTambahanSchemaService::ensureTablesExist();

        $request->validate([
            'nik' => 'required|string',
            'tanggal_pinjaman' => 'required|date',
            'jumlah_pinjaman' => 'required',
            'tenor_bulan' => 'required|integer|min:1|max:60',
        ]);

        $jumlah = (float) str_replace(['.', ','], '', $request->jumlah_pinjaman);
        $tenor = max(1, (int) $request->tenor_bulan);

        $angsuranInput = str_replace(['.', ','], '', $request->nominal_angsuran_per_bulan ?? '');
        $angsuranPerBulan = !empty($angsuranInput) && (float) $angsuranInput > 0
            ? (float) $angsuranInput
            : round($jumlah / $tenor, 0);

        $ym = date('Ym', strtotime($request->tanggal_pinjaman));
        $countMonth = DB::table('pinjaman_pegawai')
            ->where('nomor_pinjaman', 'like', 'KSB-' . $ym . '-%')
            ->count();
        $nomorPinjaman = sprintf('KSB-%s-%03d', $ym, $countMonth + 1);
        while (DB::table('pinjaman_pegawai')->where('nomor_pinjaman', $nomorPinjaman)->exists()) {
            $countMonth++;
            $nomorPinjaman = sprintf('KSB-%s-%03d', $ym, $countMonth + 1);
        }

        $potongGaji = $request->has('potong_gaji') ? (int) $request->potong_gaji : 1;

        $id = DB::table('pinjaman_pegawai')->insertGetId([
            'nomor_pinjaman' => $nomorPinjaman,
            'nik' => $request->nik,
            'tanggal_pinjaman' => $request->tanggal_pinjaman,
            'jumlah_pinjaman' => $jumlah,
            'tenor_bulan' => $tenor,
            'nominal_angsuran_per_bulan' => $angsuranPerBulan,
            'mulai_cicil_bulan' => $request->mulai_cicil_bulan ?: date('Y-m', strtotime($request->tanggal_pinjaman)),
            'potong_gaji' => $potongGaji,
            'total_dibayar' => 0,
            'sisa_pinjaman' => $jumlah,
            'status' => 'berjalan',
            'keterangan' => $request->keterangan,
            'created_by' => Auth::guard('user')->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->syncKasbonToGajiMaster($request->nik);

        return redirect('/pinjaman/' . $id)->with(['success' => 'Data Pinjaman / Kasbon (' . $nomorPinjaman . ') berhasil ditambahkan dan disinkronkan ke Master Gaji!']);
    }

    /**
     * Admin: View loan detail & installment history.
     */
    public function show($id)
    {
        FiturTambahanSchemaService::ensureTablesExist();

        $pinjaman = DB::table('pinjaman_pegawai')
            ->join('karyawan', 'pinjaman_pegawai.nik', '=', 'karyawan.nik')
            ->leftJoin('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->leftJoin('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->where('pinjaman_pegawai.id', $id)
            ->select(
                'pinjaman_pegawai.*',
                'karyawan.nama_lengkap',
                'karyawan.jabatan',
                'karyawan.no_hp',
                'cabang.nama_cabang',
                'departemen.nama_dept'
            )
            ->first();

        if (!$pinjaman) {
            return redirect('/pinjaman')->with(['warning' => 'Data pinjaman tidak ditemukan.']);
        }

        $angsuran = DB::table('pinjaman_angsuran')
            ->where('pinjaman_pegawai_id', $id)
            ->orderBy('angsuran_ke', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return view('pinjaman.show', compact('pinjaman', 'angsuran'));
    }

    /**
     * Admin: Record an installment payment (`pinjaman_angsuran`).
     */
    public function storeAngsuran(Request $request, $id)
    {
        FiturTambahanSchemaService::ensureTablesExist();

        $pinjaman = DB::table('pinjaman_pegawai')->where('id', $id)->first();
        if (!$pinjaman) {
            return redirect('/pinjaman')->with(['warning' => 'Data pinjaman tidak ditemukan.']);
        }

        $jumlahBayar = (float) str_replace(['.', ','], '', $request->jumlah_bayar ?? 0);
        if ($jumlahBayar <= 0) {
            return redirect()->back()->with(['warning' => 'Nominal angsuran harus lebih dari Rp 0.']);
        }

        if ($jumlahBayar > (float) $pinjaman->sisa_pinjaman) {
            $jumlahBayar = (float) $pinjaman->sisa_pinjaman;
        }

        $countExisting = DB::table('pinjaman_angsuran')
            ->where('pinjaman_pegawai_id', $id)
            ->count();
        $angsuranKe = $request->filled('angsuran_ke') ? (int) $request->angsuran_ke : ($countExisting + 1);

        $nomorTransaksi = sprintf('ANG-%s-%02d', str_replace('KSB-', '', $pinjaman->nomor_pinjaman), $angsuranKe);

        DB::table('pinjaman_angsuran')->insert([
            'pinjaman_pegawai_id' => $id,
            'nomor_transaksi' => $nomorTransaksi,
            'angsuran_ke' => $angsuranKe,
            'tanggal_bayar' => $request->tanggal_bayar ?: date('Y-m-d'),
            'jumlah_bayar' => $jumlahBayar,
            'metode_pembayaran' => $request->metode_pembayaran ?: 'potong_gaji',
            'keterangan' => $request->keterangan ?: ('Pembayaran cicilan ke-' . $angsuranKe),
            'diterima_oleh' => Auth::guard('user')->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->recalculateLoanBalance($id);

        return redirect('/pinjaman/' . $id)->with(['success' => 'Pembayaran angsuran ke-' . $angsuranKe . ' sebesar Rp ' . number_format($jumlahBayar, 0, ',', '.') . ' berhasil dicatat!']);
    }

    /**
     * Admin: Delete an installment record and recalculate loan balance.
     */
    public function deleteAngsuran($angsuranId)
    {
        FiturTambahanSchemaService::ensureTablesExist();

        $row = DB::table('pinjaman_angsuran')->where('id', $angsuranId)->first();
        if (!$row) {
            return redirect()->back()->with(['warning' => 'Data angsuran tidak ditemukan.']);
        }

        $pinjamanId = $row->pinjaman_pegawai_id;
        DB::table('pinjaman_angsuran')->where('id', $angsuranId)->delete();
        $this->recalculateLoanBalance($pinjamanId);

        return redirect('/pinjaman/' . $pinjamanId)->with(['success' => 'Data angsuran berhasil dihapus dan saldo pinjaman diperbarui.']);
    }

    /**
     * Admin: Delete an entire loan record.
     */
    public function destroy($id)
    {
        FiturTambahanSchemaService::ensureTablesExist();

        $pinjaman = DB::table('pinjaman_pegawai')->where('id', $id)->first();
        if (!$pinjaman) {
            return redirect('/pinjaman')->with(['warning' => 'Data pinjaman tidak ditemukan.']);
        }

        $nik = $pinjaman->nik;
        DB::table('pinjaman_angsuran')->where('pinjaman_pegawai_id', $id)->delete();
        DB::table('pinjaman_pegawai')->where('id', $id)->delete();

        $this->syncKasbonToGajiMaster($nik);

        return redirect('/pinjaman')->with(['success' => 'Data pinjaman ' . $pinjaman->nomor_pinjaman . ' berhasil dihapus.']);
    }

    /**
     * Recalculate `total_dibayar`, `sisa_pinjaman`, and `status` for a loan.
     */
    private function recalculateLoanBalance($pinjamanId): void
    {
        $pinjaman = DB::table('pinjaman_pegawai')->where('id', $pinjamanId)->first();
        if (!$pinjaman) {
            return;
        }

        $totalDibayar = (float) DB::table('pinjaman_angsuran')
            ->where('pinjaman_pegawai_id', $pinjamanId)
            ->sum('jumlah_bayar');

        $sisa = max(0, (float) $pinjaman->jumlah_pinjaman - $totalDibayar);
        $status = ($sisa <= 0) ? 'lunas' : 'berjalan';

        DB::table('pinjaman_pegawai')->where('id', $pinjamanId)->update([
            'total_dibayar' => $totalDibayar,
            'sisa_pinjaman' => $sisa,
            'status' => $status,
            'updated_at' => now(),
        ]);

        $this->syncKasbonToGajiMaster($pinjaman->nik);
    }

    /**
     * Employee Mobile Portal: View own loans & installment history.
     */
    public function karyawanIndex()
    {
        FiturTambahanSchemaService::ensureTablesExist();

        $user = Auth::guard('karyawan')->user();
        $allNiks = FiturTambahanSchemaService::getAllNiksForKaryawan($user->nik);

        $pinjaman = DB::table('pinjaman_pegawai')
            ->whereIn('nik', $allNiks)
            ->orderByRaw("CASE WHEN status = 'berjalan' THEN 0 ELSE 1 END")
            ->orderBy('tanggal_pinjaman', 'desc')
            ->get();

        $angsuranByPinjaman = [];
        if ($pinjaman->isNotEmpty()) {
            $ids = $pinjaman->pluck('id')->toArray();
            $allAngsuran = DB::table('pinjaman_angsuran')
                ->whereIn('pinjaman_pegawai_id', $ids)
                ->orderBy('angsuran_ke', 'asc')
                ->get();
            foreach ($allAngsuran as $ang) {
                $angsuranByPinjaman[$ang->pinjaman_pegawai_id][] = $ang;
            }
        }

        return view('pinjaman.karyawan_index', compact('pinjaman', 'angsuranByPinjaman', 'user'));
    }
}
