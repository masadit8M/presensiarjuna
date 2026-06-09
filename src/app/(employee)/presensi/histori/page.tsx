import React from 'react';
import type { Metadata } from 'next';
import { getSession } from '@/lib/session';
import { supabase } from '@/lib/supabase';
import { redirect } from 'next/navigation';
import Link from 'next/link';
import { ArrowLeft, Clock, Calendar, AlertCircle } from 'lucide-react';
import HistoriFilter from './histori-filter';

export const metadata: Metadata = {
  title: 'Riwayat Kehadiran - E-Presensi',
  description: 'Daftar riwayat kehadiran karyawan bulanan.',
};

interface HistoriPageProps {
  searchParams: Promise<{
    month?: string;
    year?: string;
  }>;
}

export default async function HistoriPage({ searchParams }: HistoriPageProps) {
  const session = await getSession();
  if (!session || session.role !== 'employee') {
    redirect('/login');
  }

  const params = await searchParams;
  const now = new Date();
  const month = params.month ? parseInt(params.month) : now.getMonth() + 1;
  const year = params.year ? parseInt(params.year) : now.getFullYear();

  // Construct start & end date of the selected month
  const padMonth = String(month).padStart(2, '0');
  const startDate = `${year}-${padMonth}-01`;
  const endDate = new Date(year, month, 0).toLocaleDateString('en-CA');

  // Fetch presence records for this month
  const { data: logs } = await supabase
    .from('presensi')
    .select('*, jam_kerja(*), pengajuan_izin(*)')
    .eq('nik', session.nik)
    .gte('tgl_presensi', startDate)
    .lte('tgl_presensi', endDate)
    .order('tgl_presensi', { ascending: false });

  const getFormatDate = (dateStr: string) => {
    const options: Intl.DateTimeFormatOptions = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
    return new Date(dateStr).toLocaleDateString('id-ID', options);
  };

  const getStatusBadge = (status: string, jamIn?: string, jamMasuk?: string) => {
    if (status === 'i') return <span className="px-2 py-1 bg-amber-500/10 border border-amber-500/20 text-amber-400 text-[10px] font-bold rounded-lg">Izin</span>;
    if (status === 's') return <span className="px-2 py-1 bg-rose-500/10 border border-rose-500/20 text-rose-400 text-[10px] font-bold rounded-lg">Sakit</span>;
    if (status === 'c') return <span className="px-2 py-1 bg-purple-500/10 border border-purple-500/20 text-purple-400 text-[10px] font-bold rounded-lg">Cuti</span>;
    
    // Check if late
    if (jamIn && jamMasuk) {
      if (jamIn > jamMasuk) {
        return <span className="px-2 py-1 bg-red-500/10 border border-red-500/20 text-red-400 text-[10px] font-bold rounded-lg">Terlambat</span>;
      }
    }
    return <span className="px-2 py-1 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[10px] font-bold rounded-lg">Tepat Waktu</span>;
  };

  return (
    <div className="space-y-5">
      {/* Header */}
      <div className="flex items-center space-x-3 pb-3 border-b border-white/[0.06]">
        <a href="/dashboard" className="text-slate-400 hover:text-white transition-colors">
          <ArrowLeft className="w-6 h-6" />
        </a>
        <h2 className="text-lg font-bold text-white">Riwayat Presensi</h2>
      </div>

      {/* Filter Component */}
      <HistoriFilter selectedMonth={month} selectedYear={year} />

      {/* Logs List */}
      <div className="space-y-3">
        {logs && logs.length > 0 ? (
          logs.map((log) => (
            <div 
              key={log.id} 
              className="bg-[#131936]/40 border border-white/[0.04] rounded-2xl p-4 flex items-center justify-between shadow-md"
            >
              <div className="flex items-center space-x-3">
                <div className="w-10 h-10 rounded-xl bg-slate-900/60 flex items-center justify-center text-blue-400 border border-white/[0.03]">
                  <Clock className="w-5 h-5" />
                </div>
                <div>
                  <h4 className="text-sm font-bold text-white leading-tight">
                    {log.status === 'h' ? (log.jam_kerja?.nama_jam_kerja || 'Jam Reguler') : log.status === 'i' ? 'Pengajuan Izin' : log.status === 's' ? 'Pengajuan Sakit' : 'Pengajuan Cuti'}
                  </h4>
                  <p className="text-[10px] text-slate-400 mt-1">
                    {getFormatDate(log.tgl_presensi)}
                  </p>
                </div>
              </div>

              <div className="text-right flex flex-col items-end space-y-1">
                {log.status === 'h' ? (
                  <>
                    <span className="text-xs font-bold text-white">
                      {log.jam_in ? log.jam_in.substring(0, 5) : '--:--'} - {log.jam_out ? log.jam_out.substring(0, 5) : '--:--'}
                    </span>
                    {getStatusBadge(log.status, log.jam_in, log.jam_kerja?.jam_masuk)}
                  </>
                ) : (
                  getStatusBadge(log.status)
                )}
              </div>
            </div>
          ))
        ) : (
          <div className="text-center py-12 text-slate-500 text-xs bg-[#131936]/10 border border-dashed border-white/[0.04] rounded-2xl flex flex-col items-center justify-center space-y-2">
            <AlertCircle className="w-8 h-8 text-slate-600" />
            <span>Tidak ada data presensi pada bulan ini.</span>
          </div>
        )}
      </div>
    </div>
  );
}
