'use server';

import { supabase, supabaseAdmin } from '@/lib/supabase';
import { getSession } from '@/lib/session';

export async function storeIzinAction(prevState: any, formData: FormData) {
  const session = await getSession();
  if (!session || session.role !== 'employee') {
    return { error: 'Akses ditolak. Silakan login kembali.' };
  }

  const nik = session.nik!;
  const tgl_izin_dari = formData.get('tgl_izin_dari') as string;
  const tgl_izin_sampai = formData.get('tgl_izin_sampai') as string;
  const status = formData.get('status') as string; // 'i' | 's' | 'c'
  const keterangan = formData.get('keterangan') as string;
  const kode_cuti = formData.get('kode_cuti') as string || null;
  const docFileBase64 = formData.get('doc_sid') as string; // base64 string
  const docFileName = formData.get('doc_sid_name') as string;

  if (!tgl_izin_dari || !tgl_izin_sampai || !status || !keterangan) {
    return { error: 'Semua kolom wajib diisi (Dari Tanggal, Sampai Tanggal, Tipe, Keterangan).' };
  }

  try {
    // Generate unique kode_izin: IZ-NIK-TIMESTAMP
    const uniqueId = Date.now().toString().substring(5);
    const kode_izin = `IZ-${nik}-${uniqueId}`;

    let docPath = null;

    // If sick request and doctor's certificate is uploaded
    if (status === 's' && docFileBase64 && docFileName) {
      if (!supabaseAdmin) {
        return { error: 'Kesalahan sistem database admin.' };
      }
      
      const fileBuffer = Buffer.from(docFileBase64.replace(/^data:.*?;base64,/, ''), 'base64');
      const ext = docFileName.split('.').pop();
      const fileName = `${kode_izin}.${ext}`;
      const filePath = `izin/${fileName}`;

      // Upload file to bucket 'absensi'
      const { error: uploadError } = await supabaseAdmin.storage
        .from('absensi')
        .upload(filePath, fileBuffer, {
          contentType: `image/${ext === 'pdf' ? 'pdf' : ext}`,
          upsert: true,
        });

      if (uploadError) {
        console.error('File Upload Error:', uploadError);
        return { error: 'Gagal mengunggah dokumen surat izin/sakit.' };
      }

      docPath = filePath;
    }

    // Insert into database
    const { error: insertError } = await supabase
      .from('pengajuan_izin')
      .insert({
        kode_izin,
        nik,
        tgl_izin_dari,
        tgl_izin_sampai,
        status,
        status_approved: '0', // Pending
        keterangan,
        doc_sid: docPath,
        kode_cuti: status === 'c' ? kode_cuti : null
      });

    if (insertError) {
      console.error('Insert Error:', insertError);
      return { error: 'Gagal menyimpan data pengajuan izin.' };
    }

    return { success: true, message: 'Pengajuan izin berhasil diajukan!' };

  } catch (err) {
    console.error('Exception in storeIzinAction:', err);
    return { error: 'Terjadi kesalahan sistem, silakan coba lagi.' };
  }
}

export async function deleteIzinAction(kode_izin: string) {
  const session = await getSession();
  if (!session || session.role !== 'employee') {
    return { error: 'Akses ditolak.' };
  }

  try {
    // Check if it's still pending
    const { data: izin, error: fetchError } = await supabase
      .from('pengajuan_izin')
      .select('*')
      .eq('kode_izin', kode_izin)
      .eq('nik', session.nik)
      .single();

    if (fetchError || !izin) {
      return { error: 'Pengajuan izin tidak ditemukan.' };
    }

    if (izin.status_approved !== '0') {
      return { error: 'Maaf, pengajuan yang sudah disetujui/ditolak tidak dapat dihapus.' };
    }

    // Delete doc from storage if any
    if (izin.doc_sid && supabaseAdmin) {
      await supabaseAdmin.storage
        .from('absensi')
        .remove([izin.doc_sid]);
    }

    // Delete from DB
    const { error: deleteError } = await supabase
      .from('pengajuan_izin')
      .delete()
      .eq('kode_izin', kode_izin);

    if (deleteError) {
      return { error: 'Gagal menghapus data dari database.' };
    }

    return { success: true };
  } catch (err) {
    return { error: 'Terjadi kesalahan, silakan coba lagi.' };
  }
}
