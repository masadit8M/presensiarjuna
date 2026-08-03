import React from 'react';
import type { Metadata } from 'next';
import { getSession } from '@/lib/session';
import { supabase } from '@/lib/supabase';
import { redirect } from 'next/navigation';
import { Users, UserCheck, AlertCircle, Clock, MapPin, Search, Camera } from 'lucide-react';
import PresensiPhoto from '@/components/presensi-photo';

export const metadata: Metadata = {
  title: 'Dashboard Admin - E-Presensi',
  description: 'Panel monitoring dashboard utama admin E-Presensi.',
};

export default async function AdminDashboardPage() {
  const session = await getSession();
  if (!session || (session.role !== 'administrator' && session.role !== 'admin departemen')) {
    redirect('/panel');
  }

  const today = new Date().toLocaleDateString('en-CA'); // YYYY-MM-DD local time

  // 1. Fetch Total Employees count
  const { count: totalKaryawan } = await supabase
    .from('karyawan')
    .select('*', { count: 'exact', head: true });

  // 2. Fetch Today's Attendance logs
  // We perform a joined query to pull details of employee and branch
  const { data: logs } = await supabase
    .from('presensi')
    .select('*, karyawan(*, departemen(nama_dept)), jam_kerja(*)')
    .eq('tgl_presensi', today);

  const metrics = {
    total: totalKaryawan || 0,
    hadir: 0,
    terlambat: 0,
    izin: 0,
  };

  if (logs) {
    logs.forEach((log) => {
      if (log.status === 'h') {
        metrics.hadir++;
        // Check if late
        if (log.jam_in && log.jam_kerja && log.jam_in > log.jam_kerja.jam_masuk) {
          metrics.terlambat++;
        }
      } else if (['i', 's', 'c'].includes(log.status)) {
        metrics.izin++;
      }
    });
  }

  const getFormatTime = (timeStr?: string) => {
    return timeStr ? timeStr.substring(0, 5) : '--:--';
  };

  const getStatusDisplay = (log: any) => {
    if (log.status === 'i') return <span className="px-2 py-0.5 bg-amber-500/10 border border-amber-500/20 text-amber-400 text-[10px] font-bold rounded-md uppercase">Izin</span>;
    if (log.status === 's') return <span className="px-2 py-0.5 bg-rose-500/10 border border-rose-500/20 text-rose-400 text-[10px] font-bold rounded-md uppercase">Sakit</span>;
    if (log.status === 'c') return <span className="px-2 py-0.5 bg-purple-500/10 border border-purple-500/20 text-purple-400 text-[10px] font-bold rounded-md uppercase">Cuti</span>;
    
    // Check late
    if (log.jam_in && log.jam_kerja?.jam_masuk && log.jam_in > log.jam_kerja.jam_masuk) {
      return <span className="px-2 py-0.5 bg-red-500/10 border border-red-500/20 text-red-400 text-[10px] font-bold rounded-md uppercase">Terlambat</span>;
    }
    return <span className="px-2 py-0.5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[10px] font-bold rounded-md uppercase">Hadir</span>;
  };

  return (
    <div className="space-y-8">
      {/* Page Title */}
      <div>
        <h1 className="text-2xl font-bold text-white tracking-tight">Dashboard Ringkasan</h1>
        <p className="text-slate-400 text-sm mt-1">
          Statistik kehadiran dan aktivitas karyawan hari ini: {new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })}
        </p>
      </div>

      {/* Metrics Cards Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {/* Metric: Total Karyawan */}
        <div className="bg-slate-900 border border-slate-800 rounded-3xl p-6 flex items-center space-x-4 shadow-lg shadow-black/10">
          <div className="w-12 h-12 rounded-2xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 shrink-0">
            <Users className="w-6 h-6" />
          </div>
          <div>
            <span className="text-xs text-slate-400 font-semibold uppercase tracking-wider">Total Karyawan</span>
            <h2 className="text-3xl font-extrabold text-white mt-1 leading-tight">{metrics.total}</h2>
          </div>
        </div>

        {/* Metric: Hadir */}
        <div className="bg-slate-900 border border-slate-800 rounded-3xl p-6 flex items-center space-x-4 shadow-lg shadow-black/10">
          <div className="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 shrink-0">
            <UserCheck className="w-6 h-6" />
          </div>
          <div>
            <span className="text-xs text-slate-400 font-semibold uppercase tracking-wider">Hadir Hari Ini</span>
            <h2 className="text-3xl font-extrabold text-white mt-1 leading-tight">{metrics.hadir}</h2>
          </div>
        </div>

        {/* Metric: Terlambat */}
        <div className="bg-slate-900 border border-slate-800 rounded-3xl p-6 flex items-center space-x-4 shadow-lg shadow-black/10">
          <div className="w-12 h-12 rounded-2xl bg-red-500/10 border border-red-500/20 flex items-center justify-center text-red-400 shrink-0">
            <Clock className="w-6 h-6" />
          </div>
          <div>
            <span className="text-xs text-slate-400 font-semibold uppercase tracking-wider">Terlambat</span>
            <h2 className="text-3xl font-extrabold text-white mt-1 leading-tight">{metrics.terlambat}</h2>
          </div>
        </div>

        {/* Metric: Izin/Sakit */}
        <div className="bg-slate-900 border border-slate-800 rounded-3xl p-6 flex items-center space-x-4 shadow-lg shadow-black/10">
          <div className="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 shrink-0">
            <AlertCircle className="w-6 h-6" />
          </div>
          <div>
            <span className="text-xs text-slate-400 font-semibold uppercase tracking-wider">Izin & Cuti</span>
            <h2 className="text-3xl font-extrabold text-white mt-1 leading-tight">{metrics.izin}</h2>
          </div>
        </div>
      </div>

      {/* Today's Activity Table */}
      <div className="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        <div className="px-6 py-5 border-b border-slate-800 flex items-center justify-between">
          <h3 className="font-bold text-white text-md">Aktivitas Presensi Hari Ini</h3>
          <span className="text-xs text-blue-400 font-bold bg-blue-500/10 px-3 py-1 rounded-full uppercase tracking-wider">
            Live Logs
          </span>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="bg-slate-900/50 border-b border-slate-800 text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                <th className="px-6 py-4">Foto Wajah</th>
                <th className="px-6 py-4">Nama / NIK</th>
                <th className="px-6 py-4">Departemen</th>
                <th className="px-6 py-4">Tipe Jam Kerja</th>
                <th className="px-6 py-4">Masuk</th>
                <th className="px-6 py-4">Pulang</th>
                <th className="px-6 py-4">Status</th>
                <th className="px-6 py-4">Lokasi</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800 text-sm text-slate-300">
              {logs && logs.length > 0 ? (
                logs.map((log) => (
                  <tr key={log.id} className="hover:bg-slate-800/30 transition-colors">
                    <td className="px-6 py-4">
                      <div className="flex items-center space-x-2">
                        <PresensiPhoto filename={log.foto_in} title="Foto Masuk" alt="Foto Masuk" />
                        <PresensiPhoto filename={log.foto_out} title="Foto Pulang" alt="Foto Pulang" />
                      </div>
                    </td>
                    <td className="px-6 py-4">
                      <div className="font-bold text-white">{log.karyawan?.nama_lengkap}</div>
                      <div className="text-[10px] text-slate-500 mt-0.5">NIK: {log.nik}</div>
                    </td>
                    <td className="px-6 py-4">
                      {log.karyawan?.departemen?.nama_dept || '-'}
                    </td>
                    <td className="px-6 py-4">
                      {log.status === 'h' ? (log.jam_kerja?.nama_jam_kerja || 'Reguler') : 'Cuti/Izin'}
                    </td>
                    <td className="px-6 py-4 font-mono font-medium">
                      {getFormatTime(log.jam_in)}
                    </td>
                    <td className="px-6 py-4 font-mono font-medium">
                      {getFormatTime(log.jam_out)}
                    </td>
                    <td className="px-6 py-4">
                      {getStatusDisplay(log)}
                    </td>
                    <td className="px-6 py-4">
                      {log.status === 'h' && log.lokasi_in ? (
                        <a 
                          href={`https://maps.google.com/?q=${log.lokasi_in}`} 
                          target="_blank" 
                          rel="noreferrer"
                          className="flex items-center text-xs text-blue-400 hover:underline hover:text-blue-300"
                        >
                          <MapPin className="w-3.5 h-3.5 mr-1" />
                          <span>Lihat Peta</span>
                        </a>
                      ) : (
                        <span className="text-slate-600">-</span>
                      )}
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan={8} className="px-6 py-12 text-center text-slate-500 text-xs">
                    Belum ada data presensi yang tercatat hari ini.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
