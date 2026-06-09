'use client';

import React from 'react';
import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { Home, Calendar, Camera, FileText, User } from 'lucide-react';

export default function EmployeeLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  const pathname = usePathname();

  // Helper to determine active route
  const isActive = (path: string) => {
    if (path === '/dashboard') {
      return pathname === '/dashboard';
    }
    return pathname.startsWith(path);
  };

  return (
    <div className="min-h-screen bg-slate-950 flex flex-col font-sans">
      {/* Mobile-centric Frame (Max width 450px centered for native mobile app feel) */}
      <div className="w-full max-w-md mx-auto h-screen bg-[#090d20] shadow-2xl relative flex flex-col border-x border-white/[0.03]">
        
        {/* Main Content Area */}
        <main className="flex-1 overflow-y-auto px-4 pt-4 pb-24">
          {children}
        </main>

        {/* Floating Mobile Bottom Navigation Bar */}
        <nav className="fixed bottom-4 left-4 right-4 max-w-md mx-auto h-16 bg-[#131936]/90 backdrop-blur-md border border-white/[0.08] rounded-2xl flex items-center justify-around px-2 z-50 shadow-lg shadow-black/30">
          
          {/* Tab: Home */}
          <a 
            href="/dashboard" 
            className={`flex flex-col items-center justify-center w-12 h-12 rounded-xl transition-all duration-200 ${
              isActive('/dashboard') 
                ? 'text-blue-400 bg-blue-500/10' 
                : 'text-slate-400 hover:text-slate-300'
            }`}
          >
            <Home className="w-5 h-5" />
            <span className="text-[10px] mt-1 font-medium">Home</span>
          </a>

          {/* Tab: Histori */}
          <a 
            href="/presensi/histori" 
            className={`flex flex-col items-center justify-center w-12 h-12 rounded-xl transition-all duration-200 ${
              isActive('/presensi/histori') 
                ? 'text-blue-400 bg-blue-500/10' 
                : 'text-slate-400 hover:text-slate-300'
            }`}
          >
            <Calendar className="w-5 h-5" />
            <span className="text-[10px] mt-1 font-medium">Riwayat</span>
          </a>

          {/* Tab Central: Camera Absen (Floating circle) */}
          <div className="relative -top-5">
            <a 
              href="/presensi/create" 
              className="flex items-center justify-center w-14 h-14 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white rounded-full shadow-lg shadow-blue-500/40 hover:scale-105 active:scale-95 transition-all duration-200 border-4 border-[#090d20]"
            >
              <Camera className="w-6 h-6 animate-pulse" />
            </a>
          </div>

          {/* Tab: Izin */}
          <a 
            href="/presensi/izin" 
            className={`flex flex-col items-center justify-center w-12 h-12 rounded-xl transition-all duration-200 ${
              isActive('/presensi/izin') 
                ? 'text-blue-400 bg-blue-500/10' 
                : 'text-slate-400 hover:text-slate-300'
            }`}
          >
            <FileText className="w-5 h-5" />
            <span className="text-[10px] mt-1 font-medium">Izin</span>
          </a>

          {/* Tab: Profil */}
          <a 
            href="/editprofile" 
            className={`flex flex-col items-center justify-center w-12 h-12 rounded-xl transition-all duration-200 ${
              isActive('/editprofile') 
                ? 'text-blue-400 bg-blue-500/10' 
                : 'text-slate-400 hover:text-slate-300'
            }`}
          >
            <User className="w-5 h-5" />
            <span className="text-[10px] mt-1 font-medium">Profil</span>
          </a>
          
        </nav>
      </div>
    </div>
  );
}
