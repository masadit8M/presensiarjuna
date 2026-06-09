'use server';

import { supabase } from '@/lib/supabase';
import { createSession, destroySession } from '@/lib/session';
import bcrypt from 'bcryptjs';

export async function loginEmployeeAction(prevState: any, formData: FormData) {
  const nik = formData.get('nik') as string;
  const password = formData.get('password') as string;

  if (!nik || !password) {
    return { error: 'NIK dan Password wajib diisi!' };
  }

  try {
    // 1. Fetch employee details
    const { data: karyawan, error } = await supabase
      .from('karyawan')
      .select('*')
      .eq('nik', nik)
      .single();

    if (error || !karyawan) {
      return { error: 'NIK atau Password Salah' };
    }

    // 2. Compare password
    const isMatch = await bcrypt.compare(password, karyawan.password);
    if (!isMatch) {
      return { error: 'NIK atau Password Salah' };
    }

    // 3. Create Session
    await createSession({
      nik: karyawan.nik,
      nama_lengkap: karyawan.nama_lengkap,
      role: 'employee',
      kode_dept: karyawan.kode_dept || undefined,
      kode_cabang: karyawan.kode_cabang || undefined,
      foto: karyawan.foto || undefined,
      status_location: karyawan.status_location,
    });

    return { success: true };
  } catch (err) {
    return { error: 'Terjadi kesalahan sistem, silakan coba lagi.' };
  }
}

export async function loginAdminAction(prevState: any, formData: FormData) {
  const email = formData.get('email') as string;
  const password = formData.get('password') as string;

  if (!email || !password) {
    return { error: 'Email dan Password wajib diisi!' };
  }

  try {
    // 1. Fetch admin details
    const { data: admin, error } = await supabase
      .from('admin_users')
      .select('*')
      .eq('email', email)
      .single();

    if (error || !admin) {
      return { error: 'Username atau Password Salah' };
    }

    // 2. Compare password
    const isMatch = await bcrypt.compare(password, admin.password);
    if (!isMatch) {
      return { error: 'Username atau Password Salah' };
    }

    // 3. Create Session
    await createSession({
      email: admin.email,
      nama_lengkap: admin.nama_lengkap,
      role: admin.role as 'administrator' | 'admin departemen',
      kode_dept: admin.kode_dept || undefined,
      kode_cabang: admin.kode_cabang || undefined,
    });

    return { success: true };
  } catch (err) {
    return { error: 'Terjadi kesalahan sistem, silakan coba lagi.' };
  }
}

export async function logoutAction() {
  await destroySession();
}
