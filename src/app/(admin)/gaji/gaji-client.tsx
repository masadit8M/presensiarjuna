'use client';

import React, { useState, useTransition, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import {
  generateDraftPenggajianAction,
  getPenggajianDetailAction,
  updatePenggajianDetailItemAction,
  deletePenggajianPeriodeAction,
  sendSlipGajiWaAction,
} from '@/lib/actions/gaji';
import {
  Banknote,
  Calendar,
  Users,
  Send,
  Printer,
  Edit,
  Trash2,
  CheckCircle2,
  AlertTriangle,
  Clock,
  Search,
  Settings,
  PlusCircle,
  X,
  FileText,
  DollarSign,
  Info,
  TrendingUp,
  AlertCircle,
  Check,
  RefreshCw
} from 'lucide-react';

interface GajiClientProps {
  initialPeriodeList: any[];
  cabangList: any[];
  departemenList: any[];
  isTableMissing: boolean;
}

const MONTH_NAMES = [
  '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

export default function GajiClient({
  initialPeriodeList,
  cabangList,
  departemenList,
  isTableMissing,
}: GajiClientProps) {
  const router = useRouter();
  const [isPending, startTransition] = useTransition();

  // Period Selection
  const [periodeList, setPeriodeList] = useState<any[]>(initialPeriodeList);
  const [selectedPeriodeId, setSelectedPeriodeId] = useState<string>(
    initialPeriodeList.length > 0 ? initialPeriodeList[0].id : ''
  );
  const [currentPeriode, setCurrentPeriode] = useState<any>(
    initialPeriodeList.length > 0 ? initialPeriodeList[0] : null
  );

  // Detail Slips in Current Period
  const [details, setDetails] = useState<any[]>([]);
  const [isLoadingDetails, setIsLoadingDetails] = useState(false);

  // Filters & Search
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedCabang, setSelectedCabang] = useState('');
  const [selectedDept, setSelectedDept] = useState('');
  const [selectedWaStatus, setSelectedWaStatus] = useState('');

  // Modals
  const [isGenerateModalOpen, setIsGenerateModalOpen] = useState(false);
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [editingDetail, setEditingDetail] = useState<any>(null);

  // Feedback Toast / Alert
  const [toast, setToast] = useState<{ type: 'success' | 'error' | 'info'; message: string } | null>(null);

  // Generate Form State
  const now = new Date();
  const [genBulan, setGenBulan] = useState(now.getMonth() + 1);
  const [genTahun, setGenTahun] = useState(now.getFullYear());
  const [genSkemaBunda, setGenSkemaBunda] = useState(false);
  const [genSiswaDaycare, setGenSiswaDaycare] = useState(41);

  // Edit Slip Form State
  const [editForm, setEditForm] = useState<any>({});

  // Loading indicator for sending WA per item
  const [sendingWaId, setSendingWaId] = useState<string | null>(null);

  // Fetch details when selectedPeriodeId changes
  useEffect(() => {
    if (!selectedPeriodeId) {
      setDetails([]);
      setCurrentPeriode(null);
      return;
    }

    const foundPeriode = periodeList.find((p) => p.id === selectedPeriodeId);
    setCurrentPeriode(foundPeriode || null);

    setIsLoadingDetails(true);
    getPenggajianDetailAction(selectedPeriodeId)
      .then((res) => {
        if (res.success && res.details) {
          setDetails(res.details);
          if (res.periode) setCurrentPeriode(res.periode);
        } else {
          setToast({ type: 'error', message: res.error || 'Gagal memuat rincian periode.' });
        }
      })
      .catch((err) => {
        setToast({ type: 'error', message: err.message || 'Error server.' });
      })
      .finally(() => {
        setIsLoadingDetails(false);
      });
  }, [selectedPeriodeId, periodeList]);

  // Handle toast timeout
  useEffect(() => {
    if (toast) {
      const timer = setTimeout(() => setToast(null), 6000);
      return () => clearTimeout(timer);
    }
  }, [toast]);

  // Filtered Detail Rows
  const filteredDetails = details.filter((d) => {
    const matchSearch =
      d.nama_karyawan?.toLowerCase().includes(searchTerm.toLowerCase()) ||
      d.nik?.toLowerCase().includes(searchTerm.toLowerCase()) ||
      d.jabatan?.toLowerCase().includes(searchTerm.toLowerCase());

    const matchCabang = !selectedCabang || d.kode_cabang === selectedCabang;
    const matchDept = !selectedDept || d.kode_dept === selectedDept;
    const matchWa = !selectedWaStatus || d.wa_status === selectedWaStatus;

    return matchSearch && matchCabang && matchDept && matchWa;
  });

  // Calculate stats
  const totalEmployees = details.length;
  const totalPenerimaan = details.reduce((acc, cur) => acc + (Number(cur.subtotal_penerimaan) || 0), 0);
  const totalPotongan = details.reduce((acc, cur) => acc + (Number(cur.subtotal_potongan) || 0), 0);
  const totalTHP = details.reduce((acc, cur) => acc + (Number(cur.total_gaji_bersih) || 0), 0);
  const waSentCount = details.filter((d) => d.wa_status === 'sent').length;

  // GENERATE ACTION
  const handleGenerate = () => {
    startTransition(async () => {
      const res = await generateDraftPenggajianAction(
        genBulan,
        genTahun,
        genSkemaBunda,
        genSiswaDaycare
      );

      if (res.error) {
        setToast({ type: 'error', message: res.error });
        return;
      }

      setToast({ type: 'success', message: res.message || 'Berhasil mengkalkulasi gaji!' });
      setIsGenerateModalOpen(false);

      // Refresh page data
      router.refresh();
      if (res.periodeId) {
        setSelectedPeriodeId(res.periodeId);
      }
    });
  };

  // OPEN EDIT MODAL
  const handleOpenEdit = (item: any) => {
    setEditingDetail(item);
    setEditForm({
      gaji_pokok: item.gaji_pokok || 0,
      tunj_transport: item.tunj_transport || 0,
      uang_kegiatan: item.uang_kegiatan || 0,
      ket_uang_kegiatan: item.ket_uang_kegiatan || '',
      uang_ekstra: item.uang_ekstra || 0,
      ket_uang_ekstra: item.ket_uang_ekstra || '',
      uang_lembur: item.uang_lembur || 0,
      ket_uang_lembur: item.ket_uang_lembur || '',
      bonus: item.bonus || 0,
      ket_bonus: item.ket_bonus || '',
      penyesuaian_gaji: item.penyesuaian_gaji || 0,
      ket_penyesuaian: item.ket_penyesuaian || '',
      insentif_pool_siswa: item.insentif_pool_siswa || 0,
      insentif_hadir_dasar: item.insentif_hadir_dasar || 0,
      premi_disiplin_pagi: item.premi_disiplin_pagi || 0,
      denda_kedisiplinan: item.denda_kedisiplinan || 0,
      pot_absensi: item.pot_absensi || 0,
      kasbon: item.kasbon || 0,
      ket_kasbon: item.ket_kasbon || '',
      sanksi_disiplin: item.sanksi_disiplin || 0,
      ket_sanksi: item.ket_sanksi || '',
    });
    setIsEditModalOpen(true);
  };

  // SAVE EDIT MODAL
  const handleSaveEdit = () => {
    if (!editingDetail) return;
    startTransition(async () => {
      const res = await updatePenggajianDetailItemAction(editingDetail.id, editForm);
      if (res.error) {
        setToast({ type: 'error', message: res.error });
        return;
      }
      setToast({ type: 'success', message: 'Rincian gaji berhasil disimpan!' });
      setIsEditModalOpen(false);

      // Refresh current details
      getPenggajianDetailAction(selectedPeriodeId).then((r) => {
        if (r.success && r.details) setDetails(r.details);
      });
    });
  };

  // SEND WA ACTION
  const handleSendWa = async (detailItem: any) => {
    setSendingWaId(detailItem.id);
    try {
      const res = await sendSlipGajiWaAction(detailItem.id);
      if (res.success) {
        setToast({ type: 'success', message: res.message || 'Slip PDF berhasil dikirim ke WhatsApp!' });
        setDetails((prev) =>
          prev.map((d) => (d.id === detailItem.id ? { ...d, wa_status: 'sent', wa_sent_at: new Date().toISOString() } : d))
        );
      } else {
        setToast({
          type: 'error',
          message: res.message || res.error || 'Gagal mengirim WhatsApp. Cek gateway atau nomor HP karyawan.'
        });
      }
    } catch (e: any) {
      setToast({ type: 'error', message: e.message || 'Terjadi kesalahan saat kirim WA.' });
    } finally {
      setSendingWaId(null);
    }
  };

  // DELETE PERIODE ACTION
  const handleDeletePeriode = (pId: string) => {
    if (!confirm('Apakah Anda yakin ingin menghapus seluruh data slip gaji periode ini?')) return;
    startTransition(async () => {
      const res = await deletePenggajianPeriodeAction(pId);
      if (res.error) {
        setToast({ type: 'error', message: res.error });
        return;
      }
      setToast({ type: 'success', message: 'Periode penggajian berhasil dihapus.' });
      setPeriodeList((prev) => prev.filter((p) => p.id !== pId));
      setSelectedPeriodeId('');
      setDetails([]);
      router.refresh();
    });
  };

  // Calculate live preview totals for edit modal
  const editSubtotalPenerimaan =
    Number(editForm.gaji_pokok || 0) +
    Number(editForm.tunj_transport || 0) +
    Number(editForm.uang_kegiatan || 0) +
    Number(editForm.uang_ekstra || 0) +
    Number(editForm.uang_lembur || 0) +
    Number(editForm.bonus || 0) +
    Number(editForm.penyesuaian_gaji || 0) +
    Number(editForm.insentif_pool_siswa || 0) +
    Number(editForm.insentif_hadir_dasar || 0) +
    Number(editForm.premi_disiplin_pagi || 0);

  const editSubtotalPotongan =
    Number(editForm.pot_absensi || 0) +
    Number(editForm.kasbon || 0) +
    Number(editForm.sanksi_disiplin || 0) +
    Number(editForm.denda_kedisiplinan || 0);

  const editTotalGajiBersih = Math.max(0, editSubtotalPenerimaan - editSubtotalPotongan);

  return (
    <div className="space-y-6">
      {/* Toast Alert */}
      {toast && (
        <div
          className={`fixed top-5 right-5 z-50 flex items-center gap-3 px-5 py-3.5 rounded-xl shadow-2xl border text-sm font-medium transition-all duration-300 animate-in fade-in slide-in-from-top-4 ${
            toast.type === 'success'
              ? 'bg-emerald-950/90 border-emerald-500/50 text-emerald-200'
              : toast.type === 'error'
              ? 'bg-red-950/90 border-red-500/50 text-red-200'
              : 'bg-blue-950/90 border-blue-500/50 text-blue-200'
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

      {/* Missing Table Warning Notice */}
      {isTableMissing && (
        <div className="bg-amber-950/40 border border-amber-500/40 rounded-2xl p-5 flex items-start gap-4">
          <AlertCircle className="w-6 h-6 text-amber-400 shrink-0 mt-0.5" />
          <div className="space-y-1">
            <h4 className="text-sm font-semibold text-amber-200">
              Tabel Database Penggajian Belum Dipasang
            </h4>
            <p className="text-xs text-amber-300/80 leading-relaxed">
              Tabel <code className="bg-black/30 px-1.5 py-0.5 rounded text-amber-200">penggajian_periode</code> &amp;{' '}
              <code className="bg-black/30 px-1.5 py-0.5 rounded text-amber-200">penggajian_detail</code> belum ditemukan di Supabase. 
              Silakan salin dan jalankan script SQL di{' '}
              <span className="font-mono text-amber-200">supabase/migrations/20260914_gaji_system.sql</span> melalui Dashboard SQL Editor Supabase untuk mengaktifkan penyimpanan permanen.
            </p>
          </div>
        </div>
      )}

      {/* Page Header */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div className="flex items-center gap-3">
            <div className="p-2.5 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-400">
              <Banknote className="w-6 h-6" />
            </div>
            <div>
              <h1 className="text-2xl font-bold text-white tracking-tight">Penggajian &amp; Slip Gaji</h1>
              <p className="text-xs text-slate-400 mt-0.5">
                Integrasi presensi otomatis (26 HK), cetak slip A4 paperless 2-in-1, dan kirim dokumen PDF via WhatsApp.
              </p>
            </div>
          </div>
        </div>

        {/* Action Buttons */}
        <div className="flex items-center gap-3">
          <Link
            href="/gaji/master"
            className="flex items-center gap-2 px-4 py-2.5 bg-slate-900/80 hover:bg-slate-800 border border-slate-700/80 rounded-xl text-xs font-semibold text-slate-200 hover:text-white transition-all shadow-sm"
          >
            <Settings className="w-4 h-4 text-slate-400" />
            <span>Master Gaji</span>
          </Link>

          <button
            onClick={() => setIsGenerateModalOpen(true)}
            className="flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-emerald-900/20 transition-all cursor-pointer"
          >
            <PlusCircle className="w-4 h-4" />
            <span>Hitung / Generate Gaji</span>
          </button>
        </div>
      </div>

      {/* Period Selection & Summary Cards */}
      <div className="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-5 space-y-5">
        {/* Period Selector Tabs / Dropdown */}
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800">
          <div className="flex items-center gap-3">
            <Calendar className="w-5 h-5 text-indigo-400" />
            <span className="text-sm font-semibold text-white">Pilih Periode Penggajian:</span>
            {periodeList.length > 0 ? (
              <select
                value={selectedPeriodeId}
                onChange={(e) => setSelectedPeriodeId(e.target.value)}
                className="bg-slate-950 border border-slate-700 rounded-xl px-3 py-1.5 text-xs text-white font-medium focus:outline-none focus:border-indigo-500 cursor-pointer"
              >
                {periodeList.map((p) => (
                  <option key={p.id} value={p.id}>
                    {MONTH_NAMES[p.bulan]} {p.tahun} ({p.status?.toUpperCase() || 'DRAFT'})
                  </option>
                ))}
              </select>
            ) : (
              <span className="text-xs text-slate-400 italic">Belum ada periode dibuat. Klik &quot;Hitung / Generate Gaji&quot; di atas.</span>
            )}
          </div>

          {currentPeriode && (
            <div className="flex items-center gap-2">
              <span className="text-xs text-slate-400">
                Tgl Slip: <strong className="text-slate-200">{currentPeriode.tgl_slip || '-'}</strong> | Transfer: <strong className="text-slate-200">{currentPeriode.tgl_transfer || '-'}</strong>
              </span>
              <button
                onClick={() => handleDeletePeriode(currentPeriode.id)}
                title="Hapus Periode Ini"
                className="p-1.5 text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-lg transition-colors cursor-pointer"
              >
                <Trash2 className="w-4 h-4" />
              </button>
            </div>
          )}
        </div>

        {/* Stats Grid */}
        {currentPeriode && (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div className="bg-slate-950/60 border border-slate-800/80 rounded-xl p-4">
              <div className="flex items-center justify-between">
                <span className="text-xs text-slate-400">Total Karyawan</span>
                <Users className="w-4 h-4 text-sky-400" />
              </div>
              <div className="text-2xl font-bold text-white mt-1">{totalEmployees}</div>
              <div className="text-[11px] text-slate-400 mt-1 flex items-center gap-1">
                <span>Standar: {currentPeriode.hari_kerja_standar || 26} HK</span>
                {currentPeriode.is_skema_bunda_aktif && (
                  <span className="text-amber-400 font-medium">· Skema Bunda Aktif</span>
                )}
              </div>
            </div>

            <div className="bg-slate-950/60 border border-slate-800/80 rounded-xl p-4">
              <div className="flex items-center justify-between">
                <span className="text-xs text-slate-400">Total Penerimaan</span>
                <TrendingUp className="w-4 h-4 text-emerald-400" />
              </div>
              <div className="text-xl font-bold text-emerald-400 mt-1">
                Rp {totalPenerimaan.toLocaleString('id-ID')}
              </div>
              <div className="text-[11px] text-slate-400 mt-1">Gaji pokok, transport &amp; tunjangan</div>
            </div>

            <div className="bg-slate-950/60 border border-slate-800/80 rounded-xl p-4">
              <div className="flex items-center justify-between">
                <span className="text-xs text-slate-400">Total Potongan</span>
                <AlertCircle className="w-4 h-4 text-rose-400" />
              </div>
              <div className="text-xl font-bold text-rose-400 mt-1">
                Rp {totalPotongan.toLocaleString('id-ID')}
              </div>
              <div className="text-[11px] text-slate-400 mt-1">Absensi, kasbon &amp; sanksi</div>
            </div>

            <div className="bg-slate-950/60 border border-slate-800/80 rounded-xl p-4">
              <div className="flex items-center justify-between">
                <span className="text-xs text-slate-400">Total Gaji Bersih (THP)</span>
                <DollarSign className="w-4 h-4 text-indigo-400" />
              </div>
              <div className="text-xl font-bold text-indigo-300 mt-1">
                Rp {totalTHP.toLocaleString('id-ID')}
              </div>
              <div className="text-[11px] text-slate-400 mt-1 flex items-center justify-between">
                <span>WA Terkirim:</span>
                <strong className="text-emerald-400">{waSentCount} / {totalEmployees}</strong>
              </div>
            </div>
          </div>
        )}
      </div>

      {/* Table Section */}
      {selectedPeriodeId && (
        <div className="bg-slate-900/60 border border-slate-800/80 rounded-2xl overflow-hidden space-y-4 p-5">
          {/* Filters Bar */}
          <div className="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            {/* Search */}
            <div className="relative flex-1 max-w-sm">
              <Search className="w-4 h-4 text-slate-400 absolute left-3 top-3" />
              <input
                type="text"
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                placeholder="Cari nama karyawan / NIK..."
                className="w-full bg-slate-950 border border-slate-800 rounded-xl pl-9 pr-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500"
              />
            </div>

            {/* Dropdown Filters */}
            <div className="flex flex-wrap items-center gap-3">
              {/* Filter Cabang */}
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

              {/* Filter Departemen */}
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

              {/* Filter Status WA */}
              <select
                value={selectedWaStatus}
                onChange={(e) => setSelectedWaStatus(e.target.value)}
                className="bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-indigo-500 cursor-pointer"
              >
                <option value="">Semua Status WA</option>
                <option value="sent">Terkirim</option>
                <option value="pending">Pending</option>
                <option value="failed">Gagal</option>
              </select>
            </div>
          </div>

          {/* Data Table */}
          {isLoadingDetails ? (
            <div className="py-16 text-center text-slate-400">
              <RefreshCw className="w-6 h-6 animate-spin mx-auto mb-2 text-indigo-400" />
              <p className="text-xs">Memuat rincian slip gaji...</p>
            </div>
          ) : filteredDetails.length === 0 ? (
            <div className="py-16 text-center text-slate-500 border border-dashed border-slate-800 rounded-xl">
              <FileText className="w-8 h-8 mx-auto mb-2 text-slate-600" />
              <p className="text-sm font-medium text-slate-400">Tidak ada data slip gaji.</p>
              <p className="text-xs text-slate-500 mt-0.5">
                Pastikan periode telah dihitung atau sesuaikan filter pencarian Anda.
              </p>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-left border-collapse text-xs">
                <thead>
                  <tr className="border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[11px] font-semibold bg-slate-950/40">
                    <th className="py-3 px-4">Karyawan</th>
                    <th className="py-3 px-3">Unit / Dept</th>
                    <th className="py-3 px-3">Kehadiran (HK: 26)</th>
                    <th className="py-3 px-3">Penerimaan</th>
                    <th className="py-3 px-3">Potongan</th>
                    <th className="py-3 px-4 text-right">Gaji Bersih (THP)</th>
                    <th className="py-3 px-3 text-center">Status WA</th>
                    <th className="py-3 px-4 text-center">Aksi Slip</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-800/60">
                  {filteredDetails.map((item) => (
                    <tr key={item.id} className="hover:bg-slate-800/30 transition-colors">
                      {/* Name & NIK */}
                      <td className="py-3 px-4">
                        <div className="font-semibold text-white">{item.nama_karyawan}</div>
                        <div className="text-[11px] text-slate-400 font-mono flex items-center gap-1.5 mt-0.5">
                          <span>{item.nik}</span>
                          <span>·</span>
                          <span>{item.jabatan}</span>
                        </div>
                      </td>

                      {/* Cabang / Dept */}
                      <td className="py-3 px-3">
                        <div className="flex flex-col gap-1">
                          <span className="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-500/10 text-indigo-300 border border-indigo-500/20 w-max">
                            {item.kode_cabang || '-'}
                          </span>
                          <span className="text-[11px] text-slate-400">
                            {item.kode_dept} ({item.tipe_komponen?.toUpperCase()})
                          </span>
                        </div>
                      </td>

                      {/* Kehadiran */}
                      <td className="py-3 px-3">
                        <div className="text-slate-200">
                          Hadir: <strong className="text-emerald-400">{item.hadir || 0}</strong>
                        </div>
                        <div className="text-[11px] text-slate-400 space-x-1.5 mt-0.5">
                          <span>Izin: {item.izin || 0}</span>
                          <span>·</span>
                          <span>Alpha: {item.alpha || 0}</span>
                          {Number(item.jam_telat_total) > 0 && (
                            <>
                              <span>·</span>
                              <span className="text-amber-400">Telat: {item.jam_telat_total} jam</span>
                            </>
                          )}
                        </div>
                      </td>

                      {/* Penerimaan */}
                      <td className="py-3 px-3">
                        <div className="font-medium text-emerald-400">
                          Rp {(Number(item.subtotal_penerimaan) || 0).toLocaleString('id-ID')}
                        </div>
                        <div className="text-[10px] text-slate-400 mt-0.5">
                          Pokok: Rp {(Number(item.gaji_pokok) || 0).toLocaleString('id-ID')}
                        </div>
                      </td>

                      {/* Potongan */}
                      <td className="py-3 px-3">
                        <div className="font-medium text-rose-400">
                          Rp {(Number(item.subtotal_potongan) || 0).toLocaleString('id-ID')}
                        </div>
                        <div className="text-[10px] text-slate-400 mt-0.5">
                          Absensi: Rp {(Number(item.pot_absensi) || 0).toLocaleString('id-ID')}
                        </div>
                      </td>

                      {/* THP */}
                      <td className="py-3 px-4 text-right">
                        <div className="font-bold text-sm text-indigo-300">
                          Rp {(Number(item.total_gaji_bersih) || 0).toLocaleString('id-ID')}
                        </div>
                      </td>

                      {/* Status WA */}
                      <td className="py-3 px-3 text-center">
                        {item.wa_status === 'sent' ? (
                          <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            <Check className="w-3 h-3" />
                            Terkirim
                          </span>
                        ) : item.wa_status === 'failed' ? (
                          <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-semibold bg-red-500/10 text-red-400 border border-red-500/20">
                            <AlertCircle className="w-3 h-3" />
                            Gagal
                          </span>
                        ) : (
                          <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-semibold bg-slate-800 text-slate-400 border border-slate-700">
                            <Clock className="w-3 h-3" />
                            Pending
                          </span>
                        )}
                      </td>

                      {/* Actions */}
                      <td className="py-3 px-4 text-center">
                        <div className="flex items-center justify-center gap-1.5">
                          {/* Edit Details */}
                          <button
                            onClick={() => handleOpenEdit(item)}
                            title="Edit Rincian Slip Gaji"
                            className="p-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-lg transition-colors cursor-pointer"
                          >
                            <Edit className="w-3.5 h-3.5" />
                          </button>

                          {/* Print / Download PDF */}
                          <a
                            href={`/api/gaji/slip/${item.id}`}
                            target="_blank"
                            rel="noopener noreferrer"
                            title="Cetak / Unduh Slip Gaji PDF"
                            className="p-1.5 bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-300 hover:text-white rounded-lg border border-indigo-500/30 transition-colors"
                          >
                            <Printer className="w-3.5 h-3.5" />
                          </a>

                          {/* Send via WhatsApp */}
                          <button
                            onClick={() => handleSendWa(item)}
                            disabled={sendingWaId === item.id}
                            title="Kirim Dokumen PDF via WhatsApp"
                            className="p-1.5 bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-300 hover:text-white rounded-lg border border-emerald-500/30 transition-colors disabled:opacity-50 cursor-pointer"
                          >
                            {sendingWaId === item.id ? (
                              <RefreshCw className="w-3.5 h-3.5 animate-spin text-emerald-300" />
                            ) : (
                              <Send className="w-3.5 h-3.5" />
                            )}
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      )}

      {/* MODAL 1: GENERATE / HITUNG GAJI BARU */}
      {isGenerateModalOpen && (
        <div className="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl animate-in fade-in zoom-in-95">
            <div className="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
              <div className="flex items-center gap-2">
                <Banknote className="w-5 h-5 text-emerald-400" />
                <h3 className="text-base font-bold text-white">Hitung &amp; Generate Gaji</h3>
              </div>
              <button
                onClick={() => setIsGenerateModalOpen(false)}
                className="text-slate-400 hover:text-white transition-colors cursor-pointer"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <div className="p-6 space-y-4">
              <div className="bg-indigo-950/40 border border-indigo-500/30 rounded-xl p-3.5 text-xs text-indigo-200 flex items-start gap-2.5">
                <Info className="w-4 h-4 text-indigo-400 shrink-0 mt-0.5" />
                <span>
                  Sistem otomatis menghitung rekap absensi dari database presensi bulan bersangkutan.
                  Standar kerja: <strong>26 Hari Kerja</strong>. Potongan dihitung proporsional sesuai formula Excel resmi.
                </span>
              </div>

              {/* Month and Year */}
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1.5">
                    Bulan Penggajian
                  </label>
                  <select
                    value={genBulan}
                    onChange={(e) => setGenBulan(Number(e.target.value))}
                    className="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:border-indigo-500 cursor-pointer"
                  >
                    {MONTH_NAMES.slice(1).map((m, idx) => (
                      <option key={idx + 1} value={idx + 1}>
                        {m}
                      </option>
                    ))}
                  </select>
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1.5">
                    Tahun
                  </label>
                  <input
                    type="number"
                    value={genTahun}
                    onChange={(e) => setGenTahun(Number(e.target.value))}
                    className="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500"
                  />
                </div>
              </div>

              {/* Skema Bunda Daycare Toggle */}
              <div className="pt-2 border-t border-slate-800/80">
                <label className="flex items-center gap-3 cursor-pointer select-none">
                  <input
                    type="checkbox"
                    checked={genSkemaBunda}
                    onChange={(e) => setGenSkemaBunda(e.target.checked)}
                    className="w-4 h-4 rounded border-slate-700 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                  />
                  <div>
                    <div className="text-xs font-semibold text-slate-200">
                      Aktifkan Skema Remunerasi &amp; Insentif Bunda Daycare
                    </div>
                    <div className="text-[11px] text-slate-400">
                      Insentif pool gate siswa (≥ 41 siswa) &amp; reward hadir dasar + premi disiplin 06.30.
                    </div>
                  </div>
                </label>

                {genSkemaBunda && (
                  <div className="mt-3 pl-7 space-y-2">
                    <label className="block text-xs text-slate-300 font-medium">
                      Jumlah Siswa Daycare Lunas SPP Bulan Ini:
                    </label>
                    <input
                      type="number"
                      min={0}
                      value={genSiswaDaycare}
                      onChange={(e) => setGenSiswaDaycare(Number(e.target.value))}
                      className="w-40 bg-slate-950 border border-slate-700 rounded-xl px-3 py-1.5 text-xs text-white focus:outline-none focus:border-indigo-500"
                    />
                    <p className="text-[10px] text-slate-500">
                      Syarat gate buka: ≥ 41 siswa. Jika aktif, tiap bunda menerima pool Rp 5.000 × {genSiswaDaycare} siswa = Rp {(5000 * genSiswaDaycare).toLocaleString('id-ID')}.
                    </p>
                  </div>
                )}
              </div>
            </div>

            <div className="px-6 py-4 bg-slate-950/60 border-t border-slate-800 flex items-center justify-end gap-3">
              <button
                onClick={() => setIsGenerateModalOpen(false)}
                className="px-4 py-2 text-xs font-semibold text-slate-400 hover:text-white transition-colors cursor-pointer"
              >
                Batal
              </button>
              <button
                onClick={handleGenerate}
                disabled={isPending}
                className="flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white text-xs font-semibold rounded-xl shadow-lg shadow-emerald-900/20 transition-all cursor-pointer"
              >
                {isPending && <RefreshCw className="w-3.5 h-3.5 animate-spin" />}
                <span>Proses &amp; Hitung Gaji</span>
              </button>
            </div>
          </div>
        </div>
      )}

      {/* MODAL 2: EDIT INDIVIDUAL DETAIL SLIP GAJI */}
      {isEditModalOpen && editingDetail && (
        <div className="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 overflow-y-auto">
          <div className="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-3xl my-8 overflow-hidden shadow-2xl animate-in fade-in zoom-in-95">
            {/* Modal Header */}
            <div className="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
              <div>
                <h3 className="text-base font-bold text-white">
                  Edit Rincian Slip Gaji: {editingDetail.nama_karyawan}
                </h3>
                <p className="text-xs text-slate-400 mt-0.5">
                  NIK: {editingDetail.nik} · {editingDetail.jabatan} · Cabang {editingDetail.kode_cabang} ({editingDetail.kode_dept})
                </p>
              </div>
              <button
                onClick={() => setIsEditModalOpen(false)}
                className="text-slate-400 hover:text-white transition-colors cursor-pointer"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            {/* Modal Body */}
            <div className="p-6 max-h-[70vh] overflow-y-auto space-y-6">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                {/* LEFT: PENERIMAAN */}
                <div className="space-y-4">
                  <div className="flex items-center gap-2 pb-2 border-b border-slate-800 text-emerald-400">
                    <TrendingUp className="w-4 h-4" />
                    <h4 className="text-xs font-bold uppercase tracking-wider">Komponen Penerimaan</h4>
                  </div>

                  <div>
                    <label className="block text-xs text-slate-400 mb-1">Gaji Pokok (Rp)</label>
                    <input
                      type="number"
                      value={editForm.gaji_pokok || 0}
                      onChange={(e) => setEditForm({ ...editForm, gaji_pokok: Number(e.target.value) })}
                      className="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono"
                    />
                  </div>

                  <div>
                    <label className="block text-xs text-slate-400 mb-1">Tunjangan Transport (Rp)</label>
                    <input
                      type="number"
                      value={editForm.tunj_transport || 0}
                      onChange={(e) => setEditForm({ ...editForm, tunj_transport: Number(e.target.value) })}
                      className="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono"
                    />
                  </div>

                  {/* Guru Specific: Kegiatan & Ekstra */}
                  {editingDetail.tipe_komponen === 'guru' && (
                    <>
                      <div className="grid grid-cols-2 gap-2">
                        <div>
                          <label className="block text-xs text-slate-400 mb-1">Uang Kegiatan (Rp)</label>
                          <input
                            type="number"
                            value={editForm.uang_kegiatan || 0}
                            onChange={(e) => setEditForm({ ...editForm, uang_kegiatan: Number(e.target.value) })}
                            className="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono"
                          />
                        </div>
                        <div>
                          <label className="block text-xs text-slate-400 mb-1">Keterangan Kegiatan</label>
                          <input
                            type="text"
                            placeholder="Contoh: Outing Class"
                            value={editForm.ket_uang_kegiatan || ''}
                            onChange={(e) => setEditForm({ ...editForm, ket_uang_kegiatan: e.target.value })}
                            className="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500"
                          />
                        </div>
                      </div>

                      <div className="grid grid-cols-2 gap-2">
                        <div>
                          <label className="block text-xs text-slate-400 mb-1">Uang Ekstra (Rp)</label>
                          <input
                            type="number"
                            value={editForm.uang_ekstra || 0}
                            onChange={(e) => setEditForm({ ...editForm, uang_ekstra: Number(e.target.value) })}
                            className="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono"
                          />
                        </div>
                        <div>
                          <label className="block text-xs text-slate-400 mb-1">Keterangan Ekstra</label>
                          <input
                            type="text"
                            placeholder="Contoh: Ekskul Menari"
                            value={editForm.ket_uang_ekstra || ''}
                            onChange={(e) => setEditForm({ ...editForm, ket_uang_ekstra: e.target.value })}
                            className="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500"
                          />
                        </div>
                      </div>
                    </>
                  )}

                  {/* TPA Specific: Lembur & Insentif */}
                  {editingDetail.tipe_komponen === 'tpa' && (
                    <>
                      <div className="grid grid-cols-2 gap-2">
                        <div>
                          <label className="block text-xs text-slate-400 mb-1">Uang Lembur (Rp)</label>
                          <input
                            type="number"
                            value={editForm.uang_lembur || 0}
                            onChange={(e) => setEditForm({ ...editForm, uang_lembur: Number(e.target.value) })}
                            className="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono"
                          />
                        </div>
                        <div>
                          <label className="block text-xs text-slate-400 mb-1">Keterangan Lembur</label>
                          <input
                            type="text"
                            placeholder="Contoh: Lembur Sabtu"
                            value={editForm.ket_uang_lembur || ''}
                            onChange={(e) => setEditForm({ ...editForm, ket_uang_lembur: e.target.value })}
                            className="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500"
                          />
                        </div>
                      </div>

                      <div className="grid grid-cols-2 gap-2">
                        <div>
                          <label className="block text-xs text-slate-400 mb-1">Insentif Pool Siswa (Rp)</label>
                          <input
                            type="number"
                            value={editForm.insentif_pool_siswa || 0}
                            onChange={(e) => setEditForm({ ...editForm, insentif_pool_siswa: Number(e.target.value) })}
                            className="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono"
                          />
                        </div>
                        <div>
                          <label className="block text-xs text-slate-400 mb-1">Premi Disiplin 06.30 (Rp)</label>
                          <input
                            type="number"
                            value={editForm.premi_disiplin_pagi || 0}
                            onChange={(e) => setEditForm({ ...editForm, premi_disiplin_pagi: Number(e.target.value) })}
                            className="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono"
                          />
                        </div>
                      </div>
                    </>
                  )}

                  {/* Bonus & Penyesuaian (All) */}
                  <div className="grid grid-cols-2 gap-2">
                    <div>
                      <label className="block text-xs text-slate-400 mb-1">Bonus / THR (Rp)</label>
                      <input
                        type="number"
                        value={editForm.bonus || 0}
                        onChange={(e) => setEditForm({ ...editForm, bonus: Number(e.target.value) })}
                        className="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono"
                      />
                    </div>
                    <div>
                      <label className="block text-xs text-slate-400 mb-1">Keterangan Bonus</label>
                      <input
                        type="text"
                        placeholder="Contoh: Bonus Tahunan"
                        value={editForm.ket_bonus || ''}
                        onChange={(e) => setEditForm({ ...editForm, ket_bonus: e.target.value })}
                        className="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500"
                      />
                    </div>
                  </div>
                </div>

                {/* RIGHT: POTONGAN */}
                <div className="space-y-4">
                  <div className="flex items-center gap-2 pb-2 border-b border-slate-800 text-rose-400">
                    <AlertCircle className="w-4 h-4" />
                    <h4 className="text-xs font-bold uppercase tracking-wider">Komponen Potongan</h4>
                  </div>

                  <div>
                    <label className="block text-xs text-slate-400 mb-1">
                      Potongan Absensi Resmi (Rp)
                    </label>
                    <input
                      type="number"
                      value={editForm.pot_absensi || 0}
                      onChange={(e) => setEditForm({ ...editForm, pot_absensi: Number(e.target.value) })}
                      className="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono"
                    />
                    <p className="text-[10px] text-slate-500 mt-1">
                      Formula: (Izin: {editingDetail.izin || 0} + Alpha: {editingDetail.alpha || 0}) × Gaji Harian + ({editingDetail.jam_telat_total || 0} jam telat × Gaji/Jam).
                    </p>
                  </div>

                  <div className="grid grid-cols-2 gap-2">
                    <div>
                      <label className="block text-xs text-slate-400 mb-1">Kasbon / Pinjaman (Rp)</label>
                      <input
                        type="number"
                        value={editForm.kasbon || 0}
                        onChange={(e) => setEditForm({ ...editForm, kasbon: Number(e.target.value) })}
                        className="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono"
                      />
                    </div>
                    <div>
                      <label className="block text-xs text-slate-400 mb-1">Keterangan Kasbon</label>
                      <input
                        type="text"
                        placeholder="Contoh: Cicilan 1/3"
                        value={editForm.ket_kasbon || ''}
                        onChange={(e) => setEditForm({ ...editForm, ket_kasbon: e.target.value })}
                        className="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500"
                      />
                    </div>
                  </div>

                  <div className="grid grid-cols-2 gap-2">
                    <div>
                      <label className="block text-xs text-slate-400 mb-1">Sanksi Disiplin (Rp)</label>
                      <input
                        type="number"
                        value={editForm.sanksi_disiplin || 0}
                        onChange={(e) => setEditForm({ ...editForm, sanksi_disiplin: Number(e.target.value) })}
                        className="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono"
                      />
                    </div>
                    <div>
                      <label className="block text-xs text-slate-400 mb-1">Keterangan Sanksi</label>
                      <input
                        type="text"
                        placeholder="Contoh: SP 1"
                        value={editForm.ket_sanksi || ''}
                        onChange={(e) => setEditForm({ ...editForm, ket_sanksi: e.target.value })}
                        className="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500"
                      />
                    </div>
                  </div>

                  <div>
                    <label className="block text-xs text-slate-400 mb-1">Denda Keterlambatan (Rp)</label>
                    <input
                      type="number"
                      value={editForm.denda_kedisiplinan || 0}
                      onChange={(e) => setEditForm({ ...editForm, denda_kedisiplinan: Number(e.target.value) })}
                      className="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono"
                    />
                  </div>
                </div>
              </div>

              {/* LIVE RECALCULATION PREVIEW */}
              <div className="bg-slate-950 border border-slate-800 rounded-xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div>
                  <div className="text-xs text-slate-400">
                    Subtotal Penerimaan: <strong className="text-emerald-400">Rp {editSubtotalPenerimaan.toLocaleString('id-ID')}</strong>
                  </div>
                  <div className="text-xs text-slate-400 mt-0.5">
                    Subtotal Potongan: <strong className="text-rose-400">Rp {editSubtotalPotongan.toLocaleString('id-ID')}</strong>
                  </div>
                </div>

                <div className="text-right">
                  <div className="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">
                    Estimasi Gaji Bersih (THP)
                  </div>
                  <div className="text-xl font-bold text-indigo-300 font-mono">
                    Rp {editTotalGajiBersih.toLocaleString('id-ID')}
                  </div>
                </div>
              </div>
            </div>

            {/* Modal Footer */}
            <div className="px-6 py-4 bg-slate-950/60 border-t border-slate-800 flex items-center justify-end gap-3">
              <button
                onClick={() => setIsEditModalOpen(false)}
                className="px-4 py-2 text-xs font-semibold text-slate-400 hover:text-white transition-colors cursor-pointer"
              >
                Batal
              </button>
              <button
                onClick={handleSaveEdit}
                disabled={isPending}
                className="flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white text-xs font-semibold rounded-xl shadow-lg shadow-indigo-900/20 transition-all cursor-pointer"
              >
                {isPending && <RefreshCw className="w-3.5 h-3.5 animate-spin" />}
                <span>Simpan Perubahan</span>
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
