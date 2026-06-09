'use client';

import React, { useState, useTransition } from 'react';
import { useRouter } from 'next/navigation';
import { updateEmployeeProfileAction } from '@/lib/actions/karyawan';
import { ArrowLeft, Loader2, AlertCircle, CheckCircle, User, Phone, Lock, Eye, EyeOff } from 'lucide-react';
import Link from 'next/link';

export default function EditProfileClient({ employee }: { employee: any }) {
  const router = useRouter();

  // Form States
  const [namaLengkap, setNamaLengkap] = useState(employee.nama_lengkap || '');
  const [noHp, setNoHp] = useState(employee.no_hp || '');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);

  // File Upload States
  const [fileBase64, setFileBase64] = useState<string>('');
  const [fileName, setFileName] = useState<string>('');
  const [previewUrl, setPreviewUrl] = useState<string>('');

  // Status States
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);
  const [isPending, startTransition] = useTransition();

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) {
      setError('File harus berupa gambar!');
      return;
    }

    if (file.size > 1.5 * 1024 * 1024) {
      setError('Ukuran file maksimal adalah 1.5MB.');
      e.target.value = '';
      return;
    }

    const reader = new FileReader();
    reader.onloadend = () => {
      setFileBase64(reader.result as string);
      setFileName(file.name);
      setPreviewUrl(reader.result as string);
      setError(null);
    };
    reader.readAsDataURL(file);
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    if (!namaLengkap || !noHp) {
      setError('Nama Lengkap dan Nomor Handphone wajib diisi!');
      return;
    }

    setError(null);
    setSuccess(null);

    startTransition(async () => {
      const formData = new FormData();
      formData.append('nama_lengkap', namaLengkap);
      formData.append('no_hp', noHp);
      if (password) formData.append('password', password);
      if (fileBase64) {
        formData.append('foto', fileBase64);
        formData.append('foto_name', fileName);
      }

      const result = await updateEmployeeProfileAction(null, formData);

      if (result?.error) {
        setError(result.error);
      } else if (result?.success) {
        setSuccess(result.message || 'Profil berhasil diperbarui.');
        setPassword('');
        setTimeout(() => {
          setSuccess(null);
          router.refresh();
        }, 2000);
      }
    });
  };

  // Get current photo URL or fallback avatar
  const currentPhotoUrl = employee.foto 
    ? `${process.env.NEXT_PUBLIC_SUPABASE_URL}/storage/v1/object/public/karyawan/${employee.foto}`
    : null;

  return (
    <div className="space-y-5">
      {/* Header */}
      <div className="flex items-center space-x-3 pb-3 border-b border-white/[0.06]">
        <Link href="/dashboard" className="text-slate-400 hover:text-white transition-colors">
          <ArrowLeft className="w-6 h-6" />
        </Link>
        <h2 className="text-lg font-bold text-white">Edit Profil</h2>
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

      {/* Form Container */}
      <form onSubmit={handleSubmit} className="bg-[#131936] border border-white/[0.05] rounded-[24px] p-5 shadow-lg space-y-5">
        
        {/* Avatar Upload Container */}
        <div className="flex flex-col items-center justify-center space-y-3 pb-2">
          <div className="w-24 h-24 rounded-3xl bg-slate-900 border-2 border-white/[0.08] relative overflow-hidden flex items-center justify-center">
            {previewUrl ? (
              <img src={previewUrl} alt="Preview Avatar" className="w-full h-full object-cover" />
            ) : currentPhotoUrl ? (
              <img src={currentPhotoUrl} alt="Foto Profil" className="w-full h-full object-cover" />
            ) : (
              <User className="w-12 h-12 text-slate-500" />
            )}
          </div>
          
          <div className="relative">
            <input
              type="file"
              accept="image/*"
              onChange={handleFileChange}
              disabled={isPending}
              id="avatar-input"
              className="hidden"
            />
            <label
              htmlFor="avatar-input"
              className="px-4 py-2 bg-blue-500/10 hover:bg-blue-500/20 border border-blue-500/30 text-blue-400 text-xs font-semibold rounded-xl cursor-pointer transition-colors block"
            >
              Ubah Foto Profil
            </label>
          </div>
        </div>

        {/* NIK (Disabled) */}
        <div className="space-y-1.5 opacity-60">
          <label className="text-xs font-semibold text-slate-400 pl-0.5">NIK (Tidak dapat diubah)</label>
          <input
            type="text"
            disabled
            value={employee.nik}
            className="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-sm text-slate-400 cursor-not-allowed"
          />
        </div>

        {/* Nama Lengkap */}
        <div className="space-y-1.5">
          <label className="text-xs font-semibold text-slate-300 pl-0.5 flex items-center">
            <User className="w-3.5 h-3.5 mr-1 text-blue-400" />
            <span>Nama Lengkap</span>
          </label>
          <input
            type="text"
            required
            value={namaLengkap}
            onChange={(e) => setNamaLengkap(e.target.value)}
            disabled={isPending}
            placeholder="Masukkan Nama Lengkap"
            className="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50"
          />
        </div>

        {/* Nomor Handphone */}
        <div className="space-y-1.5">
          <label className="text-xs font-semibold text-slate-300 pl-0.5 flex items-center">
            <Phone className="w-3.5 h-3.5 mr-1 text-blue-400" />
            <span>Nomor Handphone</span>
          </label>
          <input
            type="text"
            required
            value={noHp}
            onChange={(e) => setNoHp(e.target.value)}
            disabled={isPending}
            placeholder="Contoh: 0812XXXXXXXX"
            className="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50"
          />
        </div>

        {/* Ganti Password (Optional) */}
        <div className="space-y-1.5 pt-2 border-t border-white/[0.04]">
          <label className="text-xs font-semibold text-slate-300 pl-0.5 flex items-center">
            <Lock className="w-3.5 h-3.5 mr-1 text-blue-400" />
            <span>Ganti Password (Kosongkan jika tidak ingin diubah)</span>
          </label>
          <div className="relative group">
            <input
              type={showPassword ? 'text' : 'password'}
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              disabled={isPending}
              placeholder="Masukkan Password Baru"
              className="w-full pl-3 pr-12 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all"
            />
            <button
              type="button"
              onClick={() => setShowPassword(!showPassword)}
              disabled={isPending}
              className="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-500 hover:text-slate-300 transition-colors"
            >
              {showPassword ? <EyeOff className="w-5 h-5" /> : <Eye className="w-5 h-5" />}
            </button>
          </div>
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
              <span>Menyimpan Perubahan...</span>
            </>
          ) : (
            <span>Simpan Perubahan</span>
          )}
        </button>
      </form>
    </div>
  );
}
