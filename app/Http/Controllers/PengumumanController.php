<?php

namespace App\Http\Controllers;

use App\Services\FiturTambahanSchemaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PengumumanController extends Controller
{
    /**
     * Admin: List all announcements with read-receipt statistics.
     */
    public function index(Request $request)
    {
        FiturTambahanSchemaService::ensureTablesExist();

        $query = DB::table('pengumuman')
            ->leftJoin('users', 'pengumuman.created_by', '=', 'users.id')
            ->select(
                'pengumuman.*',
                'users.name as pembuat',
                DB::raw('(SELECT COUNT(*) FROM pengumuman_pegawai WHERE pengumuman_pegawai.pengumuman_id = pengumuman.id AND pengumuman_pegawai.is_dibaca = 1) as jumlah_pembaca')
            )
            ->orderBy('pengumuman.tanggal_tayang', 'desc')
            ->orderBy('pengumuman.id', 'desc');

        if ($request->filled('kategori')) {
            $query->where('pengumuman.kategori', $request->kategori);
        }

        if ($request->filled('cari')) {
            $query->where(function ($q) use ($request) {
                $q->where('pengumuman.judul', 'like', '%' . $request->cari . '%')
                    ->orWhere('pengumuman.isi', 'like', '%' . $request->cari . '%');
            });
        }

        $pengumuman = $query->paginate(15)->withQueryString();
        $totalKaryawan = DB::table('karyawan')->select(DB::raw('COUNT(DISTINCT UPPER(TRIM(nama_lengkap))) as jml'))->value('jml') ?? 0;

        return view('pengumuman.index', compact('pengumuman', 'totalKaryawan'));
    }

    /**
     * Admin: Store a new announcement.
     */
    public function store(Request $request)
    {
        FiturTambahanSchemaService::ensureTablesExist();

        $request->validate([
            'judul' => 'required|string|max:180',
            'isi' => 'required|string',
            'kategori' => 'required|in:umum,penting,kebijakan,agenda,darurat',
            'prioritas' => 'required|in:rendah,normal,tinggi',
            'tanggal_tayang' => 'required|date',
        ]);

        $lampiranName = null;
        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');
            $ext = $file->getClientOriginalExtension();
            $lampiranName = 'pengumuman_' . time() . '_' . rand(100, 999) . '.' . $ext;
            $dest = public_path('uploads/pengumuman');
            if (!is_dir($dest)) {
                @mkdir($dest, 0775, true);
            }
            $file->move($dest, $lampiranName);
        }

        DB::table('pengumuman')->insert([
            'judul' => $request->judul,
            'isi' => $request->isi,
            'kategori' => $request->kategori,
            'prioritas' => $request->prioritas,
            'lampiran' => $lampiranName,
            'tanggal_tayang' => $request->tanggal_tayang,
            'tanggal_berakhir' => $request->tanggal_berakhir ?: null,
            'status_aktif' => $request->has('status_aktif') ? (int) $request->status_aktif : 1,
            'created_by' => Auth::guard('user')->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect('/pengumuman')->with(['success' => 'Pengumuman berhasil diterbitkan ke aplikasi Guru/Karyawan!']);
    }

    /**
     * Admin: Show announcement detail & list of employees who have read it.
     */
    public function show($id)
    {
        FiturTambahanSchemaService::ensureTablesExist();

        $item = DB::table('pengumuman')
            ->leftJoin('users', 'pengumuman.created_by', '=', 'users.id')
            ->where('pengumuman.id', $id)
            ->select('pengumuman.*', 'users.name as pembuat')
            ->first();

        if (!$item) {
            return redirect('/pengumuman')->with(['warning' => 'Pengumuman tidak ditemukan.']);
        }

        $pembaca = DB::table('pengumuman_pegawai')
            ->join('karyawan', 'pengumuman_pegawai.nik', '=', 'karyawan.nik')
            ->leftJoin('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->where('pengumuman_pegawai.pengumuman_id', $id)
            ->where('pengumuman_pegawai.is_dibaca', 1)
            ->select('pengumuman_pegawai.*', 'karyawan.nama_lengkap', 'karyawan.jabatan', 'cabang.nama_cabang')
            ->orderBy('pengumuman_pegawai.dibaca_pada', 'desc')
            ->get();

        return view('pengumuman.show', compact('item', 'pembaca'));
    }

    /**
     * Admin: Toggle active status or update announcement.
     */
    public function update(Request $request, $id)
    {
        FiturTambahanSchemaService::ensureTablesExist();

        $item = DB::table('pengumuman')->where('id', $id)->first();
        if (!$item) {
            return redirect('/pengumuman')->with(['warning' => 'Pengumuman tidak ditemukan.']);
        }

        if ($request->has('toggle_status')) {
            $newStatus = $item->status_aktif ? 0 : 1;
            DB::table('pengumuman')->where('id', $id)->update([
                'status_aktif' => $newStatus,
                'updated_at' => now(),
            ]);
            return redirect()->back()->with(['success' => 'Status pengumuman berhasil diperbarui.']);
        }

        DB::table('pengumuman')->where('id', $id)->update([
            'judul' => $request->judul ?? $item->judul,
            'isi' => $request->isi ?? $item->isi,
            'kategori' => $request->kategori ?? $item->kategori,
            'prioritas' => $request->prioritas ?? $item->prioritas,
            'tanggal_tayang' => $request->tanggal_tayang ?? $item->tanggal_tayang,
            'tanggal_berakhir' => $request->tanggal_berakhir ?: null,
            'status_aktif' => $request->has('status_aktif') ? (int) $request->status_aktif : $item->status_aktif,
            'updated_at' => now(),
        ]);

        return redirect('/pengumuman')->with(['success' => 'Data pengumuman berhasil diperbarui.']);
    }

    /**
     * Admin: Delete announcement.
     */
    public function destroy($id)
    {
        FiturTambahanSchemaService::ensureTablesExist();

        $item = DB::table('pengumuman')->where('id', $id)->first();
        if ($item) {
            if (!empty($item->lampiran)) {
                $path = public_path('uploads/pengumuman/' . $item->lampiran);
                if (file_exists($path)) {
                    @unlink($path);
                }
            }
            DB::table('pengumuman_pegawai')->where('pengumuman_id', $id)->delete();
            DB::table('pengumuman')->where('id', $id)->delete();
        }

        return redirect('/pengumuman')->with(['success' => 'Pengumuman berhasil dihapus.']);
    }

    /**
     * Employee Mobile Portal: List active announcements.
     */
    public function karyawanIndex()
    {
        FiturTambahanSchemaService::ensureTablesExist();

        $user = Auth::guard('karyawan')->user();
        $allNiks = FiturTambahanSchemaService::getAllNiksForKaryawan($user->nik);
        $today = date('Y-m-d');

        $pengumuman = DB::table('pengumuman')
            ->where('status_aktif', 1)
            ->whereDate('tanggal_tayang', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('tanggal_berakhir')
                    ->orWhereDate('tanggal_berakhir', '>=', $today);
            })
            ->orderByRaw("CASE WHEN prioritas = 'tinggi' THEN 0 WHEN prioritas = 'normal' THEN 1 ELSE 2 END")
            ->orderBy('tanggal_tayang', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $dibacaIds = DB::table('pengumuman_pegawai')
            ->whereIn('nik', $allNiks)
            ->where('is_dibaca', 1)
            ->pluck('pengumuman_id')
            ->toArray();

        return view('pengumuman.karyawan_index', compact('pengumuman', 'dibacaIds', 'user'));
    }

    /**
     * Employee Mobile Portal: Read announcement detail & mark as read.
     */
    public function karyawanShow($id)
    {
        FiturTambahanSchemaService::ensureTablesExist();

        $user = Auth::guard('karyawan')->user();
        $allNiks = FiturTambahanSchemaService::getAllNiksForKaryawan($user->nik);

        $item = DB::table('pengumuman')->where('id', $id)->first();
        if (!$item) {
            return redirect('/presensi/pengumuman')->with(['warning' => 'Pengumuman tidak ditemukan.']);
        }

        // Mark as read
        try {
            $exists = DB::table('pengumuman_pegawai')
                ->where('pengumuman_id', $id)
                ->whereIn('nik', $allNiks)
                ->first();

            if (!$exists) {
                DB::table('pengumuman_pegawai')->insert([
                    'pengumuman_id' => $id,
                    'nik' => $user->nik,
                    'is_dibaca' => 1,
                    'dibaca_pada' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } elseif (!$exists->is_dibaca) {
                DB::table('pengumuman_pegawai')->where('id', $exists->id)->update([
                    'is_dibaca' => 1,
                    'dibaca_pada' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
        }

        return view('pengumuman.karyawan_show', compact('item', 'user'));
    }
}
