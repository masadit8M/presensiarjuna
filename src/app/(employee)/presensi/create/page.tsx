import React from 'react';
import type { Metadata } from 'next';
import { getSession } from '@/lib/session';
import { supabase } from '@/lib/supabase';
import { redirect } from 'next/navigation';
import PresensiClient from './presensi-client';

export const metadata: Metadata = {
  title: 'Kamera Absensi - E-Presensi',
  description: 'Kamera absensi geolocation untuk clock-in dan clock-out karyawan.',
};

// Day mapper for Indonesian days
function getIndoDayName(dayEng: string) {
  const map: Record<string, string> = {
    Sunday: 'Minggu',
    Monday: 'Senin',
    Tuesday: 'Selasa',
    Wednesday: 'Rabu',
    Thursday: 'Kamis',
    Friday: 'Jumat',
    Saturday: 'Sabtu',
  };
  return map[dayEng] || 'Senin';
}

export default async function PresensiCreatePage() {
  const session = await getSession();
  if (!session || session.role !== 'employee') {
    redirect('/login');
  }

  const nik = session.nik!;
  const today = new Date().toLocaleDateString('en-CA'); // Get YYYY-MM-DD
  const dayNameEng = new Date().toLocaleDateString('en-US', { weekday: 'long' });
  const dayNameIndo = getIndoDayName(dayNameEng);

  // 1. Fetch employee profile & branch coords
  const { data: karyawan } = await supabase
    .from('karyawan')
    .select('*, cabang(*)')
    .eq('nik', nik)
    .single();

  if (!karyawan || !karyawan.cabang) {
    return (
      <div className="p-6 text-center text-red-400 bg-slate-900 border border-red-500/20 rounded-2xl mt-12">
        Error: Profil karyawan atau koordinat kantor cabang Anda belum dikonfigurasi. Hubungi IT.
      </div>
    );
  }

  // 2. Fetch today's presence record
  const { data: presence } = await supabase
    .from('presensi')
    .select('*')
    .eq('nik', nik)
    .eq('tgl_presensi', today)
    .maybeSingle();

  // If already checked out, they cannot clock in or out again today
  if (presence && presence.jam_in && presence.jam_out) {
    return (
      <div className="p-6 text-center text-emerald-400 bg-slate-900 border border-emerald-500/20 rounded-[24px] mt-12 space-y-3">
        <h2 className="text-lg font-bold">Absensi Selesai</h2>
        <p className="text-xs text-slate-400">
          Anda telah melakukan absensi masuk ({presence.jam_in.substring(0, 5)}) dan pulang ({presence.jam_out.substring(0, 5)}) untuk hari ini.
        </p>
        <div className="pt-2">
          <a href="/dashboard" className="px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-semibold hover:bg-blue-700 transition-colors">
            Kembali ke Dashboard
          </a>
        </div>
      </div>
    );
  }

  // 3. Resolve Jam Kerja (Schedule)
  let activeJamKerja = null;
  let allSchedules: any[] = [];

  if (presence && presence.kode_jam_kerja) {
    // If they already clocked in, lock the schedule to what was used
    const { data: jk } = await supabase
      .from('jam_kerja')
      .select('*')
      .eq('kode_jam_kerja', presence.kode_jam_kerja)
      .single();
    activeJamKerja = jk;
  } else {
    // Auto-detect schedule
    // A. Check specific date schedule
    const { data: jkDate } = await supabase
      .from('konfigurasi_jamkerja_by_date')
      .select('*, jam_kerja(*)')
      .eq('nik', nik)
      .eq('tanggal', today)
      .maybeSingle();
      
    if (jkDate && jkDate.jam_kerja) {
      activeJamKerja = jkDate.jam_kerja;
    }

    // B. Check custom harian schedule
    if (!activeJamKerja) {
      const { data: jkHarian } = await supabase
        .from('konfigurasi_jamkerja')
        .select('*, jam_kerja(*)')
        .eq('nik', nik)
        .eq('hari', dayNameIndo)
        .maybeSingle();

      if (jkHarian && jkHarian.jam_kerja) {
        activeJamKerja = jkHarian.jam_kerja;
      }
    }

    // C. Check department harian schedule
    if (!activeJamKerja && karyawan.kode_dept && karyawan.kode_cabang) {
      // Find konfigurasi_jk_dept for this dept & branch
      const { data: jkDept } = await supabase
        .from('konfigurasi_jk_dept')
        .select('*')
        .eq('kode_dept', karyawan.kode_dept)
        .eq('kode_cabang', karyawan.kode_cabang)
        .maybeSingle();

      if (jkDept) {
        // Fetch detail for today's day
        const { data: jkDeptDetail } = await supabase
          .from('konfigurasi_jk_dept_detail')
          .select('*, jam_kerja(*)')
          .eq('kode_jk_dept', jkDept.kode_jk_dept)
          .eq('hari', dayNameIndo)
          .maybeSingle();

        if (jkDeptDetail && jkDeptDetail.jam_kerja) {
          activeJamKerja = jkDeptDetail.jam_kerja;
        }
      }
    }

    // D. Fetch all schedules in case we need a fallback manual pick
    const { data: jkList } = await supabase
      .from('jam_kerja')
      .select('*')
      .order('nama_jam_kerja');
    allSchedules = jkList || [];
  }

  return (
    <PresensiClient 
      karyawan={karyawan}
      branch={karyawan.cabang}
      presence={presence}
      activeJamKerja={activeJamKerja}
      allSchedules={allSchedules}
    />
  );
}
