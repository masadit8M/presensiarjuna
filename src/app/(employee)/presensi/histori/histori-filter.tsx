'use client';

import React from 'react';
import { useRouter, useSearchParams } from 'next/navigation';

export default function HistoriFilter({
  selectedMonth,
  selectedYear,
}: {
  selectedMonth: number;
  selectedYear: number;
}) {
  const router = useRouter();
  const searchParams = useSearchParams();

  const months = [
    { value: 1, label: 'Januari' },
    { value: 2, label: 'Februari' },
    { value: 3, label: 'Maret' },
    { value: 4, label: 'April' },
    { value: 5, label: 'Mei' },
    { value: 6, label: 'Juni' },
    { value: 7, label: 'Juli' },
    { value: 8, label: 'Agustus' },
    { value: 9, label: 'September' },
    { value: 10, label: 'Oktober' },
    { value: 11, label: 'November' },
    { value: 12, label: 'Desember' },
  ];

  // Generate years: current year and last 3 years
  const currentYear = new Date().getFullYear();
  const years = Array.from({ length: 4 }, (_, i) => currentYear - i);

  const handleFilterChange = (month: number, year: number) => {
    const params = new URLSearchParams(searchParams.toString());
    params.set('month', month.toString());
    params.set('year', year.toString());
    router.push(`/presensi/histori?${params.toString()}`);
  };

  return (
    <div className="bg-[#131936] border border-white/[0.05] rounded-2xl p-4 grid grid-cols-2 gap-3 shadow-lg">
      <div className="space-y-1">
        <label className="text-[10px] uppercase font-bold text-slate-400 pl-0.5">Bulan</label>
        <select
          value={selectedMonth}
          onChange={(e) => handleFilterChange(parseInt(e.target.value), selectedYear)}
          className="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
        >
          {months.map((m) => (
            <option key={m.value} value={m.value}>
              {m.label}
            </option>
          ))}
        </select>
      </div>

      <div className="space-y-1">
        <label className="text-[10px] uppercase font-bold text-slate-400 pl-0.5">Tahun</label>
        <select
          value={selectedYear}
          onChange={(e) => handleFilterChange(selectedMonth, parseInt(e.target.value))}
          className="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
        >
          {years.map((y) => (
            <option key={y} value={y}>
              {y}
            </option>
          ))}
        </select>
      </div>
    </div>
  );
}
