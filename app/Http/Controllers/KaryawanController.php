<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class KaryawanController extends Controller
{
    public function index(Request $request)
    {
        if (!\Illuminate\Support\Facades\Schema::hasColumn('karyawan', 'role_jam_kerja')) {
            \Illuminate\Support\Facades\Schema::table('karyawan', function ($table) {
                $table->string('role_jam_kerja')->nullable()->default('Normal');
            });
        }

        try {
            DB::table('karyawan')
                ->where(function ($q) {
                    $q->where('nama_lengkap', 'like', '%CINDY%')
                      ->orWhere('nama_lengkap', 'like', '%MAGDALENA%')
                      ->orWhere('nama_lengkap', 'like', '%MAHDALENA%')
                      ->orWhere('nama_lengkap', 'like', '%TITIN%')
                      ->orWhere('nama_lengkap', 'like', '%ROSHELLA%');
                })
                ->where('status_location', '!=', 0)
                ->update(['status_location' => 0]);
        } catch (\Throwable $e) {
            // Silently ignore
        }
        $kode_dept = Auth::guard('user')->user()->kode_dept;
        $kode_cabang = Auth::guard('user')->user()->kode_cabang;
        $user = User::find(Auth::guard('user')->user()->id);

        $query = Karyawan::query();
        $query->select('karyawan.*', 'nama_dept');
        $query->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept');
        $query->orderBy('nama_lengkap');
        if (!empty($request->nama_karyawan)) {
            $query->where('nama_lengkap', 'like', '%' . $request->nama_karyawan . '%');
        }

        if (!empty($request->kode_dept)) {
            $query->where('karyawan.kode_dept', $request->kode_dept);
        }

        if (!empty($request->kode_cabang)) {
            $query->where('karyawan.kode_cabang', $request->kode_cabang);
        }

        if ($user->hasRole('admin departemen')) {
            $query->where('karyawan.kode_dept', $kode_dept);
            $query->where('karyawan.kode_cabang', $kode_cabang);
        }
        $karyawan = $query->paginate(10);

        $departemen = DB::table('departemen')->get();
        $cabang = DB::table('cabang')->orderBy('kode_cabang')->get();
        return view('karyawan.index', compact('karyawan', 'departemen', 'cabang'));
    }

    public function store(Request $request)
    {
        $nik = $request->nik;
        $nama_lengkap = $request->nama_lengkap;
        $jabatan = $request->jabatan;
        $role_jam_kerja = $request->role_jam_kerja;
        $no_hp = $request->no_hp;
        $kode_dept = $request->kode_dept;
        $password = !empty($request->password) ? Hash::make($request->password) : Hash::make('12345');
        $kode_cabang = $request->kode_cabang;
        if ($request->hasFile('foto')) {
            $foto = $nik . "." . $request->file('foto')->getClientOriginalExtension();
        } else {
            $foto = null;
        }

        try {
            $data =  [
                'nik' => $nik,
                'nama_lengkap' => $nama_lengkap,
                'jabatan' => $jabatan,
                'role_jam_kerja' => $role_jam_kerja,
                'no_hp' => $no_hp,
                'kode_dept' => $kode_dept,
                'foto' => $foto,
                'password' => $password,
                'kode_cabang' => $kode_cabang
            ];
            $simpan = DB::table('karyawan')->insert($data);
            if ($simpan) {
                if ($request->hasFile('foto')) {
                    $folderPath = "public/uploads/karyawan/";
                    $request->file('foto')->storeAs($folderPath, $foto);
                }

                // Simpan atau sinkronkan komponen Master Gaji Karyawan
                $gapok = (float) str_replace(['.', ','], '', $request->gaji_pokok ?? 1200000);
                $tunjTransport = (float) str_replace(['.', ','], '', $request->tunjangan_transportasi ?? 150000);
                $tunjJabatan = (float) str_replace(['.', ','], '', $request->tunjangan_jabatan ?? 0);
                $tunjKonsumsi = (float) str_replace(['.', ','], '', $request->tunjangan_konsumsi ?? 0);
                $honorKegiatan = (float) str_replace(['.', ','], '', $request->tarif_honor_kegiatan ?? 0);
                $honorEkskul = (float) str_replace(['.', ','], '', $request->tarif_ekskul ?? 0);
                $tarifLembur = (float) str_replace(['.', ','], '', $request->tarif_lembur ?? 0);
                $bpjsKesehatan = (float) str_replace(['.', ','], '', $request->bpjs_kesehatan ?? 0);
                $potKasbon = (float) str_replace(['.', ','], '', $request->potongan_kasbon ?? 0);
                $potLainnya = (float) str_replace(['.', ','], '', $request->potongan_lainnya ?? 0);
                $insentifPagi = (float) str_replace(['.', ','], '', $request->insentif_pagi ?? 0);
                $hakPoolSpp = (float) str_replace(['.', ','], '', $request->hak_pool_spp ?? 0);
                $gajiHarian = round(($gapok + $tunjTransport) / 26, 2);

                $masterData = [
                    'nik' => $nik,
                    'gaji_pokok' => $gapok,
                    'tunjangan_transportasi' => $tunjTransport,
                    'gaji_harian' => $gajiHarian,
                    'tunjangan_jabatan' => $tunjJabatan,
                    'ket_tunjangan_jabatan' => $request->ket_tunjangan_jabatan ?? null,
                    'tunjangan_konsumsi' => $tunjKonsumsi,
                    'ket_tunjangan_konsumsi' => $request->ket_tunjangan_konsumsi ?? null,
                    'tarif_honor_kegiatan' => $honorKegiatan,
                    'ket_honor_kegiatan' => $request->ket_honor_kegiatan ?? null,
                    'tarif_ekskul' => $honorEkskul,
                    'ket_honor_ekskul' => $request->ket_honor_ekskul ?? null,
                    'tarif_lembur' => $tarifLembur,
                    'ket_tarif_lembur' => $request->ket_tarif_lembur ?? null,
                    'bpjs_kesehatan' => $bpjsKesehatan,
                    'potongan_kasbon' => $potKasbon,
                    'ket_potongan_kasbon' => $request->ket_potongan_kasbon ?? null,
                    'potongan_lainnya' => $potLainnya,
                    'ket_potongan_lainnya' => $request->ket_potongan_lainnya ?? null,
                    'insentif_pagi' => $insentifPagi,
                    'hak_pool_spp' => $hakPoolSpp,
                    'updated_at' => now(),
                ];

                $existsMaster = DB::table('gaji_master')->where('nik', $nik)->first();
                if ($existsMaster) {
                    DB::table('gaji_master')->where('nik', $nik)->update($masterData);
                } else {
                    $masterData['created_at'] = now();
                    DB::table('gaji_master')->insert($masterData);
                }

                // Sinkronkan ke seluruh NIK jika nama karyawan sama di cabang lain
                $allNiks = DB::table('karyawan')
                    ->whereRaw('TRIM(UPPER(nama_lengkap)) = ?', [trim(strtoupper($nama_lengkap))])
                    ->pluck('nik')
                    ->toArray();
                foreach ($allNiks as $otherNik) {
                    if ($otherNik != $nik) {
                        $otherData = $masterData;
                        $otherData['nik'] = $otherNik;
                        $existsOther = DB::table('gaji_master')->where('nik', $otherNik)->first();
                        if ($existsOther) {
                            DB::table('gaji_master')->where('nik', $otherNik)->update($otherData);
                        } else {
                            $otherData['created_at'] = now();
                            DB::table('gaji_master')->insert($otherData);
                        }
                    }
                }

                // Otomatis sinkronkan karyawan baru ke Data Penggajian aktif
                try {
                    \App\Http\Controllers\GajiController::syncMissingEmployeesToActivePeriods();
                } catch (\Throwable $ex) {}

                return Redirect::back()->with(['success' => 'Data Karyawan & Master Gaji Berhasil Disimpan']);
            }
        } catch (\Exception $e) {

            if ($e->getCode() == 23000) {
                $message = "Data dengan Nik " . $nik . " Sudah Ada";
            } else {
                $message = "Hubungi IT";
            }
            return Redirect::back()->with(['warning' => 'Data Gagal Disimpan ' . $message]);
        }
    }

    public function edit(Request $request)
    {
        $nik = $request->nik;
        $departemen = DB::table('departemen')->get();
        $cabang = DB::table('cabang')->orderBy('kode_cabang')->get();
        $karyawan = DB::table('karyawan')->where('nik', $nik)->first();
        $gajiMaster = DB::table('gaji_master')->where('nik', $nik)->first();
        if (!$gajiMaster && $karyawan && !empty($karyawan->nama_lengkap)) {
            $gajiMaster = DB::table('gaji_master')
                ->join('karyawan', 'gaji_master.nik', '=', 'karyawan.nik')
                ->whereRaw('TRIM(UPPER(karyawan.nama_lengkap)) = ?', [trim(strtoupper($karyawan->nama_lengkap))])
                ->where('gaji_master.gaji_pokok', '>', 0)
                ->select('gaji_master.*')
                ->first();
        }
        return view('karyawan.edit', compact('departemen', 'karyawan', 'cabang', 'gajiMaster'));
    }

    public function update($nik, Request $request)
    {
        $nik = Crypt::decrypt($nik);
        $nik_baru = $request->nik_baru;
        $nama_lengkap = $request->nama_lengkap;
        $jabatan = $request->jabatan;
        $role_jam_kerja = $request->role_jam_kerja;
        $no_hp = $request->no_hp;
        $kode_dept = $request->kode_dept;
        $kode_cabang = $request->kode_cabang;
        $old_foto = $request->old_foto;
        if ($request->hasFile('foto')) {
            $foto = $nik . "." . $request->file('foto')->getClientOriginalExtension();
        } else {
            $foto = $old_foto;
        }


        $ceknik = DB::table('karyawan')
            ->where('nik', $nik_baru)
            ->where('nik', '!=', $nik)
            ->count();
        if ($ceknik > 0) {
            return Redirect::back()->with(['warning' => 'Nik Sudah Digunakan']);
        }
        try {
            $data =  [
                'nik' => $nik_baru,
                'nama_lengkap' => $nama_lengkap,
                'jabatan' => $jabatan,
                'role_jam_kerja' => $role_jam_kerja,
                'no_hp' => $no_hp,
                'kode_dept' => $kode_dept,
                'foto' => $foto,
                'kode_cabang' => $kode_cabang
            ];
            if (!empty($request->password)) {
                $data['password'] = Hash::make($request->password);
            }
            $update = DB::table('karyawan')->where('nik', $nik)->update($data);
            // update() mengembalikan jumlah baris yang BERUBAH (0 jika data karyawan sama persis),
            // sehingga jangan dipakai sebagai penanda gagal. Kegagalan sebenarnya ditangani lewat catch.
            if ($update !== false) {
                if ($request->hasFile('foto')) {
                    $folderPath = "public/uploads/karyawan/";
                    $folderPathOld = "public/uploads/karyawan/" . $old_foto;
                    Storage::delete($folderPathOld);
                    $request->file('foto')->storeAs($folderPath, $foto);
                }

                // Update / simpan pengaturan master gaji jika ada di form
                if ($request->has('gaji_pokok')) {
                    $gapok = (float) str_replace(['.', ','], '', $request->gaji_pokok ?? 0);
                    $tunjTransport = (float) str_replace(['.', ','], '', $request->tunjangan_transportasi ?? 0);
                    $tunjJabatan = (float) str_replace(['.', ','], '', $request->tunjangan_jabatan ?? 0);
                    $tunjKonsumsi = (float) str_replace(['.', ','], '', $request->tunjangan_konsumsi ?? 0);
                    $honorKegiatan = (float) str_replace(['.', ','], '', $request->tarif_honor_kegiatan ?? 0);
                    $honorEkskul = (float) str_replace(['.', ','], '', $request->tarif_ekskul ?? 0);
                    $tarifLembur = (float) str_replace(['.', ','], '', $request->tarif_lembur ?? 0);
                    $bpjsKesehatan = (float) str_replace(['.', ','], '', $request->bpjs_kesehatan ?? 0);
                    $potKasbon = (float) str_replace(['.', ','], '', $request->potongan_kasbon ?? 0);
                    $potLainnya = (float) str_replace(['.', ','], '', $request->potongan_lainnya ?? 0);
                    $insentifPagi = (float) str_replace(['.', ','], '', $request->insentif_pagi ?? 0);
                    $hakPoolSpp = (float) str_replace(['.', ','], '', $request->hak_pool_spp ?? 0);
                    $gajiHarian = round(($gapok + $tunjTransport) / 26, 2);

                    $allNiks = DB::table('karyawan')
                        ->whereRaw('TRIM(UPPER(nama_lengkap)) = ?', [trim(strtoupper($nama_lengkap))])
                        ->pluck('nik')
                        ->toArray();
                    if (!in_array($nik_baru, $allNiks)) {
                        $allNiks[] = $nik_baru;
                    }

                    foreach ($allNiks as $targetNik) {
                        $masterData = [
                            'gaji_pokok' => $gapok,
                            'tunjangan_transportasi' => $tunjTransport,
                            'gaji_harian' => $gajiHarian,
                            'tunjangan_jabatan' => $tunjJabatan,
                            'ket_tunjangan_jabatan' => $request->ket_tunjangan_jabatan ?? null,
                            'tunjangan_konsumsi' => $tunjKonsumsi,
                            'ket_tunjangan_konsumsi' => $request->ket_tunjangan_konsumsi ?? null,
                            'tarif_honor_kegiatan' => $honorKegiatan,
                            'ket_honor_kegiatan' => $request->ket_honor_kegiatan ?? null,
                            'tarif_ekskul' => $honorEkskul,
                            'ket_honor_ekskul' => $request->ket_honor_ekskul ?? null,
                            'tarif_lembur' => $tarifLembur,
                            'ket_tarif_lembur' => $request->ket_tarif_lembur ?? null,
                            'bpjs_kesehatan' => $bpjsKesehatan,
                            'potongan_kasbon' => $potKasbon,
                            'ket_potongan_kasbon' => $request->ket_potongan_kasbon ?? null,
                            'potongan_lainnya' => $potLainnya,
                            'ket_potongan_lainnya' => $request->ket_potongan_lainnya ?? null,
                            'insentif_pagi' => $insentifPagi,
                            'hak_pool_spp' => $hakPoolSpp,
                            'updated_at' => now(),
                        ];

                        $existsMaster = DB::table('gaji_master')->where('nik', $targetNik)->first();
                        if ($existsMaster) {
                            DB::table('gaji_master')->where('nik', $targetNik)->update($masterData);
                        } else {
                            $masterData['nik'] = $targetNik;
                            $masterData['created_at'] = now();
                            DB::table('gaji_master')->insert($masterData);
                        }
                    }
                }

                // Otomatis sinkronkan perubahan karyawan ke Data Penggajian aktif
                try {
                    \App\Http\Controllers\GajiController::syncMissingEmployeesToActivePeriods();
                } catch (\Throwable $ex) {}

                return Redirect::back()->with(['success' => 'Data Karyawan & Master Gaji Berhasil Diupdate']);
            }
        } catch (\Exception $e) {
            return Redirect::back()->with(['warning' => 'Data Gagal Diupdate']);
        }
    }

    public function delete($nik)
    {
        $delete = DB::table('karyawan')->where('nik', $nik)->delete();
        if ($delete) {
            return Redirect::back()->with(['success' => 'Data Berhasil Dihapus']);
        } else {
            return Redirect::back()->with(['warning' => 'Data Gagal Dihapus']);
        }
    }

    public function resetpassword($nik)
    {
        $nik = Crypt::decrypt($nik);
        $karyawan = DB::table('karyawan')->where('nik', $nik)->first();
        $password = Hash::make('12345');
        $reset = DB::table('karyawan')->where('nik', $nik)->update([
            'password' => $password
        ]);

        if ($reset) {
            $nama = $karyawan ? $karyawan->nama_lengkap : 'Karyawan';
            return Redirect::back()->with(['success' => 'Password untuk ' . $nama . ' berhasil di-reset kembali ke default: 12345']);
        } else {
            return Redirect::back()->with(['warning' => 'Data Password Gagal di Reset']);
        }
    }

    public function lockandunlocklocation($nik)
    {
        try {
            $karyawan = DB::table('karyawan')->where('nik', $nik)->first();
            $status_location = $karyawan->status_location;
            if ($status_location == '1') {
                DB::table('karyawan')->where('nik', $nik)->update([
                    'status_location' => '0'
                ]);
            } else {
                DB::table('karyawan')->where('nik', $nik)->update([
                    'status_location' => '1'
                ]);
            }

            return Redirect::back()->with(['success' => 'Status Location Berhasil Diupdate']);
        } catch (\Exception $e) {
            return Redirect::back()->with(['warning' => 'Status Location Gagal Diupdate']);
        }
    }

    public function lockandunlockjamkerja($nik)
    {
        try {
            $karyawan = DB::table('karyawan')->where('nik', $nik)->first();
            $status_jam_kerja = $karyawan->status_jam_kerja;
            if ($status_jam_kerja == '1') {
                DB::table('karyawan')->where('nik', $nik)->update([
                    'status_jam_kerja' => '0'
                ]);
            } else {
                DB::table('karyawan')->where('nik', $nik)->update([
                    'status_jam_kerja' => '1'
                ]);
            }

            return Redirect::back()->with(['success' => 'Status Jam Kerja Berhasil Diupdate']);
        } catch (\Exception $e) {
            return Redirect::back()->with(['warning' => 'Status Jam Kerja Gagal Diupdate']);
        }
    }
}
