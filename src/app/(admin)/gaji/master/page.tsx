import React from 'react';
import type { Metadata } from 'next';
import { getSession } from '@/lib/session';
import { redirect } from 'next/navigation';
import { getMasterGajiListAction } from '@/lib/actions/gaji';
import { supabase } from '@/lib/supabase';
import MasterGajiClient from './master-gaji-client';

export const metadata: Metadata = {
  title: 'Master Gaji Karyawan - E-Presensi Admin',
  description: 'Pengaturan standar gaji pokok, tunjangan transport, dan tanggal masuk kerja karyawan Yayasan Arjuna.',
};

export default async function MasterGajiPage() {
  const session = await getSession();
  if (!session || (session.role !== 'administrator' && session.role !== 'admin departemen')) {
    redirect('/panel');
  }

  const masterRes = await getMasterGajiListAction();
  const masterList = masterRes.success ? (masterRes.list || []) : [];

  const { data: cabangList } = await supabase.from('cabang').select('*').order('nama_cabang');
  const { data: departemenList } = await supabase.from('departemen').select('*').order('nama_dept');

  return (
    <div className="space-y-6">
      <MasterGajiClient
        initialMasterList={masterList}
        cabangList={cabangList || []}
        departemenList={departemenList || []}
      />
    </div>
  );
}
