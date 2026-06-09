import React from 'react';
import type { Metadata } from 'next';
import { getSession } from '@/lib/session';
import { supabase } from '@/lib/supabase';
import { redirect } from 'next/navigation';
import Link from 'next/link';
import { ArrowLeft, Plus, Calendar, FileText, AlertCircle, HelpCircle, CheckCircle, XCircle } from 'lucide-react';
import DeleteIzinBtn from './delete-btn';

export const metadata: Metadata = {
  title: 'Data Izin Karyawan - E-Presensi',
  description: 'Daftar pengajuan izin, sakit, dan cuti karyawan.',
};

export default async function IzinPage() {
  const session = await getSession();
  if (!session || session.role !== 'employee') {
    redirect('/login');
  }

  // Fetch leave requests for the employee
  const { data: dataIzin } = await supabase
    .from('pengajuan_izin')
    .select('*, master_cuti(*)')
    .eq('nik', session.nik)
    .order('tgl_izin_dari', { ascending: false });

  const getFormatDateRange = (dari: string, sampai: string) => {
    const opt: Intl.DateTimeFormatOptions = { day: 'numeric', month: 'short' };
    const optYear: Intl.DateTimeFormatOptions = { day: 'numeric', month: 'short', year: 'numeric' };
    
    const d1 = new Date(dari);
    const d2 = new Date(sampai);
    
    if (dari === sampai) {
      return d1.toLocaleDateString('id-ID', optYear);
    }
    
    if (d1.getFullYear() === d2.getFullYear()) {
      return `${d1.toLocaleDateString('id-ID', opt)} - ${d2.toLocaleDateString('id-ID', optYear)}`;
    }
    
    return `${d1.toLocaleDateString('id-ID', optYear)} - ${d2.toLocaleDateString('id-ID', optYear)}`;
  };

  const getDaysCount = (dari: string, sampai: string) => {
    const d1 = new Date(dari);
    const d2 = new Date(sampai);
    const diff = Math.abs(d2.getTime() - d1.getTime());
    return Math.ceil(diff / (1000 * 60 * 60 * 24)) + 1;
  };

  const getStatusDisplay = (statusApproved: string) => {
    if (statusApproved === '0') {
      return (
        <span className="flex items-center space-x-1.5 px-2.5 py-1 bg-amber-500/10 border border-amber-500/20 text-amber-400 text-[10px] font-bold rounded-lg uppercase tracking-wide">
          <HelpCircle className="w-3.5 h-3.5" />
          <span>Pending</span>
        </span>
      );
    }
    if (statusApproved === '1') {
      return (
        <span className="flex items-center space-x-1.5 px-2.5 py-1 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[10px] font-bold rounded-lg uppercase tracking-wide">
          <CheckCircle className="w-3.5 h-3.5" />
          <span>Disetujui</span>
        </span>
      );
    }
    return (
      <span className="flex items-center space-x-1.5 px-2.5 py-1 bg-rose-500/10 border border-rose-500/20 text-rose-400 text-[10px] font-bold rounded-lg uppercase tracking-wide">
        <XCircle className="w-3.5 h-3.5" />
        <span>Ditolak</span>
      </span>
    );
  };

  const getLeaveTypeDetails = (status: string, cutiName?: string) => {
    if (status === 'i') return { label: 'Izin Absen', color: 'text-amber-400 bg-amber-500/10' };
    if (status === 's') return { label: 'Sakit', color: 'text-rose-400 bg-rose-500/10' };
    return { label: `Cuti: ${cutiName || 'Pribadi'}`, color: 'text-purple-400 bg-purple-500/10' };
  };

  return (
    <div className="space-y-5 relative min-h-[80vh]">
      {/* Header */}
      <div className="flex items-center justify-between pb-3 border-b border-white/[0.06]">
        <div className="flex items-center space-x-3">
          <a href="/dashboard" className="text-slate-400 hover:text-white transition-colors">
            <ArrowLeft className="w-6 h-6" />
          </a>
          <h2 className="text-lg font-bold text-white">Data Pengajuan Izin</h2>
        </div>
        
        {/* Floating Trigger Link to create */}
        <a
          href="/presensi/izin/buat"
          className="flex items-center space-x-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-md shadow-blue-500/20 transition-all active:scale-95"
        >
          <Plus className="w-4 h-4" />
          <span>Ajukan Izin</span>
        </a>
      </div>

      {/* List Container */}
      <div className="space-y-3">
        {dataIzin && dataIzin.length > 0 ? (
          dataIzin.map((item) => {
            const days = getDaysCount(item.tgl_izin_dari, item.tgl_izin_sampai);
            const type = getLeaveTypeDetails(item.status, item.master_cuti?.nama_cuti);
            
            return (
              <div 
                key={item.kode_izin} 
                className="bg-[#131936]/40 border border-white/[0.04] rounded-2xl p-4 flex flex-col space-y-3 shadow-md relative overflow-hidden"
              >
                {/* Upper row: type badge and status */}
                <div className="flex items-center justify-between">
                  <span className={`px-2.5 py-0.5 text-[10px] font-extrabold rounded-full tracking-wide uppercase ${type.color}`}>
                    {type.label}
                  </span>
                  {getStatusDisplay(item.status_approved)}
                </div>

                {/* Middle row: dates and details */}
                <div className="flex items-center justify-between">
                  <div className="space-y-1">
                    <h4 className="text-sm font-bold text-white leading-tight">
                      {getFormatDateRange(item.tgl_izin_dari, item.tgl_izin_sampai)}
                    </h4>
                    <p className="text-[10px] text-slate-400 flex items-center">
                      <Calendar className="w-3.5 h-3.5 mr-1 text-slate-500" />
                      Durasi: {days} Hari Kerja
                    </p>
                  </div>
                  
                  {/* Delete button if pending */}
                  {item.status_approved === '0' && (
                    <DeleteIzinBtn kode_izin={item.kode_izin} />
                  )}
                </div>

                {/* Lower row: keterangan text */}
                <div className="bg-slate-900/40 rounded-xl p-3 border border-white/[0.02]">
                  <p className="text-xs text-slate-300 leading-relaxed italic">
                    &ldquo;{item.keterangan}&rdquo;
                  </p>
                  {item.doc_sid && (
                    <div className="mt-2 flex items-center text-[10px] text-blue-400 font-medium hover:underline">
                      <FileText className="w-3.5 h-3.5 mr-1" />
                      <a 
                        href={`${process.env.NEXT_PUBLIC_SUPABASE_URL}/storage/v1/object/public/absensi/${item.doc_sid}`} 
                        target="_blank" 
                        rel="noreferrer"
                      >
                        Lihat Lampiran Surat Dokter
                      </a>
                    </div>
                  )}
                </div>
              </div>
            );
          })
        ) : (
          <div className="text-center py-12 text-slate-500 text-xs bg-[#131936]/10 border border-dashed border-white/[0.04] rounded-2xl flex flex-col items-center justify-center space-y-2">
            <AlertCircle className="w-8 h-8 text-slate-600" />
            <span>Tidak ada data pengajuan izin.</span>
          </div>
        )}
      </div>
    </div>
  );
}
