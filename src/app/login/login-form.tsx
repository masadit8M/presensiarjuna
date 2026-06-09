'use client';

import React, { useState, useTransition } from 'react';
import { useRouter } from 'next/navigation';
import { loginEmployeeAction } from '@/lib/actions/auth';
import { User, Lock, Eye, EyeOff, Loader2, AlertCircle } from 'lucide-react';

export default function LoginForm() {
  const [nik, setNik] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [isPending, startTransition] = useTransition();
  const router = useRouter();

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!nik || !password) {
      setError('NIK dan password tidak boleh kosong!');
      return;
    }
    setError(null);

    startTransition(async () => {
      const formData = new FormData();
      formData.append('nik', nik);
      formData.append('password', password);

      const result = await loginEmployeeAction(null, formData);
      
      if (result?.error) {
        setError(result.error);
      } else if (result?.success) {
        // Redirect to employee dashboard
        router.push('/dashboard');
        router.refresh();
      }
    });
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-5">
      {error && (
        <div className="bg-red-500/15 border border-red-500/30 rounded-2xl p-4 flex items-start space-x-3 text-red-200 text-sm animate-shake">
          <AlertCircle className="w-5 h-5 shrink-0 mt-0.5" />
          <span>{error}</span>
        </div>
      )}

      {/* Input NIK */}
      <div className="space-y-2">
        <label className="text-sm font-medium text-blue-200 block pl-1">NIK (Nomor Induk Karyawan)</label>
        <div className="relative group">
          <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-blue-300 group-focus-within:text-blue-400 transition-colors">
            <User className="w-5 h-5" />
          </div>
          <input
            type="text"
            required
            value={nik}
            onChange={(e) => setNik(e.target.value)}
            disabled={isPending}
            placeholder="Masukkan NIK Anda"
            className="w-full pl-12 pr-4 py-3.5 bg-white/5 border border-white/10 rounded-2xl text-white placeholder-blue-300/40 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
          />
        </div>
      </div>

      {/* Input Password */}
      <div className="space-y-2">
        <label className="text-sm font-medium text-blue-200 block pl-1">Password</label>
        <div className="relative group">
          <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-blue-300 group-focus-within:text-blue-400 transition-colors">
            <Lock className="w-5 h-5" />
          </div>
          <input
            type={showPassword ? 'text' : 'password'}
            required
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            disabled={isPending}
            placeholder="Masukkan Password"
            className="w-full pl-12 pr-12 py-3.5 bg-white/5 border border-white/10 rounded-2xl text-white placeholder-blue-300/40 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
          />
          <button
            type="button"
            onClick={() => setShowPassword(!showPassword)}
            disabled={isPending}
            className="absolute inset-y-0 right-0 pr-4 flex items-center text-blue-300/60 hover:text-blue-300 transition-colors"
          >
            {showPassword ? <EyeOff className="w-5 h-5" /> : <Eye className="w-5 h-5" />}
          </button>
        </div>
      </div>

      {/* Submit Button */}
      <button
        type="submit"
        disabled={isPending}
        className="w-full py-4 px-6 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white font-semibold rounded-2xl shadow-lg shadow-blue-500/20 active:scale-[0.98] transition-all duration-200 flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100 mt-2"
      >
        {isPending ? (
          <>
            <Loader2 className="w-5 h-5 animate-spin" />
            <span>Memproses Login...</span>
          </>
        ) : (
          <span>Masuk Sekarang</span>
        )}
      </button>
    </form>
  );
}
