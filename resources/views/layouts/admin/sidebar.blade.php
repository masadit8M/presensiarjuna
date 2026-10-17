<aside class="navbar navbar-vertical navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu"
            aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <h1 class="navbar-brand navbar-brand-autodark">
            <a href="{{ url('/panel/dashboardadmin') }}">
                <img src="{{ asset('tabler/static/logo-white.svg') }}" width="110" height="32" alt="Presensi Arjuna"
                    class="navbar-brand-image">
            </a>
        </h1>
        <div class="navbar-nav flex-row d-lg-none">
            <div class="nav-item dropdown">
                <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown"
                    aria-label="Open user menu">
                    <span class="avatar avatar-sm bg-primary-lt fw-bold">{{ strtoupper(substr(Auth::guard('user')->user()->name ?? 'A', 0, 2)) }}</span>
                    <div class="d-none d-xl-block ps-2">
                        <div>{{ Auth::guard('user')->user()->name ?? 'Admin' }}</div>
                        <div class="mt-1 small text-muted">{{ ucwords(Auth::guard('user')->user()->roles->pluck('name')[0] ?? 'Admin') }}</div>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <a href="{{ url('/panel/dashboardadmin') }}" class="dropdown-item">Dashboard</a>
                    <a href="{{ url('/konfigurasi/users') }}" class="dropdown-item">Pengaturan User</a>
                    <div class="dropdown-divider"></div>
                    <a href="{{ url('/proseslogoutadmin') }}" class="dropdown-item">Logout</a>
                </div>
            </div>
        </div>
        <div class="collapse navbar-collapse" id="sidebar-menu">
            <ul class="navbar-nav pt-lg-3">
                <li class="nav-item">
                    <a class="nav-link" href="/panel/dashboardadmin">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <!-- Download SVG icon from http://tabler-icons.io/i/home -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M5 12l-2 0l9 -9l9 9l-2 0" />
                                <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" />
                                <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" />
                            </svg>
                        </span>
                        <span class="nav-link-title">
                            Home
                        </span>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->is(['karyawan', 'departemen', 'cabang']) ? 'show' : '' }}"
                        href="#navbar-base" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button"
                        aria-expanded="{{ request()->is(['karyawan', 'departemen', 'cabang']) ? 'true' : '' }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <!-- Download SVG icon from http://tabler-icons.io/i/package -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5" />
                                <path d="M12 12l8 -4.5" />
                                <path d="M12 12l0 9" />
                                <path d="M12 12l-8 -4.5" />
                                <path d="M16 5.25l-8 4.5" />
                            </svg>
                        </span>
                        <span class="nav-link-title">
                            Data Master
                        </span>
                    </a>
                    <div
                        class="dropdown-menu {{ request()->is(['karyawan', 'departemen', 'cabang', 'cuti']) ? 'show' : '' }}">
                        <div class="dropdown-menu-columns">
                            <div class="dropdown-menu-column">
                                @role('administrator|admin departemen', 'user')
                                    <a class="dropdown-item {{ request()->is(['karyawan']) ? 'active' : '' }}"
                                        href="/karyawan">
                                        Karyawan
                                    </a>
                                @endrole
                                @role('administrator', 'user')
                                    <a class="dropdown-item {{ request()->is(['departemen']) ? 'active' : '' }}"
                                        href="/departemen">
                                        Departemen
                                    </a>

                                    <a class="dropdown-item {{ request()->is(['cabang']) ? 'active' : '' }}"
                                        href="/cabang">
                                        Kantor Cabang
                                    </a>
                                    <a class="dropdown-item {{ request()->is(['cuti']) ? 'active' : '' }}" href="/cuti">
                                        Cuti
                                    </a>
                                @endrole
                            </div>
                        </div>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('presensi/monitoring') ? 'active' : '' }}"
                        href="/presensi/monitoring">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <!-- Download SVG icon from http://tabler-icons.io/i/home -->
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="icon icon-tabler icon-tabler-heart-rate-monitor" width="24" height="24"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path
                                    d="M3 4m0 1a1 1 0 0 1 1 -1h16a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-16a1 1 0 0 1 -1 -1z">
                                </path>
                                <path d="M7 20h10"></path>
                                <path d="M9 16v4"></path>
                                <path d="M15 16v4"></path>
                                <path d="M7 10h2l2 3l2 -6l1 3h3"></path>
                            </svg>
                        </span>
                        <span class="nav-link-title">
                            Monitoring Presensi
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('presensi/izinsakit') ? 'active' : '' }}"
                        href="/presensi/izinsakit">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <!-- Download SVG icon from http://tabler-icons.io/i/home -->
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="icon icon-tabler icon-tabler-heart-rate-monitor" width="24" height="24"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path
                                    d="M3 4m0 1a1 1 0 0 1 1 -1h16a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-16a1 1 0 0 1 -1 -1z">
                                </path>
                                <path d="M7 20h10"></path>
                                <path d="M9 16v4"></path>
                                <path d="M15 16v4"></path>
                                <path d="M7 10h2l2 3l2 -6l1 3h3"></path>
                            </svg>
                        </span>
                        <span class="nav-link-title">
                            Data Izin / Sakit
                        </span>
                    </a>
                </li>
                @php
                    $pendingKendalaCount = 0;
                    try {
                        \App\Services\FiturTambahanSchemaService::ensureTablesExist();
                        $pendingKendalaCount = \Illuminate\Support\Facades\DB::table('pengajuan_masalah')->where('status', 'diajukan')->count();
                    } catch (\Throwable $e) {}
                @endphp
                <li class="nav-item">
                    <a class="nav-link {{ request()->is(['presensi/kendala-absen', 'presensi/log-koreksi']) ? 'active' : '' }}"
                        href="/presensi/kendala-absen">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-lifebuoy" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M12 12m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                                <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                                <path d="M15 15l3.35 3.35" />
                                <path d="M9 15l-3.35 3.35" />
                                <path d="M5.65 5.65l3.35 3.35" />
                                <path d="M18.35 5.65l-3.35 3.35" />
                            </svg>
                        </span>
                        <span class="nav-link-title">
                            Kendala & Audit Absen
                            @if ($pendingKendalaCount > 0)
                                <span class="badge bg-red text-white ms-1">{{ $pendingKendalaCount }}</span>
                            @endif
                        </span>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->is(['presensi/laporan', 'presensi/rekap', 'presensi/log-koreksi']) ? 'show' : '' }}"
                        href="#navbar-base" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button"
                        aria-expanded="{{ request()->is(['presensi/laporan', 'presensi/rekap', 'presensi/log-koreksi']) ? 'true' : '' }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <!-- Download SVG icon from http://tabler-icons.io/i/package -->
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="icon icon-tabler icon-tabler-file-description" width="24" height="24"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                                <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path>
                                <path d="M9 17h6"></path>
                                <path d="M9 13h6"></path>
                            </svg>
                        </span>
                        <span class="nav-link-title">
                            Laporan
                        </span>
                    </a>
                    <div
                        class="dropdown-menu {{ request()->is(['presensi/laporan', 'presensi/rekap', 'presensi/log-koreksi']) ? 'show' : '' }}">
                        <div class="dropdown-menu-columns">
                            <div class="dropdown-menu-column">
                                <a class="dropdown-item {{ request()->is(['presensi/laporan']) ? 'active' : '' }}"
                                    href="/presensi/laporan">
                                    Presensi
                                </a>
                                <a class="dropdown-item {{ request()->is(['presensi/rekap']) ? 'active' : '' }}"
                                    href="/presensi/rekap">
                                    Rekap Presensi
                                </a>
                                <a class="dropdown-item {{ request()->is(['presensi/log-koreksi']) ? 'active' : '' }}"
                                    href="/presensi/log-koreksi">
                                    Log Audit Koreksi Presensi
                                </a>
                            </div>
                        </div>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->is(['gaji', 'gaji/*', 'pinjaman', 'pinjaman/*']) ? 'show' : '' }}"
                        href="#navbar-base" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button"
                        aria-expanded="{{ request()->is(['gaji', 'gaji/*', 'pinjaman', 'pinjaman/*']) ? 'true' : '' }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <!-- SVG icon for cash / receipt / payroll -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-receipt-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16l-3 -2l-2 2l-2 -2l-2 2l-2 -2l-3 2" />
                                <path d="M14 8h-2.5a1.5 1.5 0 0 0 0 3h1a1.5 1.5 0 0 1 0 3h-2.5m2 0v1.5m0 -9v1.5" />
                            </svg>
                        </span>
                        <span class="nav-link-title">
                            Gaji & Slip Gaji
                        </span>
                    </a>
                    <div class="dropdown-menu {{ request()->is(['gaji', 'gaji/*', 'pinjaman', 'pinjaman/*']) ? 'show' : '' }}">
                        <div class="dropdown-menu-columns">
                            <div class="dropdown-menu-column">
                                <a class="dropdown-item {{ request()->is(['gaji', 'gaji/periode/*']) ? 'active' : '' }}"
                                    href="/gaji">
                                    Data Penggajian
                                </a>
                                <a class="dropdown-item {{ request()->is(['gaji/master']) ? 'active' : '' }}"
                                    href="/gaji/master">
                                    Master Gaji Karyawan
                                </a>
                                <a class="dropdown-item {{ request()->is(['pinjaman', 'pinjaman/*']) ? 'active' : '' }}"
                                    href="/pinjaman">
                                    Pinjaman / Kasbon Guru
                                </a>
                            </div>
                        </div>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is(['pengumuman', 'pengumuman/*']) ? 'active' : '' }}"
                        href="/pengumuman">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-speakerphone" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M18 8a3 3 0 0 1 0 6" />
                                <path d="M10 8v11a1 1 0 0 1 -1 1h-1a1 1 0 0 1 -1 -1v-5" />
                                <path d="M12 8h0l4.524 -3.77a.9 .9 0 0 1 1.476 .692v12.156a.9 .9 0 0 1 -1.476 .692l-4.524 -3.77h-8a1 1 0 0 1 -1 -1v-4a1 1 0 0 1 1 -1h8" />
                            </svg>
                        </span>
                        <span class="nav-link-title">
                            Pengumuman Yayasan
                        </span>
                    </a>
                </li>
                @role('administrator', 'user')
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->is(['konfigurasi', 'konfigurasi/*']) ? 'show' : '' }}"
                            href="#navbar-base" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button"
                            aria-expanded=" {{ request()->is(['konfigurasi', 'konfigurasi/*']) ? 'true' : '' }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <!-- Download SVG icon from http://tabler-icons.io/i/package -->
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="icon icon-tabler icon-tabler-settings-filled" width="24" height="24"
                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                    <path
                                        d="M14.647 4.081a.724 .724 0 0 0 1.08 .448c2.439 -1.485 5.23 1.305 3.745 3.744a.724 .724 0 0 0 .447 1.08c2.775 .673 2.775 4.62 0 5.294a.724 .724 0 0 0 -.448 1.08c1.485 2.439 -1.305 5.23 -3.744 3.745a.724 .724 0 0 0 -1.08 .447c-.673 2.775 -4.62 2.775 -5.294 0a.724 .724 0 0 0 -1.08 -.448c-2.439 1.485 -5.23 -1.305 -3.745 -3.744a.724 .724 0 0 0 -.447 -1.08c-2.775 -.673 -2.775 -4.62 0 -5.294a.724 .724 0 0 0 .448 -1.08c-1.485 -2.439 1.305 -5.23 3.744 -3.745a.722 .722 0 0 0 1.08 -.447c.673 -2.775 4.62 -2.775 5.294 0zm-2.647 4.919a3 3 0 1 0 0 6a3 3 0 0 0 0 -6z"
                                        stroke-width="0" fill="currentColor"></path>
                                </svg>
                            </span>
                            <span class="nav-link-title">
                                Konfigurasi
                            </span>
                        </a>
                        <div class="dropdown-menu  {{ request()->is(['konfigurasi', 'konfigurasi/*']) ? 'show' : '' }}">

                            <div class="dropdown-menu-columns">
                                <div class="dropdown-menu-column">
                                    <a class="dropdown-item  {{ request()->is(['konfigurasi/harilibur']) ? 'active' : '' }}"
                                        href="/konfigurasi/harilibur">
                                        Hari Libur
                                    </a>
                                    <a class="dropdown-item  {{ request()->is(['konfigurasi/jamkerja']) ? 'active' : '' }}"
                                        href="/konfigurasi/jamkerja">
                                        Jam Kerja
                                    </a>
                                    <a class="dropdown-item  {{ request()->is(['konfigurasi/jamkerjadept']) ? 'active' : '' }}"
                                        href="/konfigurasi/jamkerjadept">
                                        Jam Kerja Departemen
                                    </a>
                                    <a class="dropdown-item  {{ request()->is(['konfigurasi/users']) ? 'active' : '' }}"
                                        href="/konfigurasi/users">
                                        Users
                                    </a>
                                </div>
                            </div>
                        </div>
                    </li>
                @endrole
            </ul>
        </div>
    </div>
</aside>
