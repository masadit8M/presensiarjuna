'use server';

import { supabase, supabaseAdmin } from '@/lib/supabase';
import { getSession } from '@/lib/session';

// Helper to calculate distance in meters using Haversine formula
function calculateDistance(lat1: number, lon1: number, lat2: number, lon2: number) {
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
}

export async function storePresenceAction(formData: FormData) {
  const session = await getSession();
  if (!session || session.role !== 'employee') {
    return { error: 'Akses ditolak. Silakan login kembali.' };
  }

  const nik = session.nik!;
  
  // Format current date and time strictly in Asia/Jakarta (WIB) timezone
  const optionsDate = { timeZone: 'Asia/Jakarta', year: 'numeric', month: '2-digit', day: '2-digit' } as const;
  const optionsTime = { timeZone: 'Asia/Jakarta', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false } as const;
  
  const formatterDate = new Intl.DateTimeFormat('en-CA', optionsDate);
  const formatterTime = new Intl.DateTimeFormat('en-GB', optionsTime); // en-GB format outputs HH:MM:SS
  
  const today = formatterDate.format(new Date()); // YYYY-MM-DD
  const currentTime = formatterTime.format(new Date()); // HH:MM:SS
  const currentDateTimeStr = `${today} ${currentTime}`;
  
  const lokasi = formData.get('lokasi') as string; // Format: "lat,lng"
  const imageBase64 = formData.get('image') as string; // Base64 string
  const kode_jam_kerja = formData.get('kode_jam_kerja') as string;

  if (!lokasi || !imageBase64 || !kode_jam_kerja) {
    return { error: 'Data absensi tidak lengkap (Lokasi/Foto/Jadwal kosong).' };
  }

  try {
    // 1. Fetch Employee details (to verify Lock GPS and get phone number)
    const { data: karyawan, error: empError } = await supabase
      .from('karyawan')
      .select('*, cabang(*)')
      .eq('nik', nik)
      .single();

    if (empError || !karyawan) {
      return { error: 'Data karyawan tidak ditemukan.' };
    }

    const branch = karyawan.cabang;
    if (!branch) {
      return { error: 'Kantor cabang karyawan tidak terkonfigurasi.' };
    }

    // Parse coordinates
    const branchLoc = branch.lokasi_cabang.split(',');
    const userLoc = lokasi.split(',');
    const latBranch = parseFloat(branchLoc[0]);
    const lngBranch = parseFloat(branchLoc[1]);
    const latUser = parseFloat(userLoc[0]);
    const lngUser = parseFloat(userLoc[1]);

    const distance = calculateDistance(latBranch, lngBranch, latUser, lngUser);
    const radiusMeters = Math.round(distance);

    // 2. Lock GPS check: If status_location = 1, they must be inside the radius
    if (karyawan.status_location === 1 && radiusMeters > branch.radius_cabang) {
      return { 
        error: `Maaf Anda Berada Diluar Radius Kantor. Jarak Anda ${radiusMeters} meter (Maksimal radius ${branch.radius_cabang} meter).` 
      };
    }

    // 3. Fetch Work Hours (Jam Kerja) Details
    const { data: jamkerja, error: jkError } = await supabase
      .from('jam_kerja')
      .select('*')
      .eq('kode_jam_kerja', kode_jam_kerja)
      .single();

    if (jkError || !jamkerja) {
      return { error: 'Jadwal jam kerja tidak ditemukan.' };
    }

    // 4. Fetch today's presence record
    const { data: presence } = await supabase
      .from('presensi')
      .select('*')
      .eq('nik', nik)
      .eq('tgl_presensi', today)
      .maybeSingle();

    const isCheckOut = !!presence;
    const mode = isCheckOut ? 'out' : 'in';

    // Decode and upload photo to Supabase Storage
    const imageBuffer = Buffer.from(imageBase64.replace(/^data:image\/\w+;base64,/, ''), 'base64');
    const fileName = `${nik}-${today}-${mode}.png`;
    const filePath = `${fileName}`;

    if (!supabaseAdmin) {
      return { error: 'Terjadi kesalahan sistem database admin.' };
    }

    // Upload image to Supabase Bucket 'absensi'
    const { error: uploadError } = await supabaseAdmin.storage
      .from('absensi')
      .upload(filePath, imageBuffer, {
        contentType: 'image/png',
        upsert: true,
      });

    if (uploadError) {
      console.error('Storage Upload Error:', uploadError);
      return { error: 'Gagal mengunggah foto absensi ke storage.' };
    }

    if (isCheckOut) {
      // ----- PROCESS CLOCK OUT -----
      if (presence.jam_out) {
        return { error: 'Anda sudah melakukan absen pulang sebelumnya!' };
      }

      // Check if it's already time to clock out
      // For cross-day shifts (lintashari = 1), clock out is usually the next day
      const tglPulangStr = jamkerja.lintashari === 1
        ? new Date(new Date(today).getTime() + 24 * 60 * 60 * 1000).toLocaleDateString('en-CA')
        : today;
      const jamkerjaPulangStr = `${tglPulangStr} ${jamkerja.jam_pulang}`;

      // Bypassed: Allowed from 00:00 WIB to 23:59 WIB
      /*
      if (currentDateTimeStr < jamkerjaPulangStr) {
        return { error: `Maaf, belum waktunya pulang. Jam pulang dijadwalkan pukul ${jamkerja.jam_pulang.substring(0, 5)}.` };
      }
      */

      // Calculate total work hours
      let totalJam = null;
      if (presence.jam_in) {
        const timeIn = new Date(`${today} ${presence.jam_in}`);
        const timeOut = new Date(currentDateTimeStr);
        const diffMs = timeOut.getTime() - timeIn.getTime();
        totalJam = parseFloat((diffMs / (1000 * 60 * 60)).toFixed(2));
      }

      // Update Database
      const { error: updateError } = await supabase
        .from('presensi')
        .update({
          jam_out: currentTime,
          foto_out: fileName,
          lokasi_out: lokasi,
          total_jam: totalJam,
        })
        .eq('id', presence.id);

      if (updateError) {
        return { error: 'Gagal mencatat absen pulang di database.' };
      }

      // Trigger WhatsApp API Webhook
      await triggerWAWebflow(karyawan.no_hp, `Terimakasih sudah melakukan Absen Pulang, Anda melakukan absen pada jam ${currentTime.substring(0, 5)}.`);

      return { success: true, mode: 'out', message: 'Absen pulang sukses! Hati-hati di jalan.' };

    } else {
      // ----- PROCESS CLOCK IN -----
      // Validate time window for clock in (Bypassed: Allowed from 00:00 WIB to 23:59 WIB)
      /*
      if (currentTime < jamkerja.awal_jam_masuk) {
        return { error: `Maaf, belum waktunya absen masuk. Absen dibuka mulai pukul ${jamkerja.awal_jam_masuk.substring(0, 5)}.` };
      }
      if (currentTime > jamkerja.akhir_jam_masuk) {
        return { error: `Maaf, waktu absen masuk sudah habis. Batas akhir adalah pukul ${jamkerja.akhir_jam_masuk.substring(0, 5)}.` };
      }
      */

      // Insert Database
      const { error: insertError } = await supabase
        .from('presensi')
        .insert({
          nik,
          tgl_presensi: today,
          jam_in: currentTime,
          foto_in: fileName,
          lokasi_in: lokasi,
          kode_jam_kerja,
          status: 'h', // 'h' (hadir)
        });

      if (insertError) {
        console.error(insertError);
        return { error: 'Gagal mencatat absen masuk di database.' };
      }

      // Trigger WhatsApp API Webhook
      await triggerWAWebflow(karyawan.no_hp, `Terimakasih sudah melakukan Absen Masuk, Anda melakukan absen pada jam ${currentTime.substring(0, 5)}. Selamat bekerja!`);

      return { success: true, mode: 'in', message: 'Absen masuk sukses! Selamat bekerja.' };
    }

  } catch (err) {
    console.error('Exception in presence action:', err);
    return { error: 'Terjadi kesalahan sistem, silakan hubungi tim IT.' };
  }
}

// Helper to trigger WhatsApp notification gateway
async function triggerWAWebflow(phone: string, text: string) {
  const gatewayUrl = process.env.NEXT_PUBLIC_WA_GATEWAY_URL || 'https://wagateway.pedasalami.com/send-message';
  
  try {
    const res = await fetch(gatewayUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        message: text,
        number: phone,
        file_dikirim: '',
      }),
    });
    return res.ok;
  } catch (error) {
    console.error('Failed to trigger WhatsApp notification:', error);
    return false;
  }
}
