'use client';

import React, { useState, useTransition } from 'react';
import { getLaporanKehadiranAction } from '@/lib/actions/admin';
import { Calendar, User, FileText, Printer, Loader2, AlertCircle, Camera } from 'lucide-react';

interface LaporanClientProps {
  karyawanList: any[];
}

export default function LaporanClient({ karyawanList }: LaporanClientProps) {
  const [nik, setNik] = useState('');
  const [bulan, setBulan] = useState(new Date().getMonth() + 1);
  const [tahun, setTahun] = useState(new Date().getFullYear());
  
  const [isPending, startTransition] = useTransition();
  const [reportData, setReportData] = useState<any | null>(null);
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
    { value: 10, name: 'Oktobers' },
    { value: 11, name: 'November' },
    { value: 12, name: 'Desember' }
  ];

  const currentYear = new Date().getFullYear();
  const years = Array.from({ length: 5 }, (_, i) => currentYear - 3 + i);

  const handleFetchReport = (e: React.FormEvent) => {
    e.preventDefault();
    if (!nik) {
      setError('Pilih karyawan terlebih dahulu.');
      return;
    }
    setError(null);

    startTransition(async () => {
      const result = await getLaporanKehadiranAction(nik, Number(bulan), Number(tahun));
      if (result.error) {
        setError(result.error);
        setReportData(null);
      } else if (result.success) {
        setReportData(result);
      }
    });
  };

  const handlePrint = () => {
    window.print();
  };

  // Process data helper
  const getDaysInMonthData = () => {
    if (!reportData) return [];
    const daysCount = new Date(tahun, bulan, 0).getDate();
    const list = [];
    
    for (let day = 1; day <= daysCount; day++) {
      const dateStr = `${tahun}-${String(bulan).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
      const log = reportData.presensi.find((p: any) => p.tgl_presensi === dateStr);
      const isHoliday = reportData.holidays.find((h: any) => h.tanggal_libur === dateStr);
      
      list.push({
        day,
        dateStr,
        log,
        isHoliday
      });
    }
    return list;
  };

  const daysData = getDaysInMonthData();

  // Statistics Calculation
  const stats = daysData.reduce((acc, item) => {
    if (item.log) {
      if (item.log.status === 'h') {
        acc.hadir += 1;
        // check late
        if (item.log.jam_in && item.log.jam_kerja?.jam_masuk && item.log.jam_in > item.log.jam_kerja.jam_masuk) {
          acc.terlambat += 1;
        }
      } else if (item.log.status === 'i') {
        acc.izin += 1;
      } else if (item.log.status === 's') {
        acc.sakit += 1;
      } else if (item.log.status === 'c') {
        acc.cuti += 1;
      }
    } else if (item.isHoliday) {
      acc.libur += 1;
    } else {
      // Check if day is weekend (Saturday = 6, Sunday = 0)
      const d = new Date(item.dateStr);
      const dayOfWeek = d.getDay();
      if (dayOfWeek === 0 || dayOfWeek === 6) {
        acc.weekend += 1;
      } else {
        acc.alpha += 1;
      }
    }
    return acc;
  }, { hadir: 0, terlambat: 0, izin: 0, sakit: 0, cuti: 0, libur: 0, weekend: 0, alpha: 0 });

  return (
    <div className="space-y-6">
      {/* 1. Selector Form panel */}
      <div className="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-lg print:hidden">
        <form onSubmit={handleFetchReport} className="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
          <div className="space-y-1.5">
            <label className="text-[10px] uppercase font-bold text-slate-400 pl-0.5 flex items-center">
              <User className="w-3.5 h-3.5 mr-1 text-blue-500" />
              <span>Pilih Karyawan</span>
            </label>
            <select
              value={nik}
              onChange={(e) => setNik(e.target.value)}
              className="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-blue-500"
            >
              <option value="">-- Pilih Karyawan --</option>
              {karyawanList.map((k) => (
                <option key={k.nik} value={k.nik}>
                  {k.nama_lengkap} ({k.nik})
                </option>
              ))}
            </select>
          </div>

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
                <FileText className="w-4 h-4" />
                <span>Tampilkan Laporan</span>
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

      {/* 2. Print Control button */}
      {reportData && (
        <div className="flex justify-end print:hidden">
          <button
            onClick={handlePrint}
            className="bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl py-2 px-5 text-xs font-bold transition-all flex items-center space-x-1.5 shadow-md shadow-emerald-600/10"
          >
            <Printer className="w-4 h-4" />
            <span>Cetak / Simpan PDF</span>
          </button>
        </div>
      )}

      {/* 3. Laporan Sheet (Printable Area) */}
      {reportData && (
        <div className="bg-white text-slate-900 border border-slate-200 rounded-3xl p-6 md:p-10 shadow-lg print:border-none print:shadow-none print:p-0 print:m-0 print:bg-white print:text-black">
          {/* Header Laporan */}
          <div className="flex flex-col md:flex-row justify-between items-start md:items-center border-b-2 border-slate-900 pb-6 mb-6">
            <div className="space-y-1.5">
              <h2 className="text-xl md:text-2xl font-black uppercase tracking-tight text-slate-950">Laporan Kehadiran Karyawan</h2>
              <p className="text-xs text-slate-500 uppercase tracking-widest font-bold">
                Periode: {months.find(m => m.value === bulan)?.name} {tahun}
              </p>
            </div>
            <div className="mt-4 md:mt-0 text-left md:text-right text-xs space-y-1">
              <div className="font-extrabold text-sm text-slate-950">PRESENSI ARJUNA</div>
              <div className="text-slate-650">Sistem E-Presensi Berbasis Geolocation</div>
            </div>
          </div>

          {/* Profil Karyawan Info */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4 pb-6 border-b border-slate-200 mb-6 text-sm">
            <div className="space-y-2">
              <table className="w-full text-left table-fixed">
                <tbody>
                  <tr>
                    <td className="w-24 font-bold text-slate-500 uppercase text-[10px]">NIK</td>
                    <td className="w-4">:</td>
                    <td className="font-semibold text-slate-950">{reportData.karyawan.nik}</td>
                  </tr>
                  <tr>
                    <td className="font-bold text-slate-500 uppercase text-[10px]">Nama Lengkap</td>
                    <td>:</td>
                    <td className="font-semibold text-slate-950">{reportData.karyawan.nama_lengkap}</td>
                  </tr>
                  <tr>
                    <td className="font-bold text-slate-500 uppercase text-[10px]">Jabatan</td>
                    <td>:</td>
                    <td className="text-slate-700">{reportData.karyawan.jabatan}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div className="space-y-2">
              <table className="w-full text-left table-fixed">
                <tbody>
                  <tr>
                    <td className="w-24 font-bold text-slate-500 uppercase text-[10px]">Departemen</td>
                    <td className="w-4">:</td>
                    <td className="font-semibold text-slate-950">{reportData.karyawan.departemen?.nama_dept || '-'}</td>
                  </tr>
                  <tr>
                    <td className="font-bold text-slate-500 uppercase text-[10px]">Kantor Cabang</td>
                    <td>:</td>
                    <td className="font-semibold text-slate-950">{reportData.karyawan.cabang?.nama_cabang || '-'}</td>
                  </tr>
                  <tr>
                    <td className="font-bold text-slate-500 uppercase text-[10px]">No. Handphone</td>
                    <td>:</td>
                    <td className="text-slate-700">{reportData.karyawan.no_hp}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          {/* Rekap Ringkasan/Statistik */}
          <div className="grid grid-cols-2 sm:grid-cols-6 gap-3 mb-6">
            <div className="bg-slate-50 border border-slate-200 p-3 rounded-2xl text-center">
              <div className="text-[10px] uppercase font-bold text-slate-500">Hadir</div>
              <div className="text-xl font-extrabold text-emerald-600 mt-1">{stats.hadir} Hari</div>
            </div>
            <div className="bg-slate-50 border border-slate-200 p-3 rounded-2xl text-center">
              <div className="text-[10px] uppercase font-bold text-slate-500">Terlambat</div>
              <div className="text-xl font-extrabold text-red-650 mt-1">{stats.terlambat} Kali</div>
            </div>
            <div className="bg-slate-50 border border-slate-200 p-3 rounded-2xl text-center">
              <div className="text-[10px] uppercase font-bold text-slate-500">Sakit</div>
              <div className="text-xl font-extrabold text-rose-600 mt-1">{stats.sakit} Hari</div>
            </div>
            <div className="bg-slate-50 border border-slate-200 p-3 rounded-2xl text-center">
              <div className="text-[10px] uppercase font-bold text-slate-500">Izin</div>
              <div className="text-xl font-extrabold text-amber-500 mt-1">{stats.izin} Hari</div>
            </div>
            <div className="bg-slate-50 border border-slate-200 p-3 rounded-2xl text-center">
              <div className="text-[10px] uppercase font-bold text-slate-500">Cuti</div>
              <div className="text-xl font-extrabold text-purple-600 mt-1">{stats.cuti} Hari</div>
            </div>
            <div className="bg-slate-50 border border-slate-200 p-3 rounded-2xl text-center">
              <div className="text-[10px] uppercase font-bold text-slate-500">Mangkir (Alpha)</div>
              <div className="text-xl font-extrabold text-slate-650 mt-1">{stats.alpha} Hari</div>
            </div>
          </div>

          {/* Detail Tabel Presensi Harian */}
          <div className="border border-slate-200 rounded-2xl overflow-hidden mb-8">
            <table className="w-full text-left text-xs border-collapse">
              <thead>
                <tr className="bg-slate-50 border-b border-slate-200 text-slate-700 uppercase font-black tracking-wider text-[9px]">
                  <th className="px-4 py-3 text-center w-12">No</th>
                  <th className="px-4 py-3">Hari / Tanggal</th>
                  <th className="px-4 py-3 text-center">Jam Masuk</th>
                  <th className="px-4 py-3 text-center">Jam Pulang</th>
                  <th className="px-4 py-3 text-center">Status Kehadiran</th>
                  <th className="px-4 py-3">Keterangan / Jam Kerja</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 text-slate-800">
                {daysData.map((dayItem, index) => {
                  const dateObj = new Date(dayItem.dateStr);
                  const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                  const formattedDate = `${dayNames[dateObj.getDay()]}, ${dayItem.day} ${months.find(m => m.value === bulan)?.name} ${tahun}`;

                  let statusBadge = <span className="text-red-500 font-bold">ALPHA (Mangkir)</span>;
                  let jamInStr = '--:--';
                  let jamOutStr = '--:--';
                  let notes = '';

                  // Weekend check
                  const isWeekend = dateObj.getDay() === 0 || dateObj.getDay() === 6;
                  if (isWeekend) {
                    statusBadge = <span className="text-slate-400 font-medium">Libur Akhir Pekan</span>;
                  }

                  if (dayItem.isHoliday) {
                    statusBadge = <span className="text-amber-500 font-bold">Hari Libur Nasional</span>;
                    notes = dayItem.isHoliday.keterangan;
                  }

                  if (dayItem.log) {
                    const l = dayItem.log;
                    jamInStr = l.jam_in ? l.jam_in.substring(0, 5) : '--:--';
                    jamOutStr = l.jam_out ? l.jam_out.substring(0, 5) : '--:--';

                    if (l.status === 'h') {
                      const isLate = l.jam_in && l.jam_kerja?.jam_masuk && l.jam_in > l.jam_kerja.jam_masuk;
                      statusBadge = isLate 
                        ? <span className="text-red-650 font-black">HADIR (TERLAMBAT)</span> 
                        : <span className="text-emerald-600 font-black">HADIR (TEPAT WAKTU)</span>;
                      notes = l.jam_kerja?.nama_jam_kerja || 'Reguler';
                    } else if (l.status === 'i') {
                      statusBadge = <span className="text-amber-500 font-bold">IZIN</span>;
                      notes = 'Izin disetujui';
                    } else if (l.status === 's') {
                      statusBadge = <span className="text-rose-500 font-bold">SAKIT</span>;
                      notes = 'Sakit disetujui';
                    } else if (l.status === 'c') {
                      statusBadge = <span className="text-purple-600 font-bold">CUTI</span>;
                      notes = 'Cuti disetujui';
                    }
                  }

                  return (
                    <tr 
                      key={dayItem.day} 
                      className={`hover:bg-slate-50 transition-colors ${
                        isWeekend ? 'bg-slate-50/40 text-slate-500' : ''
                      } ${dayItem.isHoliday ? 'bg-amber-50/20 text-slate-650' : ''}`}
                    >
                      <td className="px-4 py-2.5 text-center font-semibold text-slate-400">{index + 1}</td>
                      <td className="px-4 py-2.5 font-medium">{formattedDate}</td>
                      <td className="px-4 py-2.5 text-center font-mono text-xs">{jamInStr}</td>
                      <td className="px-4 py-2.5 text-center font-mono text-xs">{jamOutStr}</td>
                      <td className="px-4 py-2.5 text-center text-[10px] tracking-wide font-semibold">{statusBadge}</td>
                      <td className="px-4 py-2.5 text-slate-500 italic max-w-xs truncate">{notes}</td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>

          {/* Tanda Tangan */}
          <div className="flex justify-between items-center text-xs mt-12 pb-12 pt-6">
            <div>
              <p className="text-slate-500 mb-14 uppercase text-[9px] font-bold tracking-wider">Karyawan Bersangkutan</p>
              <div className="border-b border-slate-900 w-40 mb-1"></div>
              <p className="font-extrabold text-slate-900">{reportData.karyawan.nama_lengkap}</p>
            </div>
            <div className="text-right">
              <p className="text-slate-500 mb-14 uppercase text-[9px] font-bold tracking-wider">
                Malang, {new Date().getDate()} {months.find(m => m.value === (new Date().getMonth() + 1))?.name} {new Date().getFullYear()}
              </p>
              <div className="border-b border-slate-900 w-40 mb-1 ml-auto"></div>
              <p className="font-extrabold text-slate-900">Pimpinan / HRD</p>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
