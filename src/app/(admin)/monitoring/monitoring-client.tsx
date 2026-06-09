'use client';

import React, { useEffect, useState, useTransition } from 'react';
import { getMonitoringLogsAction } from '@/lib/actions/admin';
import { Calendar, MapPin, Building2, Search, Loader2, Clock, X, Check, XSquare, Camera, AlertTriangle } from 'lucide-react';
import 'leaflet/dist/leaflet.css';

interface MonitoringClientProps {
  departemenList: any[];
  cabangList: any[];
}

export default function MonitoringClient({
  departemenList,
  cabangList,
}: MonitoringClientProps) {
  const [isPending, startTransition] = useTransition();

  // Filter States
  const todayStr = new Date().toLocaleDateString('en-CA');
  const [date, setDate] = useState(todayStr);
  const [selectedDept, setSelectedDept] = useState('');
  const [selectedBranch, setSelectedBranch] = useState('');

  // Data States
  const [logs, setLogs] = useState<any[]>([]);
  const [error, setError] = useState<string | null>(null);

  // Map Modal State
  const [activeLog, setActiveLog] = useState<any | null>(null);

  const fetchLogs = () => {
    setError(null);
    startTransition(async () => {
      const result = await getMonitoringLogsAction(date, selectedDept, selectedBranch);
      if (result.error) {
        setError(result.error);
        setLogs([]);
      } else if (result.success && result.logs) {
        setLogs(result.logs);
      }
    });
  };

  // Re-fetch when filters change
  useEffect(() => {
    fetchLogs();
  }, [date, selectedDept, selectedBranch]);

  // Handle Leaflet Map initialization in Modal
  useEffect(() => {
    if (typeof window === 'undefined' || !activeLog) return;

    const L = require('leaflet');
    
    // Parse coordinates
    const userInLoc = activeLog.lokasi_in?.split(',');
    const userOutLoc = activeLog.lokasi_out?.split(',');
    const branchLoc = activeLog.karyawan?.cabang?.lokasi_cabang?.split(',');

    if (!userInLoc || !branchLoc) {
      alert('Koordinat lokasi tidak lengkap untuk log presensi ini.');
      setActiveLog(null);
      return;
    }

    const latIn = parseFloat(userInLoc[0]);
    const lngIn = parseFloat(userInLoc[1]);
    const latBranch = parseFloat(branchLoc[0]);
    const lngBranch = parseFloat(branchLoc[1]);

    const map = L.map('monitoring-map').setView([latIn, lngIn], 16);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    // Custom Icons
    const branchIcon = L.divIcon({
      className: 'custom-leaf-icon',
      html: `<div style="background-color: #3b82f6; width: 14px; height: 14px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 8px rgba(0,0,0,0.5);"></div>`,
      iconSize: [14, 14],
      iconAnchor: [7, 7]
    });

    const userInIcon = L.divIcon({
      className: 'custom-leaf-icon',
      html: `<div style="background-color: #10b981; width: 14px; height: 14px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 8px rgba(0,0,0,0.5);"></div>`,
      iconSize: [14, 14],
      iconAnchor: [7, 7]
    });

    const userOutIcon = L.divIcon({
      className: 'custom-leaf-icon',
      html: `<div style="background-color: #ef4444; width: 14px; height: 14px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 8px rgba(0,0,0,0.5);"></div>`,
      iconSize: [14, 14],
      iconAnchor: [7, 7]
    });

    // Draw branch geofence circle
    L.circle([latBranch, lngBranch], {
      color: '#3b82f6',
      fillColor: '#3b82f6',
      fillOpacity: 0.15,
      radius: activeLog.karyawan?.cabang?.radius_cabang || 100
    }).addTo(map);

    // Add Markers
    L.marker([latBranch, lngBranch], { icon: branchIcon }).addTo(map)
      .bindPopup(`<b>${activeLog.karyawan?.cabang?.nama_cabang || 'Kantor'}</b>`).openPopup();

    L.marker([latIn, lngIn], { icon: userInIcon }).addTo(map)
      .bindPopup(`<b>Posisi Masuk: ${activeLog.jam_in.substring(0, 5)}</b>`);

    const markers = [L.marker([latBranch, lngBranch]), L.marker([latIn, lngIn])];

    // Add Clock out marker if exists
    if (userOutLoc) {
      const latOut = parseFloat(userOutLoc[0]);
      const lngOut = parseFloat(userOutLoc[1]);
      L.marker([latOut, lngOut], { icon: userOutIcon }).addTo(map)
        .bindPopup(`<b>Posisi Pulang: ${activeLog.jam_out?.substring(0, 5)}</b>`);
      markers.push(L.marker([latOut, lngOut]));
    }

    // Fit bounds to show all markers
    const group = new L.featureGroup(markers);
    map.fitBounds(group.getBounds().pad(0.2));

    return () => {
      map.remove();
    };
  }, [activeLog]);

  const getFormatTime = (timeStr?: string) => {
    return timeStr ? timeStr.substring(0, 5) : '--:--';
  };

  const getStatusDisplay = (log: any) => {
    if (log.status === 'i') return <span className="px-2 py-0.5 bg-amber-500/10 border border-amber-500/20 text-amber-400 text-[10px] font-bold rounded-md uppercase">Izin</span>;
    if (log.status === 's') return <span className="px-2 py-0.5 bg-rose-500/10 border border-rose-500/20 text-rose-400 text-[10px] font-bold rounded-md uppercase">Sakit</span>;
    if (log.status === 'c') return <span className="px-2 py-0.5 bg-purple-500/10 border border-purple-500/20 text-purple-400 text-[10px] font-bold rounded-md uppercase">Cuti</span>;
    
    // check late
    if (log.jam_in && log.jam_kerja?.jam_masuk && log.jam_in > log.jam_kerja.jam_masuk) {
      return <span className="px-2 py-0.5 bg-red-500/10 border border-red-500/20 text-red-400 text-[10px] font-bold rounded-md uppercase">Terlambat</span>;
    }
    return <span className="px-2 py-0.5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[10px] font-bold rounded-md uppercase">Hadir</span>;
  };

  return (
    <div className="space-y-6">
      {/* Search and Filters panel */}
      <div className="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-lg grid grid-cols-1 sm:grid-cols-3 gap-4">
        {/* Date Selector */}
        <div className="space-y-1">
          <label className="text-[10px] uppercase font-bold text-slate-400 pl-0.5 flex items-center">
            <Calendar className="w-3.5 h-3.5 mr-1 text-blue-500" />
            <span>Pilih Tanggal</span>
          </label>
          <input
            type="date"
            value={date}
            onChange={(e) => setDate(e.target.value)}
            disabled={isPending}
            className="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none"
          />
        </div>

        {/* Dept Selector */}
        <div className="space-y-1">
          <label className="text-[10px] uppercase font-bold text-slate-400 pl-0.5 flex items-center">
            <Building2 className="w-3.5 h-3.5 mr-1 text-blue-500" />
            <span>Departemen</span>
          </label>
          <select
            value={selectedDept}
            onChange={(e) => setSelectedDept(e.target.value)}
            disabled={isPending}
            className="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none"
          >
            <option value="">-- Semua Departemen --</option>
            {departemenList.map((d) => (
              <option key={d.kode_dept} value={d.kode_dept}>
                {d.nama_dept}
              </option>
            ))}
          </select>
        </div>

        {/* Branch Selector */}
        <div className="space-y-1">
          <label className="text-[10px] uppercase font-bold text-slate-400 pl-0.5 flex items-center">
            <MapPin className="w-3.5 h-3.5 mr-1 text-blue-500" />
            <span>Kantor Cabang</span>
          </label>
          <select
            value={selectedBranch}
            onChange={(e) => setSelectedBranch(e.target.value)}
            disabled={isPending}
            className="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none"
          >
            <option value="">-- Semua Cabang --</option>
            {cabangList.map((c) => (
              <option key={c.kode_cabang} value={c.kode_cabang}>
                {c.nama_cabang}
              </option>
            ))}
          </select>
        </div>
      </div>

      {/* Loading & Error Indicators */}
      {isPending && (
        <div className="flex items-center justify-center py-6 text-slate-400 space-x-2">
          <Loader2 className="w-5 h-5 animate-spin text-blue-500" />
          <span className="text-xs">Memuat data logs...</span>
        </div>
      )}

      {error && (
        <div className="bg-red-500/10 border border-red-500/20 rounded-2xl p-4 flex items-start space-x-3 text-red-300 text-sm">
          <AlertTriangle className="w-5 h-5 mt-0.5 shrink-0" />
          <span>{error}</span>
        </div>
      )}

      {/* Table grid */}
      {!isPending && (
        <div className="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
          <div className="overflow-x-auto">
            <table className="w-full text-left border-collapse">
              <thead>
                <tr className="bg-slate-900/50 border-b border-slate-800 text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                  <th className="px-6 py-4">Foto Wajah</th>
                  <th className="px-6 py-4">Nama / NIK</th>
                  <th className="px-6 py-4">Departemen</th>
                  <th className="px-6 py-4">Shift</th>
                  <th className="px-6 py-4 text-center">Masuk</th>
                  <th className="px-6 py-4 text-center">Pulang</th>
                  <th className="px-6 py-4 text-center">Status</th>
                  <th className="px-6 py-4 text-center">Aksi Peta</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-800 text-sm text-slate-300">
                {logs.length > 0 ? (
                  logs.map((log) => (
                    <tr key={log.id} className="hover:bg-slate-800/30 transition-colors">
                      {/* Face Capture image column */}
                      <td className="px-6 py-4">
                        <div className="flex items-center space-x-2">
                          <div className="w-9 h-12 bg-slate-950 border border-slate-800 rounded-lg overflow-hidden flex items-center justify-center shrink-0" title="Foto Masuk">
                            {log.foto_in ? (
                              <img 
                                src={`${process.env.NEXT_PUBLIC_SUPABASE_URL}/storage/v1/object/public/absensi/${log.foto_in}`} 
                                alt="In" 
                                className="w-full h-full object-cover"
                              />
                            ) : (
                              <Camera className="w-4 h-4 text-slate-700" />
                            )}
                          </div>
                          <div className="w-9 h-12 bg-slate-950 border border-slate-800 rounded-lg overflow-hidden flex items-center justify-center shrink-0" title="Foto Pulang">
                            {log.foto_out ? (
                              <img 
                                src={`${process.env.NEXT_PUBLIC_SUPABASE_URL}/storage/v1/object/public/absensi/${log.foto_out}`} 
                                alt="Out" 
                                className="w-full h-full object-cover"
                              />
                            ) : (
                              <Camera className="w-4 h-4 text-slate-700" />
                            )}
                          </div>
                        </div>
                      </td>

                      <td className="px-6 py-4">
                        <div className="font-bold text-white">{log.karyawan?.nama_lengkap}</div>
                        <div className="text-[10px] text-slate-500 mt-0.5">NIK: {log.nik}</div>
                      </td>
                      <td className="px-6 py-4">{log.karyawan?.departemen?.nama_dept || '-'}</td>
                      <td className="px-6 py-4 text-xs font-semibold">{log.jam_kerja?.nama_jam_kerja || 'Reguler'}</td>
                      <td className="px-6 py-4 text-center font-mono text-xs">{getFormatTime(log.jam_in)}</td>
                      <td className="px-6 py-4 text-center font-mono text-xs">{getFormatTime(log.jam_out)}</td>
                      <td className="px-6 py-4 text-center">{getStatusDisplay(log)}</td>
                      
                      {/* Map Pop trigger button */}
                      <td className="px-6 py-4 text-center">
                        {log.status === 'h' && log.lokasi_in ? (
                          <button
                            onClick={() => setActiveLog(log)}
                            className="bg-blue-600/10 border border-blue-500/25 hover:bg-blue-600 text-blue-400 hover:text-white rounded-lg px-2.5 py-1.5 text-xs font-semibold flex items-center justify-center mx-auto space-x-1 transition-all duration-200"
                          >
                            <MapPin className="w-3.5 h-3.5" />
                            <span>Tampilkan Peta</span>
                          </button>
                        ) : (
                          <span className="text-slate-600">-</span>
                        )}
                      </td>
                    </tr>
                  ))
                ) : (
                  <tr>
                    <td colSpan={8} className="px-6 py-12 text-center text-slate-500 text-xs">
                      Tidak ada log presensi untuk tanggal dan filter yang dipilih.
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* Map Overlay Modal */}
      {activeLog && (
        <div className="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-2xl overflow-hidden shadow-2xl animate-fade-in">
            {/* Header */}
            <div className="px-6 py-5 border-b border-slate-800 flex items-center justify-between bg-slate-900/50">
              <div>
                <h3 className="font-bold text-white text-md">
                  Lokasi Absensi: {activeLog.karyawan?.nama_lengkap}
                </h3>
                <p className="text-[10px] text-slate-400 mt-0.5">
                  NIK: {activeLog.nik} • Shift: {activeLog.jam_kerja?.nama_jam_kerja || 'Reguler'}
                </p>
              </div>
              <button 
                onClick={() => setActiveLog(null)} 
                className="text-slate-400 hover:text-white p-1.5 bg-slate-800 hover:bg-slate-750 rounded-lg transition-colors"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            {/* Map Body */}
            <div className="p-4 space-y-4">
              <div className="relative aspect-video w-full rounded-2xl overflow-hidden bg-slate-950 border border-slate-800">
                <div id="monitoring-map" className="w-full h-full z-10" />
              </div>
              
              {/* Detailed coordinate logs info */}
              <div className="grid grid-cols-2 gap-4 text-xs">
                <div className="bg-slate-950 p-3.5 rounded-xl border border-slate-800 space-y-1.5">
                  <div className="flex items-center space-x-1.5 font-semibold text-emerald-400">
                    <div className="w-2.5 h-2.5 rounded-full bg-emerald-500" />
                    <span>Titik Absen Masuk</span>
                  </div>
                  <p className="text-slate-300 font-mono text-[10px]">Posisi: {activeLog.lokasi_in || '-'}</p>
                  <p className="text-slate-400">Jam: {getFormatTime(activeLog.jam_in)}</p>
                </div>
                <div className="bg-slate-950 p-3.5 rounded-xl border border-slate-800 space-y-1.5">
                  <div className="flex items-center space-x-1.5 font-semibold text-red-400">
                    <div className="w-2.5 h-2.5 rounded-full bg-red-500" />
                    <span>Titik Absen Pulang</span>
                  </div>
                  <p className="text-slate-300 font-mono text-[10px]">Posisi: {activeLog.lokasi_out || '-'}</p>
                  <p className="text-slate-400">Jam: {getFormatTime(activeLog.jam_out)}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
