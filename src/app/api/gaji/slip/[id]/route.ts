import { supabase, supabaseAdmin } from '@/lib/supabase';
import { generateSlipGajiPdf, SlipGajiData } from '@/lib/pdf-generator';

const MONTH_NAMES = [
  '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

export async function GET(
  request: Request,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const { id } = await params;
    const client = supabaseAdmin || supabase;

    // Fetch detail & periode
    const { data: detail, error: detErr } = await client
      .from('penggajian_detail')
      .select('*, penggajian_periode(*)')
      .eq('id', id)
      .single();

    if (detErr || !detail) {
      return new Response('Data slip gaji tidak ditemukan.', { status: 404 });
    }

    const periode = detail.penggajian_periode;
    const periodeStr = `${MONTH_NAMES[periode.bulan]} ${periode.tahun}`.toUpperCase();

    const slipData: SlipGajiData = {
      nama_karyawan: detail.nama_karyawan,
      jabatan: detail.jabatan,
      kode_cabang: detail.kode_cabang,
      kode_dept: detail.kode_dept,
      tipe_komponen: detail.tipe_komponen,
      tgl_masuk: detail.tgl_masuk ? new Date(detail.tgl_masuk).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-',
      tgl_slip: `${new Date(periode.tgl_slip).getDate()}-${MONTH_NAMES[periode.bulan].substring(0, 3)}-${periode.tahun}`,
      periode_str: periodeStr,
      gaji_pokok: detail.gaji_pokok,
      tunj_transport: detail.tunj_transport,
      uang_kegiatan: detail.uang_kegiatan,
      ket_uang_kegiatan: detail.ket_uang_kegiatan,
      uang_ekstra: detail.uang_ekstra,
      ket_uang_ekstra: detail.ket_uang_ekstra,
      uang_lembur: detail.uang_lembur,
      ket_uang_lembur: detail.ket_uang_lembur,
      bonus: detail.bonus,
      ket_bonus: detail.ket_bonus,
      penyesuaian_gaji: detail.penyesuaian_gaji,
      ket_penyesuaian: detail.ket_penyesuaian,
      insentif_pool_siswa: detail.insentif_pool_siswa,
      insentif_hadir_dasar: detail.insentif_hadir_dasar,
      premi_disiplin_pagi: detail.premi_disiplin_pagi,
      denda_kedisiplinan: detail.denda_kedisiplinan,
      pot_absensi: detail.pot_absensi,
      kasbon: detail.kasbon,
      ket_kasbon: detail.ket_kasbon,
      sanksi_disiplin: detail.sanksi_disiplin,
      ket_sanksi: detail.ket_sanksi,
      subtotal_penerimaan: detail.subtotal_penerimaan,
      subtotal_potongan: detail.subtotal_potongan,
      total_gaji_bersih: detail.total_gaji_bersih,
    };

    const pdfBytes = await generateSlipGajiPdf(slipData);
    const cleanName = detail.nama_karyawan.replace(/[^a-zA-Z0-9_-]/g, '_');
    const filename = `Slip_Gaji_${cleanName}_${MONTH_NAMES[periode.bulan]}_${periode.tahun}.pdf`;

    return new Response(Buffer.from(pdfBytes), {
      status: 200,
      headers: {
        'Content-Type': 'application/pdf',
        'Content-Disposition': `inline; filename="${filename}"`,
        'Cache-Control': 'no-cache',
      },
    });
  } catch (error: any) {
    console.error('Error generating PDF slip:', error);
    return new Response('Terjadi kesalahan saat memproses PDF.', { status: 500 });
  }
}
