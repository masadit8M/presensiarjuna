'use client';

import React, { useState, useTransition } from 'react';
import Link from 'next/link';
import { updateMasterGajiAction } from '@/lib/actions/gaji';
import {
  Settings,
  ArrowLeft,
  Search,
  Edit,
  Save,
  X,
  CheckCircle2,
  AlertTriangle,
  Banknote,
  Calendar,
  Building2,
  RefreshCw,
  CreditCard
} from 'lucide-react';

interface MasterGajiClientProps {
  initialMasterList: any[];
  cabangList: any[];
  departemenList: any[];
}

export default function MasterGajiClient({
  initialMasterList,
  cabangList,
  departemenList,
}: MasterGajiClientProps) {
  const [masterList, setMasterList] = useState<any[]>(initialMasterList);
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedCabang, setSelectedCabang] = useState('');
  const [selectedDept, setSelectedDept] = useState('');

  const [isPending, startTransition] = useTransition();
  const [toast, setToast] = useState<{ type: 'success' | 'error'; message: string } | null>(null);

  // Edit Modal State
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingItem, setEditingItem] = useState<any>(null);
  const [form, setForm] = useState<{
    tipe_komponen: string;
    gaji_pokok: number;
    tunj_transport: number;
    tgl_masuk: string;
    no_rek: string;
    nama_bank: string;
    catatan: string;
  }>({
    tipe_komponen: 'guru',
    gaji_pokok: 0,
    tunj_transport: 150000,
    tgl_masuk: '',
    no_rek: '',
    nama_bank: '',
    catatan: '',
  });

  const handleOpenEdit = (item: any) => {
    setEditingItem(item);
    setForm({
      tipe_komponen: item.tipe_komponen || (item.kode_dept === 'TPA' ? 'tpa' : 'guru'),
      gaji_pokok: item.gaji_pokok || 0,
      tunj_transport: item.tunj_transport || 150000,
      tgl_masuk: item.tgl_masuk || '',
      no_rek: item.no_rek || '',
      nama_bank: item.nama_bank || '',
      catatan: item.catatan || '',
    });
    setIsModalOpen(true);
  };

  const handleSave = () => {
    if (!editingItem) return;
    startTransition(async () => {
      const res = await updateMasterGajiAction(editingItem.nik, form);
      if (res.error) {
        setToast({ type: 'error', message: res.error });
        return;
      }

      setToast({ type: 'success', message: `Master gaji untuk ${editingItem.nama_lengkap} berhasil disimpan!` });
      // Update local state
      setMasterList((prev) =>
        prev.map((m) =>
          m.nik === editingItem.nik
            ? {
                ...m,
                ...form,
              }
            : m
        )
      );
      setIsModalOpen(false);
    });
  };

  const filteredList = masterList.filter((m) => {
    const matchSearch =
      m.nama_lengkap?.toLowerCase().includes(searchTerm.toLowerCase()) ||
      m.nik?.toLowerCase().includes(searchTerm.toLowerCase()) ||
      m.jabatan?.toLowerCase().includes(searchTerm.toLowerCase());

    const matchCabang = !selectedCabang || m.kode_cabang === selectedCabang;
    const matchDept = !selectedDept || m.kode_dept === selectedDept;

    return matchSearch && matchCabang && matchDept;
  });

  return (
    <div className="space-y-6">
      {/* Toast Alert */}
      {toast && (
        <div
          className={`fixed top-5 right-5 z-50 flex items-center gap-3 px-5 py-3.5 rounded-xl shadow-2xl border text-sm font-medium transition-all duration-300 animate-in fade-in slide-in-from-top-4 ${
            toast.type === 'success'
              ? 'bg-emerald-950/90 border-emerald-500/50 text-emerald-200'
              : 'bg-red-950/90 border-red-500/50 text-red-200'
          }`}
        >
          {toast.type === 'success' ? (
            <CheckCircle2 className="w-5 h-5 text-emerald-400 shrink-0" />
          ) : (
            <AlertTriangle className="w-5 h-5 text-red-400 shrink-0" />
          )}
          <span>{toast.message}</span>
          <button onClick={() => setToast(null)} className="ml-2 hover:opacity-75">
            <X className="w-4 h-4" />
          </button>
        </div>
      )}

      {/* Header */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <Link
            href="/gaji"
            className="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-400 hover:text-indigo-300 mb-2 transition-colors"
          >
            <ArrowLeft className="w-3.5 h-3.5" />
            <span>Kembali ke Penggajian &amp; Slip Gaji</span>
          </Link>
          <div className="flex items-center gap-3">
            <div className="p-2.5 bg-indigo-500/10 border border-indigo-500/20 rounded-xl text-indigo-400">
              <Settings className="w-6 h-6" />
            </div>
            <div>
              <h1 className="text-2xl font-bold text-white tracking-tight">Master Gaji Karyawan</h1>
              <p className="text-xs text-slate-400 mt-0.5">
                Konfigurasi gaji pokok dasar, tunjangan transport, dan tanggal mulai bekerja untuk setiap guru &amp; staff.
              </p>
            </div>
          </div>
        </div>
      </div>

      {/* Filter and Table Container */}
      <div className="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-5 space-y-4">
        {/* Filter Bar */}
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div className="relative flex-1 max-w-sm">
            <Search className="w-4 h-4 text-slate-400 absolute left-3 top-3" />
            <input
              type="text"
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              placeholder="Cari karyawan / NIK / jabatan..."
              className="w-full bg-slate-950 border border-slate-800 rounded-xl pl-9 pr-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500"
            />
          </div>

          <div className="flex items-center gap-3">
            <select
              value={selectedCabang}
              onChange={(e) => setSelectedCabang(e.target.value)}
              className="bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-indigo-500 cursor-pointer"
            >
              <option value="">Semua Cabang</option>
              {cabangList.map((c) => (
                <option key={c.kode_cabang} value={c.kode_cabang}>
                  Cabang {c.nama_cabang}
                </option>
              ))}
            </select>

            <select
              value={selectedDept}
              onChange={(e) => setSelectedDept(e.target.value)}
              className="bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-indigo-500 cursor-pointer"
            >
              <option value="">Semua Departemen</option>
              {departemenList.map((d) => (
                <option key={d.kode_dept} value={d.kode_dept}>
                  {d.nama_dept} ({d.kode_dept})
                </option>
              ))}
            </select>
          </div>
        </div>

        {/* Master Table */}
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse text-xs">
            <thead>
              <tr className="border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[11px] font-semibold bg-slate-950/40">
                <th className="py-3 px-4">Karyawan</th>
                <th className="py-3 px-3">Cabang / Dept</th>
                <th className="py-3 px-3">Tipe Komponen</th>
                <th className="py-3 px-3">Gaji Pokok</th>
                <th className="py-3 px-3">Tunj. Transport</th>
                <th className="py-3 px-3">Tgl Mulai Kerja</th>
                <th className="py-3 px-3">Rekening Bank</th>
                <th className="py-3 px-4 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800/60">
              {filteredList.map((item) => (
                <tr key={item.nik} className="hover:bg-slate-800/30 transition-colors">
                  {/* Karyawan */}
                  <td className="py-3 px-4">
                    <div className="font-semibold text-white">{item.nama_lengkap}</div>
                    <div className="text-[11px] text-slate-400 font-mono flex items-center gap-1.5 mt-0.5">
                      <span>{item.nik}</span>
                      <span>·</span>
                      <span>{item.jabatan}</span>
                    </div>
                  </td>

                  {/* Cabang & Dept */}
                  <td className="py-3 px-3">
                    <div className="flex flex-col gap-1">
                      <span className="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-500/10 text-indigo-300 border border-indigo-500/20 w-max">
                        {item.kode_cabang || '-'}
                      </span>
                      <span className="text-[11px] text-slate-400">
                        {item.departemen || item.kode_dept}
                      </span>
                    </div>
                  </td>

                  {/* Tipe Komponen */}
                  <td className="py-3 px-3">
                    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider ${
                      item.tipe_komponen === 'tpa'
                        ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20'
                        : item.tipe_komponen === 'staff'
                        ? 'bg-sky-500/10 text-sky-400 border border-sky-500/20'
                        : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
                    }`}>
                      {item.tipe_komponen === 'tpa' ? 'Bunda Daycare' : item.tipe_komponen === 'staff' ? 'Staff Operasional' : 'Guru KB / TK'}
                    </span>
                  </td>

                  {/* Gaji Pokok */}
                  <td className="py-3 px-3">
                    <div className="font-semibold text-white font-mono">
                      Rp {Number(item.gaji_pokok || 0).toLocaleString('id-ID')}
                    </div>
                    <div className="text-[10px] text-slate-500">
                      / 26 HK = Rp {Math.round(Number(item.gaji_pokok || 0) / 26).toLocaleString('id-ID')}/hari
                    </div>
                  </td>

                  {/* Tunj Transport */}
                  <td className="py-3 px-3">
                    <div className="text-emerald-400 font-mono">
                      Rp {Number(item.tunj_transport || 0).toLocaleString('id-ID')}
                    </div>
                  </td>

                  {/* Tgl Masuk */}
                  <td className="py-3 px-3 text-slate-300">
                    {item.tgl_masuk ? (
                      <span>{new Date(item.tgl_masuk).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}</span>
                    ) : (
                      <span className="text-slate-500 italic">-</span>
                    )}
                  </td>

                  {/* Bank */}
                  <td className="py-3 px-3 text-slate-300">
                    {item.no_rek ? (
                      <div>
                        <div className="font-mono text-slate-200">{item.no_rek}</div>
                        <div className="text-[10px] text-slate-400">{item.nama_bank || 'Bank'}</div>
                      </div>
                    ) : (
                      <span className="text-slate-500 italic">-</span>
                    )}
                  </td>

                  {/* Action */}
                  <td className="py-3 px-4 text-center">
                    <button
                      onClick={() => handleOpenEdit(item)}
                      className="p-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-lg transition-colors cursor-pointer"
                      title="Edit Master Gaji"
                    >
                      <Edit className="w-3.5 h-3.5" />
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* EDIT MODAL */}
      {isModalOpen && editingItem && (
        <div className="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl animate-in fade-in zoom-in-95">
            <div className="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
              <div>
                <h3 className="text-base font-bold text-white">Edit Master Gaji Karyawan</h3>
                <p className="text-xs text-slate-400 mt-0.5">
                  {editingItem.nama_lengkap} ({editingItem.nik}) · {editingItem.jabatan}
                </p>
              </div>
              <button
                onClick={() => setIsModalOpen(false)}
                className="text-slate-400 hover:text-white transition-colors cursor-pointer"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <div className="p-6 space-y-4">
              {/* Tipe Komponen */}
              <div>
                <label className="block text-xs font-semibold text-slate-300 mb-1.5">
                  Tipe Komponen Slip Gaji
                </label>
                <select
                  value={form.tipe_komponen}
                  onChange={(e) => setForm({ ...form, tipe_komponen: e.target.value })}
                  className="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500 cursor-pointer"
                >
                  <option value="guru">Guru KB / TK (Uang Kegiatan &amp; Uang Ekstra)</option>
                  <option value="tpa">Bunda TPA Daycare (Uang Lembur &amp; Skema Remunerasi Pool)</option>
                  <option value="staff">Staff Operasional (Satpam &amp; Konsumsi)</option>
                </select>
              </div>

              {/* Gaji Pokok & Transport */}
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1.5">
                    Gaji Pokok (Rp)
                  </label>
                  <input
                    type="number"
                    value={form.gaji_pokok}
                    onChange={(e) => setForm({ ...form, gaji_pokok: Number(e.target.value) })}
                    className="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono"
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1.5">
                    Tunjangan Transport (Rp)
                  </label>
                  <input
                    type="number"
                    value={form.tunj_transport}
                    onChange={(e) => setForm({ ...form, tunj_transport: Number(e.target.value) })}
                    className="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono"
                  />
                </div>
              </div>

              {/* Tgl Masuk */}
              <div>
                <label className="block text-xs font-semibold text-slate-300 mb-1.5">
                  Tanggal Mulai Bekerja (Tgl Masuk)
                </label>
                <input
                  type="date"
                  value={form.tgl_masuk}
                  onChange={(e) => setForm({ ...form, tgl_masuk: e.target.value })}
                  className="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500 cursor-pointer"
                />
              </div>

              {/* Rekening Bank */}
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1.5">
                    Nama Bank
                  </label>
                  <input
                    type="text"
                    placeholder="BSI / BCA / Mandiri / BRI"
                    value={form.nama_bank}
                    onChange={(e) => setForm({ ...form, nama_bank: e.target.value })}
                    className="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500"
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1.5">
                    Nomor Rekening
                  </label>
                  <input
                    type="text"
                    placeholder="1234567890"
                    value={form.no_rek}
                    onChange={(e) => setForm({ ...form, no_rek: e.target.value })}
                    className="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono"
                  />
                </div>
              </div>

              {/* Catatan */}
              <div>
                <label className="block text-xs font-semibold text-slate-300 mb-1.5">
                  Catatan Penggajian (Opsional)
                </label>
                <textarea
                  rows={2}
                  placeholder="Catatan khusus atau masa percobaan..."
                  value={form.catatan}
                  onChange={(e) => setForm({ ...form, catatan: e.target.value })}
                  className="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500 resize-none"
                />
              </div>
            </div>

            <div className="px-6 py-4 bg-slate-950/60 border-t border-slate-800 flex items-center justify-end gap-3">
              <button
                onClick={() => setIsModalOpen(false)}
                className="px-4 py-2 text-xs font-semibold text-slate-400 hover:text-white transition-colors cursor-pointer"
              >
                Batal
              </button>
              <button
                onClick={handleSave}
                disabled={isPending}
                className="flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white text-xs font-semibold rounded-xl shadow-lg shadow-indigo-900/20 transition-all cursor-pointer"
              >
                {isPending && <RefreshCw className="w-3.5 h-3.5 animate-spin" />}
                <span>Simpan Master Gaji</span>
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
