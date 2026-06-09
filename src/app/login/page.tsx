import React from 'react';
import type { Metadata } from 'next';
import Link from 'next/link';
import LoginForm from './login-form';

export const metadata: Metadata = {
  title: 'Login Karyawan - E-Presensi Geolocation',
  description: 'Silakan login dengan NIK Anda untuk melakukan presensi kehadiran harian.',
};

export default function LoginPage() {
  return (
    <main className="min-h-screen w-full flex items-center justify-center bg-gradient-to-br from-[#0c1020] via-[#0d1636] to-[#050814] p-4 relative overflow-hidden select-none">
      {/* Decorative Blur Spheres for Glassmorphism backdrop */}
      <div className="absolute top-1/4 left-1/4 w-72 h-72 bg-blue-600/10 rounded-full blur-[100px] pointer-events-none" />
      <div className="absolute bottom-1/4 right-1/4 w-72 h-72 bg-indigo-600/10 rounded-full blur-[100px] pointer-events-none" />

      {/* Mobile-first card container */}
      <div className="w-full max-w-[440px] bg-white/[0.04] backdrop-blur-xl border border-white/[0.08] rounded-[32px] px-6 py-8 md:px-8 shadow-2xl relative z-10">
        
        {/* Header Section */}
        <div className="text-center mb-6">
          <div className="inline-block relative mb-4">
            <img 
              src="/assets/img/login/login.webp" 
              alt="Presensi Arjuna Logo" 
              className="w-48 mx-auto h-auto max-h-40 object-contain drop-shadow-xl animate-fade-in"
            />
          </div>
          <h1 className="text-2xl font-extrabold text-white tracking-wide">
            Presensi Arjuna
          </h1>
          <p className="text-blue-300/60 text-sm mt-1">
            Silakan login untuk memulai kehadiran
          </p>
        </div>

        {/* Form Section */}
        <LoginForm />

        {/* Footer Redirect */}
        <div className="text-center mt-8 pt-6 border-t border-white/[0.06]">
          <Link 
            href="/panel" 
            className="text-sm text-blue-400 hover:text-blue-300 font-medium transition-colors"
          >
            Masuk sebagai Administrator / Admin Dept
          </Link>
        </div>
      </div>
    </main>
  );
}
