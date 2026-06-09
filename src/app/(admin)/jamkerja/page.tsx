import React from 'react';
import type { Metadata } from 'next';
import { getSession } from '@/lib/session';
import { supabase } from '@/lib/supabase';
import { redirect } from 'next/navigation';
import JamKerjaTable from './jamkerja-table';

export const metadata: Metadata = {
  title: 'Kelola Jam Kerja - E-Presensi Admin',
  description: 'Manajemen jam kerja harian, jam masuk, jam pulang, dan shift lintas hari.',
};

export default async function AdminJamKerjaPage() {
  const session = await getSession();
  if (!session || (session.role !== 'administrator' && session.role !== 'admin departemen')) {
    redirect('/panel');
  }

  // Fetch work hours shifts from Supabase
  const { data: jamKerjaList } = await supabase
    .from('jam_kerja')
    .select('*')
    .order('jam_masuk');

  return (
    <div className="space-y-6">
      {/* Title */}
      <div>
        <h1 className="text-2xl font-bold text-white tracking-tight">Konfigurasi Jam Kerja</h1>
        <p className="text-slate-400 text-sm mt-1">
          Atur shift kerja karyawan, jam masuk kantor, batas awal dan akhir pemindaian kehadiran, serta aktifkan fungsionalitas lintas hari.
        </p>
      </div>

      {/* Main Table */}
      <JamKerjaTable jamKerjaList={jamKerjaList || []} />
    </div>
  );
}
