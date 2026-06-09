'use client';

import React, { useState, useTransition } from 'react';
import { useRouter } from 'next/navigation';
import { saveDepartemenAction, deleteDepartemenAction } from '@/lib/actions/admin';
import { Edit, Trash2, Search, X, Loader2, AlertCircle, Plus } from 'lucide-react';

export default function DepartemenTable({ departemenList }: { departemenList: any[] }) {
  const router = useRouter();
  const [isPending, startTransition] = useTransition();

  // Filter state
  const [searchTerm, setSearchTerm] = useState('');

  // Modal States
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingDept, setEditingDept] = useState<any>(null); // null means "Create"
  const [error, setError] = useState<string | null>(null);

  // Form Fields
  const [kodeDept, setKodeDept] = useState('');
  const [namaDept, setNamaDept] = useState('');

  const openCreateModal = () => {
    setEditingDept(null);
    setKodeDept('');
    setNamaDept('');
    setError(null);
    setIsModalOpen(true);
  };

  const openEditModal = (d: any) => {
    setEditingDept(d);
    setKodeDept(d.kode_dept);
    setNamaDept(d.nama_dept);
    setError(null);
    setIsModalOpen(true);
  };

  const handleFormSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);

    startTransition(async () => {
      const formData = new FormData();
      formData.append('kode_dept', kodeDept);
      formData.append('nama_dept', namaDept);
      formData.append('is_edit', editingDept ? 'true' : 'false');

      const result = await saveDepartemenAction(formData);

      if (result?.error) {
        setError(result.error);
      } else {
        setIsModalOpen(false);
        router.refresh();
      }
    });
  };

  const handleDelete = (kode: string, name: string) => {
    if (!confirm(`Apakah Anda yakin ingin menghapus departemen "${name}"? Karyawan yang dikaitkan ke departemen ini tidak akan memiliki relasi divisi!`)) {
      return;
    }

    startTransition(async () => {
      const result = await deleteDepartemenAction(kode);
      if (result?.error) {
        alert(result.error);
      } else {
        router.refresh();
      }
    });
  };

  const filteredDept = departemenList.filter(
    (d) =>
      d.nama_dept.toLowerCase().includes(searchTerm.toLowerCase()) ||
      d.kode_dept.toLowerCase().includes(searchTerm.toLowerCase())
  );

  return (
    <div className="space-y-6">
      {/* Search and Add */}
      <div className="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-lg flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div className="relative max-w-sm flex-1">
          <Search className="w-4 h-4 text-slate-500 absolute left-3 top-3.5" />
          <input
            type="text"
            placeholder="Cari Departemen..."
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
          <span>Tambah Departemen</span>
        </button>
      </div>

      {/* Table */}
      <div className="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="bg-slate-900/50 border-b border-slate-800 text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                <th className="px-6 py-4">Kode Departemen</th>
                <th className="px-6 py-4">Nama Departemen</th>
                <th className="px-6 py-4 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800 text-sm text-slate-300">
              {filteredDept.length > 0 ? (
                filteredDept.map((d) => (
                  <tr key={d.kode_dept} className="hover:bg-slate-800/30 transition-colors">
                    <td className="px-6 py-4 font-bold text-white uppercase">{d.kode_dept}</td>
                    <td className="px-6 py-4">{d.nama_dept}</td>
                    <td className="px-6 py-4">
                      <div className="flex items-center justify-center space-x-2">
                        {/* Edit */}
                        <button
                          onClick={() => openEditModal(d)}
                          disabled={isPending}
                          className="p-2 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 rounded-lg transition-colors"
                          title="Edit Departemen"
                        >
                          <Edit className="w-3.5 h-3.5" />
                        </button>

                        {/* Delete */}
                        <button
                          onClick={() => handleDelete(d.kode_dept, d.nama_dept)}
                          disabled={isPending}
                          className="p-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg transition-colors"
                          title="Hapus Departemen"
                        >
                          <Trash2 className="w-3.5 h-3.5" />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan={3} className="px-6 py-12 text-center text-slate-500 text-xs">
                    Belum ada data departemen.
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
          <div className="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-sm overflow-hidden shadow-2xl animate-fade-in">
            {/* Header */}
            <div className="px-6 py-5 border-b border-slate-800 flex items-center justify-between">
              <h3 className="font-bold text-white text-md">
                {editingDept ? 'Edit Departemen' : 'Tambah Departemen Baru'}
              </h3>
              <button 
                onClick={() => setIsModalOpen(false)} 
                className="text-slate-400 hover:text-white"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            {/* Form */}
            <form onSubmit={handleFormSubmit} className="p-6 space-y-4">
              {error && (
                <div className="bg-red-500/10 border border-red-500/20 rounded-xl p-3 flex items-start space-x-2 text-red-300 text-xs">
                  <AlertCircle className="w-4 h-4 mt-0.5 shrink-0" />
                  <span>{error}</span>
                </div>
              )}

              {/* Kode Dept */}
              <div className="space-y-1">
                <label className="text-xs font-semibold text-slate-300">Kode Departemen</label>
                <input
                  type="text"
                  required
                  disabled={!!editingDept || isPending}
                  value={kodeDept}
                  onChange={(e) => setKodeDept(e.target.value.toUpperCase())}
                  placeholder="Contoh: IT, HRD, MKT"
                  className="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-blue-500 disabled:opacity-50"
                />
              </div>

              {/* Nama Dept */}
              <div className="space-y-1">
                <label className="text-xs font-semibold text-slate-300">Nama Departemen</label>
                <input
                  type="text"
                  required
                  disabled={isPending}
                  value={namaDept}
                  onChange={(e) => setNamaDept(e.target.value)}
                  placeholder="Contoh: Human Resources, Pemasaran"
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
