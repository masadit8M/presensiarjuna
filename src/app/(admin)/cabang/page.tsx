import React from 'react';
import type { Metadata } from 'next';
import { getSession } from '@/lib/session';
import { supabase } from '@/lib/supabase';
import { redirect } from 'next/navigation';
import CabangTable from './cabang-table';

export const metadata: Metadata = {
  title: 'Kelola Cabang - E-Presensi Admin',
  description: 'Manajemen kantor cabang dan pengaturan radius koordinat GPS pengunci.',
};

export default async function AdminCabangPage() {
  const session = await getSession();
  if (!session || (session.role !== 'administrator' && session.role !== 'admin departemen')) {
    redirect('/panel');
  }

  // Fetch branches list from Supabase
  const { data: cabangList } = await supabase
    .from('cabang')
    .select('*')
    .order('nama_cabang');

  return (
    <div className="space-y-6">
      {/* Title */}
      <div>
        <h1 className="text-2xl font-bold text-white tracking-tight">Data Kantor Cabang</h1>
        <p className="text-slate-400 text-sm mt-1">
          Daftarkan kantor cabang baru, tentukan titik koordinat lintang/bujur (Latitude/Longitude) pusat kantor, dan atur batas radius absensi karyawan.
        </p>
      </div>

      {/* Main Table */}
      <CabangTable cabangList={cabangList || []} />
    </div>
  );
}
