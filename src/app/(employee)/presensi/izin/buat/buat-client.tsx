'use client';

import React, { useState, useTransition } from 'react';
import { useRouter } from 'next/navigation';
import { storeIzinAction } from '@/lib/actions/izin';
import { ArrowLeft, Loader2, AlertCircle, Calendar, FileText, CheckCircle } from 'lucide-react';
import Link from 'next/link';

export default function BuatIzinClient({ masterCuti }: { masterCuti: any[] }) {
  const router = useRouter();
  
  // Form States
  const [tglDari, setTglDari] = useState('');
  const [tglSampai, setTglSampai] = useState('');
  const [status, setStatus] = useState<'i' | 's' | 'c'>('i');
  const [keterangan, setKeterangan] = useState('');
  const [selectedCutiId, setSelectedCutiId] = useState('');
  
  // File Upload States (Sakit only)
  const [fileBase64, setFileBase64] = useState<string>('');
  const [fileName, setFileName] = useState<string>('');

  // Status States
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);
  const [isPending, startTransition] = useTransition();

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    if (file.size > 2 * 1024 * 1024) {
      setError('Ukuran file maksimal adalah 2MB.');
      e.target.value = '';
      return;
    }

    const reader = new FileReader();
    reader.onloadend = () => {
      setFileBase64(reader.result as string);
      setFileName(file.name);
      setError(null);
    };
    reader.readAsDataURL(file);
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    if (!tglDari || !tglSampai || !status || !keterangan) {
      setError('Harap isi semua kolom wajib!');
      return;
    }

    if (new Date(tglSampai) < new Date(tglDari)) {
      setError('Tanggal selesai tidak boleh sebelum tanggal mulai.');
      return;
    }

    if (status === 'c' && !selectedCutiId) {
      setError('Harap pilih jenis cuti.');
      return;
    }

    if (status === 's' && !fileBase64) {
      setError('Harap lampirkan surat dokter untuk sakit.');
      return;
    }

    setError(null);
    setSuccess(null);

    startTransition(async () => {
      const formData = new FormData();
      formData.append('tgl_izin_dari', tglDari);
      formData.append('tgl_izin_sampai', tglSampai);
      formData.append('status', status);
      formData.append('keterangan', keterangan);
      if (status === 'c') formData.append('kode_cuti', selectedCutiId);
      if (status === 's' && fileBase64) {
        formData.append('doc_sid', fileBase64);
        formData.append('doc_sid_name', fileName);
      }

      const result = await storeIzinAction(null, formData);

      if (result?.error) {
        setError(result.error);
      } else if (result?.success) {
        setSuccess(result.message || 'Pengajuan sukses dikirim.');
        setTimeout(() => {
          router.push('/presensi/izin');
          router.refresh();
        }, 2000);
      }
    });
  };

  return (
    <div className="space-y-5">
      {/* Header */}
      <div className="flex items-center space-x-3 pb-3 border-b border-white/[0.06]">
        <Link href="/presensi/izin" className="text-slate-400 hover:text-white transition-colors">
          <ArrowLeft className="w-6 h-6" />
        </Link>
        <h2 className="text-lg font-bold text-white">Form Pengajuan Izin</h2>
      </div>

      {/* Success Notification */}
      {success && (
        <div className="bg-emerald-500/15 border border-emerald-500/30 rounded-2xl p-4 flex items-start space-x-3 text-emerald-300 text-sm">
          <CheckCircle className="w-5 h-5 shrink-0 mt-0.5" />
          <span>{success}</span>
        </div>
      )}

      {/* Error Notification */}
      {error && (
        <div className="bg-red-500/15 border border-red-500/30 rounded-2xl p-4 flex items-start space-x-3 text-red-300 text-sm animate-shake">
          <AlertCircle className="w-5 h-5 shrink-0 mt-0.5" />
          <span>{error}</span>
        </div>
      )}

      {/* Form Card */}
      <form onSubmit={handleSubmit} className="bg-[#131936] border border-white/[0.05] rounded-[24px] p-5 shadow-lg space-y-4">
        {/* Tgl Mulai */}
        <div className="space-y-1.5">
          <label className="text-xs font-semibold text-slate-300 pl-0.5 flex items-center">
            <Calendar className="w-3.5 h-3.5 mr-1 text-blue-400" />
            <span>Mulai Tanggal</span>
          </label>
          <input
            type="date"
            required
            value={tglDari}
            onChange={(e) => setTglDari(e.target.value)}
            disabled={isPending}
            className="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50"
          />
        </div>

        {/* Tgl Selesai */}
        <div className="space-y-1.5">
          <label className="text-xs font-semibold text-slate-300 pl-0.5 flex items-center">
            <Calendar className="w-3.5 h-3.5 mr-1 text-blue-400" />
            <span>Sampai Tanggal</span>
          </label>
          <input
            type="date"
            required
            value={tglSampai}
            onChange={(e) => setTglSampai(e.target.value)}
            disabled={isPending}
            className="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50"
          />
        </div>

        {/* Status / Tipe Pengajuan */}
        <div className="space-y-1.5">
          <label className="text-xs font-semibold text-slate-300 pl-0.5">Tipe Pengajuan</label>
          <select
            value={status}
            onChange={(e) => {
              setStatus(e.target.value as 'i' | 's' | 'c');
              setError(null);
            }}
            disabled={isPending}
            className="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50"
          >
            <option value="i">Izin Absen (Tidak Masuk Kerja)</option>
            <option value="s">Sakit (Butuh Surat Dokter)</option>
            <option value="c">Cuti Tahunan / Khusus</option>
          </select>
        </div>

        {/* Conditional Field: Cuti Type */}
        {status === 'c' && (
          <div className="space-y-1.5 animate-fade-in">
            <label className="text-xs font-semibold text-slate-300 pl-0.5">Jenis Cuti</label>
            <select
              value={selectedCutiId}
              required
              onChange={(e) => setSelectedCutiId(e.target.value)}
              disabled={isPending}
              className="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50"
            >
              <option value="">-- Pilih Jenis Cuti --</option>
              {masterCuti.map((c) => (
                <option key={c.kode_cuti} value={c.kode_cuti}>
                  {c.nama_cuti} ({c.jml_hari} Hari)
                </option>
              ))}
            </select>
          </div>
        )}

        {/* Conditional Field: Sakit Document (Surat Dokter) */}
        {status === 's' && (
          <div className="space-y-1.5 animate-fade-in">
            <label className="text-xs font-semibold text-slate-300 pl-0.5 flex items-center">
              <FileText className="w-3.5 h-3.5 mr-1 text-rose-400" />
              <span>Lampirkan Surat Keterangan Dokter (Gambar/PDF)</span>
            </label>
            <input
              type="file"
              required
              accept="image/*,application/pdf"
              onChange={handleFileChange}
              disabled={isPending}
              className="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-blue-500/10 file:text-blue-400 hover:file:bg-blue-500/20"
            />
          </div>
        )}

        {/* Keterangan */}
        <div className="space-y-1.5">
          <label className="text-xs font-semibold text-slate-300 pl-0.5">Keterangan / Alasan</label>
          <textarea
            required
            rows={4}
            value={keterangan}
            onChange={(e) => setKeterangan(e.target.value)}
            disabled={isPending}
            placeholder="Tuliskan keterangan detail pengajuan Anda..."
            className="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50"
          />
        </div>

        {/* Submit */}
        <button
          type="submit"
          disabled={isPending}
          className="w-full py-3.5 px-6 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/15 active:scale-[0.98] transition-all duration-200 flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {isPending ? (
            <>
              <Loader2 className="w-5 h-5 animate-spin" />
              <span>Mengirim Pengajuan...</span>
            </>
          ) : (
            <span>Kirim Pengajuan</span>
          )}
        </button>
      </form>
    </div>
  );
}
