'use client';

import React, { useState, useTransition } from 'react';
import { useRouter } from 'next/navigation';
import { saveJamKerjaAction, deleteJamKerjaAction } from '@/lib/actions/admin';
import { Clock, Edit, Trash2, Search, X, Loader2, AlertCircle, Plus } from 'lucide-react';

export default function JamKerjaTable({ jamKerjaList }: { jamKerjaList: any[] }) {
  const router = useRouter();
  const [isPending, startTransition] = useTransition();

  // Filter state
  const [searchTerm, setSearchTerm] = useState('');

  // Modal States
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingJk, setEditingJk] = useState<any>(null); // null means "Create"
  const [error, setError] = useState<string | null>(null);

  // Form Fields
  const [kodeJamKerja, setKodeJamKerja] = useState('');
  const [namaJamKerja, setNamaJamKerja] = useState('');
  const [jamMasuk, setJamMasuk] = useState('');
  const [jamPulang, setJamPulang] = useState('');
  const [awalJamMasuk, setAwalJamMasuk] = useState('');
  const [akhirJamMasuk, setAkhirJamMasuk] = useState('');
  const [lintashari, setLintashari] = useState('0');

  const openCreateModal = () => {
    setEditingJk(null);
    setKodeJamKerja('');
    setNamaJamKerja('');
    setJamMasuk('');
    setJamPulang('');
    setAwalJamMasuk('');
    setAkhirJamMasuk('');
    setLintashari('0');
    setError(null);
    setIsModalOpen(true);
  };

  const openEditModal = (jk: any) => {
    setEditingJk(jk);
    setKodeJamKerja(jk.kode_jam_kerja);
    setNamaJamKerja(jk.nama_jam_kerja);
    setJamMasuk(jk.jam_masuk);
    setJamPulang(jk.jam_pulang);
    setAwalJamMasuk(jk.awal_jam_masuk);
    setAkhirJamMasuk(jk.akhir_jam_masuk);
    setLintashari(jk.lintashari.toString());
    setError(null);
    setIsModalOpen(true);
  };

  const handleFormSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);

    startTransition(async () => {
      const formData = new FormData();
      formData.append('kode_jam_kerja', kodeJamKerja);
      formData.append('nama_jam_kerja', namaJamKerja);
      formData.append('jam_masuk', jamMasuk);
      formData.append('jam_pulang', jamPulang);
      formData.append('awal_jam_masuk', awalJamMasuk);
      formData.append('akhir_jam_masuk', akhirJamMasuk);
      formData.append('lintashari', lintashari);
      formData.append('is_edit', editingJk ? 'true' : 'false');

      const result = await saveJamKerjaAction(formData);

      if (result?.error) {
        setError(result.error);
      } else {
        setIsModalOpen(false);
        router.refresh();
      }
    });
  };

  const handleDelete = (kode: string, name: string) => {
    if (!confirm(`Apakah Anda yakin ingin menghapus jam kerja "${name}"?`)) {
      return;
    }

    startTransition(async () => {
      const result = await deleteJamKerjaAction(kode);
      if (result?.error) {
        alert(result.error);
      } else {
        router.refresh();
      }
    });
  };

  const filteredJk = jamKerjaList.filter(
    (jk) =>
      jk.nama_jam_kerja.toLowerCase().includes(searchTerm.toLowerCase()) ||
      jk.kode_jam_kerja.toLowerCase().includes(searchTerm.toLowerCase())
  );

  const formatTime = (t: string) => {
    return t ? t.substring(0, 5) : '--:--';
  };

  return (
    <div className="space-y-6">
      {/* Search and Add panel */}
      <div className="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-lg flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div className="relative max-w-sm flex-1">
          <Search className="w-4 h-4 text-slate-500 absolute left-3 top-3.5" />
          <input
            type="text"
            placeholder="Cari Jam Kerja..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="w-full bg-slate-950 border border-slate-700 rounded-xl pl-9 pr-4 py-2.5 text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
          />
        </div>

        <button
          onClick={openCreateModal}
          className="bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl px-4 py-2.5 text-xs flex items-center justify-center space-x-1.5 shadow-md shadow-blue-600/10 active:scale-[0.98] transition-all"
        >
          <Plus className="w-4 h-4" />
          <span>Tambah Jam Kerja</span>
        </button>
      </div>

      {/* Table grid */}
      <div className="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="bg-slate-900/50 border-b border-slate-800 text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                <th className="px-6 py-4">Kode</th>
                <th className="px-6 py-4">Nama Jam Kerja</th>
                <th className="px-6 py-4 text-center">Batas Awal Masuk</th>
                <th className="px-6 py-4 text-center">Jam Masuk</th>
                <th className="px-6 py-4 text-center">Batas Akhir Masuk</th>
                <th className="px-6 py-4 text-center">Jam Pulang</th>
                <th className="px-6 py-4 text-center">Cross-Day</th>
                <th className="px-6 py-4 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800 text-sm text-slate-300">
              {filteredJk.length > 0 ? (
                filteredJk.map((jk) => (
                  <tr key={jk.kode_jam_kerja} className="hover:bg-slate-800/30 transition-colors">
                    <td className="px-6 py-4 font-bold text-white uppercase">{jk.kode_jam_kerja}</td>
                    <td className="px-6 py-4">{jk.nama_jam_kerja}</td>
                    <td className="px-6 py-4 text-center font-mono text-xs">{formatTime(jk.awal_jam_masuk)}</td>
                    <td className="px-6 py-4 text-center font-mono text-slate-200 font-bold">{formatTime(jk.jam_masuk)}</td>
                    <td className="px-6 py-4 text-center font-mono text-xs">{formatTime(jk.akhir_jam_masuk)}</td>
                    <td className="px-6 py-4 text-center font-mono text-slate-200 font-bold">{formatTime(jk.jam_pulang)}</td>
                    <td className="px-6 py-4 text-center">
                      <span className={`px-2 py-0.5 rounded text-[10px] font-bold ${
                        jk.lintashari === 1 
                          ? 'bg-purple-500/10 text-purple-400' 
                          : 'bg-slate-800 text-slate-500'
                      }`}>
                        {jk.lintashari === 1 ? 'YA' : 'TIDAK'}
                      </span>
                    </td>
                    <td className="px-6 py-4">
                      <div className="flex items-center justify-center space-x-2">
                        {/* Edit */}
                        <button
                          onClick={() => openEditModal(jk)}
                          disabled={isPending}
                          className="p-2 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 rounded-lg transition-colors"
                          title="Edit Jam Kerja"
                        >
                          <Edit className="w-3.5 h-3.5" />
                        </button>

                        {/* Delete */}
                        <button
                          onClick={() => handleDelete(jk.kode_jam_kerja, jk.nama_jam_kerja)}
                          disabled={isPending}
                          className="p-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg transition-colors"
                          title="Hapus Jam Kerja"
                        >
                          <Trash2 className="w-3.5 h-3.5" />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan={8} className="px-6 py-12 text-center text-slate-500 text-xs">
                    Belum ada data konfigurasi jam kerja.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Modal Form */}
      {isModalOpen && (
        <div className="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-md overflow-hidden shadow-2xl animate-fade-in">
            {/* Modal Header */}
            <div className="px-6 py-5 border-b border-slate-800 flex items-center justify-between">
              <h3 className="font-bold text-white text-md">
                {editingJk ? 'Edit Jam Kerja' : 'Tambah Jam Kerja Baru'}
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
                  <AlertCircle className="w-4 h-4 mt-0.5 shrink-0" />
                  <span>{error}</span>
                </div>
              )}

              {/* Kode Jam Kerja */}
              <div className="space-y-1">
                <label className="text-xs font-semibold text-slate-300">Kode Jam Kerja</label>
                <input
                  type="text"
                  required
                  disabled={!!editingJk || isPending}
                  value={kodeJamKerja}
                  onChange={(e) => setKodeJamKerja(e.target.value.toUpperCase())}
                  placeholder="Contoh: RGL, SHF1, SHF2"
                  className="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                />
              </div>

              {/* Nama Jam Kerja */}
              <div className="space-y-1">
                <label className="text-xs font-semibold text-slate-300">Nama Shift / Jam Kerja</label>
                <input
                  type="text"
                  required
                  disabled={isPending}
                  value={namaJamKerja}
                  onChange={(e) => setNamaJamKerja(e.target.value)}
                  placeholder="Contoh: Reguler Pagi, Shift Siang"
                  className="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                />
              </div>

              {/* Jam Masuk & Pulang */}
              <div className="grid grid-cols-2 gap-3">
                <div className="space-y-1">
                  <label className="text-xs font-semibold text-slate-300">Jam Masuk</label>
                  <input
                    type="text"
                    required
                    disabled={isPending}
                    value={jamMasuk}
                    onChange={(e) => setJamMasuk(e.target.value)}
                    placeholder="HH:MM:SS"
                    className="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                  />
                </div>
                <div className="space-y-1">
                  <label className="text-xs font-semibold text-slate-300">Jam Pulang</label>
                  <input
                    type="text"
                    required
                    disabled={isPending}
                    value={jamPulang}
                    onChange={(e) => setJamPulang(e.target.value)}
                    placeholder="HH:MM:SS"
                    className="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                  />
                </div>
              </div>

              {/* Awal & Akhir Jam Masuk */}
              <div className="grid grid-cols-2 gap-3">
                <div className="space-y-1">
                  <label className="text-xs font-semibold text-slate-300">Awal Absen Masuk</label>
                  <input
                    type="text"
                    required
                    disabled={isPending}
                    value={awalJamMasuk}
                    onChange={(e) => setAwalJamMasuk(e.target.value)}
                    placeholder="HH:MM:SS"
                    className="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                  />
                </div>
                <div className="space-y-1">
                  <label className="text-xs font-semibold text-slate-300">Akhir Absen Masuk</label>
                  <input
                    type="text"
                    required
                    disabled={isPending}
                    value={akhirJamMasuk}
                    onChange={(e) => setAkhirJamMasuk(e.target.value)}
                    placeholder="HH:MM:SS"
                    className="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                  />
                </div>
              </div>

              {/* Lintashari / Cross-Day Shift */}
              <div className="space-y-1">
                <label className="text-xs font-semibold text-slate-300">Shift Lintas Hari (Cross-Day Shift)</label>
                <select
                  value={lintashari}
                  onChange={(e) => setLintashari(e.target.value)}
                  disabled={isPending}
                  className="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none"
                >
                  <option value="0">TIDAK (Shift selesai pada hari yang sama)</option>
                  <option value="1">YA (Shift selesai pada hari berikutnya, cth: shift malam)</option>
                </select>
                <p className="text-[10px] text-slate-500 leading-relaxed pl-0.5">
                  Gunakan tipe YA jika jam pulang melewati tengah malam pukul 00:00 (cth: masuk 22:00 pulang 06:00 keesokan harinya).
                </p>
              </div>

              {/* Modal Buttons */}
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
                    <span>Simpan</span>
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
