'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { 
  LayoutDashboard, Users, MapPin, Building2, Clock, 
  Map, FileSpreadsheet, FileText, LogOut, Menu, X, Banknote
} from 'lucide-react';
import { logoutAction } from '@/lib/actions/auth';

export default function AdminLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  const pathname = usePathname();
  const [isSidebarOpen, setIsSidebarOpen] = useState(false);

  const isActive = (path: string) => {
    if (path === '/panel/dashboardadmin') {
      return pathname === '/panel/dashboardadmin';
    }
    return pathname.startsWith(path);
  };

  const menuItems = [
    { label: 'Dashboard', icon: LayoutDashboard, href: '/panel/dashboardadmin' },
    { label: 'Data Karyawan', icon: Users, href: '/karyawan' },
    { label: 'Data Cabang', icon: MapPin, href: '/cabang' },
    { label: 'Data Departemen', icon: Building2, href: '/departemen' },
    { label: 'Konfigurasi Jam Kerja', icon: Clock, href: '/jamkerja' },
    { label: 'Monitoring Presensi', icon: Map, href: '/monitoring' },
    { label: 'Laporan Kehadiran', icon: FileText, href: '/laporan' },
    { label: 'Rekap Presensi', icon: FileSpreadsheet, href: '/rekap' },
    { label: 'Penggajian & Slip Gaji', icon: Banknote, href: '/gaji' },
  ];

  return (
    <div className="min-h-screen bg-slate-950 flex font-sans">
      {/* Mobile Sidebar Overlay */}
      {isSidebarOpen && (
        <div 
          onClick={() => setIsSidebarOpen(false)}
          className="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 lg:hidden"
        />
      )}

      {/* Sidebar Navigation */}
      <aside className={`fixed inset-y-0 left-0 w-64 bg-slate-900 border-r border-slate-800 flex flex-col z-50 transition-transform duration-300 lg:translate-x-0 lg:static lg:h-screen ${
        isSidebarOpen ? 'translate-x-0' : '-translate-x-full'
      }`}>
        {/* Brand Logo Header */}
        <div className="h-16 border-b border-slate-800 flex items-center justify-between px-6 shrink-0 bg-slate-900/50">
          <div className="flex items-center space-x-2.5">
            <div className="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold text-lg shadow-md shadow-blue-600/30">
              A
            </div>
            <span className="text-white font-bold tracking-tight text-sm uppercase">Arjuna Admin</span>
          </div>
          <button 
            onClick={() => setIsSidebarOpen(false)}
            className="lg:hidden text-slate-400 hover:text-white"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Sidebar Nav Links */}
        <nav className="flex-1 overflow-y-auto py-6 px-4 space-y-1.5">
          {menuItems.map((item) => {
            const Icon = item.icon;
            const active = isActive(item.href);
            return (
              <Link
                key={item.href}
                href={item.href}
                onClick={() => setIsSidebarOpen(false)}
                className={`flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200 ${
                  active 
                    ? 'text-white bg-blue-600 shadow-md shadow-blue-600/20' 
                    : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/40'
                }`}
              >
                <Icon className="w-4 h-4 shrink-0" />
                <span>{item.label}</span>
              </Link>
            );
          })}
        </nav>

        {/* Logout at bottom */}
        <div className="p-4 border-t border-slate-800 bg-slate-900/50 shrink-0">
          <form action={logoutAction}>
            <button
              type="submit"
              className="w-full flex items-center space-x-3 px-4 py-3 text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-xl text-sm font-medium transition-colors"
            >
              <LogOut className="w-4 h-4" />
              <span>Log Out</span>
            </button>
          </form>
        </div>
      </aside>

      {/* Main Content frame */}
      <div className="flex-1 flex flex-col min-w-0 overflow-x-hidden">
        {/* Top Header Navbar */}
        <header className="h-16 bg-slate-900 border-b border-slate-800 flex items-center justify-between px-6 shrink-0 relative z-30">
          {/* Mobile hamburger menu */}
          <button 
            onClick={() => setIsSidebarOpen(true)}
            className="lg:hidden text-slate-400 hover:text-white"
          >
            <Menu className="w-6 h-6" />
          </button>

          <div className="hidden lg:block">
            <h2 className="text-sm font-semibold text-slate-400 uppercase tracking-wider">E-Presensi Portal</h2>
          </div>

          {/* User Profile info */}
          <div className="flex items-center space-x-4">
            <div className="text-right">
              <h4 className="text-xs font-bold text-white">Administrator</h4>
              <span className="text-[10px] text-blue-400 bg-blue-500/10 px-2 py-0.5 rounded-full font-semibold mt-0.5 inline-block uppercase">
                Super Admin
              </span>
            </div>
            <div className="w-9 h-9 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-400">
              <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                <path strokeLinecap="round" strokeLinejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
          </div>
        </header>

        {/* Content body wrapper */}
        <main className="flex-1 overflow-y-auto p-6 md:p-8">
          {children}
        </main>
      </div>
    </div>
  );
}
