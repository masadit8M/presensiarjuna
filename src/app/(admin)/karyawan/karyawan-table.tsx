'use client';

import React, { useState, useTransition } from 'react';
import { useRouter } from 'next/navigation';
import { 
  createKaryawanAction, updateKaryawanAction, deleteKaryawanAction, 
  resetKaryawanPasswordAction, toggleLockLocationAction, toggleLockJamKerjaAction 
} from '@/lib/actions/admin';
import { 
  UserPlus, ShieldAlert, Key, MapPin, Edit, Trash2, Search, X, Lock, Unlock, ShieldAlert as LockAlert, Loader2
} from 'lucide-react';

interface KaryawanTableProps {
  karyawanList: any[];
  departemenList: any[];
  cabangList: any[];
}

export default function KaryawanTable({
  karyawanList,
  departemenList,
  cabangList,
}: KaryawanTableProps) {
  const router = useRouter();
  const [isPending, startTransition] = useTransition();

  // Filter States
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedDept, setSelectedDept] = useState('');
  const [selectedBranch, setSelectedBranch] = useState('');

  // Modal States
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingKaryawan, setEditingKaryawan] = useState<any>(null); // null means "Create" mode
  const [error, setError] = useState<string | null>(null);

  // Form Fields State
  const [nik, setNik] = useState('');
  const [namaLengkap, setNamaLengkap] = useState('');
  const [jabatan, setJabatan] = useState('');
  const [noHp, setNoHp] = useState('');
  const [kodeDept, setKodeDept] = useState('');
  const [kodeCabang, setKodeCabang] = useState('');
  const [password, setPassword] = useState('');

  const openCreateModal = () => {
    setEditingKaryawan(null);
    setNik('');
    setNamaLengkap('');
    setJabatan('');
    setNoHp('');
    setKodeDept('');
    setKodeCabang('');
    setPassword('');
    setError(null);
    setIsModalOpen(true);
  };

  const openEditModal = (k: any) => {
    setEditingKaryawan(k);
    setNik(k.nik);
    setNamaLengkap(k.nama_lengkap);
    setJabatan(k.jabatan);
    setNoHp(k.no_hp);
    setKodeDept(k.kode_dept || '');
    setKodeCabang(k.kode_cabang || '');
    setPassword('');
    setError(null);
    setIsModalOpen(true);
  };

  const handleFormSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);

    startTransition(async () => {
      const formData = new FormData();
      formData.append('nik', nik);
      formData.append('nama_lengkap', namaLengkap);
      formData.append('jabatan', jabatan);
      formData.append('no_hp', noHp);
      formData.append('kode_dept', kodeDept);
      formData.append('kode_cabang', kodeCabang);
      formData.append('password', password);

      const result = editingKaryawan 
        ? await updateKaryawanAction(editingKaryawan.nik, formData)
        : await createKaryawanAction(formData);

      if (result?.error) {
        setError(result.error);
      } else {
        setIsModalOpen(false);
        router.refresh();
      }
    });
  };

  const handleDelete = (nik: string, name: string) => {
    if (!confirm(`Apakah Anda yakin ingin menghapus data karyawan "${name}"? Seluruh histori presensi karyawan ini juga akan terhapus!`)) {
      return;
    }

    startTransition(async () => {
      const result = await deleteKaryawanAction(nik);
      if (result?.error) {
        alert(result.error);
      } else {
        router.refresh();
      }
    });
  };

  const handleResetPassword = (nik: string, name: string) => {
    const val = prompt(`Masukkan password baru untuk karyawan "${name}":`, "12345");
    if (val === null) return; // user cancelled
    
    const cleanPassword = val.trim();
    if (!cleanPassword) {
      alert("Password tidak boleh kosong!");
      return;
    }

    startTransition(async () => {
      const result = await resetKaryawanPasswordAction(nik, cleanPassword);
      if (result?.error) {
        alert(result.error);
      } else {
        alert(`Password untuk "${name}" berhasil diubah menjadi "${cleanPassword}"`);
        router.refresh();
      }
    });
  };

  const handleToggleLockGPS = (nik: string, currentStatus: number) => {
    startTransition(async () => {
      const result = await toggleLockLocationAction(nik, currentStatus);
      if (result?.error) {
        alert(result.error);
      } else {
        router.refresh();
      }
    });
  };

  const handleToggleLockJam = (nik: string, currentStatus: number) => {
    startTransition(async () => {
      const result = await toggleLockJamKerjaAction(nik, currentStatus);
      if (result?.error) {
        alert(result.error);
      } else {
        router.refresh();
      }
    });
  };

  // Filter logs logic
  const filteredKaryawan = karyawanList.filter((k) => {
    const matchesSearch = k.nama_lengkap.toLowerCase().includes(searchTerm.toLowerCase()) || k.nik.includes(searchTerm);
    const matchesDept = selectedDept ? k.kode_dept === selectedDept : true;
    const matchesBranch = selectedBranch ? k.kode_cabang === selectedBranch : true;
    return matchesSearch && matchesDept && matchesBranch;
  });

  return (
    <div className="space-y-6">
      {/* Top action and filter bar */}
      <div className="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-lg flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        {/* Search & Select Grid */}
        <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 flex-1">
          <div className="relative">
            <Search className="w-4 h-4 text-slate-500 absolute left-3 top-3.5" />
            <input
              type="text"
              placeholder="Cari Nama / NIK..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="w-full bg-slate-950 border border-slate-700 rounded-xl pl-9 pr-4 py-2.5 text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
            />
          </div>

          <select
            value={selectedDept}
            onChange={(e) => setSelectedDept(e.target.value)}
            className="bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none"
          >
            <option value="">-- Semua Departemen --</option>
            {departemenList.map((d) => (
              <option key={d.kode_dept} value={d.kode_dept}>
                {d.nama_dept}
              </option>
            ))}
          </select>

          <select
            value={selectedBranch}
            onChange={(e) => setSelectedBranch(e.target.value)}
            className="bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none"
          >
            <option value="">-- Semua Cabang --</option>
            {cabangList.map((c) => (
              <option key={c.kode_cabang} value={c.kode_cabang}>
                {c.nama_cabang}
              </option>
            ))}
          </select>
        </div>

        {/* Add Button */}
        <button
          onClick={openCreateModal}
          className="bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl px-4 py-2.5 text-xs flex items-center justify-center space-x-1.5 shadow-md shadow-blue-600/10 active:scale-[0.98] transition-all"
        >
          <UserPlus className="w-4 h-4" />
          <span>Tambah Karyawan</span>
        </button>
      </div>

      {/* Main Table */}
      <div className="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="bg-slate-900/50 border-b border-slate-800 text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                <th className="px-6 py-4">Nama / NIK</th>
                <th className="px-6 py-4">Jabatan</th>
                <th className="px-6 py-4">Departemen</th>
                <th className="px-6 py-4">Kantor Cabang</th>
                <th className="px-6 py-4">No. HP</th>
                <th className="px-6 py-4 text-center">Lock GPS</th>
                <th className="px-6 py-4 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800 text-sm text-slate-300">
              {filteredKaryawan.length > 0 ? (
                filteredKaryawan.map((k) => (
                  <tr key={k.nik} className="hover:bg-slate-800/30 transition-colors">
                    <td className="px-6 py-4">
                      <div className="font-bold text-white">{k.nama_lengkap}</div>
                      <div className="text-[10px] text-slate-500 mt-0.5">NIK: {k.nik}</div>
                    </td>
                    <td className="px-6 py-4">{k.jabatan}</td>
                    <td className="px-6 py-4">{k.departemen?.nama_dept || '-'}</td>
                    <td className="px-6 py-4">{k.cabang?.nama_cabang || '-'}</td>
                    <td className="px-6 py-4 font-mono text-xs">{k.no_hp}</td>
                    
                    {/* Toggle Lock GPS column */}
                    <td className="px-6 py-4 text-center">
                      <button
                        onClick={() => handleToggleLockGPS(k.nik, k.status_location)}
                        disabled={isPending}
                        className={`inline-flex items-center space-x-1 px-3 py-1 rounded-full text-[10px] font-bold border transition-colors ${
                          k.status_location === 1
                            ? 'bg-blue-500/10 border-blue-500/20 text-blue-400 hover:bg-blue-500/20'
                            : 'bg-slate-800 border-slate-700 text-slate-400 hover:bg-slate-700'
                        }`}
                        title={k.status_location === 1 ? 'GPS Terkunci (Radius Diaktifkan)' : 'GPS Terbuka (Bypass Radius)'}
                      >
                        {k.status_location === 1 ? (
                          <>
                            <Lock className="w-3 h-3" />
                            <span>Locked</span>
                          </>
                        ) : (
                          <>
                            <Unlock className="w-3 h-3" />
                            <span>Unlocked</span>
                          </>
                        )}
                      </button>
                    </td>

                    {/* Actions Row */}
                    <td className="px-6 py-4">
                      <div className="flex items-center justify-center space-x-2">
                        {/* Reset Password */}
                        <button
                          onClick={() => handleResetPassword(k.nik, k.nama_lengkap)}
                          disabled={isPending}
                          className="p-2 bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 rounded-lg transition-colors"
                          title="Riset Password ke 12345"
                        >
                          <Key className="w-3.5 h-3.5" />
                        </button>
                        
                        {/* Edit */}
                        <button
                          onClick={() => openEditModal(k)}
                          disabled={isPending}
                          className="p-2 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 rounded-lg transition-colors"
                          title="Edit Karyawan"
                        >
                          <Edit className="w-3.5 h-3.5" />
                        </button>

                        {/* Delete */}
                        <button
                          onClick={() => handleDelete(k.nik, k.nama_lengkap)}
                          disabled={isPending}
                          className="p-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg transition-colors"
                          title="Hapus Karyawan"
                        >
                          <Trash2 className="w-3.5 h-3.5" />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan={7} className="px-6 py-12 text-center text-slate-500 text-xs">
                    Tidak ada data karyawan yang cocok dengan kriteria pencarian.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* CRUD Modal Form (Overlay popup) */}
      {isModalOpen && (
        <div className="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-lg overflow-hidden shadow-2xl animate-fade-in">
            {/* Modal Header */}
            <div className="px-6 py-5 border-b border-slate-800 flex items-center justify-between">
              <h3 className="font-bold text-white text-md">
                {editingKaryawan ? 'Edit Data Karyawan' : 'Tambah Karyawan Baru'}
              </h3>
              <button 
                onClick={() => setIsModalOpen(false)} 
                className="text-slate-400 hover:text-white"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            {/* Modal Body / Form */}
            <form onSubmit={handleFormSubmit} className="p-6 space-y-4">
              {error && (
                <div className="bg-red-500/10 border border-red-500/20 rounded-xl p-3 flex items-start space-x-2 text-red-300 text-xs">
                  <LockAlert className="w-4 h-4 mt-0.5 shrink-0" />
                  <span>{error}</span>
                </div>
              )}

              {/* NIK Input */}
              <div className="space-y-1">
                <label className="text-xs font-semibold text-slate-300">NIK (Nomor Induk Karyawan)</label>
                <input
                  type="text"
                  required
                  disabled={!!editingKaryawan || isPending}
                  value={nik}
                  onChange={(e) => setNik(e.target.value)}
                  placeholder="Masukkan NIK"
                  className="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                />
              </div>

              {/* Nama Lengkap Input */}
              <div className="space-y-1">
                <label className="text-xs font-semibold text-slate-300">Nama Lengkap Karyawan</label>
                <input
                  type="text"
                  required
                  disabled={isPending}
                  value={namaLengkap}
                  onChange={(e) => setNamaLengkap(e.target.value)}
                  placeholder="Masukkan Nama Lengkap"
                  className="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                />
              </div>

              {/* Jabatan Input */}
              <div className="space-y-1">
                <label className="text-xs font-semibold text-slate-300">Jabatan / Posisi</label>
                <input
                  type="text"
                  required
                  disabled={isPending}
                  value={jabatan}
                  onChange={(e) => setJabatan(e.target.value)}
                  placeholder="Contoh: Staff IT, Marketing Manager"
                  className="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                />
              </div>

              {/* Nomor Handphone Input */}
              <div className="space-y-1">
                <label className="text-xs font-semibold text-slate-300">Nomor Handphone (Aktif WA)</label>
                <input
                  type="text"
                  required
                  disabled={isPending}
                  value={noHp}
                  onChange={(e) => setNoHp(e.target.value)}
                  placeholder="Contoh: 0812XXXXXXXX"
                  className="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                />
              </div>

              {/* Password Input */}
              <div className="space-y-1">
                <label className="text-xs font-semibold text-slate-300">
                  Password {editingKaryawan ? '(Kosongkan jika tidak ingin diubah)' : '(Kosongkan untuk default "12345")'}
                </label>
                <input
                  type="password"
                  disabled={isPending}
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  placeholder={editingKaryawan ? "Masukkan password baru jika ingin diubah" : "Default: 12345"}
                  className="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                />
              </div>

              {/* Departemen Dropdown */}
              <div className="space-y-1">
                <label className="text-xs font-semibold text-slate-300">Departemen / Unit</label>
                <select
                  required
                  disabled={isPending}
                  value={kodeDept}
                  onChange={(e) => setKodeDept(e.target.value)}
                  className="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none"
                >
                  <option value="">-- Pilih Departemen --</option>
                  {departemenList.map((d) => (
                    <option key={d.kode_dept} value={d.kode_dept}>
                      {d.nama_dept}
                    </option>
                  ))}
                </select>
              </div>

              {/* Kantor Cabang Dropdown */}
              <div className="space-y-1">
                <label className="text-xs font-semibold text-slate-300">Kantor Cabang (Lokasi Geofence)</label>
                <select
                  required
                  disabled={isPending}
                  value={kodeCabang}
                  onChange={(e) => setKodeCabang(e.target.value)}
                  className="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none"
                >
                  <option value="">-- Pilih Kantor Cabang --</option>
                  {cabangList.map((c) => (
                    <option key={c.kode_cabang} value={c.kode_cabang}>
                      {c.nama_cabang}
                    </option>
                  ))}
                </select>
              </div>

              {/* Modal Footer Buttons */}
              <div className="flex items-center justify-end space-x-3 pt-4 border-t border-slate-800 mt-2">
                <button
                  type="button"
                  onClick={() => setIsModalOpen(false)}
                  disabled={isPending}
                  className="px-4 py-2 border border-slate-750 text-slate-400 hover:text-white rounded-xl text-xs font-semibold transition-colors"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  disabled={isPending}
                  className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-md shadow-blue-600/10 flex items-center space-x-1.5 transition-colors disabled:opacity-50"
                >
                  {isPending ? (
                    <>
                      <Loader2 className="w-3.5 h-3.5 animate-spin" />
                      <span>Menyimpan...</span>
                    </>
                  ) : (
                    <span>Simpan Data</span>
                  )}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
