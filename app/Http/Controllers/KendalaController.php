<?php

namespace App\Http\Controllers;

use App\Services\FiturTambahanSchemaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KendalaController extends Controller
{
    /**
     * Employee Mobile Portal: List own attendance issue tickets.
     */
    public function karyawanIndex()
    {
        FiturTambahanSchemaService::ensureTablesExist();

        $user = Auth::guard('karyawan')->user();
        $allNiks = FiturTambahanSchemaService::getAllNiksForKaryawan($user->nik);

        $tiket = DB::table('pengajuan_masalah')
            ->whereIn('nik', $allNiks)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return view('kendala.karyawan_index', compact('tiket', 'user'));
    }

    /**
     * Employee Mobile Portal: Show form to report attendance issue.
     */
    public function karyawanCreate()
    {
        FiturTambahanSchemaService::ensureTablesExist();
        $user = Auth::guard('karyawan')->user();
        return view('kendala.karyawan_create', compact('user'));
    }

    /**
     * Employee Mobile Portal: Store new attendance issue ticket.
     */
    public function karyawanStore(Request $request)
    {
        FiturTambahanSchemaService::ensureTablesExist();

        $request->validate([
            'kategori_masalah' => 'required|string',
            'tanggal_kejadian' => 'required|date',
            'deskripsi' => 'required|string',
        ]);

        $user = Auth::guard('karyawan')->user();
        $ymd = date('Ymd');
        $countToday = DB::table('pengajuan_masalah')
            ->where('nomor_tiket', 'like', 'TKT-' . $ymd . '-%')
            ->count();
        $nomorTiket = sprintf('TKT-%s-%03d', $ymd, $countToday + 1);
        while (DB::table('pengajuan_masalah')->where('nomor_tiket', $nomorTiket)->exists()) {
            $countToday++;
            $nomorTiket = sprintf('TKT-%s-%03d', $ymd, $countToday + 1);
        }

        $fotoName = null;
        if ($request->hasFile('foto_bukti')) {
            $file = $request->file('foto_bukti');
            $ext = $file->getClientOriginalExtension();
            $fotoName = 'kendala_' . $user->nik . '_' . time() . '.' . $ext;
            $dest = public_path('uploads/kendala');
            if (!is_dir($dest)) {
                @mkdir($dest, 0775, true);
            }
            $file->move($dest, $fotoName);
        }

        $labelMap = [
            'lupa_absen_masuk' => 'Lupa Absen Masuk',
            'lupa_absen_pulang' => 'Lupa Absen Pulang',
            'aplikasi_error' => 'Aplikasi / Sistem Error',
            'gps_tidak_akurat' => 'Kendala Titik GPS / Lokasi',
            'kamera_error' => 'Kendala Kamera Selfie',
            'perubahan_jam_kerja' => 'Perubahan / Tukar Jam Kerja',
            'lainnya' => 'Kendala Presensi Lainnya',
        ];
        $judul = $request->judul ?: ($labelMap[$request->kategori_masalah] ?? 'Laporan Kendala Presensi');

        DB::table('pengajuan_masalah')->insert([
            'nomor_tiket' => $nomorTiket,
            'nik' => $user->nik,
            'kategori_masalah' => $request->kategori_masalah,
            'judul' => $judul,
            'tanggal_kejadian' => $request->tanggal_kejadian,
            'jam_kejadian' => $request->jam_kejadian ?: date('H:i:s'),
            'deskripsi' => $request->deskripsi,
            'foto_bukti' => $fotoName,
            'status' => 'diajukan',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect('/presensi/kendala')->with(['success' => 'Laporan kendala absen (' . $nomorTiket . ') berhasil dikirim ke Admin!']);
    }

    /**
     * Admin: List all attendance issue tickets (`pengajuan_masalah`).
     */
    public function adminIndex(Request $request)
    {
        FiturTambahanSchemaService::ensureTablesExist();

        $query = DB::table('pengajuan_masalah')
            ->join('karyawan', 'pengajuan_masalah.nik', '=', 'karyawan.nik')
            ->leftJoin('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->leftJoin('users', 'pengajuan_masalah.diproses_oleh_user_id', '=', 'users.id')
            ->select(
                'pengajuan_masalah.*',
                'karyawan.nama_lengkap',
                'karyawan.jabatan',
                'karyawan.kode_cabang',
                'cabang.nama_cabang',
                'users.name as nama_admin'
            )
            ->orderByRaw("CASE WHEN pengajuan_masalah.status = 'diajukan' THEN 0 WHEN pengajuan_masalah.status = 'diproses' THEN 1 ELSE 2 END")
            ->orderBy('pengajuan_masalah.created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('pengajuan_masalah.status', $request->status);
        }

        if ($request->filled('kode_cabang')) {
            $query->where('karyawan.kode_cabang', $request->kode_cabang);
        }

        if ($request->filled('cari')) {
            $s = $request->cari;
            $query->where(function ($q) use ($s) {
                $q->where('karyawan.nama_lengkap', 'like', '%' . $s . '%')
                    ->orWhere('pengajuan_masalah.nik', 'like', '%' . $s . '%')
                    ->orWhere('pengajuan_masalah.nomor_tiket', 'like', '%' . $s . '%');
            });
        }

        $tiket = $query->paginate(20)->withQueryString();

        $stats = [
            'diajukan' => DB::table('pengajuan_masalah')->where('status', 'diajukan')->count(),
            'diproses' => DB::table('pengajuan_masalah')->where('status', 'diproses')->count(),
            'selesai' => DB::table('pengajuan_masalah')->where('status', 'selesai')->count(),
            'ditolak' => DB::table('pengajuan_masalah')->where('status', 'ditolak')->count(),
        ];

        $cabang = DB::table('cabang')->orderBy('nama_cabang')->get();
        $jamkerja = DB::table('jam_kerja')->orderBy('kode_jam_kerja')->get();

        return view('kendala.admin_index', compact('tiket', 'stats', 'cabang', 'jamkerja'));
    }

    /**
     * Admin: Process/resolve an attendance issue ticket, with optional 1-click attendance correction.
     */
    public function adminProses(Request $request, $id)
    {
        FiturTambahanSchemaService::ensureTablesExist();

        $tiket = DB::table('pengajuan_masalah')->where('id', $id)->first();
        if (!$tiket) {
            return redirect()->back()->with(['warning' => 'Tiket kendala tidak ditemukan.']);
        }

        $adminUser = Auth::guard('user')->user();
        $status = $request->status ?: 'selesai';
        $tindakan = $request->tindakan_admin ?: 'tanpa_perubahan_data';

        DB::table('pengajuan_masalah')->where('id', $id)->update([
            'status' => $status,
            'tindakan_admin' => $tindakan,
            'catatan_admin' => $request->catatan_admin,
            'diproses_oleh_user_id' => $adminUser ? $adminUser->id : null,
            'tanggal_diproses' => now(),
            'updated_at' => now(),
        ]);

        // Optional 1-click attendance update if Admin opted to apply correction directly
        if ($status === 'selesai' && in_array($tindakan, ['koreksi_absen_masuk', 'koreksi_absen_pulang', 'koreksi_penuh'])) {
            try {
                $existingPresensi = DB::table('presensi')
                    ->where('nik', $tiket->nik)
                    ->where('tgl_presensi', $tiket->tanggal_kejadian)
                    ->first();

                $jamInBaru = $request->filled('jam_in_koreksi')
                    ? $request->jam_in_koreksi
                    : ($existingPresensi->jam_in ?? ($tiket->jam_kejadian ?: '07:15:00'));

                $jamOutBaru = $request->filled('jam_out_koreksi')
                    ? $request->jam_out_koreksi
                    : ($existingPresensi->jam_out ?? ($tiket->jam_kejadian ?: '15:30:00'));

                $kodeJk = $request->filled('kode_jam_kerja')
                    ? $request->kode_jam_kerja
                    : ($existingPresensi->kode_jam_kerja ?? (DB::table('jam_kerja')->value('kode_jam_kerja') ?? 'JK01'));

                $sebelum = $existingPresensi ? json_encode([
                    'jam_in' => $existingPresensi->jam_in,
                    'jam_out' => $existingPresensi->jam_out,
                    'kode_jam_kerja' => $existingPresensi->kode_jam_kerja,
                    'status' => $existingPresensi->status ?? 'h',
                ]) : 'Belum ada data presensi';

                if ($existingPresensi) {
                    $updateFields = ['kode_jam_kerja' => $kodeJk];
                    if ($tindakan === 'koreksi_absen_masuk' || $tindakan === 'koreksi_penuh') {
                        $updateFields['jam_in'] = $jamInBaru;
                    }
                    if ($tindakan === 'koreksi_absen_pulang' || $tindakan === 'koreksi_penuh') {
                        $updateFields['jam_out'] = $jamOutBaru;
                    }
                    DB::table('presensi')
                        ->where('id', $existingPresensi->id)
                        ->update($updateFields);
                } else {
                    DB::table('presensi')->insert([
                        'nik' => $tiket->nik,
                        'tgl_presensi' => $tiket->tanggal_kejadian,
                        'jam_in' => $jamInBaru,
                        'jam_out' => $jamOutBaru,
                        'kode_jam_kerja' => $kodeJk,
                        'status' => 'h',
                    ]);
                }

                $sesudah = json_encode([
                    'jam_in' => ($tindakan === 'koreksi_absen_masuk' || $tindakan === 'koreksi_penuh' || !$existingPresensi) ? $jamInBaru : ($existingPresensi->jam_in ?? null),
                    'jam_out' => ($tindakan === 'koreksi_absen_pulang' || $tindakan === 'koreksi_penuh' || !$existingPresensi) ? $jamOutBaru : ($existingPresensi->jam_out ?? null),
                    'kode_jam_kerja' => $kodeJk,
                    'tiket' => $tiket->nomor_tiket,
                ]);

                DB::table('revisi_absensi')->insert([
                    'nik' => $tiket->nik,
                    'tgl_presensi' => $tiket->tanggal_kejadian,
                    'jenis_tindakan' => 'tiket_kendala',
                    'user_id_eksekutor' => $adminUser ? $adminUser->id : null,
                    'nama_eksekutor' => $adminUser ? $adminUser->name : 'Admin',
                    'alasan' => 'Penyelesaian Tiket ' . $tiket->nomor_tiket . ' (' . $tiket->judul . '): ' . ($request->catatan_admin ?? '-'),
                    'data_sebelumnya' => $sebelum,
                    'data_perubahan' => $sesudah,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $e) {
            }
        }

        return redirect()->back()->with(['success' => 'Tiket ' . $tiket->nomor_tiket . ' berhasil diperbarui!']);
    }

    /**
     * Admin: View Audit Log of Attendance Corrections (`revisi_absensi`).
     */
    public function logKoreksi(Request $request)
    {
        FiturTambahanSchemaService::ensureTablesExist();

        $query = DB::table('revisi_absensi')
            ->leftJoin('karyawan', 'revisi_absensi.nik', '=', 'karyawan.nik')
            ->leftJoin('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->select(
                'revisi_absensi.*',
                'karyawan.nama_lengkap',
                'karyawan.jabatan',
                'cabang.nama_cabang'
            )
            ->orderBy('revisi_absensi.created_at', 'desc')
            ->orderBy('revisi_absensi.id', 'desc');

        if ($request->filled('cari')) {
            $s = $request->cari;
            $query->where(function ($q) use ($s) {
                $q->where('karyawan.nama_lengkap', 'like', '%' . $s . '%')
                    ->orWhere('revisi_absensi.nik', 'like', '%' . $s . '%')
                    ->orWhere('revisi_absensi.nama_eksekutor', 'like', '%' . $s . '%')
                    ->orWhere('revisi_absensi.alasan', 'like', '%' . $s . '%');
            });
        }

        if ($request->filled('kode_cabang')) {
            $query->where('karyawan.kode_cabang', $request->kode_cabang);
        }

        $logs = $query->paginate(25)->withQueryString();
        $cabang = DB::table('cabang')->orderBy('nama_cabang')->get();

        return view('kendala.log_koreksi', compact('logs', 'cabang'));
    }
}
