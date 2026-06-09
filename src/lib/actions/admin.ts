'use server';

import { supabase, supabaseAdmin } from '@/lib/supabase';
import { getSession } from '@/lib/session';
import bcrypt from 'bcryptjs';

// Verify admin permissions helper
async function verifyAdmin() {
  const session = await getSession();
  if (!session || (session.role !== 'administrator' && session.role !== 'admin departemen')) {
    throw new Error('Unauthorized');
  }
  return session;
}

// ==========================================
// 1. KARYAWAN (EMPLOYEE) ACTIONS
// ==========================================

export async function createKaryawanAction(formData: FormData) {
  try {
    await verifyAdmin();
    const nik = formData.get('nik') as string;
    const nama_lengkap = formData.get('nama_lengkap') as string;
    const jabatan = formData.get('jabatan') as string;
    const no_hp = formData.get('no_hp') as string;
    const kode_dept = formData.get('kode_dept') as string;
    const kode_cabang = formData.get('kode_cabang') as string;
    const password = await bcrypt.hash('12345', 10); // default password

    if (!nik || !nama_lengkap || !jabatan || !no_hp || !kode_dept || !kode_cabang) {
      return { error: 'Semua kolom wajib diisi!' };
    }

    const { error } = await supabase
      .from('karyawan')
      .insert({
        nik,
        nama_lengkap,
        jabatan,
        no_hp,
        kode_dept,
        kode_cabang,
        password,
        status_location: 1, // locked by default
      });

    if (error) {
      if (error.code === '23505') {
        return { error: `NIK "${nik}" sudah digunakan.` };
      }
      return { error: 'Gagal menambahkan data karyawan.' };
    }

    return { success: true };
  } catch (err) {
    return { error: 'Terjadi kesalahan hak akses.' };
  }
}

export async function updateKaryawanAction(nik: string, formData: FormData) {
  try {
    await verifyAdmin();
    const nama_lengkap = formData.get('nama_lengkap') as string;
    const jabatan = formData.get('jabatan') as string;
    const no_hp = formData.get('no_hp') as string;
    const kode_dept = formData.get('kode_dept') as string;
    const kode_cabang = formData.get('kode_cabang') as string;

    if (!nama_lengkap || !jabatan || !no_hp || !kode_dept || !kode_cabang) {
      return { error: 'Semua kolom wajib diisi!' };
    }

    const { error } = await supabase
      .from('karyawan')
      .update({
        nama_lengkap,
        jabatan,
        no_hp,
        kode_dept,
        kode_cabang,
      })
      .eq('nik', nik);

    if (error) {
      return { error: 'Gagal memperbarui data karyawan.' };
    }

    return { success: true };
  } catch (err) {
    return { error: 'Terjadi kesalahan.' };
  }
}

export async function deleteKaryawanAction(nik: string) {
  try {
    await verifyAdmin();
    
    // Fetch profile to delete avatar photo
    const { data: karyawan } = await supabase
      .from('karyawan')
      .select('foto')
      .eq('nik', nik)
      .single();

    if (karyawan?.foto && supabaseAdmin) {
      await supabaseAdmin.storage
        .from('karyawan')
        .remove([karyawan.foto]);
    }

    const { error } = await supabase
      .from('karyawan')
      .delete()
      .eq('nik', nik);

    if (error) {
      return { error: 'Gagal menghapus karyawan.' };
    }

    return { success: true };
  } catch (err) {
    return { error: 'Terjadi kesalahan.' };
  }
}

export async function resetKaryawanPasswordAction(nik: string) {
  try {
    await verifyAdmin();
    const password = await bcrypt.hash('12345', 10);

    const { error } = await supabase
      .from('karyawan')
      .update({ password })
      .eq('nik', nik);

    if (error) {
      return { error: 'Gagal meriset password.' };
    }

    return { success: true };
  } catch (err) {
    return { error: 'Terjadi kesalahan.' };
  }
}

export async function toggleLockLocationAction(nik: string, currentStatus: number) {
  try {
    await verifyAdmin();
    const nextStatus = currentStatus === 1 ? 0 : 1;

    const { error } = await supabase
      .from('karyawan')
      .update({ status_location: nextStatus })
      .eq('nik', nik);

    if (error) {
      return { error: 'Gagal memperbarui status lokasi.' };
    }

    return { success: true };
  } catch (err) {
    return { error: 'Terjadi kesalahan.' };
  }
}

export async function toggleLockJamKerjaAction(nik: string, currentStatus: number) {
  try {
    await verifyAdmin();
    const nextStatus = currentStatus === 1 ? 0 : 1;

    const { error } = await supabase
      .from('karyawan')
      .update({ status_jam_kerja: nextStatus })
      .eq('nik', nik);

    if (error) {
      return { error: 'Gagal memperbarui status jam kerja.' };
    }

    return { success: true };
  } catch (err) {
    return { error: 'Terjadi kesalahan.' };
  }
}

// ==========================================
// 2. CABANG (BRANCH) ACTIONS
// ==========================================

export async function saveCabangAction(formData: FormData) {
  try {
    await verifyAdmin();
    const kode_cabang = formData.get('kode_cabang') as string;
    const nama_cabang = formData.get('nama_cabang') as string;
    const lokasi_cabang = formData.get('lokasi_cabang') as string; // Format: "lat,lng"
    const radius_cabang = parseInt(formData.get('radius_cabang') as string);
    const isEdit = formData.get('is_edit') === 'true';

    if (!kode_cabang || !nama_cabang || !lokasi_cabang || isNaN(radius_cabang)) {
      return { error: 'Semua kolom wajib diisi!' };
    }

    if (isEdit) {
      const { error } = await supabase
        .from('cabang')
        .update({
          nama_cabang,
          lokasi_cabang,
          radius_cabang,
        })
        .eq('kode_cabang', kode_cabang);
      
      if (error) return { error: 'Gagal memperbarui data kantor cabang.' };
    } else {
      const { error } = await supabase
        .from('cabang')
        .insert({
          text_code: kode_cabang, // map PK
          kode_cabang,
          nama_cabang,
          lokasi_cabang,
          radius_cabang,
        });

      if (error) {
        if (error.code === '23505') return { error: `Kode cabang "${kode_cabang}" sudah digunakan.` };
        return { error: 'Gagal menyimpan data kantor cabang baru.' };
      }
    }

    return { success: true };
  } catch (err) {
    return { error: 'Terjadi kesalahan.' };
  }
}

export async function deleteCabangAction(kode_cabang: string) {
  try {
    await verifyAdmin();
    const { error } = await supabase
      .from('cabang')
      .delete()
      .eq('kode_cabang', kode_cabang);

    if (error) return { error: 'Gagal menghapus kantor cabang.' };
    return { success: true };
  } catch (err) {
    return { error: 'Terjadi kesalahan.' };
  }
}

// ==========================================
// 3. DEPARTEMEN (DEPARTMENT) ACTIONS
// ==========================================

export async function saveDepartemenAction(formData: FormData) {
  try {
    await verifyAdmin();
    const kode_dept = formData.get('kode_dept') as string;
    const nama_dept = formData.get('nama_dept') as string;
    const isEdit = formData.get('is_edit') === 'true';

    if (!kode_dept || !nama_dept) {
      return { error: 'Semua kolom wajib diisi!' };
    }

    if (isEdit) {
      const { error } = await supabase
        .from('departemen')
        .update({ nama_dept })
        .eq('kode_dept', kode_dept);
      if (error) return { error: 'Gagal memperbarui departemen.' };
    } else {
      const { error } = await supabase
        .from('departemen')
        .insert({ kode_dept, nama_dept });
      if (error) {
        if (error.code === '23505') return { error: `Kode dept "${kode_dept}" sudah digunakan.` };
        return { error: 'Gagal menambahkan departemen baru.' };
      }
    }

    return { success: true };
  } catch (err) {
    return { error: 'Terjadi kesalahan.' };
  }
}

export async function deleteDepartemenAction(kode_dept: string) {
  try {
    await verifyAdmin();
    const { error } = await supabase
      .from('departemen')
      .delete()
      .eq('kode_dept', kode_dept);

    if (error) return { error: 'Gagal menghapus departemen.' };
    return { success: true };
  } catch (err) {
    return { error: 'Terjadi kesalahan.' };
  }
}

// ==========================================
// 4. JAM KERJA (WORK SHIFT) ACTIONS
// ==========================================

export async function saveJamKerjaAction(formData: FormData) {
  try {
    await verifyAdmin();
    const kode_jam_kerja = formData.get('kode_jam_kerja') as string;
    const nama_jam_kerja = formData.get('nama_jam_kerja') as string;
    const jam_masuk = formData.get('jam_masuk') as string;
    const jam_pulang = formData.get('jam_pulang') as string;
    const awal_jam_masuk = formData.get('awal_jam_masuk') as string;
    const akhir_jam_masuk = formData.get('akhir_jam_masuk') as string;
    const lintashari = parseInt(formData.get('lintashari') as string || '0');
    const isEdit = formData.get('is_edit') === 'true';

    if (!kode_jam_kerja || !nama_jam_kerja || !jam_masuk || !jam_pulang || !awal_jam_masuk || !akhir_jam_masuk) {
      return { error: 'Semua kolom wajib diisi!' };
    }

    const jkData = {
      nama_jam_kerja,
      jam_masuk,
      jam_pulang,
      awal_jam_masuk,
      akhir_jam_masuk,
      lintashari,
    };

    if (isEdit) {
      const { error } = await supabase
        .from('jam_kerja')
        .update(jkData)
        .eq('kode_jam_kerja', kode_jam_kerja);
      if (error) return { error: 'Gagal memperbarui jam kerja.' };
    } else {
      const { error } = await supabase
        .from('jam_kerja')
        .insert({ kode_jam_kerja, ...jkData });
      if (error) {
        if (error.code === '23505') return { error: `Kode jam kerja "${kode_jam_kerja}" sudah digunakan.` };
        return { error: 'Gagal menambahkan jam kerja baru.' };
      }
    }

    return { success: true };
  } catch (err) {
    return { error: 'Terjadi kesalahan.' };
  }
}

export async function deleteJamKerjaAction(kode_jam_kerja: string) {
  try {
    await verifyAdmin();
    const { error } = await supabase
      .from('jam_kerja')
      .delete()
      .eq('kode_jam_kerja', kode_jam_kerja);

    if (error) return { error: 'Gagal menghapus jam kerja.' };
    return { success: true };
  } catch (err) {
    return { error: 'Terjadi kesalahan.' };
  }
}

// ==========================================
// 5. APPROVE IZIN / SAKIT ACTIONS
// ==========================================

export async function approveIzinAction(kode_izin: string, status_approved: string) {
  try {
    await verifyAdmin();

    // 1. Fetch leave application
    const { data: izin, error: fetchError } = await supabase
      .from('pengajuan_izin')
      .select('*')
      .eq('kode_izin', kode_izin)
      .single();

    if (fetchError || !izin) {
      return { error: 'Pengajuan izin tidak ditemukan.' };
    }

    // Begin Transaction mimicking by manually inserting multiple entries if approved, or deleting if cancelled
    if (status_approved === '1') {
      // Approve: insert presence records for all days within range
      const startDate = new Date(izin.tgl_izin_dari);
      const endDate = new Date(izin.tgl_izin_sampai);
      
      const insertPromises = [];
      let currentDate = new Date(startDate);

      while (currentDate <= endDate) {
        const dateStr = currentDate.toISOString().split('T')[0];
        insertPromises.push(
          supabase.from('presensi').insert({
            nik: izin.nik,
            tgl_presensi: dateStr,
            status: izin.status, // 'i'/'s'/'c'
            kode_izin: izin.kode_izin
          })
        );
        currentDate.setDate(currentDate.getDate() + 1);
      }

      await Promise.all(insertPromises);
    } else if (status_approved === '2') {
      // Reject: delete presence records if previously approved
      await supabase
        .from('presensi')
        .delete()
        .eq('kode_izin', kode_izin);
    }

    // Update status in pengajuan_izin
    const { error: updateError } = await supabase
      .from('pengajuan_izin')
      .update({ status_approved })
      .eq('kode_izin', kode_izin);

    if (updateError) {
      return { error: 'Gagal memperbarui status persetujuan.' };
    }

    return { success: true };
  } catch (err) {
    return { error: 'Terjadi kesalahan.' };
  }
}

export async function getMonitoringLogsAction(date: string, deptId?: string, branchId?: string) {
  try {
    await verifyAdmin();
    
    const { data, error } = await supabase
      .from('presensi')
      .select('*, karyawan(*, departemen(*), cabang(*)), jam_kerja(*)')
      .eq('tgl_presensi', date);
      
    if (error) {
      console.error('Fetch monitoring error:', error);
      return { error: 'Gagal memuat log presensi.' };
    }

    let filtered = data || [];
    
    // Filter on nested relations if specified
    if (deptId) {
      filtered = filtered.filter(p => p.karyawan?.kode_dept === deptId);
    }
    if (branchId) {
      filtered = filtered.filter(p => p.karyawan?.kode_cabang === branchId);
    }

    return { success: true, logs: filtered };
  } catch (err) {
    return { error: 'Terjadi kesalahan dalam mengambil data.' };
  }
}

export async function getKaryawanListAction() {
  try {
    await verifyAdmin();
    const { data, error } = await supabase
      .from('karyawan')
      .select('nik, nama_lengkap, jabatan, kode_dept, kode_cabang')
      .order('nama_lengkap', { ascending: true });

    if (error) return { error: 'Gagal memuat data karyawan.' };
    return { success: true, karyawan: data };
  } catch (err) {
    return { error: 'Unauthorized' };
  }
}

export async function getLaporanKehadiranAction(nik: string, bulan: number, tahun: number) {
  try {
    await verifyAdmin();
    
    // 1. Fetch employee details
    const { data: karyawan, error: empError } = await supabase
      .from('karyawan')
      .select('*, departemen(*), cabang(*)')
      .eq('nik', nik)
      .single();

    if (empError || !karyawan) {
      return { error: 'Data karyawan tidak ditemukan.' };
    }

    // 2. Fetch presence records for the month
    const startDate = `${tahun}-${String(bulan).padStart(2, '0')}-01`;
    const daysInMonth = new Date(tahun, bulan, 0).getDate();
    const endDate = `${tahun}-${String(bulan).padStart(2, '0')}-${String(daysInMonth).padStart(2, '0')}`;

    const { data: presensi, error: presError } = await supabase
      .from('presensi')
      .select('*, jam_kerja(*)')
      .eq('nik', nik)
      .gte('tgl_presensi', startDate)
      .lte('tgl_presensi', endDate)
      .order('tgl_presensi', { ascending: true });

    if (presError) {
      return { error: 'Gagal memuat log presensi.' };
    }

    // 3. Fetch holidays for the month
    const { data: holidays, error: holError } = await supabase
      .from('harilibur')
      .select('*')
      .gte('tanggal_libur', startDate)
      .lte('tanggal_libur', endDate);

    return { 
      success: true, 
      karyawan, 
      presensi: presensi || [], 
      holidays: holidays || [] 
    };
  } catch (err) {
    return { error: 'Unauthorized' };
  }
}

export async function getRekapKehadiranAction(bulan: number, tahun: number, kode_dept?: string, kode_cabang?: string) {
  try {
    await verifyAdmin();

    // 1. Query karyawan filtered by dept and branch
    let karyawanQuery = supabase
      .from('karyawan')
      .select('nik, nama_lengkap, jabatan, kode_dept, kode_cabang, departemen(*), cabang(*)');

    if (kode_dept) {
      karyawanQuery = karyawanQuery.eq('kode_dept', kode_dept);
    }
    if (kode_cabang) {
      karyawanQuery = karyawanQuery.eq('kode_cabang', kode_cabang);
    }

    const { data: karyawanList, error: empError } = await karyawanQuery.order('nama_lengkap', { ascending: true });

    if (empError || !karyawanList || karyawanList.length === 0) {
      return { success: true, karyawanList: [], presensiList: [], holidays: [] };
    }

    const nikList = karyawanList.map(k => k.nik);

    // 2. Fetch presence records for the month for all these employees
    const startDate = `${tahun}-${String(bulan).padStart(2, '0')}-01`;
    const daysInMonth = new Date(tahun, bulan, 0).getDate();
    const endDate = `${tahun}-${String(bulan).padStart(2, '0')}-${String(daysInMonth).padStart(2, '0')}`;

    const { data: presensiList, error: presError } = await supabase
      .from('presensi')
      .select('*, jam_kerja(*)')
      .in('nik', nikList)
      .gte('tgl_presensi', startDate)
      .lte('tgl_presensi', endDate);

    if (presError) {
      return { error: 'Gagal memuat log presensi rekap.' };
    }

    // 3. Fetch holidays for the month
    const { data: holidays } = await supabase
      .from('harilibur')
      .select('*')
      .gte('tanggal_libur', startDate)
      .lte('tanggal_libur', endDate);

    return {
      success: true,
      karyawanList,
      presensiList: presensiList || [],
      holidays: holidays || []
    };
  } catch (err) {
    return { error: 'Unauthorized' };
  }
}

