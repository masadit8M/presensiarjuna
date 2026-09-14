import React from 'react';
import type { Metadata } from 'next';
import { getSession } from '@/lib/session';
import { redirect } from 'next/navigation';
import { getPenggajianPeriodeListAction } from '@/lib/actions/gaji';
import { supabase } from '@/lib/supabase';
import GajiClient from './gaji-client';

export const metadata: Metadata = {
  title: 'Penggajian & Slip Gaji - E-Presensi Admin',
  description: 'Manajemen penggajian karyawan, integrasi presensi otomatis, cetak slip gaji paperless, dan kirim dokumen PDF via WhatsApp.',
};

export default async function AdminGajiPage() {
  const session = await getSession();
  if (!session || (session.role !== 'administrator' && session.role !== 'admin departemen')) {
    redirect('/panel');
  }

  // 1. Fetch period list
  const periodeRes = await getPenggajianPeriodeListAction();
  const periodeList = periodeRes.success ? (periodeRes.list || []) : [];
  const tableMissing = !!periodeRes.tableMissing;

  // 2. Fetch branches & departments for filters
  const { data: cabangList } = await supabase
    .from('cabang')
    .select('*')
    .order('nama_cabang');

  const { data: departemenList } = await supabase
    .from('departemen')
    .select('*')
    .order('nama_dept');

  return (
    <div className="space-y-6">
      <GajiClient
        initialPeriodeList={periodeList}
        cabangList={cabangList || []}
        departemenList={departemenList || []}
        isTableMissing={tableMissing}
      />
    </div>
  );
}
