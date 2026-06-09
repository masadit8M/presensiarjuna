import React from 'react';
import { Loader2 } from 'lucide-react';

export default function EmployeeLoading() {
  return (
    <div className="flex flex-col items-center justify-center min-h-[60vh] space-y-3">
      <Loader2 className="w-8 h-8 text-blue-500 animate-spin" />
      <p className="text-xs text-slate-400 font-medium animate-pulse">Memuat halaman...</p>
    </div>
  );
}
