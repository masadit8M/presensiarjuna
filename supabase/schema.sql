-- Enable UUID extension if needed
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- 1. Table Departemen
CREATE TABLE IF NOT EXISTS departemen (
    kode_dept VARCHAR(20) PRIMARY KEY,
    nama_dept VARCHAR(50) NOT NULL
);

-- 2. Table Cabang
CREATE TABLE IF NOT EXISTS cabang (
    kode_cabang VARCHAR(20) PRIMARY KEY,
    nama_cabang VARCHAR(100) NOT NULL,
    lokasi_cabang VARCHAR(255) NOT NULL, -- Format: "latitude,longitude"
    radius_cabang INTEGER NOT NULL DEFAULT 100 -- Radius in meters
);

-- 3. Table Karyawan (Employees)
CREATE TABLE IF NOT EXISTS karyawan (
    nik VARCHAR(30) PRIMARY KEY,
    nama_lengkap VARCHAR(150) NOT NULL,
    jabatan VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL, -- bcrypt hash
    foto VARCHAR(255) DEFAULT NULL,
    kode_dept VARCHAR(20) REFERENCES departemen(kode_dept) ON UPDATE CASCADE ON DELETE SET NULL,
    kode_cabang VARCHAR(20) REFERENCES cabang(kode_cabang) ON UPDATE CASCADE ON DELETE SET NULL,
    status_location SMALLINT NOT NULL DEFAULT 1, -- 1: Lock GPS, 0: Unlock GPS
    status_jam_kerja SMALLINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 4. Table Jam Kerja (Work Hours)
CREATE TABLE IF NOT EXISTS jam_kerja (
    kode_jam_kerja VARCHAR(20) PRIMARY KEY,
    nama_jam_kerja VARCHAR(50) NOT NULL,
    jam_masuk TIME NOT NULL,
    jam_pulang TIME NOT NULL,
    awal_jam_masuk TIME NOT NULL,
    akhir_jam_masuk TIME NOT NULL,
    lintashari SMALLINT NOT NULL DEFAULT 0, -- 1: Yes, 0: No
    awal_jam_istirahat TIME DEFAULT NULL,
    akhir_jam_istirahat TIME DEFAULT NULL
);

-- 5. Table Master Cuti (Leave types)
CREATE TABLE IF NOT EXISTS master_cuti (
    kode_cuti VARCHAR(20) PRIMARY KEY,
    nama_cuti VARCHAR(100) NOT NULL,
    jml_hari SMALLINT NOT NULL
);

-- 6. Table Pengajuan Izin / Sakit / Cuti
CREATE TABLE IF NOT EXISTS pengajuan_izin (
    kode_izin VARCHAR(50) PRIMARY KEY,
    nik VARCHAR(30) NOT NULL REFERENCES karyawan(nik) ON UPDATE CASCADE ON DELETE CASCADE,
    tgl_izin_dari DATE NOT NULL,
    tgl_izin_sampai DATE NOT NULL,
    status CHAR(1) NOT NULL, -- 'i' for izin, 's' for sakit, 'c' for cuti
    status_approved CHAR(1) NOT NULL DEFAULT '0', -- '0': Pending, '1': Approved, '2': Rejected
    keterangan TEXT NOT NULL,
    doc_sid VARCHAR(255) DEFAULT NULL, -- URL or filename of doctor certificate/letter
    kode_cuti VARCHAR(20) REFERENCES master_cuti(kode_cuti) ON UPDATE CASCADE ON DELETE SET NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 7. Table Presensi (Attendance Records)
CREATE TABLE IF NOT EXISTS presensi (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    nik VARCHAR(30) NOT NULL REFERENCES karyawan(nik) ON UPDATE CASCADE ON DELETE CASCADE,
    tgl_presensi DATE NOT NULL,
    jam_in TIME DEFAULT NULL,
    jam_out TIME DEFAULT NULL,
    foto_in VARCHAR(255) DEFAULT NULL,
    foto_out VARCHAR(255) DEFAULT NULL,
    lokasi_in VARCHAR(255) DEFAULT NULL, -- Format: "latitude,longitude"
    lokasi_out VARCHAR(255) DEFAULT NULL, -- Format: "latitude,longitude"
    kode_jam_kerja VARCHAR(20) REFERENCES jam_kerja(kode_jam_kerja) ON UPDATE CASCADE ON DELETE SET NULL,
    status CHAR(1) NOT NULL DEFAULT 'h', -- 'h' for hadir, 'i' for izin, 's' for sakit, 'c' for cuti
    kode_izin VARCHAR(50) REFERENCES pengajuan_izin(kode_izin) ON UPDATE CASCADE ON DELETE SET NULL,
    total_jam DOUBLE PRECISION DEFAULT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT unique_nik_tgl UNIQUE (nik, tgl_presensi)
);

-- 8. Table Hari Libur (Holidays)
CREATE TABLE IF NOT EXISTS harilibur (
    kode_libur VARCHAR(50) PRIMARY KEY,
    tanggal_libur DATE NOT NULL,
    keterangan VARCHAR(255) NOT NULL
);

-- 9. Table Konfigurasi Jam Kerja (Employee specific schedule)
CREATE TABLE IF NOT EXISTS konfigurasi_jamkerja (
    nik VARCHAR(30) NOT NULL REFERENCES karyawan(nik) ON UPDATE CASCADE ON DELETE CASCADE,
    hari VARCHAR(20) NOT NULL,
    kode_jam_kerja VARCHAR(20) NOT NULL REFERENCES jam_kerja(kode_jam_kerja) ON UPDATE CASCADE ON DELETE CASCADE,
    PRIMARY KEY (nik, hari)
);

-- 10. Table Konfigurasi Jam Kerja Departemen (Group level schedule)
CREATE TABLE IF NOT EXISTS konfigurasi_jk_dept (
    kode_jk_dept VARCHAR(50) PRIMARY KEY,
    kode_dept VARCHAR(20) NOT NULL REFERENCES departemen(kode_dept) ON UPDATE CASCADE ON DELETE CASCADE,
    kode_cabang VARCHAR(20) NOT NULL REFERENCES cabang(kode_cabang) ON UPDATE CASCADE ON DELETE CASCADE
);

-- 11. Table Detail Konfigurasi Jam Kerja Departemen
CREATE TABLE IF NOT EXISTS konfigurasi_jk_dept_detail (
    kode_jk_dept VARCHAR(50) NOT NULL REFERENCES konfigurasi_jk_dept(kode_jk_dept) ON UPDATE CASCADE ON DELETE CASCADE,
    hari VARCHAR(20) NOT NULL,
    kode_jam_kerja VARCHAR(20) NOT NULL REFERENCES jam_kerja(kode_jam_kerja) ON UPDATE CASCADE ON DELETE CASCADE,
    PRIMARY KEY (kode_jk_dept, hari)
);

-- 12. Table Konfigurasi Jam Kerja by Date (Override schedule on specific dates)
CREATE TABLE IF NOT EXISTS konfigurasi_jamkerja_by_date (
    id SERIAL PRIMARY KEY,
    nik VARCHAR(30) NOT NULL REFERENCES karyawan(nik) ON UPDATE CASCADE ON DELETE CASCADE,
    tanggal DATE NOT NULL,
    kode_jam_kerja VARCHAR(20) NOT NULL REFERENCES jam_kerja(kode_jam_kerja) ON UPDATE CASCADE ON DELETE CASCADE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT unique_nik_tanggal UNIQUE (nik, tanggal)
);

-- 13. Table Users (Admin Users, mapped with Supabase auth.users or standalone credentials)
-- In Next.js + Supabase, we can use Supabase Auth metadata for role division,
-- but we also maintain an admin users table for compatibility and queries.
CREATE TABLE IF NOT EXISTS admin_users (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL, -- bcrypt hash for standalone login
    nama_lengkap VARCHAR(150) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'admin departemen', -- 'administrator', 'admin departemen'
    kode_dept VARCHAR(20) REFERENCES departemen(kode_dept) ON UPDATE CASCADE ON DELETE SET NULL,
    kode_cabang VARCHAR(20) REFERENCES cabang(kode_cabang) ON UPDATE CASCADE ON DELETE SET NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);


-- Initial Mock Data (Optional, but useful for testing)
INSERT INTO departemen (kode_dept, nama_dept) VALUES
('IT', 'Information Technology'),
('HR', 'Human Resources'),
('FIN', 'Finance'),
('OPS', 'Operations')
ON CONFLICT (kode_dept) DO NOTHING;

INSERT INTO cabang (kode_cabang, nama_cabang, lokasi_cabang, radius_cabang) VALUES
('PST', 'Kantor Pusat', '-7.9818987,112.626503', 100), -- Malang area example
('CB1', 'Cabang Surabaya', '-7.250444,112.768845', 150)
ON CONFLICT (kode_cabang) DO NOTHING;

INSERT INTO jam_kerja (kode_jam_kerja, nama_jam_kerja, jam_masuk, jam_pulang, awal_jam_masuk, akhir_jam_masuk, lintashari) VALUES
('RGL', 'Reguler Pagi', '08:00:00', '17:00:00', '07:00:00', '09:00:00', 0),
('SHF1', 'Shift Pagi', '06:00:00', '14:00:00', '05:00:00', '07:00:00', 0),
('SHF2', 'Shift Siang', '14:00:00', '22:00:00', '13:00:00', '15:00:00', 0),
('SHF3', 'Shift Malam (Lintas Hari)', '22:00:00', '06:00:00', '21:00:00', '23:00:00', 1)
ON CONFLICT (kode_jam_kerja) DO NOTHING;

-- Default password is "12345" hashed using bcrypt
-- Hash for '12345': $2b$10$h4euut2Q0OtcO3Q3Rn7Oq.boL9lUJKjqyRrXzGoGYe6u6gCAdPKcO
INSERT INTO karyawan (nik, nama_lengkap, jabatan, no_hp, password, kode_dept, kode_cabang, status_location) VALUES
('12345', 'Karyawan Demo', 'Staff IT', '081234567890', '$2b$10$h4euut2Q0OtcO3Q3Rn7Oq.boL9lUJKjqyRrXzGoGYe6u6gCAdPKcO', 'IT', 'PST', 1)
ON CONFLICT (nik) DO NOTHING;

-- Initial Admin User (Default email: admin@presensi.local, password: 12345)
-- Hash for '12345': $2b$10$h4euut2Q0OtcO3Q3Rn7Oq.boL9lUJKjqyRrXzGoGYe6u6gCAdPKcO
INSERT INTO admin_users (email, password, nama_lengkap, role, kode_dept, kode_cabang) VALUES
('admin@presensi.local', '$2b$10$h4euut2Q0OtcO3Q3Rn7Oq.boL9lUJKjqyRrXzGoGYe6u6gCAdPKcO', 'Administrator', 'administrator', 'IT', 'PST')
ON CONFLICT (email) DO NOTHING;

