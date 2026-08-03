'use client';

import React, { useState } from 'react';
import { Camera, X, Maximize2 } from 'lucide-react';

interface PresensiPhotoProps {
  filename: string | null | undefined;
  title: string;
  alt: string;
  bucket?: string;
  className?: string;
  allowZoom?: boolean;
}

export default function PresensiPhoto({
  filename,
  title,
  alt,
  bucket = 'absensi',
  className = 'w-9 h-12',
  allowZoom = true,
}: PresensiPhotoProps) {
  const [hasError, setHasError] = useState(false);
  const [isOpen, setIsOpen] = useState(false);

  if (!filename || filename.trim() === '' || hasError) {
    return (
      <div
        className={`${className} bg-slate-950 border border-slate-800 rounded-lg overflow-hidden flex items-center justify-center shrink-0 select-none`}
        title={hasError ? `${title} (Foto Tidak Ditemukan di Storage)` : `${title} (Belum Absen)`}
      >
        <Camera className="w-4 h-4 text-slate-700" />
      </div>
    );
  }

  let srcUrl = '';
  if (filename.startsWith('http://') || filename.startsWith('https://')) {
    srcUrl = filename;
  } else {
    const baseUrl = (process.env.NEXT_PUBLIC_SUPABASE_URL || '').replace(/\/+$/, '');
    const cleanFilename = filename.replace(/^\/+/, '');
    srcUrl = `${baseUrl}/storage/v1/object/public/${bucket}/${cleanFilename}`;
  }

  return (
    <>
      <div
        className={`${className} bg-slate-950 border border-slate-800 rounded-lg overflow-hidden flex items-center justify-center shrink-0 relative group ${
          allowZoom ? 'cursor-pointer hover:border-blue-500/50 transition-colors' : ''
        }`}
        title={`${title} - Klik untuk memperbesar`}
        onClick={() => allowZoom && setIsOpen(true)}
      >
        <img
          src={srcUrl}
          alt={alt}
          className="w-full h-full object-cover"
          onError={(e) => {
            e.currentTarget.style.display = 'none';
            setHasError(true);
          }}
        />
        {allowZoom && (
          <div className="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
            <Maximize2 className="w-3.5 h-3.5 text-white" />
          </div>
        )}
      </div>

      {/* Image Preview Modal */}
      {isOpen && (
        <div 
          className="fixed inset-0 bg-black/80 backdrop-blur-sm z-[999] flex items-center justify-center p-4 animate-fade-in"
          onClick={() => setIsOpen(false)}
        >
          <div 
            className="relative bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden max-w-lg w-full shadow-2xl p-4 space-y-3"
            onClick={(e) => e.stopPropagation()}
          >
            <div className="flex items-center justify-between border-b border-slate-800 pb-3">
              <span className="text-sm font-bold text-white flex items-center gap-2">
                <Camera className="w-4 h-4 text-blue-400" />
                {title}
              </span>
              <button
                onClick={() => setIsOpen(false)}
                className="text-slate-400 hover:text-white p-1 bg-slate-800 rounded-lg transition-colors"
              >
                <X className="w-5 h-5" />
              </button>
            </div>
            <div className="relative aspect-square w-full rounded-2xl overflow-hidden bg-black flex items-center justify-center border border-slate-800">
              <img
                src={srcUrl}
                alt={alt}
                className="max-w-full max-h-full object-contain"
                onError={(e) => {
                  e.currentTarget.style.display = 'none';
                }}
              />
            </div>
          </div>
        </div>
      )}
    </>
  );
}
