import React from 'react';
import { getKaryawanListAction } from '@/lib/actions/admin';
import LaporanClient from './laporan-client';

export const revalidate = 0;

export default async function LaporanPage() {
  const result = await getKaryawanListAction();
  const karyawanList = result.success && result.karyawan ? result.karyawan : [];

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between print:hidden">
        <div>
          <h1 className="text-2xl font-bold text-white tracking-tight">Laporan Kehadiran</h1>
          <p className="text-xs text-slate-400 mt-1">
            Cetak kartu kehadiran dan rekapitulasi presensi bulanan per karyawan.
          </p>
        </div>
      </div>

      <LaporanClient karyawanList={karyawanList} />
    </div>
  );
}
