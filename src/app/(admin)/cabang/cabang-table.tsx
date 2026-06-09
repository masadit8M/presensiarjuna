'use client';

import React, { useState, useTransition } from 'react';
import { useRouter } from 'next/navigation';
import { saveCabangAction, deleteCabangAction } from '@/lib/actions/admin';
import { MapPin, Edit, Trash2, Search, X, Loader2, AlertCircle, Plus } from 'lucide-react';

export default function CabangTable({ cabangList }: { cabangList: any[] }) {
  const router = useRouter();
  const [isPending, startTransition] = useTransition();

  // Filter state
  const [searchTerm, setSearchTerm] = useState('');

  // Modal States
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingCabang, setEditingCabang] = useState<any>(null); // null means "Create"
  const [error, setError] = useState<string | null>(null);

  // Form Fields
  const [kodeCabang, setKodeCabang] = useState('');
  const [namaCabang, setNamaCabang] = useState('');
  const [lokasiCabang, setLokasiCabang] = useState('');
  const [radiusCabang, setRadiusCabang] = useState('100');

  const openCreateModal = () => {
    setEditingCabang(null);
    setKodeCabang('');
    setNamaCabang('');
    setLokasiCabang('');
    setRadiusCabang('100');
    setError(null);
    setIsModalOpen(true);
  };

  const openEditModal = (c: any) => {
    setEditingCabang(c);
    setKodeCabang(c.kode_cabang);
    setNamaCabang(c.nama_cabang);
    setLokasiCabang(c.lokasi_cabang);
    setRadiusCabang(c.radius_cabang.toString());
    setError(null);
    setIsModalOpen(true);
  };

  const handleFormSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);

    startTransition(async () => {
      const formData = new FormData();
      formData.append('kode_cabang', kodeCabang);
      formData.append('nama_cabang', namaCabang);
      formData.append('lokasi_cabang', lokasiCabang);
      formData.append('radius_cabang', radiusCabang);
      formData.append('is_edit', editingCabang ? 'true' : 'false');

      const result = await saveCabangAction(formData);

      if (result?.error) {
        setError(result.error);
      } else {
        setIsModalOpen(false);
        router.refresh();
      }
    });
  };

  const handleDelete = (kode: string, name: string) => {
    if (!confirm(`Apakah Anda yakin ingin menghapus kantor cabang "${name}"? Karyawan yang ditugaskan ke cabang ini tidak akan memiliki geofence terikat!`)) {
      return;
    }

    startTransition(async () => {
      const result = await deleteCabangAction(kode);
      if (result?.error) {
        alert(result.error);
      } else {
        router.refresh();
      }
    });
  };

  const filteredCabang = cabangList.filter(
    (c) =>
      c.nama_cabang.toLowerCase().includes(searchTerm.toLowerCase()) ||
      c.kode_cabang.toLowerCase().includes(searchTerm.toLowerCase())
  );

  return (
    <div className="space-y-6">
      {/* Search and Add panel */}
      <div className="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-lg flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div className="relative max-w-sm flex-1">
          <Search className="w-4 h-4 text-slate-500 absolute left-3 top-3.5" />
          <input
            type="text"
            placeholder="Cari Kantor Cabang..."
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
          <span>Tambah Cabang</span>
        </button>
      </div>

      {/* Table grid */}
      <div className="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="bg-slate-900/50 border-b border-slate-800 text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                <th className="px-6 py-4">Kode Cabang</th>
                <th className="px-6 py-4">Nama Cabang</th>
                <th className="px-6 py-4">Titik Geofence (Lat, Lng)</th>
                <th className="px-6 py-4">Radius Pengunci (Meter)</th>
                <th className="px-6 py-4 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800 text-sm text-slate-300">
              {filteredCabang.length > 0 ? (
                filteredCabang.map((c) => (
                  <tr key={c.kode_cabang} className="hover:bg-slate-800/30 transition-colors">
                    <td className="px-6 py-4 font-bold text-white uppercase">{c.kode_cabang}</td>
                    <td className="px-6 py-4">{c.nama_cabang}</td>
                    <td className="px-6 py-4">
                      <a 
                        href={`https://maps.google.com/?q=${c.lokasi_cabang}`} 
                        target="_blank" 
                        rel="noreferrer"
                        className="inline-flex items-center text-xs text-blue-400 hover:underline hover:text-blue-300 font-mono"
                      >
                        <MapPin className="w-3.5 h-3.5 mr-1" />
                        <span>{c.lokasi_cabang}</span>
                      </a>
                    </td>
                    <td className="px-6 py-4 font-semibold text-slate-200">{c.radius_cabang} m</td>
                    <td className="px-6 py-4">
                      <div className="flex items-center justify-center space-x-2">
                        {/* Edit */}
                        <button
                          onClick={() => openEditModal(c)}
                          disabled={isPending}
                          className="p-2 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 rounded-lg transition-colors"
                          title="Edit Cabang"
                        >
                          <Edit className="w-3.5 h-3.5" />
                        </button>

                        {/* Delete */}
                        <button
                          onClick={() => handleDelete(c.kode_cabang, c.nama_cabang)}
                          disabled={isPending}
                          className="p-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg transition-colors"
                          title="Hapus Cabang"
                        >
                          <Trash2 className="w-3.5 h-3.5" />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan={5} className="px-6 py-12 text-center text-slate-500 text-xs">
                    Belum ada data kantor cabang.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Add/Edit Modal */}
      {isModalOpen && (
        <div className="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-md overflow-hidden shadow-2xl animate-fade-in">
            {/* Modal Header */}
            <div className="px-6 py-5 border-b border-slate-800 flex items-center justify-between">
              <h3 className="font-bold text-white text-md">
                {editingCabang ? 'Edit Kantor Cabang' : 'Tambah Kantor Cabang Baru'}
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

              {/* Kode Cabang */}
              <div className="space-y-1">
                <label className="text-xs font-semibold text-slate-300">Kode Cabang</label>
                <input
                  type="text"
                  required
                  disabled={!!editingCabang || isPending}
                  value={kodeCabang}
                  onChange={(e) => setKodeCabang(e.target.value.toUpperCase())}
                  placeholder="Contoh: PST, CB1, PLG"
                  className="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-blue-500 disabled:opacity-50"
                />
              </div>

              {/* Nama Cabang */}
              <div className="space-y-1">
                <label className="text-xs font-semibold text-slate-300">Nama Kantor Cabang</label>
                <input
                  type="text"
                  required
                  disabled={isPending}
                  value={namaCabang}
                  onChange={(e) => setNamaCabang(e.target.value)}
                  placeholder="Contoh: Kantor Pusat, Cabang Surabaya"
                  className="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                />
              </div>

              {/* Lokasi Cabang */}
              <div className="space-y-1">
                <label className="text-xs font-semibold text-slate-300">
                  Koordinat Lokasi Geofence (Latitude, Longitude)
                </label>
                <input
                  type="text"
                  required
                  disabled={isPending}
                  value={lokasiCabang}
                  onChange={(e) => setLokasiCabang(e.target.value)}
                  placeholder="Contoh: -7.98189,112.62650"
                  className="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                />
                <p className="text-[10px] text-slate-500 leading-relaxed pl-0.5">
                  Buka Google Maps, salin koordinat lat/long titik tengah kantor Anda (contoh: `-7.98234,112.63432`).
                </p>
              </div>

              {/* Radius Cabang */}
              <div className="space-y-1">
                <label className="text-xs font-semibold text-slate-300">Jarak Radius Pengunci Geofence (Meter)</label>
                <input
                  type="number"
                  required
                  disabled={isPending}
                  value={radiusCabang}
                  onChange={(e) => setRadiusCabang(e.target.value)}
                  placeholder="Contoh: 100, 150"
                  className="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                />
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
                    <span>Simpan Cabang</span>
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
