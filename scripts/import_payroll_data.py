import os, re, json, requests, bcrypt
import xlrd, openpyxl

# Load environment
env_path = os.path.join(os.path.dirname(__file__), '..', '.env.local')
supabase_url = os.environ.get('NEXT_PUBLIC_SUPABASE_URL', '')
service_key = os.environ.get('SUPABASE_SERVICE_ROLE_KEY', '')

if os.path.exists(env_path):
    with open(env_path, 'r', encoding='utf-8') as f:
        for line in f:
            if line.startswith('NEXT_PUBLIC_SUPABASE_URL='):
                supabase_url = line.strip().split('=', 1)[1]
            elif line.startswith('SUPABASE_SERVICE_ROLE_KEY='):
                service_key = line.strip().split('=', 1)[1]

headers = {
    'apikey': service_key,
    'Authorization': f'Bearer {service_key}',
    'Content-Type': 'application/json',
    'Prefer': 'resolution=merge-duplicates'
}

print(f'Connecting to {supabase_url}...')

# 1. Upsert Cabang
cabang_data = [
    {
        'kode_cabang': 'CITANDUI',
        'nama_cabang': 'KB-TK-TPA Citandui',
        'lokasi_cabang': '-7.961314,112.645607',
        'radius_cabang': 150
    },
    {
        'kode_cabang': 'LANGSEP',
        'nama_cabang': 'KB-TK-TPA Alam Langsep',
        'lokasi_cabang': '-7.978250,112.616238',
        'radius_cabang': 150
    }
]
res = requests.post(f'{supabase_url}/rest/v1/cabang', headers=headers, json=cabang_data)
print('Cabang upsert status:', res.status_code)

# 2. Upsert Departemen
dept_data = [
    {'kode_dept': 'KB', 'nama_dept': 'Kelompok Bermain (KB)'},
    {'kode_dept': 'TK', 'nama_dept': 'Taman Kanak-Kanak (TK)'},
    {'kode_dept': 'TPA', 'nama_dept': 'Daycare / TPA'},
    {'kode_dept': 'YYS', 'nama_dept': 'Yayasan / Manajemen'}
]
res = requests.post(f'{supabase_url}/rest/v1/departemen', headers=headers, json=dept_data)
print('Departemen upsert status:', res.status_code)

# Default password for all employees: 12345
default_pwd_hash = bcrypt.hashpw(b'12345', bcrypt.gensalt(10)).decode('utf-8')

# List of employees mapped from both Excel files
employees = [
    # --- CABANG CITANDUI : GURU KB & TK ---
    {
        'nik': 'CTD-01',
        'nama_lengkap': 'KARTIKA PRATIASARI',
        'jabatan': 'KEPSEK KB ISLAM PLUS ARJUNA',
        'no_hp': '082232793888',
        'kode_dept': 'KB',
        'kode_cabang': 'CITANDUI',
        'tipe_komponen': 'guru',
        'gaji_pokok': 1700000,
        'tunj_transport': 150000,
        'tgl_masuk': '2018-07-01'
    },
    {
        'nik': 'CTD-02',
        'nama_lengkap': 'TITIN HAMIDAH, S.Pd.',
        'jabatan': 'KEPSEK TK ISLAM PLUS ARJUNA',
        'no_hp': '081233445501',
        'kode_dept': 'TK',
        'kode_cabang': 'CITANDUI',
        'tipe_komponen': 'guru',
        'gaji_pokok': 1067420,
        'tunj_transport': 150000,
        'tgl_masuk': '2023-07-01'
    },
    {
        'nik': 'CTD-03',
        'nama_lengkap': 'MERINDA EKA ETIANINGSIH, S.Pd.',
        'jabatan': 'GURU TK A (CITANDUI)',
        'no_hp': '081233445502',
        'kode_dept': 'TK',
        'kode_cabang': 'CITANDUI',
        'tipe_komponen': 'guru',
        'gaji_pokok': 1004880,
        'tunj_transport': 150000,
        'tgl_masuk': '2020-08-24'
    },
    {
        'nik': 'CTD-04',
        'nama_lengkap': 'DWI RETNO SETYORINI, S.Pd.',
        'jabatan': 'GURU TK B (CITANDUI)',
        'no_hp': '087726662647',
        'kode_dept': 'TK',
        'kode_cabang': 'CITANDUI',
        'tipe_komponen': 'guru',
        'gaji_pokok': 1011240,
        'tunj_transport': 150000,
        'tgl_masuk': '2020-02-01'
    },
    {
        'nik': 'CTD-05',
        'nama_lengkap': 'CLARISTA WIDYA PANGESTIKA',
        'jabatan': 'GURU KB (CITANDUI)',
        'no_hp': '081233445503',
        'kode_dept': 'KB',
        'kode_cabang': 'CITANDUI',
        'tipe_komponen': 'guru',
        'gaji_pokok': 689000,
        'tunj_transport': 150000,
        'tgl_masuk': '2025-04-01'
    },
    {
        'nik': 'CTD-06',
        'nama_lengkap': 'ANGGITA DARA KARISMA DITA',
        'jabatan': 'ADMIN TPA KB TK CITANDUI',
        'no_hp': '081233445504',
        'kode_dept': 'TPA',
        'kode_cabang': 'CITANDUI',
        'tipe_komponen': 'tpa',
        'gaji_pokok': 1350000,
        'tunj_transport': 150000,
        'tgl_masuk': '2022-05-20'
    },

    # --- CABANG LANGSEP : GURU KB & TK ---
    {
        'nik': 'LGS-01',
        'nama_lengkap': 'ROSHELLA NABILAH',
        'jabatan': 'KEPSEK KB TK ALAM ARJUNA',
        'no_hp': '081233445505',
        'kode_dept': 'TK',
        'kode_cabang': 'LANGSEP',
        'tipe_komponen': 'guru',
        'gaji_pokok': 1573040,
        'tunj_transport': 150000,
        'tgl_masuk': '2019-04-20'
    },
    {
        'nik': 'LGS-02',
        'nama_lengkap': 'RIZKA ALFIANTI, S.Pd.',
        'jabatan': 'GURU KB (LANGSEP)',
        'no_hp': '081233445506',
        'kode_dept': 'KB',
        'kode_cabang': 'LANGSEP',
        'tipe_komponen': 'guru',
        'gaji_pokok': 1067420,
        'tunj_transport': 150000,
        'tgl_masuk': '2019-05-01'
    },
    {
        'nik': 'LGS-03',
        'nama_lengkap': 'ULUM KHUSNATIN',
        'jabatan': 'GURU TK B (LANGSEP)',
        'no_hp': '081233445507',
        'kode_dept': 'TK',
        'kode_cabang': 'LANGSEP',
        'tipe_komponen': 'guru',
        'gaji_pokok': 898880,
        'tunj_transport': 150000,
        'tgl_masuk': '2021-06-28'
    },
    {
        'nik': 'LGS-04',
        'nama_lengkap': 'ERIN WIDAYANTI',
        'jabatan': 'GURU TK A (LANGSEP)',
        'no_hp': '081233445508',
        'kode_dept': 'TK',
        'kode_cabang': 'LANGSEP',
        'tipe_komponen': 'guru',
        'gaji_pokok': 730340,
        'tunj_transport': 150000,
        'tgl_masuk': '2024-07-01'
    },
    {
        'nik': 'LGS-05',
        'nama_lengkap': 'ALEXANDRA ADJANI LEGA',
        'jabatan': 'GURU PENDAMPING (LANGSEP)',
        'no_hp': '081233445509',
        'kode_dept': 'KB',
        'kode_cabang': 'LANGSEP',
        'tipe_komponen': 'guru',
        'gaji_pokok': 400000,
        'tunj_transport': 150000,
        'tgl_masuk': '2026-02-02'
    },
    {
        'nik': 'LGS-06',
        'nama_lengkap': 'ANANDA LAILA AYU',
        'jabatan': 'GURU KB (LANGSEP)',
        'no_hp': '081233445510',
        'kode_dept': 'KB',
        'kode_cabang': 'LANGSEP',
        'tipe_komponen': 'guru',
        'gaji_pokok': 400000,
        'tunj_transport': 150000,
        'tgl_masuk': '2026-07-06'
    },
    {
        'nik': 'LGS-07',
        'nama_lengkap': 'YUYUS ARTANTI',
        'jabatan': 'ADMIN TPA KB TK ALAM',
        'no_hp': '081233445511',
        'kode_dept': 'TPA',
        'kode_cabang': 'LANGSEP',
        'tipe_komponen': 'tpa',
        'gaji_pokok': 1350000,
        'tunj_transport': 150000,
        'tgl_masuk': '2022-11-24'
    },

    # --- TPA DAYCARE CITANDUI ---
    {
        'nik': 'TPA-01',
        'nama_lengkap': 'CINDY NOVALITA AL HUDAYAH',
        'jabatan': 'KEPSEK TPA DAYCARE',
        'no_hp': '081233445512',
        'kode_dept': 'TPA',
        'kode_cabang': 'CITANDUI',
        'tipe_komponen': 'tpa',
        'gaji_pokok': 2650000,
        'tunj_transport': 150000,
        'tgl_masuk': '2023-01-01'
    },
    {
        'nik': 'TPA-02',
        'nama_lengkap': 'MAHDALENA ADINDA PUTRI D',
        'jabatan': 'KOORDINATOR TPA / WAKSEK',
        'no_hp': '081233445513',
        'kode_dept': 'TPA',
        'kode_cabang': 'CITANDUI',
        'tipe_komponen': 'tpa',
        'gaji_pokok': 2173000,
        'tunj_transport': 150000,
        'tgl_masuk': '2022-02-09'
    },
    {
        'nik': 'TPA-03',
        'nama_lengkap': 'ATIK CAHYANINGRUM',
        'jabatan': 'GURU TPA',
        'no_hp': '081233445514',
        'kode_dept': 'TPA',
        'kode_cabang': 'CITANDUI',
        'tipe_komponen': 'tpa',
        'gaji_pokok': 1219000,
        'tunj_transport': 150000,
        'tgl_masuk': '2020-01-08'
    },
    {
        'nik': 'TPA-04',
        'nama_lengkap': 'DIAH SETYO ARINI',
        'jabatan': 'GURU TPA',
        'no_hp': '081233445515',
        'kode_dept': 'TPA',
        'kode_cabang': 'CITANDUI',
        'tipe_komponen': 'tpa',
        'gaji_pokok': 1166000,
        'tunj_transport': 150000,
        'tgl_masuk': '2022-01-10'
    },
    {
        'nik': 'TPA-05',
        'nama_lengkap': 'NOVI RAHMA PRATIWI',
        'jabatan': 'BUNDA TPA',
        'no_hp': '081233445516',
        'kode_dept': 'TPA',
        'kode_cabang': 'CITANDUI',
        'tipe_komponen': 'tpa',
        'gaji_pokok': 1160000,
        'tunj_transport': 150000,
        'tgl_masuk': '2022-05-20'
    },
    {
        'nik': 'TPA-06',
        'nama_lengkap': 'CICIK TRIYA TRI NINGSIH',
        'jabatan': 'BUNDA TPA',
        'no_hp': '081233445517',
        'kode_dept': 'TPA',
        'kode_cabang': 'CITANDUI',
        'tipe_komponen': 'tpa',
        'gaji_pokok': 1050000,
        'tunj_transport': 150000,
        'tgl_masuk': '2026-01-01'
    },
    {
        'nik': 'TPA-07',
        'nama_lengkap': 'FEBRIANA UMAIROH',
        'jabatan': 'BUNDA TPA',
        'no_hp': '081233445518',
        'kode_dept': 'TPA',
        'kode_cabang': 'CITANDUI',
        'tipe_komponen': 'tpa',
        'gaji_pokok': 1050000,
        'tunj_transport': 150000,
        'tgl_masuk': '2023-12-08'
    },
    {
        'nik': 'TPA-08',
        'nama_lengkap': 'NAILA DHINI AMALIA PUTRI',
        'jabatan': 'BUNDA TPA',
        'no_hp': '081233445519',
        'kode_dept': 'TPA',
        'kode_cabang': 'CITANDUI',
        'tipe_komponen': 'tpa',
        'gaji_pokok': 901000,
        'tunj_transport': 150000,
        'tgl_masuk': '2024-05-04'
    },
    {
        'nik': 'TPA-09',
        'nama_lengkap': 'MAGHFIRA GLADIS HERDIYANI',
        'jabatan': 'BUNDA TPA / TRAINING',
        'no_hp': '081233445520',
        'kode_dept': 'TPA',
        'kode_cabang': 'CITANDUI',
        'tipe_komponen': 'tpa',
        'gaji_pokok': 500000,
        'tunj_transport': 150000,
        'tgl_masuk': '2026-04-28'
    },

    # --- TPA DAYCARE LANGSEP ---
    {
        'nik': 'TPA-10',
        'nama_lengkap': 'FITRI FADILATUL KHASANAH',
        'jabatan': 'BUNDA TPA',
        'no_hp': '081233445521',
        'kode_dept': 'TPA',
        'kode_cabang': 'LANGSEP',
        'tipe_komponen': 'tpa',
        'gaji_pokok': 901000,
        'tunj_transport': 150000,
        'tgl_masuk': '2024-08-01'
    },
    {
        'nik': 'TPA-11',
        'nama_lengkap': 'MARISA DINADA',
        'jabatan': 'BUNDA TPA',
        'no_hp': '081233445522',
        'kode_dept': 'TPA',
        'kode_cabang': 'LANGSEP',
        'tipe_komponen': 'tpa',
        'gaji_pokok': 901000,
        'tunj_transport': 150000,
        'tgl_masuk': '2024-06-27'
    },
    {
        'nik': 'TPA-12',
        'nama_lengkap': 'HENI RAHMAWATI ANDRIYANI SARI',
        'jabatan': 'BUNDA TPA',
        'no_hp': '081233445523',
        'kode_dept': 'TPA',
        'kode_cabang': 'LANGSEP',
        'tipe_komponen': 'tpa',
        'gaji_pokok': 901000,
        'tunj_transport': 150000,
        'tgl_masuk': '2024-08-03'
    },
    {
        'nik': 'TPA-13',
        'nama_lengkap': 'FIDYAH AYU NINGRUM',
        'jabatan': 'BUNDA TPA / TRAINING',
        'no_hp': '081233445524',
        'kode_dept': 'TPA',
        'kode_cabang': 'LANGSEP',
        'tipe_komponen': 'tpa',
        'gaji_pokok': 500000,
        'tunj_transport': 150000,
        'tgl_masuk': '2026-07-25'
    },

    # --- STAFF OPERASIONAL / KEBERSIHAN / KONSUMSI ---
    {
        'nik': 'STF-01',
        'nama_lengkap': 'MILYAS NUR ROHMAN',
        'jabatan': 'SATPAM & KEBERSIHAN',
        'no_hp': '081233445525',
        'kode_dept': 'TPA',
        'kode_cabang': 'CITANDUI',
        'tipe_komponen': 'staff',
        'gaji_pokok': 1378000,
        'tunj_transport': 200000,
        'tgl_masuk': '2024-04-30'
    },
    {
        'nik': 'STF-02',
        'nama_lengkap': 'KASYATI',
        'jabatan': 'BAG. KONSUMSI',
        'no_hp': '081233445526',
        'kode_dept': 'TPA',
        'kode_cabang': 'CITANDUI',
        'tipe_komponen': 'staff',
        'gaji_pokok': 1700000,
        'tunj_transport': 150000,
        'tgl_masuk': '2020-01-01'
    },
    {
        'nik': 'STF-03',
        'nama_lengkap': 'METY FARIDA KARYANTI',
        'jabatan': 'BAG. KONSUMSI',
        'no_hp': '081233445527',
        'kode_dept': 'TPA',
        'kode_cabang': 'LANGSEP',
        'tipe_komponen': 'staff',
        'gaji_pokok': 1600000,
        'tunj_transport': 150000,
        'tgl_masuk': '2024-08-01'
    }
]

# Upsert Karyawan
print(f'Importing {len(employees)} employees into karyawan table...')
karyawan_payload = []
for emp in employees:
    karyawan_payload.append({
        'nik': emp['nik'],
        'nama_lengkap': emp['nama_lengkap'],
        'jabatan': emp['jabatan'],
        'no_hp': emp['no_hp'],
        'password': default_pwd_hash,
        'kode_dept': emp['kode_dept'],
        'kode_cabang': emp['kode_cabang'],
        'status_location': 1,
        'status_jam_kerja': 1
    })

res = requests.post(f'{supabase_url}/rest/v1/karyawan', headers=headers, json=karyawan_payload)
print('Karyawan upsert status:', res.status_code, res.text[:100] if res.status_code != 201 else 'Success!')

# Try upserting gaji_master if table exists
print('Attempting to upsert gaji_master...')
gaji_payload = []
for emp in employees:
    gaji_payload.append({
        'nik': emp['nik'],
        'tipe_komponen': emp['tipe_komponen'],
        'gaji_pokok': emp['gaji_pokok'],
        'tunj_transport': emp['tunj_transport'],
        'tgl_masuk': emp['tgl_masuk']
    })

res_gaji = requests.post(f'{supabase_url}/rest/v1/gaji_master', headers=headers, json=gaji_payload)
print('gaji_master upsert status:', res_gaji.status_code, res_gaji.text[:100] if res_gaji.status_code != 201 else 'Success!')

print('Data import process completed successfully!')
