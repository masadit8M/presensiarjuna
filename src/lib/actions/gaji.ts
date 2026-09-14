'use server';

import { supabase, supabaseAdmin } from '@/lib/supabase';
import { getSession } from '@/lib/session';
import { generateSlipGajiPdf, SlipGajiData } from '@/lib/pdf-generator';

async function verifyAdmin() {
  const session = await getSession();
  if (!session || (session.role !== 'administrator' && session.role !== 'admin departemen')) {
    throw new Error('Unauthorized');
  }
  return session;
}

const MONTH_NAMES = [
  '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

// Fallback Master Data if database tables are not yet created
const DEFAULT_MASTER_SALARY: Record<string, { tipe: string; pokok: number; transport: number; tgl_masuk: string }> = {
  'CTD-01': { tipe: 'guru', pokok: 1700000, transport: 150000, tgl_masuk: '2018-07-01' }, // KARTIKA PRATIASARI
  'CTD-02': { tipe: 'guru', pokok: 1067420, transport: 150000, tgl_masuk: '2023-07-01' }, // TITIN HAMIDAH
  'CTD-03': { tipe: 'guru', pokok: 1004880, transport: 150000, tgl_masuk: '2020-08-24' }, // MERINDA EKA
  'CTD-04': { tipe: 'guru', pokok: 1011240, transport: 150000, tgl_masuk: '2020-02-01' }, // DWI RETNO S.
  'CTD-05': { tipe: 'guru', pokok: 689000,  transport: 150000, tgl_masuk: '2025-04-01' }, // CLARISTA WIDYA
  'CTD-06': { tipe: 'tpa',  pokok: 1350000, transport: 150000, tgl_masuk: '2022-05-20' }, // ANGGITA DARA

  'LGS-01': { tipe: 'guru', pokok: 1573040, transport: 150000, tgl_masuk: '2019-04-20' }, // ROSHELLA NABILAH
  'LGS-02': { tipe: 'guru', pokok: 1067420, transport: 150000, tgl_masuk: '2019-05-01' }, // RIZKA ALFIANTI
  'LGS-03': { tipe: 'guru', pokok: 898880,  transport: 150000, tgl_masuk: '2021-06-28' }, // ULUM KHUSNATIN
  'LGS-04': { tipe: 'guru', pokok: 730340,  transport: 150000, tgl_masuk: '2024-07-01' }, // ERIN WIDAYANTI
  'LGS-05': { tipe: 'guru', pokok: 400000,  transport: 150000, tgl_masuk: '2026-02-02' }, // ALEXANDRA ADJANI
  'LGS-06': { tipe: 'guru', pokok: 400000,  transport: 150000, tgl_masuk: '2026-07-06' }, // ANANDA LAILA AYU
  'LGS-07': { tipe: 'tpa',  pokok: 1350000, transport: 150000, tgl_masuk: '2022-11-24' }, // YUYUS ARTANTI

  'TPA-01': { tipe: 'tpa',  pokok: 2650000, transport: 150000, tgl_masuk: '2023-01-01' }, // CINDY NOVALITA
  'TPA-02': { tipe: 'tpa',  pokok: 2173000, transport: 150000, tgl_masuk: '2022-02-09' }, // MAHDALENA
  'TPA-03': { tipe: 'tpa',  pokok: 1219000, transport: 150000, tgl_masuk: '2020-01-08' }, // ATIK CAHYANINGRUM
  'TPA-04': { tipe: 'tpa',  pokok: 1166000, transport: 150000, tgl_masuk: '2022-01-10' }, // DIAH SETYO ARINI
  'TPA-05': { tipe: 'tpa',  pokok: 1160000, transport: 150000, tgl_masuk: '2022-05-20' }, // NOVI RAHMA PRATIWI
  'TPA-06': { tipe: 'tpa',  pokok: 1050000, transport: 150000, tgl_masuk: '2026-01-01' }, // CICIK TRIYA
  'TPA-07': { tipe: 'tpa',  pokok: 1050000, transport: 150000, tgl_masuk: '2023-12-08' }, // FEBRIANA UMAIROH
  'TPA-08': { tipe: 'tpa',  pokok: 901000,  transport: 150000, tgl_masuk: '2024-05-04' }, // NAILA DHINI
  'TPA-09': { tipe: 'tpa',  pokok: 500000,  transport: 150000, tgl_masuk: '2026-04-28' }, // MAGHFIRA GLADIS

  'TPA-10': { tipe: 'tpa',  pokok: 901000,  transport: 150000, tgl_masuk: '2024-08-01' }, // FITRI FADILATUL
  'TPA-11': { tipe: 'tpa',  pokok: 901000,  transport: 150000, tgl_masuk: '2024-06-27' }, // MARISA DINADA
  'TPA-12': { tipe: 'tpa',  pokok: 901000,  transport: 150000, tgl_masuk: '2024-08-03' }, // HENI RAHMAWATI
  'TPA-13': { tipe: 'tpa',  pokok: 500000,  transport: 150000, tgl_masuk: '2026-07-25' }, // FIDYAH AYU

  'STF-01': { tipe: 'staff', pokok: 1378000, transport: 200000, tgl_masuk: '2024-04-30' }, // MILYAS (SATPAM)
  'STF-02': { tipe: 'staff', pokok: 1700000, transport: 150000, tgl_masuk: '2020-01-01' }, // KASYATI (KONSUMSI)
  'STF-03': { tipe: 'staff', pokok: 1600000, transport: 150000, tgl_masuk: '2024-08-01' }, // METY FARIDA (KONSUMSI)
};

// 1. GET LIST PERIODE PENGGAJIAN
export async function getPenggajianPeriodeListAction() {
  try {
    await verifyAdmin();
    const { data, error } = await supabase
      .from('penggajian_periode')
      .select('*')
      .order('tahun', { ascending: false })
      .order('bulan', { ascending: false });

    if (error) {
      // If table doesn't exist yet
      if (error.code === 'PGRST205' || error.message?.includes('schema cache')) {
        return { success: true, list: [], tableMissing: true };
      }
      return { error: 'Gagal mengambil daftar periode: ' + error.message };
    }

    return { success: true, list: data || [], tableMissing: false };
  } catch (err: any) {
    return { error: err.message || 'Unauthorized' };
  }
}

// 2. GET DETAIL SLIP GAJI PERIODE
export async function getPenggajianDetailAction(periodeId: string) {
  try {
    await verifyAdmin();
    
    // Fetch periode
    const { data: periode, error: perError } = await supabase
      .from('penggajian_periode')
      .select('*')
      .eq('id', periodeId)
      .single();

    if (perError || !periode) {
      return { error: 'Periode penggajian tidak ditemukan.' };
    }

    // Fetch details
    const { data: details, error: detError } = await supabase
      .from('penggajian_detail')
      .select('*, karyawan(no_hp, kode_cabang, kode_dept)')
      .eq('periode_id', periodeId)
      .order('nama_karyawan', { ascending: true });

    if (detError) {
      return { error: 'Gagal mengambil rincian slip gaji: ' + detError.message };
    }

    return { success: true, periode, details: details || [] };
  } catch (err: any) {
    return { error: err.message || 'Unauthorized' };
  }
}

// 3. GENERATE DRAFT PENGGAJIAN BULANAN
export async function generateDraftPenggajianAction(
  bulan: number,
  tahun: number,
  isSkemaBundaAktif: boolean = false,
  siswaDaycareLunas: number = 0
) {
  try {
    await verifyAdmin();

    const client = supabaseAdmin || supabase;

    // Check if table exists
    const { error: testTableError } = await client
      .from('penggajian_periode')
      .select('id')
      .limit(1);

    if (testTableError && (testTableError.code === 'PGRST205' || testTableError.message?.includes('schema cache'))) {
      return {
        error: 'Tabel penggajian belum terpasang di database Supabase. Silakan jalankan script migrasi supabase/migrations/20260914_gaji_system.sql di SQL Editor Supabase terlebih dahulu.'
      };
    }

    // Standard 26 workdays
    const hkStandar = 26;

    // Determine Slip Date (Last day of the month)
    const lastDayOfMonth = new Date(tahun, bulan, 0).getDate();
    const tglSlipStr = `${lastDayOfMonth} ${MONTH_NAMES[bulan]} ${tahun}`;
    const tglSlipDate = `${tahun}-${String(bulan).padStart(2, '0')}-${String(lastDayOfMonth).padStart(2, '0')}`;
    
    // Transfer date (1st of next month)
    const nextMonth = bulan === 12 ? 1 : bulan + 1;
    const nextYear = bulan === 12 ? tahun + 1 : tahun;
    const tglTransferDate = `${nextYear}-${String(nextMonth).padStart(2, '0')}-01`;

    // 1. Fetch or create periode record
    let periodeId: string;
    const { data: existingPeriode } = await client
      .from('penggajian_periode')
      .select('*')
      .eq('bulan', bulan)
      .eq('tahun', tahun)
      .maybeSingle();

    if (existingPeriode) {
      periodeId = existingPeriode.id;
      // Update config
      await client
        .from('penggajian_periode')
        .update({
          is_skema_bunda_aktif: isSkemaBundaAktif,
          siswa_daycare_lunas: siswaDaycareLunas,
          hari_kerja_standar: hkStandar,
          tgl_slip: tglSlipDate,
          tgl_transfer: tglTransferDate,
        })
        .eq('id', periodeId);
    } else {
      const { data: newPeriode, error: createPerError } = await client
        .from('penggajian_periode')
        .insert({
          bulan,
          tahun,
          tgl_slip: tglSlipDate,
          tgl_transfer: tglTransferDate,
          hari_kerja_standar: hkStandar,
          status: 'draft',
          is_skema_bunda_aktif: isSkemaBundaAktif,
          siswa_daycare_lunas: siswaDaycareLunas,
        })
        .select()
        .single();

      if (createPerError || !newPeriode) {
        return { error: 'Gagal membuat periode penggajian: ' + createPerError?.message };
      }
      periodeId = newPeriode.id;
    }

    // 2. Fetch all active employees
    const { data: karyawanList, error: empError } = await client
      .from('karyawan')
      .select('nik, nama_lengkap, jabatan, no_hp, kode_dept, kode_cabang')
      .order('nama_lengkap', { ascending: true });

    if (empError || !karyawanList || karyawanList.length === 0) {
      return { error: 'Tidak ada data karyawan ditemukan.' };
    }

    // 3. Fetch Master Gaji (with fallback to DEFAULT_MASTER_SALARY)
    const { data: masterGajiList } = await client
      .from('gaji_master')
      .select('*');

    const masterMap = new Map<string, any>();
    if (masterGajiList) {
      masterGajiList.forEach((m: any) => masterMap.set(m.nik, m));
    }

    // 4. Fetch Presensi Records for the entire month
    const startDateStr = `${tahun}-${String(bulan).padStart(2, '0')}-01`;
    const endDateStr = `${tahun}-${String(bulan).padStart(2, '0')}-${String(lastDayOfMonth).padStart(2, '0')}`;

    const { data: presensiList } = await client
      .from('presensi')
      .select('*, jam_kerja(*)')
      .gte('tgl_presensi', startDateStr)
      .lte('tgl_presensi', endDateStr);

    // 5. Calculate & Upsert Detail for each employee
    let grandTotalPenerimaan = 0;
    let grandTotalPotongan = 0;
    let grandTotalBersih = 0;

    const detailInserts = [];

    // Calculate pool incentive per Bunda if gate is open (>= 41 students)
    let insentifPoolPerBunda = 0;
    if (isSkemaBundaAktif && siswaDaycareLunas >= 41) {
      insentifPoolPerBunda = siswaDaycareLunas * 5000;
    }

    for (const k of karyawanList) {
      const nik = k.nik;
      const dbMaster = masterMap.get(nik);
      const defMaster = DEFAULT_MASTER_SALARY[nik];

      const tipeKomponen = dbMaster?.tipe_komponen || defMaster?.tipe || (k.kode_dept === 'TPA' ? 'tpa' : 'guru');
      const gajiPokok = dbMaster?.gaji_pokok || defMaster?.pokok || 0;
      const tunjTransport = dbMaster?.tunj_transport || defMaster?.transport || 150000;
      const tglMasuk = dbMaster?.tgl_masuk || defMaster?.tgl_masuk || null;

      // Filter presensi logs for this employee
      const empLogs = (presensiList || []).filter((p: any) => p.nik === nik);
      
      let hadirCount = 0;
      let izinCount = 0;
      let sakitCount = 0;
      let cutiCount = 0;
      let alphaCount = 0;
      let terlambatCount = 0;
      let jamTelatTotal = 0;

      // Bunda Daycare Remuneration metrics
      let hadirDasarCount = 0;
      let premiPagiCount = 0;
      let dendaKedisiplinan = 0;

      for (const log of empLogs) {
        if (log.status === 'h') {
          hadirCount++;
          hadirDasarCount++;

          // Check clock-in time
          const jamIn = log.jam_in ? log.jam_in.substring(0, 5) : '08:00';
          if (jamIn <= '06:30') {
            premiPagiCount++;
          } else if (jamIn <= '07:00') {
            // Telat pagi: Premi pagi hangus, tetap dapat hadir dasar
          } else {
            // Telat berat > 07:00: Keduanya hangus
            hadirDasarCount = Math.max(0, hadirDasarCount - 1);
          }

          // Check standard late against schedule
          if (log.jam_kerja?.jam_masuk && log.jam_in > log.jam_kerja.jam_masuk) {
            terlambatCount++;
            // Calculate late hours roughly
            const schedMasuk = log.jam_kerja.jam_masuk;
            const diffMin = (new Date(`1970-01-01T${log.jam_in}`).getTime() - new Date(`1970-01-01T${schedMasuk}`).getTime()) / (1000 * 60);
            if (diffMin > 0) {
              jamTelatTotal += parseFloat((diffMin / 60).toFixed(2));
            }
          }
        } else if (log.status === 'i') {
          izinCount++;
        } else if (log.status === 's') {
          sakitCount++;
        } else if (log.status === 'c') {
          cutiCount++;
        }
      }

      // If absence logs exist, calculate Alpha from (HK - Hadir - Izin - Sakit - Cuti)
      if (empLogs.length > 0 && hadirCount < hkStandar) {
        alphaCount = Math.max(0, hkStandar - (hadirCount + izinCount + sakitCount + cutiCount));
      }

      // 6. FORMULA POTONGAN ABSEN RESMI (Sesuai Excel)
      // Gaji Harian = Gaji Pokok / 26
      // Gaji / Jam = Gaji Harian / 10
      // Pot. Absen = (Hari Izin + Alpha) * Gaji Harian + (Jam Telat * Gaji/Jam)
      const gajiHarian = gajiPokok > 0 ? (gajiPokok / hkStandar) : 0;
      const gajiPerJam = gajiHarian / 10;
      const hariPotong = izinCount + alphaCount;
      const potAbsensi = Math.round((hariPotong * gajiHarian) + (jamTelatTotal * gajiPerJam));

      // 7. SKEMA REMUNERASI BUNDA TPA (Jika aktif & tipe tpa)
      let insentifPool = 0;
      let insentifHadirDasar = 0;
      let premiPagi = 0;

      if (isSkemaBundaAktif && (tipeKomponen === 'tpa' || k.jabatan?.toUpperCase().includes('BUNDA'))) {
        insentifPool = insentifPoolPerBunda;
        insentifHadirDasar = hadirDasarCount * 5000;
        premiPagi = premiPagiCount * 5000;
      }

      const subtotalPenerimaan = Math.round(
        gajiPokok + tunjTransport + insentifPool + insentifHadirDasar + premiPagi
      );
      const subtotalPotongan = Math.round(potAbsensi + dendaKedisiplinan);
      const totalGajiBersih = Math.max(0, subtotalPenerimaan - subtotalPotongan);

      grandTotalPenerimaan += subtotalPenerimaan;
      grandTotalPotongan += subtotalPotongan;
      grandTotalBersih += totalGajiBersih;

      detailInserts.push({
        periode_id: periodeId,
        nik,
        nama_karyawan: k.nama_lengkap,
        jabatan: k.jabatan,
        kode_dept: k.kode_dept,
        kode_cabang: k.kode_cabang,
        tipe_komponen: tipeKomponen,
        tgl_masuk: tglMasuk,
        hk: hkStandar,
        hadir: hadirCount,
        izin: izinCount,
        sakit: sakitCount,
        cuti: cutiCount,
        alpha: alphaCount,
        terlambat_count: terlambatCount,
        jam_telat_total: jamTelatTotal,
        gaji_pokok: gajiPokok,
        tunj_transport: tunjTransport,
        uang_kegiatan: 0,
        ket_uang_kegiatan: '',
        uang_ekstra: 0,
        ket_uang_ekstra: '',
        uang_lembur: 0,
        ket_uang_lembur: '',
        bonus: 0,
        ket_bonus: '',
        penyesuaian_gaji: 0,
        ket_penyesuaian: '',
        insentif_pool_siswa: insentifPool,
        insentif_hadir_dasar: insentifHadirDasar,
        premi_disiplin_pagi: premiPagi,
        denda_kedisiplinan: dendaKedisiplinan,
        pot_absensi: potAbsensi,
        kasbon: 0,
        ket_kasbon: '',
        sanksi_disiplin: 0,
        ket_sanksi: '',
        subtotal_penerimaan: subtotalPenerimaan,
        subtotal_potongan: subtotalPotongan,
        total_gaji_bersih: totalGajiBersih,
        wa_status: 'pending',
      });
    }

    // Upsert all details
    const { error: upsertError } = await client
      .from('penggajian_detail')
      .upsert(detailInserts, { onConflict: 'periode_id,nik' });

    if (upsertError) {
      return { error: 'Gagal menyimpan detail slip gaji: ' + upsertError.message };
    }

    // Update periode totals
    await client
      .from('penggajian_periode')
      .update({
        total_penerimaan: grandTotalPenerimaan,
        total_potongan: grandTotalPotongan,
        total_gaji_bersih: grandTotalBersih,
      })
      .eq('id', periodeId);

    return {
      success: true,
      periodeId,
      message: `Berhasil generate ${detailInserts.length} slip gaji periode ${MONTH_NAMES[bulan]} ${tahun}!`
    };

  } catch (err: any) {
    return { error: err.message || 'Terjadi kesalahan sistem' };
  }
}

// 4. UPDATE INDIVIDUAL DETAIL SLIP GAJI (EDITABLE BY SUPERADMIN)
export async function updatePenggajianDetailItemAction(id: string, updates: any) {
  try {
    await verifyAdmin();
    const client = supabaseAdmin || supabase;

    // Fetch current row
    const { data: current, error: fetchErr } = await client
      .from('penggajian_detail')
      .select('*')
      .eq('id', id)
      .single();

    if (fetchErr || !current) {
      return { error: 'Data slip gaji tidak ditemukan.' };
    }

    // Merge numeric values
    const gajiPokok = Number(updates.gaji_pokok ?? current.gaji_pokok);
    const tunjTransport = Number(updates.tunj_transport ?? current.tunj_transport);
    const uangKegiatan = Number(updates.uang_kegiatan ?? current.uang_kegiatan);
    const uangEkstra = Number(updates.uang_ekstra ?? current.uang_ekstra);
    const uangLembur = Number(updates.uang_lembur ?? current.uang_lembur);
    const bonus = Number(updates.bonus ?? current.bonus);
    const penyesuaianGaji = Number(updates.penyesuaian_gaji ?? current.penyesuaian_gaji);

    const insentifPool = Number(updates.insentif_pool_siswa ?? current.insentif_pool_siswa);
    const insentifHadir = Number(updates.insentif_hadir_dasar ?? current.insentif_hadir_dasar);
    const premiPagi = Number(updates.premi_disiplin_pagi ?? current.premi_disiplin_pagi);

    const potAbsensi = Number(updates.pot_absensi ?? current.pot_absensi);
    const kasbon = Number(updates.kasbon ?? current.kasbon);
    const sanksiDisiplin = Number(updates.sanksi_disiplin ?? current.sanksi_disiplin);
    const dendaKedisiplinan = Number(updates.denda_kedisiplinan ?? current.denda_kedisiplinan);

    // Recalculate totals
    const subtotalPenerimaan = Math.round(
      gajiPokok + tunjTransport + uangKegiatan + uangEkstra + uangLembur + bonus + penyesuaianGaji + insentifPool + insentifHadir + premiPagi
    );
    const subtotalPotongan = Math.round(
      potAbsensi + kasbon + sanksiDisiplin + dendaKedisiplinan
    );
    const totalGajiBersih = Math.max(0, subtotalPenerimaan - subtotalPotongan);

    const updatePayload = {
      ...updates,
      gaji_pokok: gajiPokok,
      tunj_transport: tunjTransport,
      uang_kegiatan: uangKegiatan,
      uang_ekstra: uangEkstra,
      uang_lembur: uangLembur,
      bonus: bonus,
      penyesuaian_gaji: penyesuaianGaji,
      insentif_pool_siswa: insentifPool,
      insentif_hadir_dasar: insentifHadir,
      premi_disiplin_pagi: premiPagi,
      pot_absensi: potAbsensi,
      kasbon: kasbon,
      sanksi_disiplin: sanksiDisiplin,
      denda_kedisiplinan: dendaKedisiplinan,
      subtotal_penerimaan: subtotalPenerimaan,
      subtotal_potongan: subtotalPotongan,
      total_gaji_bersih: totalGajiBersih,
      updated_at: new Date().toISOString()
    };

    const { error: updateErr } = await client
      .from('penggajian_detail')
      .update(updatePayload)
      .eq('id', id);

    if (updateErr) {
      return { error: 'Gagal memperbarui rincian: ' + updateErr.message };
    }

    // Recalculate periode totals
    const { data: allDetails } = await client
      .from('penggajian_detail')
      .select('subtotal_penerimaan, subtotal_potongan, total_gaji_bersih')
      .eq('periode_id', current.periode_id);

    if (allDetails) {
      const totPenerimaan = allDetails.reduce((acc, cur) => acc + Number(cur.subtotal_penerimaan || 0), 0);
      const totPotongan = allDetails.reduce((acc, cur) => acc + Number(cur.subtotal_potongan || 0), 0);
      const totBersih = allDetails.reduce((acc, cur) => acc + Number(cur.total_gaji_bersih || 0), 0);

      await client
        .from('penggajian_periode')
        .update({
          total_penerimaan: totPenerimaan,
          total_potongan: totPotongan,
          total_gaji_bersih: totBersih,
        })
        .eq('id', current.periode_id);
    }

    return { success: true, message: 'Rincian gaji berhasil disimpan.' };
  } catch (err: any) {
    return { error: err.message || 'Terjadi kesalahan sistem' };
  }
}

// 5. DELETE PERIODE PENGGAJIAN
export async function deletePenggajianPeriodeAction(periodeId: string) {
  try {
    await verifyAdmin();
    const client = supabaseAdmin || supabase;
    const { error } = await client
      .from('penggajian_periode')
      .delete()
      .eq('id', periodeId);

    if (error) return { error: 'Gagal menghapus periode: ' + error.message };
    return { success: true };
  } catch (err: any) {
    return { error: err.message || 'Unauthorized' };
  }
}

// 6. SEND SLIP GAJI VIA WHATSAPP (KIRIM DOKUMEN FILE .PDF)
export async function sendSlipGajiWaAction(detailId: string) {
  try {
    await verifyAdmin();
    const client = supabaseAdmin || supabase;

    // Fetch detail & employee
    const { data: detail, error: detErr } = await client
      .from('penggajian_detail')
      .select('*, penggajian_periode(*), karyawan(*)')
      .eq('id', detailId)
      .single();

    if (detErr || !detail) {
      return { error: 'Data slip gaji tidak ditemukan.' };
    }

    const employee = detail.karyawan;
    let phone = employee?.no_hp || '';
    if (!phone) {
      return { error: `Nomor WhatsApp karyawan "${detail.nama_karyawan}" belum terdaftar di data profil!` };
    }

    // Format phone: 08xx -> 628xx
    phone = phone.replace(/\D/g, '');
    if (phone.startsWith('0')) {
      phone = '62' + phone.substring(1);
    }

    const periode = detail.penggajian_periode;
    const periodeStr = `${MONTH_NAMES[periode.bulan]} ${periode.tahun}`.toUpperCase();

    // Prepare SlipGajiData object for PDF generator
    const slipData: SlipGajiData = {
      nama_karyawan: detail.nama_karyawan,
      jabatan: detail.jabatan,
      kode_cabang: detail.kode_cabang,
      kode_dept: detail.kode_dept,
      tipe_komponen: detail.tipe_komponen,
      tgl_masuk: detail.tgl_masuk ? new Date(detail.tgl_masuk).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-',
      tgl_slip: `${new Date(periode.tgl_slip).getDate()}-${MONTH_NAMES[periode.bulan].substring(0, 3)}-${periode.tahun}`,
      periode_str: periodeStr,
      gaji_pokok: detail.gaji_pokok,
      tunj_transport: detail.tunj_transport,
      uang_kegiatan: detail.uang_kegiatan,
      ket_uang_kegiatan: detail.ket_uang_kegiatan,
      uang_ekstra: detail.uang_ekstra,
      ket_uang_ekstra: detail.ket_uang_ekstra,
      uang_lembur: detail.uang_lembur,
      ket_uang_lembur: detail.ket_uang_lembur,
      bonus: detail.bonus,
      ket_bonus: detail.ket_bonus,
      penyesuaian_gaji: detail.penyesuaian_gaji,
      ket_penyesuaian: detail.ket_penyesuaian,
      insentif_pool_siswa: detail.insentif_pool_siswa,
      insentif_hadir_dasar: detail.insentif_hadir_dasar,
      premi_disiplin_pagi: detail.premi_disiplin_pagi,
      denda_kedisiplinan: detail.denda_kedisiplinan,
      pot_absensi: detail.pot_absensi,
      kasbon: detail.kasbon,
      ket_kasbon: detail.ket_kasbon,
      sanksi_disiplin: detail.sanksi_disiplin,
      ket_sanksi: detail.ket_sanksi,
      subtotal_penerimaan: detail.subtotal_penerimaan,
      subtotal_potongan: detail.subtotal_potongan,
      total_gaji_bersih: detail.total_gaji_bersih,
    };

    // 1. Generate PDF Buffer
    const pdfBytes = await generateSlipGajiPdf(slipData);

    // 2. Upload PDF to Supabase Storage 'media_bucket' (public)
    const fileName = `slip_gaji/SLIP_${detail.nik}_${periode.tahun}_${String(periode.bulan).padStart(2, '0')}.pdf`;
    
    const { error: uploadError } = await client.storage
      .from('media_bucket')
      .upload(fileName, Buffer.from(pdfBytes), {
        contentType: 'application/pdf',
        upsert: true
      });

    let publicPdfUrl = '';
    if (!uploadError) {
      const { data: pubData } = client.storage.from('media_bucket').getPublicUrl(fileName);
      publicPdfUrl = pubData.publicUrl;
    }

    // 3. Prepare Professional WhatsApp Message Text
    const messageText = `*YAYASAN ARJUNA CENDEKIA*
*SLIP GAJI ELEKTRONIK*
-----------------------------------------
Yth. Ibu/Bpk *${detail.nama_karyawan}*
Jabatan: ${detail.jabatan}
Periode: ${periodeStr}

*RINCIAN GAJI:*
• Gaji Pokok: Rp ${detail.gaji_pokok.toLocaleString('id-ID')}
• Tunj. Transport: Rp ${detail.tunj_transport.toLocaleString('id-ID')}` +
(detail.uang_kegiatan > 0 ? `\n• Uang Kegiatan: Rp ${detail.uang_kegiatan.toLocaleString('id-ID')} ${detail.ket_uang_kegiatan ? `(${detail.ket_uang_kegiatan})` : ''}` : '') +
(detail.uang_ekstra > 0 ? `\n• Uang Ekstra: Rp ${detail.uang_ekstra.toLocaleString('id-ID')} ${detail.ket_uang_ekstra ? `(${detail.ket_uang_ekstra})` : ''}` : '') +
(detail.uang_lembur > 0 ? `\n• Lembur: Rp ${detail.uang_lembur.toLocaleString('id-ID')} ${detail.ket_uang_lembur ? `(${detail.ket_uang_lembur})` : ''}` : '') +
(detail.insentif_pool_siswa > 0 ? `\n• Insentif Pool Siswa: Rp ${detail.insentif_pool_siswa.toLocaleString('id-ID')}` : '') +
(detail.insentif_hadir_dasar > 0 || detail.premi_disiplin_pagi > 0 ? `\n• Insentif Hadir & Disiplin 06.30: Rp ${(detail.insentif_hadir_dasar + detail.premi_disiplin_pagi).toLocaleString('id-ID')}` : '') +
(detail.bonus > 0 ? `\n• Bonus/THR: Rp ${detail.bonus.toLocaleString('id-ID')}` : '') +
(detail.penyesuaian_gaji > 0 ? `\n• Penyesuaian: Rp ${detail.penyesuaian_gaji.toLocaleString('id-ID')}` : '') +
`\n*Subtotal Penerimaan: Rp ${detail.subtotal_penerimaan.toLocaleString('id-ID')}*

*POTONGAN:*
• Potongan Absensi: Rp ${detail.pot_absensi.toLocaleString('id-ID')}` +
(detail.kasbon > 0 ? `\n• Kasbon/Pinjaman: Rp ${detail.kasbon.toLocaleString('id-ID')} ${detail.ket_kasbon ? `(${detail.ket_kasbon})` : ''}` : '') +
(detail.sanksi_disiplin > 0 ? `\n• Sanksi Disiplin: Rp ${detail.sanksi_disiplin.toLocaleString('id-ID')}` : '') +
(detail.denda_kedisiplinan > 0 ? `\n• Denda Keterlambatan: Rp ${detail.denda_kedisiplinan.toLocaleString('id-ID')}` : '') +
`\n*Subtotal Potongan: Rp ${detail.subtotal_potongan.toLocaleString('id-ID')}*
-----------------------------------------
*TOTAL GAJI BERSIH (THP): Rp ${detail.total_gaji_bersih.toLocaleString('id-ID')}*
-----------------------------------------
File dokumen resmi Slip Gaji (.pdf) terlampir di atas.
_Dibuat oleh: Kartika P. (Bendahara Yayasan)_`;

    // 4. Send via WhatsApp Gateway (Document file attachment in file_dikirim)
    const gatewayUrl = process.env.NEXT_PUBLIC_WA_GATEWAY_URL || 'https://wagateway.pedasalami.com/send-message';
    
    let isSuccess = false;
    try {
      const res = await fetch(gatewayUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          message: messageText,
          number: phone,
          file_dikirim: publicPdfUrl || '', // URL dokumen PDF resmi
        }),
      });
      isSuccess = res.ok;
    } catch (e) {
      console.error('Failed calling WA gateway:', e);
    }

    // 5. Record status
    await client
      .from('penggajian_detail')
      .update({
        wa_status: isSuccess ? 'sent' : 'failed',
        wa_sent_at: isSuccess ? new Date().toISOString() : null,
        pdf_url: publicPdfUrl || null
      })
      .eq('id', detailId);

    if (!isSuccess) {
      return { 
        success: false, 
        message: 'Gagal menghubungi WhatsApp gateway server. Anda dapat mengunduh PDF atau mengirim via WhatsApp Web.',
        pdfUrl: publicPdfUrl,
        phone,
        messageText
      };
    }

    return { 
      success: true, 
      message: `Slip gaji berhasil dikirim ke nomor WhatsApp ${phone}!`,
      pdfUrl: publicPdfUrl
    };

  } catch (err: any) {
    return { error: err.message || 'Terjadi kesalahan sistem pengiriman WA' };
  }
}

// 7. GET MASTER GAJI KARYAWAN
export async function getMasterGajiListAction() {
  try {
    await verifyAdmin();
    const client = supabaseAdmin || supabase;

    // Fetch all employees
    const { data: karyawanList } = await client
      .from('karyawan')
      .select('*, cabang(*), departemen(*)')
      .order('nama_lengkap', { ascending: true });

    // Fetch master gaji
    const { data: masterList } = await client
      .from('gaji_master')
      .select('*');

    const masterMap = new Map<string, any>();
    if (masterList) {
      masterList.forEach((m: any) => masterMap.set(m.nik, m));
    }

    const merged = (karyawanList || []).map((k: any) => {
      const dbM = masterMap.get(k.nik);
      const defM = DEFAULT_MASTER_SALARY[k.nik];
      return {
        nik: k.nik,
        nama_lengkap: k.nama_lengkap,
        jabatan: k.jabatan,
        no_hp: k.no_hp,
        kode_cabang: k.kode_cabang,
        cabang: k.cabang?.nama_cabang || k.kode_cabang,
        kode_dept: k.kode_dept,
        departemen: k.departemen?.nama_dept || k.kode_dept,
        tipe_komponen: dbM?.tipe_komponen || defM?.tipe || (k.kode_dept === 'TPA' ? 'tpa' : 'guru'),
        gaji_pokok: dbM?.gaji_pokok ?? (defM?.pokok ?? 0),
        tunj_transport: dbM?.tunj_transport ?? (defM?.transport ?? 150000),
        tgl_masuk: dbM?.tgl_masuk || defM?.tgl_masuk || '',
        no_rek: dbM?.no_rek || '',
        nama_bank: dbM?.nama_bank || '',
        catatan: dbM?.catatan || '',
      };
    });

    return { success: true, list: merged };
  } catch (err: any) {
    return { error: err.message || 'Unauthorized' };
  }
}

// 8. UPDATE MASTER GAJI KARYAWAN
export async function updateMasterGajiAction(nik: string, data: any) {
  try {
    await verifyAdmin();
    const client = supabaseAdmin || supabase;

    const payload = {
      nik,
      tipe_komponen: data.tipe_komponen || 'guru',
      gaji_pokok: Number(data.gaji_pokok || 0),
      tunj_transport: Number(data.tunj_transport || 150000),
      tgl_masuk: data.tgl_masuk || null,
      no_rek: data.no_rek || null,
      nama_bank: data.nama_bank || null,
      catatan: data.catatan || null,
      updated_at: new Date().toISOString()
    };

    const { error } = await client
      .from('gaji_master')
      .upsert(payload, { onConflict: 'nik' });

    if (error) {
      return { error: 'Gagal memperbarui master gaji: ' + error.message };
    }

    return { success: true, message: 'Master gaji berhasil disimpan!' };
  } catch (err: any) {
    return { error: err.message || 'Unauthorized' };
  }
}
