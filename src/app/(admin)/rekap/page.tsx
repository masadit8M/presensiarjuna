import React from 'react';
import { supabase } from '@/lib/supabase';
import RekapClient from './rekap-client';

export const revalidate = 0;

export default async function RekapPage() {
  // Fetch departments list
  const { data: departemenList } = await supabase
    .from('departemen')
    .select('*')
    .order('nama_dept', { ascending: true });

  // Fetch branches list
  const { data: cabangList } = await supabase
    .from('cabang')
    .select('*')
    .order('nama_cabang', { ascending: true });

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between print:hidden">
        <div>
          <h1 className="text-2xl font-bold text-white tracking-tight">Rekap Presensi Bulanan</h1>
          <p className="text-xs text-slate-400 mt-1">
            Lihat rekapitulasi kehadiran seluruh karyawan dalam bentuk grid kalender bulanan dan ekspor ke Excel.
          </p>
        </div>
      </div>

      <RekapClient 
        departemenList={departemenList || []} 
        cabangList={cabangList || []} 
      />
    </div>
  );
}
