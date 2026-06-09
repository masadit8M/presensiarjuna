'use server';

import { supabase, supabaseAdmin } from '@/lib/supabase';
import { getSession, createSession } from '@/lib/session';
import bcrypt from 'bcryptjs';

export async function updateEmployeeProfileAction(prevState: any, formData: FormData) {
  const session = await getSession();
  if (!session || session.role !== 'employee') {
    return { error: 'Akses ditolak.' };
  }

  const nik = session.nik!;
  const nama_lengkap = formData.get('nama_lengkap') as string;
  const no_hp = formData.get('no_hp') as string;
  const password = formData.get('password') as string;
  const photoBase64 = formData.get('foto') as string;
  const photoName = formData.get('foto_name') as string;

  if (!nama_lengkap || !no_hp) {
    return { error: 'Nama Lengkap dan Nomor Handphone wajib diisi!' };
  }

  try {
    // 1. Fetch current record
    const { data: current, error: fetchError } = await supabase
      .from('karyawan')
      .select('*')
      .eq('nik', nik)
      .single();

    if (fetchError || !current) {
      return { error: 'Profil karyawan tidak ditemukan.' };
    }

    let foto = current.foto;

    // 2. Upload photo if provided
    if (photoBase64 && photoName && supabaseAdmin) {
      const fileBuffer = Buffer.from(photoBase64.replace(/^data:.*?;base64,/, ''), 'base64');
      const ext = photoName.split('.').pop();
      const fileName = `${nik}.${ext}`;
      const filePath = `${fileName}`;

      // Upload to 'karyawan' bucket
      const { error: uploadError } = await supabaseAdmin.storage
        .from('karyawan')
        .upload(filePath, fileBuffer, {
          contentType: `image/${ext}`,
          upsert: true
        });

      if (uploadError) {
        console.error('Storage Upload Error:', uploadError);
        return { error: 'Gagal mengunggah foto profil.' };
      }

      foto = fileName;
    }

    // 3. Prepare data to update
    const updateData: any = {
      nama_lengkap,
      no_hp,
      foto,
    };

    if (password) {
      updateData.password = await bcrypt.hash(password, 10);
    }

    // 4. Update DB
    const { error: updateError } = await supabase
      .from('karyawan')
      .update(updateData)
      .eq('nik', nik);

    if (updateError) {
      console.error('Update Error:', updateError);
      return { error: 'Gagal memperbarui profil di database.' };
    }

    // 5. Update session cookie
    await createSession({
      ...session,
      nama_lengkap,
      foto: foto || undefined,
    });

    return { success: true, message: 'Profil berhasil diperbarui!' };

  } catch (err) {
    console.error('Exception in updateProfileAction:', err);
    return { error: 'Terjadi kesalahan sistem, silakan coba lagi.' };
  }
}
