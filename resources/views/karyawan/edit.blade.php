<form action="/karyawan/{{ Crypt::encrypt($karyawan->nik) }}/update" method="POST" id="frmEditkaryawan"
    enctype="multipart/form-data">
    @csrf
    <div class="row">
        <div class="col-12">
            <div class="input-icon mb-3">
                <span class="input-icon-addon">
                    <!-- Download SVG icon from http://tabler-icons.io/i/user -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-barcode" width="24"
                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M4 7v-1a2 2 0 0 1 2 -2h2"></path>
                        <path d="M4 17v1a2 2 0 0 0 2 2h2"></path>
                        <path d="M16 4h2a2 2 0 0 1 2 2v1"></path>
                        <path d="M16 20h2a2 2 0 0 0 2 -2v-1"></path>
                        <path d="M5 11h1v2h-1z"></path>
                        <path d="M10 11l0 2"></path>
                        <path d="M14 11h1v2h-1z"></path>
                        <path d="M19 11l0 2"></path>
                    </svg>
                </span>
                <input type="text" value="{{ $karyawan->nik }}" id="nik" class="form-control" placeholder="Nik"
                    name="nik_baru">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="input-icon mb-3">
                <span class="input-icon-addon">
                    <!-- Download SVG icon from http://tabler-icons.io/i/user -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-user" width="24"
                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M12 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"></path>
                        <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"></path>
                    </svg>
                </span>
                <input type="text" id="nama_lengkap" value="{{ $karyawan->nama_lengkap }}" class="form-control"
                    name="nama_lengkap" placeholder="Nama Lengkap">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="input-icon mb-3">
                <span class="input-icon-addon">
                    <!-- Download SVG icon from http://tabler-icons.io/i/user -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-device-analytics"
                        width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M3 4m0 1a1 1 0 0 1 1 -1h16a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-16a1 1 0 0 1 -1 -1z">
                        </path>
                        <path d="M7 20l10 0"></path>
                        <path d="M9 16l0 4"></path>
                        <path d="M15 16l0 4"></path>
                        <path d="M8 12l3 -3l2 2l3 -3"></path>
                    </svg>
                </span>
                <input type="text" id="jabatan" value="{{ $karyawan->jabatan }}" class="form-control"
                    name="jabatan" placeholder="Jabatan">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="input-icon mb-3">
                <span class="input-icon-addon">
                    <!-- Download SVG icon from http://tabler-icons.io/i/user -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-phone" width="24"
                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path
                            d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2">
                        </path>
                    </svg>
                </span>
                <input type="text" id="no_hp" value="{{ $karyawan->no_hp }}" class="form-control" name="no_hp"
                    placeholder="No. HP">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="input-icon mb-3">
                <span class="input-icon-addon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-key" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M16.555 3.843l3.602 3.602a2.877 2.877 0 0 1 0 4.069l-2.643 2.643a2.877 2.877 0 0 1 -4.069 0l-.301 -.301l-6.558 6.558a2 2 0 0 1 -1.414 .586h-2.172a1 1 0 0 1 -1 -1v-2.172a2 2 0 0 1 .586 -1.414l6.558 -6.558l-.301 -.301a2.877 2.877 0 0 1 0 -4.069l2.643 -2.643a2.877 2.877 0 0 1 4.069 0z"/></svg>
                </span>
                <input type="password" id="password" class="form-control" name="password"
                    placeholder="Password Baru (Kosongkan jika tidak ingin diubah)">
            </div>
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-12">
            <input type="file" name="foto" class="form-control">
            <input type="hidden" name="old_foto" value="{{ $karyawan->foto }}">
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-12">
            <select name="role_jam_kerja" id="role_jam_kerja" class="form-select">
                <option value="Normal" {{ isset($karyawan->role_jam_kerja) && $karyawan->role_jam_kerja == 'Normal' ? 'selected' : '' }}>Role Jam Kerja: Normal</option>
                <option value="Guru" {{ isset($karyawan->role_jam_kerja) && $karyawan->role_jam_kerja == 'Guru' ? 'selected' : '' }}>Role Jam Kerja: Guru</option>
                <option value="Kepala Sekolah" {{ isset($karyawan->role_jam_kerja) && $karyawan->role_jam_kerja == 'Kepala Sekolah' ? 'selected' : '' }}>Role Jam Kerja: Kepala Sekolah</option>
            </select>
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-12">
            <select name="kode_dept" id="kode_dept" class="form-select">
                <option value="">Departemen</option>
                @foreach ($departemen as $d)
                    <option {{ $karyawan->kode_dept == $d->kode_dept ? 'selected' : '' }} value="{{ $d->kode_dept }}">
                        {{ $d->nama_dept }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-12">
            <select name="kode_cabang" id="kode_cabang" class="form-select">
                <option value="">Cabang</option>
                @foreach ($cabang as $d)
                    <option {{ $karyawan->kode_cabang == $d->kode_cabang ? 'selected' : '' }}
                        value="{{ $d->kode_cabang }}">{{ strtoupper($d->nama_cabang) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- PENGATURAN MASTER GAJI KARYAWAN -->
    <div class="card border-primary mt-3 mb-2 shadow-sm">
        <div class="card-header bg-primary-lt py-2">
            <div>
                <strong class="text-primary"><svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-cash me-1" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 9m0 2a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2z"/><path d="M14 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 9v-2a2 2 0 0 0 -2 -2h-10a2 2 0 0 0 -2 2v6a2 2 0 0 0 2 2h2"/></svg>Pengaturan Master Gaji (Tersinkron Otomatis)</strong>
                <div class="small text-muted">Data ini tersinkron dengan Master Gaji & Periode Penggajian aktif.</div>
            </div>
        </div>
        <div class="card-body py-2">
            <div class="row g-2 mb-2">
                <div class="col-md-6">
                    <label class="form-label small mb-1 fw-bold">Gaji Pokok</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="gaji_pokok" id="edit_gapok" class="form-control" value="{{ round($gajiMaster->gaji_pokok ?? 1200000) }}" oninput="hitungHarianEdit()">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label small mb-1 fw-bold">Tunjangan Transportasi</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="tunjangan_transportasi" id="edit_transp" class="form-control" value="{{ round($gajiMaster->tunjangan_transportasi ?? 150000) }}" oninput="hitungHarianEdit()">
                    </div>
                </div>
            </div>

            @php
                $curGapok = $gajiMaster->gaji_pokok ?? 1200000;
                $curTransp = $gajiMaster->tunjangan_transportasi ?? 150000;
                $curHarian = round(($curGapok + $curTransp) / 26);
            @endphp
            <div class="alert alert-info py-2 px-3 mb-2">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small">Gaji Harian Standar (26 HK):</span>
                    <strong class="text-primary" id="labelHarianEdit">Rp {{ number_format($curHarian, 0, ',', '.') }} <span class="fw-normal small text-muted">/hari</span></strong>
                </div>
            </div>

            <div class="row g-2 mb-2">
                <div class="col-md-6">
                    <label class="form-label small mb-1">Tunjangan Jabatan / Uang Ekstra</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="tunjangan_jabatan" class="form-control" value="{{ round($gajiMaster->tunjangan_jabatan ?? 0) }}">
                    </div>
                    <input type="text" name="ket_tunjangan_jabatan" class="form-control form-control-sm mt-1" placeholder="Ket: (cth: Kepala Sekolah, Wali Kelas)" value="{{ $gajiMaster->ket_tunjangan_jabatan ?? '' }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small mb-1">Tunjangan Konsumsi</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="tunjangan_konsumsi" class="form-control" value="{{ round($gajiMaster->tunjangan_konsumsi ?? 0) }}">
                    </div>
                    <input type="text" name="ket_tunjangan_konsumsi" class="form-control form-control-sm mt-1" placeholder="Ket: (cth: Uang Makan Piket)" value="{{ $gajiMaster->ket_tunjangan_konsumsi ?? '' }}">
                </div>
            </div>

            <div class="row g-2 mb-2">
                <div class="col-md-6">
                    <label class="form-label small mb-1">Honor Kegiatan (Guru)</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="tarif_honor_kegiatan" class="form-control" value="{{ round($gajiMaster->tarif_honor_kegiatan ?? 0) }}">
                    </div>
                    <input type="text" name="ket_honor_kegiatan" class="form-control form-control-sm mt-1" placeholder="Ket: (cth: Panitia Parenting)" value="{{ $gajiMaster->ket_honor_kegiatan ?? '' }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small mb-1">Honor Ekskul (Guru)</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="tarif_ekskul" class="form-control" value="{{ round($gajiMaster->tarif_ekskul ?? 0) }}">
                    </div>
                    <input type="text" name="ket_honor_ekskul" class="form-control form-control-sm mt-1" placeholder="Ket: (cth: Pembina Tari)" value="{{ $gajiMaster->ket_honor_ekskul ?? '' }}">
                </div>
            </div>

            <div class="row g-2 mb-2">
                <div class="col-md-6">
                    <label class="form-label small mb-1">Tarif Lembur (TPA)</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="tarif_lembur" class="form-control" value="{{ round($gajiMaster->tarif_lembur ?? 0) }}">
                    </div>
                    <input type="text" name="ket_tarif_lembur" class="form-control form-control-sm mt-1" placeholder="Ket: (cth: Lembur Sore)" value="{{ $gajiMaster->ket_tarif_lembur ?? '' }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small mb-1">Potongan BPJS</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="bpjs_kesehatan" class="form-control" value="{{ round($gajiMaster->bpjs_kesehatan ?? 0) }}">
                    </div>
                </div>
            </div>

            <div class="row g-2 mb-2">
                <div class="col-md-6">
                    <label class="form-label small mb-1 text-danger">Potongan Kasbon / Pinjaman Rutin</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="potongan_kasbon" class="form-control" value="{{ round($gajiMaster->potongan_kasbon ?? 0) }}">
                    </div>
                    <input type="text" name="ket_potongan_kasbon" class="form-control form-control-sm mt-1" placeholder="Ket: (cth: Pinjaman Koperasi)" value="{{ $gajiMaster->ket_potongan_kasbon ?? '' }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small mb-1">Potongan Lainnya</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="potongan_lainnya" class="form-control" value="{{ round($gajiMaster->potongan_lainnya ?? 0) }}">
                    </div>
                    <input type="text" name="ket_potongan_lainnya" class="form-control form-control-sm mt-1" placeholder="Ket potongan lainnya" value="{{ $gajiMaster->ket_potongan_lainnya ?? '' }}">
                </div>
            </div>

            <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label small mb-1 text-success">Insentif Pagi ≤ 06:30 (TPA)</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="insentif_pagi" class="form-control" value="{{ round($gajiMaster->insentif_pagi ?? 0) }}" placeholder="50000">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label small mb-1 text-success">Pool SPP &gt;40 Siswa (TPA)</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="hak_pool_spp" class="form-control" value="{{ round($gajiMaster->hak_pool_spp ?? 0) }}" placeholder="100000">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-12">
            <div class="form-group">
                <button class="btn btn-primary w-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-send" width="24"
                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M10 14l11 -11"></path>
                        <path d="M21 3l-6.5 18a.55 .55 0 0 1 -1 0l-3.5 -7l-7 -3.5a.55 .55 0 0 1 0 -1l18 -6.5"></path>
                    </svg>
                    Simpan
                </button>
            </div>
        </div>
    </div>
</form>
<script>
    $("#frmEditkaryawan").submit(function() {
        var nik = $("#frmEditkaryawan").find("#nik").val();
        var nama_lengkap = $("#frmEditkaryawan").find("#nama_lengkap").val();
        var jabatan = $("#frmEditkaryawan").find("#jabatan").val();
        var no_hp = $("#frmEditkaryawan").find("#no_hp").val();
        var kode_dept = $("#frmEditkaryawan").find("#kode_dept").val();
        var kode_cabang = $("#frmEditkaryawan").find("#kode_cabang").val();


        if (nik == "") {
            // alert('Nik Harus Diisi');
            Swal.fire({
                title: 'Warning!',
                text: 'Nik Harus Diisi !',
                icon: 'warning',
                confirmButtonText: 'Ok'
            }).then((result) => {
                $("#nik").focus();
            });

            return false;
        } else if (nama_lengkap == "") {
            Swal.fire({
                title: 'Warning!',
                text: 'Nama Harus Diisi !',
                icon: 'warning',
                confirmButtonText: 'Ok'
            }).then((result) => {
                $("#nama_lengkap").focus();
            });

            return false;
        } else if (jabatan == "") {
            Swal.fire({
                title: 'Warning!',
                text: 'Jabatan Harus Diisi !',
                icon: 'warning',
                confirmButtonText: 'Ok'
            }).then((result) => {
                $("#jabatan").focus();
            });

            return false;
        } else if (no_hp == "") {
            Swal.fire({
                title: 'Warning!',
                text: 'No. HP Harus Diisi !',
                icon: 'warning',
                confirmButtonText: 'Ok'
            }).then((result) => {
                $("#no_hp").focus();
            });

            return false;
        } else if (kode_dept == "") {
            Swal.fire({
                title: 'Warning!',
                text: 'Departemen Harus Diisi !',
                icon: 'warning',
                confirmButtonText: 'Ok'
            }).then((result) => {
                $("#kode_dept").focus();
            });

            return false;
        } else if (kode_cabang == "") {
            Swal.fire({
                title: 'Warning!',
                text: 'Cabang Harus Diisi !',
                icon: 'warning',
                confirmButtonText: 'Ok'
            }).then((result) => {
                $("#kode_cabang").focus();
            });

            return false;
        }
    });

    function hitungHarianEdit() {
        var gapok = parseFloat($('#edit_gapok').val()) || 0;
        var transp = parseFloat($('#edit_transp').val()) || 0;
        var harian = Math.round((gapok + transp) / 26);
        $('#labelHarianEdit').html('Rp ' + harian.toLocaleString('id-ID') + ' <span class="fw-normal small text-muted">/hari</span>');
    }
</script>
