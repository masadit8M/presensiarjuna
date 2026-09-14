-- ============================================================
-- MIGRATION: SISTEM PENGGAJIAN & SLIP GAJI YAYASAN ARJUNA CENDEKIA
-- Tanggal: 14 September 2026
-- ============================================================

-- 1. Table Master Komponen Gaji Tetap Karyawan
CREATE TABLE IF NOT EXISTS public.gaji_master (
    nik VARCHAR(30) PRIMARY KEY REFERENCES public.karyawan(nik) ON UPDATE CASCADE ON DELETE CASCADE,
    tipe_komponen VARCHAR(20) NOT NULL DEFAULT 'guru', -- 'guru', 'tpa', 'staff'
    gaji_pokok NUMERIC(12, 2) NOT NULL DEFAULT 0,
    tunj_transport NUMERIC(12, 2) NOT NULL DEFAULT 150000,
    tgl_masuk DATE DEFAULT NULL,
    no_rek VARCHAR(50) DEFAULT NULL,
    nama_bank VARCHAR(50) DEFAULT NULL,
    catatan TEXT DEFAULT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 2. Table Master Periode Penggajian Bulanan
CREATE TABLE IF NOT EXISTS public.penggajian_periode (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    bulan SMALLINT NOT NULL CHECK (bulan >= 1 AND bulan <= 12),
    tahun INTEGER NOT NULL CHECK (tahun >= 2020),
    tgl_slip DATE NOT NULL,
    tgl_transfer DATE DEFAULT NULL,
    hari_kerja_standar SMALLINT NOT NULL DEFAULT 26,
    status VARCHAR(20) NOT NULL DEFAULT 'draft', -- 'draft', 'final'
    total_penerimaan NUMERIC(15, 2) NOT NULL DEFAULT 0,
    total_potongan NUMERIC(15, 2) NOT NULL DEFAULT 0,
    total_gaji_bersih NUMERIC(15, 2) NOT NULL DEFAULT 0,
    catatan TEXT DEFAULT NULL,
    
    -- Skema Remunerasi Baru Bunda TPA (Opsional/Bisa diaktifkan per periode)
    is_skema_bunda_aktif BOOLEAN NOT NULL DEFAULT FALSE,
    siswa_daycare_lunas INTEGER NOT NULL DEFAULT 0, -- Jumlah siswa lunas SPP daycare (Gerbang 41)
    
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT unique_periode_bulan_tahun UNIQUE (bulan, tahun)
);

-- 3. Table Detail Slip Gaji per Karyawan (Fully Editable by Superadmin)
CREATE TABLE IF NOT EXISTS public.penggajian_detail (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    periode_id UUID NOT NULL REFERENCES public.penggajian_periode(id) ON DELETE CASCADE,
    nik VARCHAR(30) NOT NULL REFERENCES public.karyawan(nik) ON UPDATE CASCADE,
    nama_karyawan VARCHAR(150) NOT NULL,
    jabatan VARCHAR(100) NOT NULL,
    kode_dept VARCHAR(20) DEFAULT NULL,
    kode_cabang VARCHAR(20) DEFAULT NULL,
    tipe_komponen VARCHAR(20) NOT NULL DEFAULT 'guru', -- 'guru', 'tpa', 'staff'
    tgl_masuk DATE DEFAULT NULL,
    
    -- Rekap Presensi Terintegrasi (Hasil Kalkulasi Otomatis Presensi)
    hk SMALLINT NOT NULL DEFAULT 26,
    hadir SMALLINT NOT NULL DEFAULT 0,
    izin SMALLINT NOT NULL DEFAULT 0,
    sakit SMALLINT NOT NULL DEFAULT 0,
    cuti SMALLINT NOT NULL DEFAULT 0,
    alpha SMALLINT NOT NULL DEFAULT 0,
    terlambat_count SMALLINT NOT NULL DEFAULT 0,
    jam_telat_total NUMERIC(6, 2) NOT NULL DEFAULT 0,
    
    -- Rincian Penerimaan (Editable)
    gaji_pokok NUMERIC(12, 2) NOT NULL DEFAULT 0,
    tunj_transport NUMERIC(12, 2) NOT NULL DEFAULT 150000,
    uang_kegiatan NUMERIC(12, 2) NOT NULL DEFAULT 0,
    ket_uang_kegiatan TEXT DEFAULT NULL,
    uang_ekstra NUMERIC(12, 2) NOT NULL DEFAULT 0,
    ket_uang_ekstra TEXT DEFAULT NULL,
    uang_lembur NUMERIC(12, 2) NOT NULL DEFAULT 0,
    ket_uang_lembur TEXT DEFAULT NULL,
    bonus NUMERIC(12, 2) NOT NULL DEFAULT 0,
    ket_bonus TEXT DEFAULT NULL,
    penyesuaian_gaji NUMERIC(12, 2) NOT NULL DEFAULT 0,
    ket_penyesuaian TEXT DEFAULT NULL,
    
    -- Tambahan Skema Remunerasi Bunda TPA
    insentif_pool_siswa NUMERIC(12, 2) NOT NULL DEFAULT 0,
    insentif_hadir_dasar NUMERIC(12, 2) NOT NULL DEFAULT 0,
    premi_disiplin_pagi NUMERIC(12, 2) NOT NULL DEFAULT 0,
    denda_kedisiplinan NUMERIC(12, 2) NOT NULL DEFAULT 0,
    
    -- Rincian Potongan (Editable)
    pot_absensi NUMERIC(12, 2) NOT NULL DEFAULT 0,
    kasbon NUMERIC(12, 2) NOT NULL DEFAULT 0,
    ket_kasbon TEXT DEFAULT NULL,
    sanksi_disiplin NUMERIC(12, 2) NOT NULL DEFAULT 0,
    ket_sanksi TEXT DEFAULT NULL,
    
    -- Kalkulasi Akhir
    subtotal_penerimaan NUMERIC(12, 2) NOT NULL DEFAULT 0,
    subtotal_potongan NUMERIC(12, 2) NOT NULL DEFAULT 0,
    total_gaji_bersih NUMERIC(12, 2) NOT NULL DEFAULT 0,
    
    -- Status & Distribusi Slip Gaji
    wa_status VARCHAR(20) NOT NULL DEFAULT 'pending', -- 'pending', 'sent', 'failed'
    wa_sent_at TIMESTAMP WITH TIME ZONE DEFAULT NULL,
    pdf_url TEXT DEFAULT NULL,
    
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT unique_detail_periode_nik UNIQUE (periode_id, nik)
);

-- Indexing for high-performance reporting
CREATE INDEX IF NOT EXISTS idx_penggajian_periode_thn_bln ON public.penggajian_periode(tahun, bulan);
CREATE INDEX IF NOT EXISTS idx_penggajian_detail_periode ON public.penggajian_detail(periode_id);
CREATE INDEX IF NOT EXISTS idx_penggajian_detail_nik ON public.penggajian_detail(nik);

-- Enable Row Level Security (RLS)
ALTER TABLE public.gaji_master ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.penggajian_periode ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.penggajian_detail ENABLE ROW LEVEL SECURITY;

-- Allow service role full access
DROP POLICY IF EXISTS "Service role full access on gaji_master" ON public.gaji_master;
CREATE POLICY "Service role full access on gaji_master" ON public.gaji_master FOR ALL USING (true);

DROP POLICY IF EXISTS "Service role full access on penggajian_periode" ON public.penggajian_periode;
CREATE POLICY "Service role full access on penggajian_periode" ON public.penggajian_periode FOR ALL USING (true);

DROP POLICY IF EXISTS "Service role full access on penggajian_detail" ON public.penggajian_detail;
CREATE POLICY "Service role full access on penggajian_detail" ON public.penggajian_detail FOR ALL USING (true);

