'use client';

import React, { useState, useTransition } from 'react';
import { getRekapKehadiranAction } from '@/lib/actions/admin';
import { Calendar, Building2, MapPin, Search, Loader2, AlertCircle, Printer, Download } from 'lucide-react';

interface RekapClientProps {
  departemenList: any[];
  cabangList: any[];
}

export default function RekapClient({ departemenList, cabangList }: RekapClientProps) {
  const [bulan, setBulan] = useState(new Date().getMonth() + 1);
  const [tahun, setTahun] = useState(new Date().getFullYear());
  const [selectedDept, setSelectedDept] = useState('');
  const [selectedBranch, setSelectedBranch] = useState('');

  const [isPending, startTransition] = useTransition();
  const [rekapData, setRekapData] = useState<any | null>(null);
  const [error, setError] = useState<string | null>(null);

  const months = [
    { value: 1, name: 'Januari' },
    { value: 2, name: 'Februari' },
    { value: 3, name: 'Maret' },
    { value: 4, name: 'April' },
    { value: 5, name: 'Mei' },
    { value: 6, name: 'Juni' },
    { value: 7, name: 'Juli' },
    { value: 8, name: 'Agustus' },
    { value: 9, name: 'September' },
    { value: 10, name: 'Oktober' },
    { value: 11, name: 'November' },
    { value: 12, name: 'Desember' }
  ];

  const currentYear = new Date().getFullYear();
  const years = Array.from({ length: 5 }, (_, i) => currentYear - 3 + i);

  const handleFetchRekap = (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);

    startTransition(async () => {
      const result = await getRekapKehadiranAction(Number(bulan), Number(tahun), selectedDept || undefined, selectedBranch || undefined);
      if (result.error) {
        setError(result.error);
        setRekapData(null);
      } else if (result.success) {
        setRekapData(result);
      }
    });
  };

  const handlePrint = () => {
    window.print();
  };

  const handleExportExcel = () => {
    if (!rekapData) return;
    
    // Simple HTML Table export trick to Excel format
    const tableHtml = document.getElementById('rekap-table-to-export')?.outerHTML;
    if (!tableHtml) return;

    const fileDetails = `rekap_presensi_${bulan}_${tahun}`;
    const uri = 'data:application/vnd.ms-excel;base64,';
    const template = `
      <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
        <head>
          <!--[if gte mso 9]>
            <xml>
              <x:ExcelWorkbook>
                <x:ExcelWorksheets>
                  <x:ExcelWorksheet>
                    <x:Name>Rekap Presensi</x:Name>
                    <x:WorksheetOptions>
                      <x:DisplayGridlines/>
                    </x:WorksheetOptions>
                  </x:ExcelWorksheet>
                </x:ExcelWorksheets>
              </x:ExcelWorkbook>
            </xml>
          <![endif]-->
          <meta charset="utf-8">
        </head>
        <body>
          ${tableHtml}
        </body>
      </html>
    `;
    
    const base64 = (s: string) => window.btoa(unescape(encodeURIComponent(s)));
    
    const link = document.createElement('a');
    link.href = uri + base64(template);
    link.download = `${fileDetails}.xls`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  const getDaysInMonth = () => {
    return new Date(tahun, bulan, 0).getDate();
  };

  const daysCount = rekapData ? getDaysInMonth() : 0;
  const dayNumbers = Array.from({ length: daysCount }, (_, i) => i + 1);

  // Helper to generate the day summary character
  const getDayAttendanceSymbol = (nik: string, day: number) => {
    if (!rekapData) return '';
    const dateStr = `${tahun}-${String(bulan).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    const log = rekapData.presensiList.find((p: any) => p.nik === nik && p.tgl_presensi === dateStr);
    const isHoliday = rekapData.holidays.find((h: any) => h.tanggal_libur === dateStr);

    if (log) {
      if (log.status === 'h') {
        const isLate = log.jam_in && log.jam_kerja?.jam_masuk && log.jam_in > log.jam_kerja.jam_masuk;
        return isLate ? 'T' : 'H';
      }
      return log.status.toUpperCase(); // I, S, C
    }

    if (isHoliday) return 'L';

    const d = new Date(dateStr);
    const dayOfWeek = d.getDay();
    if (dayOfWeek === 0 || dayOfWeek === 6) return 'W'; // Weekend

    return 'A'; // Absent / Alpha
  };

  // Helper to compute summary count per employee row
  const getEmployeeStats = (nik: string) => {
    const stats = { hadir: 0, terlambat: 0, izin: 0, sakit: 0, cuti: 0, alpha: 0 };
    for (let day = 1; day <= daysCount; day++) {
      const symbol = getDayAttendanceSymbol(nik, day);
      if (symbol === 'H') stats.hadir += 1;
      if (symbol === 'T') {
        stats.hadir += 1;
        stats.terlambat += 1;
      }
      if (symbol === 'I') stats.izin += 1;
      if (symbol === 'S') stats.sakit += 1;
      if (symbol === 'C') stats.cuti += 1;
      if (symbol === 'A') stats.alpha += 1;
    }
    return stats;
  };

  const getSymbolClass = (symbol: string) => {
    if (symbol === 'H') return 'text-emerald-600 font-bold bg-emerald-50';
    if (symbol === 'T') return 'text-red-500 font-black bg-red-50';
    if (symbol === 'I') return 'text-amber-500 font-bold bg-amber-50';
    if (symbol === 'S') return 'text-rose-500 font-bold bg-rose-50';
    if (symbol === 'C') return 'text-purple-650 font-bold bg-purple-50';
    if (symbol === 'L') return 'text-blue-500 bg-blue-50/50';
    if (symbol === 'W') return 'text-slate-400 bg-slate-100/40';
    return 'text-slate-350 font-medium bg-slate-50'; // 'A'
  };

  return (
    <div className="space-y-6">
      {/* 1. Filters selector panel */}
      <div className="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-lg print:hidden">
        <form onSubmit={handleFetchRekap} className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4 items-end">
          <div className="space-y-1.5">
            <label className="text-[10px] uppercase font-bold text-slate-400 pl-0.5 flex items-center">
              <Calendar className="w-3.5 h-3.5 mr-1 text-blue-500" />
              <span>Bulan</span>
            </label>
            <select
              value={bulan}
              onChange={(e) => setBulan(Number(e.target.value))}
              className="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-blue-500"
            >
              {months.map((m) => (
                <option key={m.value} value={m.value}>
                  {m.name}
                </option>
              ))}
            </select>
          </div>

          <div className="space-y-1.5">
            <label className="text-[10px] uppercase font-bold text-slate-400 pl-0.5 flex items-center">
              <Calendar className="w-3.5 h-3.5 mr-1 text-blue-500" />
              <span>Tahun</span>
            </label>
            <select
              value={tahun}
              onChange={(e) => setTahun(Number(e.target.value))}
              className="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-blue-500"
            >
              {years.map((y) => (
                <option key={y} value={y}>
                  {y}
                </option>
              ))}
            </select>
          </div>

          <div className="space-y-1.5">
            <label className="text-[10px] uppercase font-bold text-slate-400 pl-0.5 flex items-center">
              <Building2 className="w-3.5 h-3.5 mr-1 text-blue-500" />
              <span>Departemen</span>
            </label>
            <select
              value={selectedDept}
              onChange={(e) => setSelectedDept(e.target.value)}
              className="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-blue-500"
            >
              <option value="">-- Semua Departemen --</option>
              {departemenList.map((d) => (
                <option key={d.kode_dept} value={d.kode_dept}>
                  {d.nama_dept}
                </option>
              ))}
            </select>
          </div>

          <div className="space-y-1.5">
            <label className="text-[10px] uppercase font-bold text-slate-400 pl-0.5 flex items-center">
              <MapPin className="w-3.5 h-3.5 mr-1 text-blue-500" />
              <span>Cabang</span>
            </label>
            <select
              value={selectedBranch}
              onChange={(e) => setSelectedBranch(e.target.value)}
              className="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-blue-500"
            >
              <option value="">-- Semua Cabang --</option>
              {cabangList.map((c) => (
                <option key={c.text_code || c.kode_cabang} value={c.kode_cabang}>
                  {c.nama_cabang}
                </option>
              ))}
            </select>
          </div>

          <button
            type="submit"
            disabled={isPending}
            className="w-full bg-blue-600 hover:bg-blue-500 text-white rounded-xl py-2 px-4 text-xs font-bold transition-all flex items-center justify-center space-x-1.5"
          >
            {isPending ? (
              <>
                <Loader2 className="w-4 h-4 animate-spin" />
                <span>Memuat...</span>
              </>
            ) : (
              <>
                <Search className="w-4 h-4" />
                <span>Tampilkan Rekap</span>
              </>
            )}
          </button>
        </form>

        {error && (
          <div className="bg-red-500/10 border border-red-500/20 text-red-300 rounded-xl p-3.5 mt-4 flex items-center space-x-2 text-xs">
            <AlertCircle className="w-4 h-4 shrink-0" />
            <span>{error}</span>
          </div>
        )}
      </div>

      {/* 2. Operations button panel */}
      {rekapData && (
        <div className="flex justify-end space-x-3 print:hidden">
          <button
            onClick={handleExportExcel}
            className="bg-sky-655 hover:bg-sky-600 text-white bg-sky-600 rounded-xl py-2 px-5 text-xs font-bold transition-all flex items-center space-x-1.5 shadow-md shadow-sky-650/10"
          >
            <Download className="w-4 h-4" />
            <span>Eksport Excel (.xls)</span>
          </button>
          <button
            onClick={handlePrint}
            className="bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl py-2 px-5 text-xs font-bold transition-all flex items-center space-x-1.5 shadow-md shadow-emerald-600/10"
          >
            <Printer className="w-4 h-4" />
            <span>Cetak Rekap</span>
          </button>
        </div>
      )}

      {/* 3. Rekap Sheet grid */}
      {rekapData && (
        <div className="bg-white text-slate-900 border border-slate-200 rounded-3xl p-6 md:p-8 shadow-lg print:border-none print:shadow-none print:p-0 print:m-0 print:bg-white print:text-black">
          {/* Header */}
          <div className="border-b-2 border-slate-900 pb-5 mb-5 flex flex-col md:flex-row justify-between items-start md:items-center">
            <div className="space-y-1">
              <h2 className="text-lg md:text-xl font-black uppercase text-slate-950">Rekapitulasi Presensi Karyawan</h2>
              <p className="text-xs text-slate-500 font-bold uppercase tracking-widest">
                Periode: {months.find(m => m.value === bulan)?.name} {tahun}
              </p>
            </div>
            <div className="mt-3 md:mt-0 text-left md:text-right text-[10px] font-bold text-slate-400">
              <div className="text-slate-950 font-black">PRESENSI ARJUNA</div>
              <div>DEPARTEMEN / CABANG: {selectedDept || 'SEMUA'} / {selectedBranch || 'SEMUA'}</div>
            </div>
          </div>

          {/* Indicator Legends */}
          <div className="flex flex-wrap gap-x-5 gap-y-2 mb-6 text-[10px] font-bold text-slate-650 bg-slate-50 border border-slate-150 p-3 rounded-2xl print:bg-transparent print:border-none print:p-0">
            <span className="flex items-center space-x-1">
              <span className="w-4 h-4 rounded flex items-center justify-center bg-emerald-100 text-emerald-700 text-[9px]">H</span>
              <span>Hadir Tepat Waktu</span>
            </span>
            <span className="flex items-center space-x-1">
              <span className="w-4 h-4 rounded flex items-center justify-center bg-red-100 text-red-700 text-[9px]">T</span>
              <span>Terlambat</span>
            </span>
            <span className="flex items-center space-x-1">
              <span className="w-4 h-4 rounded flex items-center justify-center bg-amber-100 text-amber-700 text-[9px]">I</span>
              <span>Izin</span>
            </span>
            <span className="flex items-center space-x-1">
              <span className="w-4 h-4 rounded flex items-center justify-center bg-rose-100 text-rose-700 text-[9px]">S</span>
              <span>Sakit</span>
            </span>
            <span className="flex items-center space-x-1">
              <span className="w-4 h-4 rounded flex items-center justify-center bg-purple-100 text-purple-700 text-[9px]">C</span>
              <span>Cuti</span>
            </span>
            <span className="flex items-center space-x-1">
              <span className="w-4 h-4 rounded flex items-center justify-center bg-slate-200 text-slate-700 text-[9px]">A</span>
              <span>Alpha</span>
            </span>
            <span className="flex items-center space-x-1">
              <span className="w-4 h-4 rounded flex items-center justify-center bg-blue-100 text-blue-700 text-[9px]">L</span>
              <span>Libur Nasional</span>
            </span>
            <span className="flex items-center space-x-1">
              <span className="w-4 h-4 rounded flex items-center justify-center bg-slate-100 text-slate-500 text-[9px]">W</span>
              <span>Weekend</span>
            </span>
          </div>

          {/* Large Table Container */}
          <div className="overflow-x-auto border border-slate-200 rounded-2xl">
            <table id="rekap-table-to-export" className="w-full text-left text-[10px] border-collapse min-w-[1200px]">
              <thead>
                <tr className="bg-slate-50 border-b border-slate-200 text-slate-700 uppercase font-black tracking-wider text-[9px]">
                  <th className="px-3 py-3 border-r border-slate-200 w-32 sticky left-0 bg-slate-50 z-10">Karyawan / NIK</th>
                  {dayNumbers.map((day) => (
                    <th key={day} className="py-3 text-center border-r border-slate-200 w-7">{day}</th>
                  ))}
                  <th className="py-3 text-center border-r border-slate-200 w-8 text-emerald-650 bg-emerald-50/50">H</th>
                  <th className="py-3 text-center border-r border-slate-200 w-8 text-red-650 bg-red-50/50">T</th>
                  <th className="py-3 text-center border-r border-slate-200 w-8 text-amber-600 bg-amber-50/50">I</th>
                  <th className="py-3 text-center border-r border-slate-200 w-8 text-rose-650 bg-rose-50/50">S</th>
                  <th className="py-3 text-center border-r border-slate-200 w-8 text-purple-700 bg-purple-50/50">C</th>
                  <th className="py-3 text-center w-8 text-slate-600 bg-slate-100/50">A</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-150 text-slate-800">
                {rekapData.karyawanList.length > 0 ? (
                  rekapData.karyawanList.map((emp: any) => {
                    const rowStats = getEmployeeStats(emp.nik);
                    return (
                      <tr key={emp.nik} className="hover:bg-slate-50/50 transition-colors">
                        {/* Employee info sticky left */}
                        <td className="px-3 py-2 border-r border-slate-200 font-bold sticky left-0 bg-white hover:bg-slate-50/80 z-10 w-32 shadow-[2px_0_5px_rgba(0,0,0,0.05)]">
                          <div className="truncate text-slate-900 leading-tight">{emp.nama_lengkap}</div>
                          <div className="text-[8px] text-slate-400 mt-0.5">NIK: {emp.nik}</div>
                        </td>

                        {/* Days columns */}
                        {dayNumbers.map((day) => {
                          const sym = getDayAttendanceSymbol(emp.nik, day);
                          return (
                            <td 
                              key={day} 
                              className={`text-center border-r border-slate-150 font-bold py-2 text-[9px] ${getSymbolClass(sym)}`}
                            >
                              {sym === 'W' || sym === 'L' ? '' : sym}
                            </td>
                          );
                        })}

                        {/* Totals columns */}
                        <td className="text-center font-extrabold border-r border-slate-200 bg-emerald-50/30 text-emerald-600">{rowStats.hadir}</td>
                        <td className="text-center font-extrabold border-r border-slate-200 bg-red-50/30 text-red-550 text-red-500">{rowStats.terlambat}</td>
                        <td className="text-center font-extrabold border-r border-slate-200 bg-amber-50/30 text-amber-500">{rowStats.izin}</td>
                        <td className="text-center font-extrabold border-r border-slate-200 bg-rose-50/30 text-rose-500">{rowStats.sakit}</td>
                        <td className="text-center font-extrabold border-r border-slate-200 bg-purple-50/30 text-purple-600">{rowStats.cuti}</td>
                        <td className="text-center font-extrabold bg-slate-100/30 text-slate-500">{rowStats.alpha}</td>
                      </tr>
                    );
                  })
                ) : (
                  <tr>
                    <td colSpan={daysCount + 7} className="px-6 py-10 text-center text-slate-400 text-xs font-semibold">
                      Tidak ada data karyawan ditemukan.
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        </div>
      )}
    </div>
  );
}
