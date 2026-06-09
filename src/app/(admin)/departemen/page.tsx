import React from 'react';
import type { Metadata } from 'next';
import { getSession } from '@/lib/session';
import { supabase } from '@/lib/supabase';
import { redirect } from 'next/navigation';
import DepartemenTable from './departemen-table';

export const metadata: Metadata = {
  title: 'Kelola Departemen - E-Presensi Admin',
  description: 'Manajemen data departemen dan pembagian divisi karyawan.',
};

export default async function AdminDepartemenPage() {
  const session = await getSession();
  if (!session || (session.role !== 'administrator' && session.role !== 'admin departemen')) {
    redirect('/panel');
  }

  // Fetch departments list from Supabase
  const { data: departemenList } = await supabase
    .from('departemen')
    .select('*')
    .order('nama_dept');

  return (
    <div className="space-y-6">
      {/* Title */}
      <div>
        <h1 className="text-2xl font-bold text-white tracking-tight">Data Departemen</h1>
        <p className="text-slate-400 text-sm mt-1">
          Kelola data pembagian departemen kerja, unit operasional, dan divisi karyawan Anda.
        </p>
      </div>

      {/* Main Table */}
      <DepartemenTable departemenList={departemenList || []} />
    </div>
  );
}
