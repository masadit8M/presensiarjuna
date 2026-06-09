import React from 'react';
import { supabase } from '@/lib/supabase';
import MonitoringClient from './monitoring-client';

export const revalidate = 0;

export default async function MonitoringPage() {
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
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-white tracking-tight">Monitoring Presensi</h1>
          <p className="text-xs text-slate-400 mt-1">
            Pantau kehadiran karyawan dan lokasi absensi secara real-time pada peta.
          </p>
        </div>
      </div>

      <MonitoringClient 
        departemenList={departemenList || []} 
        cabangList={cabangList || []} 
      />
    </div>
  );
}
