import React from 'react';
import type { Metadata } from 'next';
import { getSession } from '@/lib/session';
import { supabase } from '@/lib/supabase';
import { redirect } from 'next/navigation';
import EditProfileClient from './editprofile-client';

export const metadata: Metadata = {
  title: 'Edit Profil - E-Presensi',
  description: 'Ubah informasi profil, nomor handphone, dan password karyawan.',
};

export default async function EditProfilePage() {
  const session = await getSession();
  if (!session || session.role !== 'employee') {
    redirect('/login');
  }

  // Fetch the employee details
  const { data: employee } = await supabase
    .from('karyawan')
    .select('*')
    .eq('nik', session.nik)
    .single();

  if (!employee) {
    return (
      <div className="p-6 text-center text-red-400 bg-slate-900 border border-red-500/20 rounded-2xl mt-12">
        Error: Data karyawan tidak ditemukan.
      </div>
    );
  }

  return (
    <EditProfileClient employee={employee} />
  );
}
