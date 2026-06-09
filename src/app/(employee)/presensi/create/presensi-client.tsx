'use client';

import React, { useEffect, useRef, useState, useTransition } from 'react';
import { useRouter } from 'next/navigation';
import Webcam from 'react-webcam';
import { storePresenceAction } from '@/lib/actions/presence';
import { Camera, MapPin, Loader2, AlertTriangle, ArrowLeft, Clock, CheckCircle } from 'lucide-react';
import Link from 'next/link';

// Leaflet styles and dynamic imports
import 'leaflet/dist/leaflet.css';

interface PresensiClientProps {
  karyawan: any;
  branch: any;
  presence: any;
  activeJamKerja: any;
  allSchedules: any[];
}

export default function PresensiClient({
  karyawan,
  branch,
  presence,
  activeJamKerja,
  allSchedules,
}: PresensiClientProps) {
  const router = useRouter();
  const webcamRef = useRef<Webcam>(null);
  
  // Geolocation and Geofencing States
  const [userCoords, setUserCoords] = useState<{ lat: number; lng: number } | null>(null);
  const [distance, setDistance] = useState<number | null>(null);
  const [gpsError, setGpsError] = useState<string | null>(null);
  const [loadingGps, setLoadingGps] = useState(true);
  
  // Selected Schedule State
  const [selectedScheduleId, setSelectedScheduleId] = useState<string>(
    activeJamKerja?.kode_jam_kerja || ''
  );

  // Status and Submission States
  const [submissionError, setSubmissionError] = useState<string | null>(null);
  const [submissionSuccess, setSubmissionSuccess] = useState<string | null>(null);
  const [isPending, startTransition] = useTransition();

  const isCheckOut = !!presence;
  const activeSchedule = activeJamKerja || allSchedules.find(s => s.kode_jam_kerja === selectedScheduleId);

  // 1. Get branch coordinates
  const branchCoords = branch.lokasi_cabang.split(',');
  const latBranch = parseFloat(branchCoords[0]);
  const lngBranch = parseFloat(branchCoords[1]);

  // 2. Calculate distance using Haversine formula on client
  const calculateDistance = (lat1: number, lon1: number, lat2: number, lon2: number) => {
    const R = 6371e3; // Earth radius in meters
    const phi1 = (lat1 * Math.PI) / 180;
    const phi2 = (lat2 * Math.PI) / 180;
    const deltaPhi = ((lat2 - lat1) * Math.PI) / 180;
    const deltaLambda = ((lon2 - lon1) * Math.PI) / 180;

    const a =
      Math.sin(deltaPhi / 2) * Math.sin(deltaPhi / 2) +
      Math.cos(phi1) * Math.cos(phi2) *
      Math.sin(deltaLambda / 2) * Math.sin(deltaLambda / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

    return R * c; // in meters
  };

  // 3. Setup HTML5 Geolocation Watcher
  useEffect(() => {
    if (typeof window === 'undefined') return;

    if (!navigator.geolocation) {
      setGpsError('Browser Anda tidak mendukung deteksi lokasi (GPS).');
      setLoadingGps(false);
      return;
    }

    const watchId = navigator.geolocation.watchPosition(
      (position) => {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        setUserCoords({ lat, lng });
        
        // Calculate distance to branch
        const dist = calculateDistance(latBranch, lngBranch, lat, lng);
        setDistance(Math.round(dist));
        setGpsError(null);
        setLoadingGps(false);
      },
      (error) => {
        console.error('GPS Error:', error);
        let errorMsg = 'Gagal mengakses GPS. Pastikan izin lokasi diaktifkan.';
        if (error.code === error.PERMISSION_DENIED) {
          errorMsg = 'Akses lokasi ditolak. Aktifkan izin GPS pada pengaturan browser handphone Anda.';
        }
        setGpsError(errorMsg);
        setLoadingGps(false);
      },
      { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );

    return () => {
      navigator.geolocation.clearWatch(watchId);
    };
  }, [latBranch, lngBranch]);

  // 4. Render Leaflet Map dynamically
  useEffect(() => {
    if (typeof window === 'undefined' || !userCoords || loadingGps) return;
    
    // We import leaflet dynamically to avoid Next.js SSR build errors
    const L = require('leaflet');

    // Setup map
    const map = L.map('map-container').setView([userCoords.lat, userCoords.lng], 16);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    // Custom CSS Circular Icons
    const branchIcon = L.divIcon({
      className: 'custom-leaf-icon',
      html: `<div style="background-color: #3b82f6; width: 14px; height: 14px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 8px rgba(0,0,0,0.5);"></div>`,
      iconSize: [14, 14],
      iconAnchor: [7, 7]
    });

    const userIcon = L.divIcon({
      className: 'custom-leaf-icon',
      html: `<div style="background-color: #10b981; width: 14px; height: 14px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 8px rgba(0,0,0,0.5);"></div>`,
      iconSize: [14, 14],
      iconAnchor: [7, 7]
    });

    // Add geofence radius circle
    L.circle([latBranch, lngBranch], {
      color: '#3b82f6',
      fillColor: '#3b82f6',
      fillOpacity: 0.15,
      radius: branch.radius_cabang
    }).addTo(map);

    // Add branch marker
    L.marker([latBranch, lngBranch], { icon: branchIcon }).addTo(map)
      .bindPopup(`<b>${branch.nama_cabang}</b>`).openPopup();

    // Add user marker
    L.marker([userCoords.lat, userCoords.lng], { icon: userIcon }).addTo(map)
      .bindPopup('<b>Posisi Anda</b>');

    // Pan map to fit both markers
    const group = new L.featureGroup([
      L.marker([latBranch, lngBranch]),
      L.marker([userCoords.lat, userCoords.lng])
    ]);
    map.fitBounds(group.getBounds().pad(0.15));

    return () => {
      map.remove();
    };
  }, [userCoords, loadingGps]);

  // 5. Submit presence action
  const handlePresenceSubmit = () => {
    if (!userCoords) {
      setSubmissionError('Presensi Gagal karena GPS belum mendapatkan koordinat. Tunggu sebentar.');
      return;
    }
    if (!activeSchedule) {
      setSubmissionError('Presensi Gagal karena Jadwal jam kerja belum dipilih.');
      return;
    }

    const imageSrc = webcamRef.current?.getScreenshot();
    if (!imageSrc) {
      setSubmissionError('Presensi Gagal karena Gagal menangkap foto wajah dari kamera.');
      return;
    }

    setSubmissionError(null);
    setSubmissionSuccess(null);

    startTransition(async () => {
      const formData = new FormData();
      formData.append('lokasi', `${userCoords.lat},${userCoords.lng}`);
      formData.append('image', imageSrc);
      formData.append('kode_jam_kerja', activeSchedule.kode_jam_kerja);

      const result = await storePresenceAction(formData);

      if (result.error) {
        setSubmissionError(`Presensi Gagal karena ${result.error}`);
      } else if (result.success) {
        setSubmissionSuccess(`Presensi Sukses: ${result.message || 'Absensi sukses dicatat.'}`);
        // Wait 2.5 seconds and redirect
        setTimeout(() => {
          router.push('/dashboard');
          router.refresh();
        }, 2500);
      }
    });
  };

  // Check if button should be disabled due to radius lock
  const isOutOfRadius = distance !== null && distance > branch.radius_cabang;
  const isLockedGPS = karyawan.status_location === 1;
  const isDisabledSubmit = isPending || loadingGps || !!gpsError || (isOutOfRadius && isLockedGPS) || !activeSchedule;

  return (
    <div className="space-y-5">
      {/* Top Header */}
      <div className="flex items-center space-x-3 pb-3 border-b border-white/[0.06]">
        <Link href="/dashboard" className="text-slate-400 hover:text-white transition-colors">
          <ArrowLeft className="w-6 h-6" />
        </Link>
        <h2 className="text-lg font-bold text-white">
          {isCheckOut ? 'Absen Pulang' : 'Absen Masuk'}
        </h2>
      </div>

      {/* Schedule Selection Dropdown (Only if not clocked in yet and no specific auto schedule) */}
      {!isCheckOut && !activeJamKerja ? (
        <div className="bg-[#131936] border border-white/[0.05] rounded-2xl p-4 space-y-2">
          <label className="text-xs font-semibold text-slate-300 block">
            Pilih Jadwal Jam Kerja Hari Ini:
          </label>
          <select
            value={selectedScheduleId}
            onChange={(e) => setSelectedScheduleId(e.target.value)}
            disabled={isPending}
            className="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50"
          >
            <option value="">-- Pilih Jam Kerja --</option>
            {allSchedules.map((s) => (
              <option key={s.kode_jam_kerja} value={s.kode_jam_kerja}>
                {s.nama_jam_kerja} ({s.jam_masuk.substring(0,5)} - {s.jam_pulang.substring(0,5)})
              </option>
            ))}
          </select>
        </div>
      ) : (
        /* Schedule Info Banner if locked */
        activeSchedule && (
          <div className="bg-[#131936] border border-white/[0.05] rounded-2xl p-4 flex items-center justify-between">
            <div className="flex items-center space-x-3">
              <div className="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-400">
                <Clock className="w-5 h-5" />
              </div>
              <div>
                <h4 className="text-xs text-slate-400 font-medium">Jadwal Aktif</h4>
                <h3 className="text-sm font-bold text-white mt-0.5">{activeSchedule.nama_jam_kerja}</h3>
              </div>
            </div>
            <div className="text-right">
              <span className="text-xs font-bold text-blue-300 bg-blue-500/10 px-2.5 py-1 rounded-lg">
                {activeSchedule.jam_masuk.substring(0,5)} - {activeSchedule.jam_pulang.substring(0,5)}
              </span>
            </div>
          </div>
        )
      )}



      {/* Geofence Status Information */}
      {!loadingGps && !gpsError && distance !== null && (
        <div className={`p-4 rounded-2xl flex items-start space-x-3 text-xs border ${
          isOutOfRadius 
            ? 'bg-rose-500/10 border-rose-500/20 text-rose-300' 
            : 'bg-emerald-500/10 border-emerald-500/20 text-emerald-300'
        }`}>
          <MapPin className="w-5 h-5 shrink-0 mt-0.5" />
          <div className="space-y-1">
            <p className="font-bold">
              {isOutOfRadius 
                ? `Di Luar Radius Kantor (${distance} meter)` 
                : `Di Dalam Radius Kantor (${distance} meter)`
              }
            </p>
            <p className="text-[10px] text-slate-400">
              {isLockedGPS 
                ? 'Fitur Lock GPS Aktif. Anda wajib berada di dalam radius kantor untuk dapat melakukan absensi.' 
                : 'Fitur Lock GPS Tidak Aktif. Anda berada di luar radius tetapi tetap dapat melakukan absensi.'
              }
            </p>
          </div>
        </div>
      )}

      {/* Camera Capture Panel */}
      <div className="bg-black border border-white/[0.05] rounded-3xl overflow-hidden aspect-[3/4] relative shadow-lg">
        {/* Loading overlay for webcam */}
        <div className="absolute inset-0 bg-slate-950 flex flex-col items-center justify-center space-y-2 text-slate-400 select-none z-0">
          <Loader2 className="w-8 h-8 animate-spin text-blue-500" />
          <span className="text-xs">Mengaktifkan Kamera Wajah...</span>
        </div>
        
        <Webcam
          audio={false}
          ref={webcamRef}
          screenshotFormat="image/png"
          videoConstraints={{
            width: 480,
            height: 640,
            facingMode: 'user' // Front camera
          }}
          className="w-full h-full object-cover relative z-10"
        />

        {/* Camera visual frame overlay */}
        <div className="absolute inset-0 border-[20px] border-black/40 pointer-events-none z-20" />
        <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-48 h-64 border border-dashed border-white/40 rounded-full pointer-events-none z-20" />
      </div>

      {/* Map Geofence Visualization */}
      <div className="bg-[#131936] border border-white/[0.05] rounded-3xl overflow-hidden p-3 shadow-lg">
        <h4 className="text-xs font-semibold text-slate-300 mb-2 pl-1 flex items-center space-x-1.5">
          <MapPin className="w-4 h-4 text-blue-400" />
          <span>Peta Lokasi Geofence</span>
        </h4>
        <div className="relative aspect-video rounded-2xl overflow-hidden bg-slate-950">
          {loadingGps ? (
            <div className="absolute inset-0 flex flex-col items-center justify-center space-y-2 text-slate-400 text-xs">
              <Loader2 className="w-6 h-6 animate-spin text-blue-500" />
              <span>Memperoleh GPS & Peta...</span>
            </div>
          ) : (
            <div id="map-container" className="w-full h-full z-10" />
          )}
        </div>
      </div>

      {/* Success Banner */}
      {submissionSuccess && (
        <div className="bg-emerald-500/15 border border-emerald-500/30 rounded-2xl p-4 flex items-start space-x-3 text-emerald-300 text-sm animate-fade-in">
          <CheckCircle className="w-5 h-5 shrink-0 mt-0.5" />
          <span>{submissionSuccess}</span>
        </div>
      )}

      {/* Error Banners */}
      {submissionError && (
        <div className="bg-red-500/15 border border-red-500/30 rounded-2xl p-4 flex items-start space-x-3 text-red-300 text-sm animate-fade-in">
          <AlertTriangle className="w-5 h-5 shrink-0 mt-0.5" />
          <span>{submissionError}</span>
        </div>
      )}

      {gpsError && (
        <div className="bg-red-500/15 border border-red-500/30 rounded-2xl p-4 flex items-start space-x-3 text-red-300 text-sm animate-fade-in">
          <AlertTriangle className="w-5 h-5 shrink-0 mt-0.5" />
          <span>{gpsError}</span>
        </div>
      )}

      {/* Actions Submission button */}
      <button
        type="button"
        disabled={isDisabledSubmit}
        onClick={handlePresenceSubmit}
        className="w-full py-4 px-6 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white font-bold rounded-2xl shadow-lg shadow-blue-500/20 active:scale-[0.98] transition-all duration-200 flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100"
      >
        {isPending ? (
          <>
            <Loader2 className="w-5 h-5 animate-spin" />
            <span>Menyimpan Absensi...</span>
          </>
        ) : (
          <>
            <Camera className="w-5 h-5" />
            <span>{isCheckOut ? 'Absen Pulang Sekarang' : 'Absen Masuk Sekarang'}</span>
          </>
        )}
      </button>
    </div>
  );
}
