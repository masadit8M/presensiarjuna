import React from 'react';
import type { Metadata } from 'next';
import { getSession } from '@/lib/session';
import { supabase } from '@/lib/supabase';
import { redirect } from 'next/navigation';
import BuatIzinClient from './buat-client';

export const metadata: Metadata = {
  title: 'Ajukan Izin - E-Presensi',
  description: 'Halaman pengisian formulir pengajuan izin, sakit, dan cuti karyawan.',
};

export default async function BuatIzinPage() {
  const session = await getSession();
  if (!session || session.role !== 'employee') {
    redirect('/login');
  }

  // Fetch leave types (Cuti) from master_cuti table
  const { data: masterCuti } = await supabase
    .from('master_cuti')
    .select('*')
    .order('nama_cuti');

  return (
    <BuatIzinClient masterCuti={masterCuti || []} />
  );
}
