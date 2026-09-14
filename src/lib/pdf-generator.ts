import { PDFDocument, rgb, StandardFonts } from 'pdf-lib';

export interface SlipGajiData {
  nama_karyawan: string;
  jabatan: string;
  kode_cabang?: string;
  kode_dept?: string;
  tipe_komponen?: string;
  tgl_masuk?: string;
  tgl_slip: string;
  periode_str: string;
  
  // Komponen Penghasilan
  gaji_pokok: number;
  tunj_transport: number;
  uang_kegiatan?: number;
  ket_uang_kegiatan?: string;
  uang_ekstra?: number;
  ket_uang_ekstra?: string;
  uang_lembur?: number;
  ket_uang_lembur?: string;
  bonus?: number;
  ket_bonus?: string;
  penyesuaian_gaji?: number;
  ket_penyesuaian?: string;
  
  // Skema Remunerasi Bunda TPA
  insentif_pool_siswa?: number;
  insentif_hadir_dasar?: number;
  premi_disiplin_pagi?: number;
  denda_kedisiplinan?: number;
  
  // Komponen Potongan
  pot_absensi?: number;
  kasbon?: number;
  ket_kasbon?: string;
  sanksi_disiplin?: number;
  ket_sanksi?: string;
  
  // Total
  subtotal_penerimaan: number;
  subtotal_potongan: number;
  total_gaji_bersih: number;
}

function formatRupiah(num: number): string {
  if (!num || num === 0) return '-';
  return 'Rp ' + Math.round(num).toLocaleString('id-ID');
}

export async function generateSlipGajiPdf(data: SlipGajiData): Promise<Uint8Array> {
  const pdfDoc = await PDFDocument.create();
  const page = pdfDoc.addPage([595.28, 841.89]); // A4 Size (points)
  const fontRegular = await pdfDoc.embedFont(StandardFonts.Helvetica);
  const fontBold = await pdfDoc.embedFont(StandardFonts.HelveticaBold);

  const { width, height } = page.getSize();
  const margin = 40;
  const contentWidth = width - margin * 2;

  // Render two slips per A4 sheet (Top half & Bottom half)
  const renderSingleSlip = (originY: number) => {
    let curY = originY;

    // 1. Kop Header (Bersama & Sederhana)
    const cabangName = (data.kode_cabang || '').toUpperCase() === 'LANGSEP' 
      ? 'KB - TK - TPA ALAM ARJUNA (LANGSEP)' 
      : 'KB - TK - TPA ISLAM PLUS ARJUNA (CITANDUI)';

    const alamatStr = (data.kode_cabang || '').toUpperCase() === 'LANGSEP'
      ? 'Sekretariat: Jl. Raya Langsep 23 B Telp. (0341) 567723 Malang'
      : 'Sekretariat: Jl. Citandui 15 B Malang Telp. (0341) 4371932';

    // Yayasan Title
    page.drawText('YAYASAN ARJUNA CENDEKIA', {
      x: margin + (contentWidth - fontBold.widthOfTextAtSize('YAYASAN ARJUNA CENDEKIA', 11)) / 2,
      y: curY,
      size: 11,
      font: fontBold,
      color: rgb(0.1, 0.1, 0.1)
    });
    curY -= 14;

    page.drawText(cabangName, {
      x: margin + (contentWidth - fontBold.widthOfTextAtSize(cabangName, 13)) / 2,
      y: curY,
      size: 13,
      font: fontBold,
      color: rgb(0.05, 0.2, 0.5)
    });
    curY -= 12;

    page.drawText(alamatStr, {
      x: margin + (contentWidth - fontRegular.widthOfTextAtSize(alamatStr, 8)) / 2,
      y: curY,
      size: 8,
      font: fontRegular,
      color: rgb(0.3, 0.3, 0.3)
    });
    curY -= 8;

    // Double horizontal border lines
    page.drawLine({
      start: { x: margin, y: curY },
      end: { x: width - margin, y: curY },
      thickness: 1.5,
      color: rgb(0.1, 0.1, 0.1)
    });
    page.drawLine({
      start: { x: margin, y: curY - 2 },
      end: { x: width - margin, y: curY - 2 },
      thickness: 0.5,
      color: rgb(0.2, 0.2, 0.2)
    });
    curY -= 14;

    // 2. Title "SLIP GAJI"
    page.drawText('SLIP GAJI', {
      x: margin + (contentWidth - fontBold.widthOfTextAtSize('SLIP GAJI', 11)) / 2,
      y: curY,
      size: 11,
      font: fontBold,
      color: rgb(0.1, 0.1, 0.1)
    });
    curY -= 14;

    // 3. Metadata Grid (2 Columns)
    const leftColX = margin + 10;
    const rightColX = margin + 270;
    const metaFontSize = 8.5;

    // Row 1
    page.drawText(`Tanggal   : ${data.tgl_slip}`, { x: leftColX, y: curY, size: metaFontSize, font: fontRegular });
    page.drawText(`Tgl Masuk Kerja : ${data.tgl_masuk || '-'}`, { x: rightColX, y: curY, size: metaFontSize, font: fontRegular });
    curY -= 12;

    // Row 2
    page.drawText(`Nama      : ${data.nama_karyawan}`, { x: leftColX, y: curY, size: metaFontSize, font: fontBold });
    page.drawText(`Periode             : ${data.periode_str}`, { x: rightColX, y: curY, size: metaFontSize, font: fontBold });
    curY -= 12;

    // Row 3
    page.drawText(`Jabatan   : ${data.jabatan}`, { x: leftColX, y: curY, size: metaFontSize, font: fontRegular });
    page.drawText('(KETERANGAN)', { x: rightColX + 70, y: curY, size: 7.5, font: fontRegular, color: rgb(0.4, 0.4, 0.4) });
    curY -= 14;

    // 4. Items Table
    const itemLabelX = margin + 60;
    const itemColonX = margin + 210;
    const itemValueX = margin + 290;
    const itemDescX = margin + 310;
    const rowHeight = 11;
    const itemFontSize = 8;

    const drawRow = (label: string, value: number, desc?: string, isNegative?: boolean, isSubtotal?: boolean) => {
      const f = isSubtotal ? fontBold : fontRegular;
      page.drawText(label, { x: itemLabelX, y: curY, size: itemFontSize, font: f });
      page.drawText(':', { x: itemColonX, y: curY, size: itemFontSize, font: f });
      
      const valStr = formatRupiah(value);
      const textX = itemValueX - f.widthOfTextAtSize(valStr, itemFontSize);
      page.drawText(valStr, { x: textX, y: curY, size: itemFontSize, font: f, color: isNegative ? rgb(0.8, 0.1, 0.1) : rgb(0, 0, 0) });
      
      if (desc) {
        page.drawText(desc, { x: itemDescX, y: curY, size: 7, font: fontRegular, color: rgb(0.3, 0.3, 0.3) });
      }
      curY -= rowHeight;
    };

    // Income items
    drawRow('GAJI POKOK', data.gaji_pokok);
    drawRow('TUNJ. TRANSPORTASI', data.tunj_transport);

    if (data.tipe_komponen === 'guru' || (data.uang_kegiatan && data.uang_kegiatan > 0)) {
      drawRow('UANG KEGIATAN', data.uang_kegiatan || 0, data.ket_uang_kegiatan);
    }
    if (data.tipe_komponen === 'guru' || (data.uang_ekstra && data.uang_ekstra > 0)) {
      drawRow('UANG EKSTRA', data.uang_ekstra || 0, data.ket_uang_ekstra);
    }
    if (data.tipe_komponen === 'tpa' || data.tipe_komponen === 'staff' || (data.uang_lembur && data.uang_lembur > 0)) {
      drawRow('LEMBUR / OVERTIME', data.uang_lembur || 0, data.ket_uang_lembur);
    }
    if (data.bonus && data.bonus > 0) {
      drawRow('BONUS / THR', data.bonus, data.ket_bonus);
    }

    // Remunerasi Baru Bunda TPA (jika ada nilai)
    if (data.insentif_pool_siswa && data.insentif_pool_siswa > 0) {
      drawRow('INSENTIF POOL SISWA', data.insentif_pool_siswa, 'Pool Gerbang 41 Siswa');
    }
    if ((data.insentif_hadir_dasar && data.insentif_hadir_dasar > 0) || (data.premi_disiplin_pagi && data.premi_disiplin_pagi > 0)) {
      const totInsentifPagi = (data.insentif_hadir_dasar || 0) + (data.premi_disiplin_pagi || 0);
      drawRow('INSENTIF HADIR & PAGI 06.30', totInsentifPagi, 'Disiplin Pagi');
    }
    if (data.penyesuaian_gaji && data.penyesuaian_gaji > 0) {
      drawRow('PENYESUAIAN GAJI', data.penyesuaian_gaji, data.ket_penyesuaian);
    }

    // Subtotal Penerimaan Line
    page.drawLine({
      start: { x: margin + 180, y: curY + 2 },
      end: { x: itemValueX + 5, y: curY + 2 },
      thickness: 0.8,
      color: rgb(0.2, 0.2, 0.2)
    });
    drawRow('SUBTOTAL PENERIMAAN', data.subtotal_penerimaan, undefined, false, true);

    // Deduction items
    drawRow('POT. ABSENSI', data.pot_absensi || 0, undefined, true);
    if (data.kasbon && data.kasbon > 0) {
      drawRow('PINJAMAN / KAS BON', data.kasbon, data.ket_kasbon, true);
    }
    if (data.sanksi_disiplin && data.sanksi_disiplin > 0) {
      drawRow('SANKSI DISIPLIN', data.sanksi_disiplin, data.ket_sanksi, true);
    }
    if (data.denda_kedisiplinan && data.denda_kedisiplinan > 0) {
      drawRow('DENDA KEDISIPLINAN', data.denda_kedisiplinan, 'Izin Mendadak / Keterlambatan', true);
    }

    // Subtotal / Total Line
    page.drawLine({
      start: { x: margin + 60, y: curY + 2 },
      end: { x: itemValueX + 5, y: curY + 2 },
      thickness: 1.2,
      color: rgb(0.1, 0.1, 0.1)
    });
    drawRow('TOTAL DITERIMA (THP)', data.total_gaji_bersih, undefined, false, true);
    curY -= 6;

    // 5. Signature Footer (Kartika P. Bendahara Yayasan & Penerima)
    const signDateStr = `MALANG, ${data.tgl_slip.toUpperCase()}`;
    page.drawText(signDateStr, {
      x: margin + 300,
      y: curY,
      size: 7.5,
      font: fontBold,
      color: rgb(0.1, 0.1, 0.1)
    });
    curY -= 12;

    page.drawText('Dibuat Oleh,', { x: leftColX + 20, y: curY, size: 8, font: fontRegular });
    page.drawText('Penerima,', { x: margin + 300, y: curY, size: 8, font: fontRegular });
    curY -= 28;

    // Names printed cleanly without manual scribble
    page.drawText('Kartika P.', { x: leftColX + 20, y: curY, size: 8.5, font: fontBold });
    page.drawText(`( ${data.nama_karyawan} )`, { x: margin + 280, y: curY, size: 8.5, font: fontRegular });
    curY -= 10;

    page.drawText('Bendahara Yayasan', { x: leftColX + 20, y: curY, size: 7.5, font: fontRegular, color: rgb(0.3, 0.3, 0.3) });
    page.drawText(`Staff ${cabangName}`, { x: margin + 280, y: curY, size: 7.5, font: fontRegular, color: rgb(0.3, 0.3, 0.3) });
  };

  // Render Top Slip (Y: height - 35)
  renderSingleSlip(height - 30);

  // Dashed Cut Line in middle of A4
  const midY = height / 2;
  page.drawLine({
    start: { x: margin - 15, y: midY },
    end: { x: width - margin + 15, y: midY },
    thickness: 0.7,
    dashArray: [4, 4],
    color: rgb(0.5, 0.5, 0.5)
  });
  page.drawText('Gunting di sini (Arsip Yayasan / Salinan Karyawan)', {
    x: margin + 170,
    y: midY + 3,
    size: 6.5,
    font: fontRegular,
    color: rgb(0.5, 0.5, 0.5)
  });

  // Render Bottom Slip (Y: midY - 25)
  renderSingleSlip(midY - 25);

  return await pdfDoc.save();
}
