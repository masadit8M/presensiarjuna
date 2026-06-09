import React from 'react';
import type { Metadata } from 'next';
import Link from 'next/link';
import { getSession } from '@/lib/session';
import { supabase } from '@/lib/supabase';
import { LogOut, User, Calendar, Clock, CheckCircle, AlertCircle, FileText, ChevronRight } from 'lucide-react';
import { logoutAction } from '@/lib/actions/auth';
import { redirect } from 'next/navigation';

export const metadata: Metadata = {
  title: 'Dashboard Karyawan - E-Presensi',
  description: 'Informasi status kehadiran harian, riwayat presensi, dan data izin cuti.',
};

export default async function DashboardPage() {
  const session = await getSession();
  if (!session || session.role !== 'employee') {
    redirect('/login');
  }

  const today = new Date().toLocaleDateString('en-CA'); // Get YYYY-MM-DD in local time
  
  // 1. Fetch Employee Profile details
  const { data: employee } = await supabase
    .from('karyawan')
    .select('*, departemen(nama_dept), cabang(nama_cabang)')
    .eq('nik', session.nik)
    .single();

  // 2. Fetch Today's Attendance
  const { data: todayPresence } = await supabase
    .from('presensi')
    .select('*, jam_kerja(*)')
    .eq('nik', session.nik)
    .eq('tgl_presensi', today)
    .maybeSingle();

  // 3. Fetch current month statistics
  const now = new Date();
  const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1).toLocaleDateString('en-CA');
  const endOfMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0).toLocaleDateString('en-CA');

  const { data: monthlyPresence } = await supabase
    .from('presensi')
    .select('*')
    .eq('nik', session.nik)
    .gte('tgl_presensi', startOfMonth)
    .lte('tgl_presensi', endOfMonth);

  const stats = {
    hadir: 0,
    izin: 0,
    sakit: 0,
    cuti: 0,
  };

  if (monthlyPresence) {
    monthlyPresence.forEach((p) => {
      if (p.status === 'h') stats.hadir++;
      else if (p.status === 'i') stats.izin++;
      else if (p.status === 's') stats.sakit++;
      else if (p.status === 'c') stats.cuti++;
    });
  }

  // 4. Fetch recent logs (Last 5 records of this month)
  const { data: recentLogs } = await supabase
    .from('presensi')
    .select('*, jam_kerja(*)')
    .eq('nik', session.nik)
    .order('tgl_presensi', { ascending: false })
    .limit(5);

  const getFormatDate = (dateStr: string) => {
    const options: Intl.DateTimeFormatOptions = { weekday: 'long', day: 'numeric', month: 'short', year: 'numeric' };
    return new Date(dateStr).toLocaleDateString('id-ID', options);
  };

  const getStatusBadge = (status: string, jamIn?: string, jamMasuk?: string) => {
    if (status === 'i') return <span className="px-2 py-1 bg-amber-500/10 border border-amber-500/20 text-amber-400 text-[10px] font-semibold rounded-lg">Izin</span>;
    if (status === 's') return <span className="px-2 py-1 bg-rose-500/10 border border-rose-500/20 text-rose-400 text-[10px] font-semibold rounded-lg">Sakit</span>;
    if (status === 'c') return <span className="px-2 py-1 bg-purple-500/10 border border-purple-500/20 text-purple-400 text-[10px] font-semibold rounded-lg">Cuti</span>;
    
    // Check if late
    if (jamIn && jamMasuk) {
      if (jamIn > jamMasuk) {
        return <span className="px-2 py-1 bg-red-500/10 border border-red-500/20 text-red-400 text-[10px] font-semibold rounded-lg">Terlambat</span>;
      }
    }
    return <span className="px-2 py-1 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[10px] font-semibold rounded-lg">Tepat Waktu</span>;
  };

  return (
    <div className="space-y-6">
      {/* Header Section */}
      <div className="flex items-center justify-between">
        <div className="flex items-center space-x-3">
          <div className="w-10 h-10 rounded-full bg-blue-500/15 border border-blue-500/20 flex items-center justify-center text-blue-400">
            <User className="w-5 h-5" />
          </div>
          <div>
            <h4 className="text-[11px] text-slate-400 uppercase tracking-wider font-semibold">Selamat Datang</h4>
            <h2 className="text-md font-bold text-white leading-tight">{employee?.nama_lengkap}</h2>
          </div>
        </div>
        
        {/* Logout Handler directly via form submit with action */}
        <form action={logoutAction}>
          <button 
            type="submit" 
            className="w-10 h-10 rounded-xl bg-red-500/10 border border-red-500/20 flex items-center justify-center text-red-400 hover:bg-red-500/20 hover:text-red-300 transition-colors"
            title="Keluar Aplikasi"
          >
            <LogOut className="w-5 h-5" />
          </button>
        </form>
      </div>

      {/* Profile Info Glassmorphic Card */}
      <div className="p-5 bg-gradient-to-r from-blue-600/20 to-indigo-600/20 border border-white/[0.06] rounded-[24px] shadow-lg relative overflow-hidden">
        {/* Decorative ambient background */}
        <div className="absolute -right-10 -bottom-10 w-24 h-24 bg-blue-500/20 rounded-full blur-2xl pointer-events-none" />
        
        <div className="flex items-center space-x-4">
          <div className="w-16 h-16 rounded-2xl bg-white/[0.04] border border-white/[0.08] overflow-hidden flex items-center justify-center shrink-0">
            {employee?.foto ? (
              <img 
                src={`${process.env.NEXT_PUBLIC_SUPABASE_URL}/storage/v1/object/public/karyawan/${employee.foto}`} 
                alt={employee.nama_lengkap} 
                className="w-full h-full object-cover"
                onError={(e) => {
                  (e.target as HTMLImageElement).src = '/assets/img/sample/avatar/avatar1.jpg'; // fallback
                }}
              />
            ) : (
              <User className="w-8 h-8 text-blue-300/40" />
            )}
          </div>
          <div>
            <span className="px-2 py-0.5 bg-blue-500/20 border border-blue-500/30 text-blue-300 text-[10px] font-bold rounded-full uppercase tracking-wider">
              {employee?.jabatan}
            </span>
            <h3 className="text-lg font-bold text-white mt-1 leading-snug">{employee?.nama_lengkap}</h3>
            <p className="text-xs text-slate-400 flex items-center mt-0.5">
              <span>NIK: {employee?.nik}</span>
              <span className="mx-2">•</span>
              <span>Dept: {employee?.departemen?.nama_dept || '-'}</span>
            </p>
          </div>
        </div>
      </div>

      {/* Today's Attendance (Jam Masuk / Jam Keluar) Card */}
      <div className="bg-[#131936] border border-white/[0.05] rounded-[24px] p-5 shadow-lg space-y-4">
        <div className="flex items-center justify-between pb-3 border-b border-white/[0.06]">
          <div className="flex items-center space-x-2">
            <Calendar className="w-5 h-5 text-blue-400" />
            <span className="text-sm font-semibold text-slate-200">Presensi Hari Ini</span>
          </div>
          <span className="text-xs text-slate-400 font-medium">
            {new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}
          </span>
        </div>

        <div className="grid grid-cols-2 gap-4">
          {/* Clock In */}
          <div className="bg-slate-900/40 border border-white/[0.03] rounded-2xl p-4 flex flex-col items-center justify-center text-center">
            <Clock className="w-5 h-5 text-emerald-400 mb-1" />
            <span className="text-[11px] text-slate-400 font-medium">Jam Masuk</span>
            <span className="text-lg font-bold text-white mt-1">
              {todayPresence?.jam_in ? todayPresence.jam_in.substring(0, 5) : '--:--'}
            </span>
            {todayPresence?.jam_in ? (
              <span className="text-[9px] text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-full mt-1.5 font-bold">
                Sudah Absen
              </span>
            ) : (
              <span className="text-[9px] text-slate-400 bg-slate-500/10 px-2 py-0.5 rounded-full mt-1.5 font-bold">
                Belum Absen
              </span>
            )}
          </div>

          {/* Clock Out */}
          <div className="bg-slate-900/40 border border-white/[0.03] rounded-2xl p-4 flex flex-col items-center justify-center text-center">
            <Clock className="w-5 h-5 text-rose-400 mb-1" />
            <span className="text-[11px] text-slate-400 font-medium">Jam Pulang</span>
            <span className="text-lg font-bold text-white mt-1">
              {todayPresence?.jam_out ? todayPresence.jam_out.substring(0, 5) : '--:--'}
            </span>
            {todayPresence?.jam_out ? (
              <span className="text-[9px] text-rose-400 bg-rose-500/10 px-2 py-0.5 rounded-full mt-1.5 font-bold">
                Sudah Pulang
              </span>
            ) : todayPresence?.jam_in ? (
              <span className="text-[9px] text-blue-400 bg-blue-500/10 px-2 py-0.5 rounded-full mt-1.5 font-bold">
                Menunggu Pulang
              </span>
            ) : (
              <span className="text-[9px] text-slate-400 bg-slate-500/10 px-2 py-0.5 rounded-full mt-1.5 font-bold">
                Belum Absen
              </span>
            )}
          </div>
        </div>
      </div>

      {/* Month Statistics Grid */}
      <div className="space-y-3">
        <h3 className="text-sm font-semibold text-slate-200 pl-1">Statistik Bulan Ini</h3>
        <div className="grid grid-cols-4 gap-3">
          {/* Hadir */}
          <div className="bg-[#131936]/40 border border-white/[0.03] rounded-2xl p-3 flex flex-col items-center text-center">
            <div className="w-8 h-8 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-400 mb-2">
              <CheckCircle className="w-4 h-4" />
            </div>
            <span className="text-[10px] text-slate-400 font-medium">Hadir</span>
            <span className="text-md font-bold text-white mt-1">{stats.hadir}</span>
          </div>

          {/* Izin */}
          <div className="bg-[#131936]/40 border border-white/[0.03] rounded-2xl p-3 flex flex-col items-center text-center">
            <div className="w-8 h-8 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-400 mb-2">
              <FileText className="w-4 h-4" />
            </div>
            <span className="text-[10px] text-slate-400 font-medium">Izin</span>
            <span className="text-md font-bold text-white mt-1">{stats.izin}</span>
          </div>

          {/* Sakit */}
          <div className="bg-[#131936]/40 border border-white/[0.03] rounded-2xl p-3 flex flex-col items-center text-center">
            <div className="w-8 h-8 rounded-xl bg-rose-500/10 flex items-center justify-center text-rose-400 mb-2">
              <AlertCircle className="w-4 h-4" />
            </div>
            <span className="text-[10px] text-slate-400 font-medium">Sakit</span>
            <span className="text-md font-bold text-white mt-1">{stats.sakit}</span>
          </div>

          {/* Cuti */}
          <div className="bg-[#131936]/40 border border-white/[0.03] rounded-2xl p-3 flex flex-col items-center text-center">
            <div className="w-8 h-8 rounded-xl bg-purple-500/10 flex items-center justify-center text-purple-400 mb-2">
              <Calendar className="w-4 h-4" />
            </div>
            <span className="text-[10px] text-slate-400 font-medium">Cuti</span>
            <span className="text-md font-bold text-white mt-1">{stats.cuti}</span>
          </div>
        </div>
      </div>

      {/* Recent Activity List */}
      <div className="space-y-3">
        <div className="flex items-center justify-between pl-1 pr-1">
          <h3 className="text-sm font-semibold text-slate-200">Riwayat Terakhir</h3>
          <a href="/presensi/histori" className="text-xs text-blue-400 hover:text-blue-300 font-medium flex items-center">
            <span>Lihat Semua</span>
            <ChevronRight className="w-4 h-4 ml-0.5" />
          </a>
        </div>

        <div className="space-y-3">
          {recentLogs && recentLogs.length > 0 ? (
            recentLogs.map((log) => (
              <div 
                key={log.id} 
                className="bg-[#131936]/50 border border-white/[0.04] rounded-2xl p-4 flex items-center justify-between"
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
            <div className="text-center py-6 text-slate-500 text-xs bg-[#131936]/20 border border-dashed border-white/[0.04] rounded-2xl">
              Belum ada riwayat aktivitas absensi.
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
