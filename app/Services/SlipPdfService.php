<?php

namespace App\Services;

require_once __DIR__ . '/fpdf.php';

use FPDF;

class SlipPdfService
{
    /**
     * Generate PDF file for a given payroll detail record.
     * 
     * @param object $detail (from penggajian_detail with karyawan & periode joined)
     * @return string Path to generated file relative to public/
     */
    public static function generatePdf($detail)
    {
        $dir = function_exists('public_path') ? public_path('uploads/slip') : realpath(__DIR__ . '/../../public') . '/uploads/slip';
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        $filename = 'slip_' . $detail->id . '_' . $detail->nik . '.pdf';
        $fullPath = $dir . '/' . $filename;

        // Create PDF with A4 Portrait (can fit 2 slips per page or 1 clean half-page slip)
        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->SetMargins(15, 12, 15);
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();

        self::renderSlipContent($pdf, $detail, 12);

        // Optional: Render second copy on bottom half (2-in-1 paperless slip like photo sample)
        $pdf->SetDrawColor(180, 180, 180);
        $pdf->Line(10, 145, 200, 145);
        self::renderSlipContent($pdf, $detail, 152);

        $pdf->Output('F', $fullPath);

        return 'uploads/slip/' . $filename;
    }

    private static function renderSlipContent($pdf, $detail, $startY)
    {
        $pdf->SetY($startY);

        // Branch & Dept naming
        $cabang = strtoupper($detail->kode_cabang ?? 'CTD');
        $isLangsep = str_contains($cabang, 'LSP') || str_contains(strtoupper($detail->nama_cabang ?? ''), 'LANGSEP');
        
        $lembagaName = $isLangsep ? 'TPA - KB - TK ALAM ARJUNA' : 'KB TK ISLAM PLUS ARJUNA';
        $subHeader = $isLangsep ? 'KEL. BARENG KEC. KLOJEN KOTA MALANG' : 'KEC. BLIMBING KEL. PURWANTORO KOTA MALANG';
        $alamat = $isLangsep 
            ? 'Sekretariat: Jl. Raya Langsep 23 B Telp. (0341) 567723 Malang 65112' 
            : 'Sekretariat: Jl. Citandui 15 B Malang Kodepos 65116 Telp. (0341) 4371932';

        // Kop Lembaga
        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->Cell(0, 5, 'YAYASAN ARJUNA CENDEKIA', 0, 1, 'C');
        $pdf->SetFont('Helvetica', 'B', 11);
        $pdf->Cell(0, 5, $lembagaName, 0, 1, 'C');
        $pdf->SetFont('Helvetica', '', 8);
        $pdf->Cell(0, 4, $subHeader, 0, 1, 'C');
        $pdf->Cell(0, 4, $alamat, 0, 1, 'C');

        // Line separator (double line)
        $currentY = $pdf->GetY() + 1;
        $pdf->SetLineWidth(0.6);
        $pdf->Line(15, $currentY, 195, $currentY);
        $pdf->SetLineWidth(0.2);
        $pdf->Line(15, $currentY + 0.8, 195, $currentY + 0.8);
        $pdf->SetY($currentY + 2.5);

        // Title
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->Cell(0, 5, 'SLIP GAJI', 0, 1, 'C');
        $pdf->Ln(1);

        // Employee and Period Info (2 columns)
        $pdf->SetFont('Helvetica', '', 8);
        $periodeStr = strtoupper($detail->nama_periode ?? ($detail->bulan . '/' . $detail->tahun));
        $tglSlip = date('d-M-Y', strtotime($detail->tgl_selesai ?? date('Y-m-d')));

        // Left Col: Tanggal, Nama, Jabatan
        // Right Col: Tgl Masuk, Periode
        $wLeftLabel = 22;
        $wLeftVal = 65;
        $wRightLabel = 25;
        $wRightVal = 65;

        // Row 1
        $pdf->Cell($wLeftLabel, 4, 'Tanggal', 0, 0);
        $pdf->Cell(3, 4, ':', 0, 0);
        $pdf->Cell($wLeftVal, 4, $tglSlip, 0, 0);
        $pdf->Cell($wRightLabel, 4, 'Periode', 0, 0);
        $pdf->Cell(3, 4, ':', 0, 0);
        $pdf->Cell($wRightVal, 4, $periodeStr, 0, 1);

        // Row 2
        $pdf->Cell($wLeftLabel, 4, 'Nama', 0, 0);
        $pdf->Cell(3, 4, ':', 0, 0);
        $pdf->SetFont('Helvetica', 'B', 8);
        $pdf->Cell($wLeftVal, 4, strtoupper($detail->nama_lengkap ?? '-'), 0, 0);
        $pdf->SetFont('Helvetica', '', 8);
        $pdf->Cell($wRightLabel, 4, 'Hari Kerja (HK)', 0, 0);
        $pdf->Cell(3, 4, ':', 0, 0);
        $pdf->Cell($wRightVal, 4, ($detail->hadir ?? 0) . ' / ' . ($detail->hk_standar ?? 26) . ' HK', 0, 1);

        // Row 3
        $pdf->Cell($wLeftLabel, 4, 'Jabatan', 0, 0);
        $pdf->Cell(3, 4, ':', 0, 0);
        $pdf->Cell($wLeftVal, 4, strtoupper($detail->jabatan ?? '-'), 0, 0);
        $pdf->Cell($wRightLabel, 4, 'Kehadiran', 0, 0);
        $pdf->Cell(3, 4, ':', 0, 0);
        $pdf->Cell($wRightVal, 4, 'Izin: ' . ($detail->izin ?? 0) . ' | Sakit: ' . ($detail->sakit ?? 0) . ' | Alpha: ' . ($detail->alpha ?? 0), 0, 1);

        $pdf->Ln(2);

        // Table Component Breakdown
        $pdf->SetFont('Helvetica', 'B', 8);
        $pdf->Cell(90, 4, '(PENGHASILAN / PENERIMAAN)', 0, 0);
        $pdf->Cell(5, 4, '', 0, 0);
        $pdf->Cell(85, 4, '(POTONGAN)', 0, 1);

        $pdf->SetFont('Helvetica', '', 8);

        // Prepare items array
        $pendapatan = [
            ['GAJI POKOK', $detail->gaji_pokok],
            ['TUNJ. TRANSPORTASI', $detail->tunjangan_transportasi],
        ];

        if (($detail->tunjangan_jabatan ?? 0) > 0) {
            $pendapatan[] = ['TUNJ. JABATAN', $detail->tunjangan_jabatan];
        }
        if (($detail->tunjangan_konsumsi ?? 0) > 0) {
            $pendapatan[] = ['TUNJ. KONSUMSI', $detail->tunjangan_konsumsi];
        }
        if (($detail->tunjangan_kehadiran ?? 0) > 0) {
            $pendapatan[] = ['TUNJ. KEHADIRAN', $detail->tunjangan_kehadiran];
        }
        if (($detail->honor_kegiatan ?? 0) > 0) {
            $pendapatan[] = ['UANG KEGIATAN', $detail->honor_kegiatan];
        }
        if (($detail->honor_ekskul ?? 0) > 0) {
            $pendapatan[] = ['UANG EKSTRA / EKSKUL', $detail->honor_ekskul];
        }
        if (($detail->upah_lembur ?? 0) > 0) {
            $pendapatan[] = ['UANG LEMBUR', $detail->upah_lembur];
        }
        if (($detail->insentif_pool_tpa ?? 0) > 0) {
            $pendapatan[] = ['INSENTIF POOL TPA (GATE 41)', $detail->insentif_pool_tpa];
        }
        if (($detail->reward_disiplin ?? 0) > 0) {
            $pendapatan[] = ['REWARD DISIPLIN 06.30', $detail->reward_disiplin];
        }
        if (($detail->bonus_tambahan ?? 0) > 0) {
            $pendapatan[] = ['BONUS / THR', $detail->bonus_tambahan];
        }
        if (($detail->tunjangan_lainnya ?? 0) > 0) {
            $pendapatan[] = ['PENYESUAIAN / LAINNYA', $detail->tunjangan_lainnya];
        }

        $potongan = [];
        if (($detail->potongan_absen ?? 0) > 0) {
            $potongan[] = ['POT. ABSENSI (IZIN/ALPHA)', $detail->potongan_absen];
        }
        if (($detail->potongan_terlambat ?? 0) > 0) {
            $potongan[] = ['POT. TERLAMBAT (' . ($detail->terlambat_jam ?? 0) . ' Jam)', $detail->potongan_terlambat];
        }
        if (($detail->potongan_kasbon ?? 0) > 0) {
            $potongan[] = ['PINJAMAN / KAS BON', $detail->potongan_kasbon];
        }
        if (($detail->potongan_bpjs ?? 0) > 0) {
            $potongan[] = ['BPJS', $detail->potongan_bpjs];
        }
        if (($detail->potongan_lainnya ?? 0) > 0) {
            $potongan[] = ['SANKSI / POT. LAIN', $detail->potongan_lainnya];
        }

        $maxRows = max(count($pendapatan), count($potongan), 4);

        for ($i = 0; $i < $maxRows; $i++) {
            // Left (Pendapatan)
            if (isset($pendapatan[$i])) {
                $pdf->Cell(50, 4, $pendapatan[$i][0], 0, 0);
                $pdf->Cell(5, 4, ': Rp', 0, 0);
                $pdf->Cell(35, 4, number_format($pendapatan[$i][1], 0, ',', '.'), 0, 0, 'R');
            } else {
                $pdf->Cell(90, 4, '', 0, 0);
            }

            $pdf->Cell(5, 4, '', 0, 0);

            // Right (Potongan)
            if (isset($potongan[$i])) {
                $pdf->Cell(45, 4, $potongan[$i][0], 0, 0);
                $pdf->Cell(5, 4, ': Rp', 0, 0);
                $pdf->Cell(35, 4, number_format($potongan[$i][1], 0, ',', '.'), 0, 1, 'R');
            } else {
                $pdf->Cell(85, 4, '', 0, 1);
            }
        }

        // Subtotals line
        $subtotalY = $pdf->GetY() + 1;
        $pdf->SetDrawColor(200, 200, 200);
        $pdf->Line(15, $subtotalY, 105, $subtotalY);
        $pdf->Line(110, $subtotalY, 195, $subtotalY);
        $pdf->SetY($subtotalY + 1);

        $pdf->SetFont('Helvetica', 'B', 8);
        $pdf->Cell(50, 4, 'TOTAL PENGHASILAN', 0, 0);
        $pdf->Cell(5, 4, ': Rp', 0, 0);
        $pdf->Cell(35, 4, number_format($detail->total_penghasilan, 0, ',', '.'), 0, 0, 'R');

        $pdf->Cell(5, 4, '', 0, 0);

        $pdf->Cell(45, 4, 'TOTAL POTONGAN', 0, 0);
        $pdf->Cell(5, 4, ': Rp', 0, 0);
        $pdf->Cell(35, 4, number_format($detail->total_potongan, 0, ',', '.'), 0, 1, 'R');

        // Total Gaji Bersih / THP
        $thpY = $pdf->GetY() + 1;
        $pdf->SetDrawColor(50, 50, 50);
        $pdf->Line(15, $thpY, 195, $thpY);
        $pdf->SetY($thpY + 1);

        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->Cell(140, 5, 'TOTAL GAJI BERSIH (TAKE HOME PAY)', 0, 0);
        $pdf->Cell(10, 5, ': Rp', 0, 0);
        $pdf->Cell(45, 5, number_format($detail->gaji_bersih, 0, ',', '.'), 0, 1, 'R');

        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
        $pdf->Ln(2);

        // Signatures (exact match: Kartika P. Bendahara Yayasan)
        $tglCetak = 'MALANG, ' . strtoupper(date('d F Y'));
        $pdf->SetFont('Helvetica', '', 8);
        $pdf->Cell(95, 4, 'Dibuat Oleh,', 0, 0, 'C');
        $pdf->Cell(95, 4, $tglCetak, 0, 1, 'C');

        $pdf->Cell(95, 4, '', 0, 0, 'C');
        $pdf->Cell(95, 4, 'Penerima,', 0, 1, 'C');

        $pdf->Ln(10);

        $pdf->SetFont('Helvetica', 'B', 8);
        $pdf->Cell(95, 4, '(Kartika P.)', 0, 0, 'C');
        $pdf->Cell(95, 4, '( ' . strtoupper($detail->nama_lengkap ?? '........................') . ' )', 0, 1, 'C');

        $pdf->SetFont('Helvetica', '', 8);
        $pdf->Cell(95, 4, 'Bendahara Yayasan', 0, 0, 'C');
        $pdf->Cell(95, 4, 'Staff / Guru', 0, 1, 'C');
    }
}
