import React from 'react';
import type { Metadata } from 'next';
import Link from 'next/link';
import AdminLoginForm from './admin-login-form';

export const metadata: Metadata = {
  title: 'Admin Portal - E-Presensi Geolocation',
  description: 'Halaman khusus login administrator dan admin departemen E-Presensi.',
};

export default function AdminLoginPage() {
  return (
    <main className="min-h-screen w-full flex items-center justify-center bg-slate-950 p-4 relative overflow-hidden select-none">
      {/* Grid Background Effect */}
      <div className="absolute inset-0 bg-[linear-gradient(to_right,#0f172a_1px,transparent_1px),linear-gradient(to_bottom,#0f172a_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)] pointer-events-none" />
      
      {/* Decorative Glow */}
      <div className="absolute top-0 left-1/2 -translate-x-1/2 w-[500px] h-[300px] bg-blue-500/10 rounded-full blur-[120px] pointer-events-none" />

      {/* Login Card */}
      <div className="w-full max-w-[460px] bg-slate-900 border border-slate-800 rounded-3xl px-8 py-10 shadow-2xl relative z-10">
        
        {/* Header */}
        <div className="text-center mb-8">
          <div className="w-16 h-16 bg-blue-600/15 border border-blue-500/30 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg 
              className="w-8 h-8 text-blue-500" 
              fill="none" 
              viewBox="0 0 24 24" 
              stroke="currentColor" 
              strokeWidth="2"
            >
              <path strokeLinecap="round" strokeLinejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </div>
          <h1 className="text-2xl font-bold text-white tracking-tight">
            Administrator Portal
          </h1>
          <p className="text-slate-400 text-sm mt-1">
            E-Presensi Geolocation TK Islam Plus Arjuna
          </p>
        </div>

        {/* Admin Login Form */}
        <AdminLoginForm />

        {/* Back Link */}
        <div className="text-center mt-8 pt-6 border-t border-slate-800">
          <Link 
            href="/login" 
            className="text-sm text-slate-400 hover:text-white transition-colors"
          >
            ← Kembali ke Portal Karyawan
          </Link>
        </div>
      </div>
    </main>
  );
}
