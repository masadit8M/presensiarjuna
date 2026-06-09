import React from 'react';
import type { Metadata } from 'next';
import { getSession } from '@/lib/session';
import { supabase } from '@/lib/supabase';
import { redirect } from 'next/navigation';
import KaryawanTable from './karyawan-table';

export const metadata: Metadata = {
  title: 'Kelola Karyawan - E-Presensi Admin',
  description: 'Manajemen data karyawan, departemen, penugasan cabang, reset password, dan status lock GPS.',
};

export default async function AdminKaryawanPage() {
  const session = await getSession();
  if (!session || (session.role !== 'administrator' && session.role !== 'admin departemen')) {
    redirect('/panel');
  }

  // 1. Fetch employee list (joined with departemen & cabang)
  const { data: karyawanList } = await supabase
    .from('karyawan')
    .select('*, departemen(nama_dept), cabang(nama_cabang, radius_cabang, lokasi_cabang)')
    .order('nama_lengkap');

  // 2. Fetch departemen list for dropdown forms
  const { data: departemenList } = await supabase
    .from('departemen')
    .select('*')
    .order('nama_dept');

  // 3. Fetch cabang list for dropdown forms
  const { data: cabangList } = await supabase
    .from('cabang')
    .select('*')
    .order('nama_cabang');

  return (
    <div className="space-y-6">
      {/* Title */}
      <div>
        <h1 className="text-2xl font-bold text-white tracking-tight">Data Karyawan</h1>
        <p className="text-slate-400 text-sm mt-1">
          Kelola data profil karyawan, reset sandi, penugasan departemen & kantor cabang, serta kontrol status GPS Lock.
        </p>
      </div>

      {/* Main Table Wrapper */}
      <KaryawanTable 
        karyawanList={karyawanList || []}
        departemenList={departemenList || []}
        cabangList={cabangList || []}
      />
    </div>
  );
}
