@extends('layouts.presensi')
@section('header')
    <!-- App Header -->
    <div class="appHeader bg-primary text-light">
        <div class="left">
            <a href="javascript:;" class="headerButton goBack">
                <ion-icon name="chevron-back-outline"></ion-icon>
            </a>
        </div>
        <div class="pageTitle">E-Presensi</div>
        <div class="right"></div>
    </div>
    <!-- * App Header -->
    <style>
        .webcam-capture,
        .webcam-capture video {
            display: inline-block;
            width: 100% !important;
            margin: auto;
            height: auto !important;
            border-radius: 15px;

        }

        #map {
            height: 200px;
        }

        .jam-digital-malasngoding {

            background-color: #27272783;
            position: absolute;
            top: 65px;
            right: 10px;
            z-index: 9999;
            width: 150px;
            border-radius: 10px;
            padding: 5px;
        }



        .jam-digital-malasngoding p {
            color: #fff;
            font-size: 16px;
            text-align: left;
            margin-top: 0;
            margin-bottom: 0;
        }
    </style>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
@endsection
@section('content')
    <div class="row" style="margin-top: 60px">
        <div class="col">
            <input type="hidden" id="lokasi">
            <div class="webcam-capture"></div>
        </div>
    </div>
    <div class="jam-digital-malasngoding">
        <p>{{ $hariini }}</p>
        <p id="jam"></p>
        <p>{{ $jamkerja->nama_jam_kerja }}</p>
        @if(isset($isBypassRadius) && $isBypassRadius)
            <p><span class="badge bg-success text-white" style="font-size: 11px; padding: 2px 6px;">Bebas Lokasi</span></p>
        @endif
        <p>Mulai : {{ date('H:i', strtotime($jamkerja->awal_jam_masuk)) }}</p>
        <p>Masuk : {{ date('H:i', strtotime($jamkerja->jam_masuk)) }}</p>
        <p>Akhir : {{ date('H:i', strtotime($jamkerja->akhir_jam_masuk)) }}</p>
        <p>Pulang : {{ date('H:i', strtotime($jamkerja->jam_pulang)) }}</p>
    </div>
    <div class="row">
        <div class="col">
            @if (isset($datapresensi) && $datapresensi != null && !empty($datapresensi->jam_out))
                <div class="card bg-success text-white mb-2 shadow-sm">
                    <div class="card-body p-2 text-center">
                        <ion-icon name="checkmark-done-circle-outline" style="font-size: 26px; vertical-align: middle;"></ion-icon>
                        <strong style="font-size: 14px; display: block; margin-top: 3px;"> Presensi Hari Ini Lengkap</strong>
                        <div style="font-size: 12px; margin-top: 3px;">
                            Masuk: <b>{{ $datapresensi->jam_in ?? '-' }}</b> | Pulang: <b>{{ $datapresensi->jam_out ?? '-' }}</b>
                        </div>
                    </div>
                </div>
                <a href="/dashboard" class="btn btn-secondary btn-block">
                    <ion-icon name="home-outline"></ion-icon>
                    Kembali ke Dashboard
                </a>
            @elseif ($cek > 0)
                <div class="card bg-success text-white mb-2 shadow-sm">
                    <div class="card-body p-2 text-center">
                        <ion-icon name="checkmark-circle-outline" style="font-size: 26px; vertical-align: middle;"></ion-icon>
                        <strong style="font-size: 14px; display: block; margin-top: 2px;"> Presensi Masuk Berhasil!</strong>
                        <div style="font-size: 12px; margin-top: 2px;">
                            Jam Kedatangan Anda: <b>{{ $datapresensi->jam_in ?? '-' }}</b>
                        </div>
                        <div style="font-size: 11px; margin-top: 4px; opacity: 0.95;">
                            ✨ Presensi kedatangan (masuk) Anda sudah tercatat aman. Presensi pulang di bawah ini bersifat <b>opsional</b>.
                        </div>
                    </div>
                </div>
                <button id="takeabsen" class="btn btn-danger btn-block mb-2">
                    <ion-icon name="camera-outline"></ion-icon>
                    Absen Pulang (Opsional)
                </button>
                <a href="/dashboard" class="btn btn-outline-secondary btn-block">
                    <ion-icon name="home-outline"></ion-icon> Kembali ke Dashboard
                </a>
            @else
                <button id="takeabsen" class="btn btn-primary btn-block">
                    <ion-icon name="camera-outline"></ion-icon>
                    Absen Masuk
                </button>
            @endif
        </div>
    </div>
    <div class="row mt-2">
        <div class="col">
            <div id="map"></div>
        </div>
    </div>

    <audio id="notifikasi_in">
        <source src="{{ asset('assets/sound/notifikasi_in.mp3') }}" type="audio/mpeg">
    </audio>
    <audio id="notifikasi_out">
        <source src="{{ asset('assets/sound/notifikasi_out.mp3') }}" type="audio/mpeg">
    </audio>
    <audio id="radius_sound">
        <source src="{{ asset('assets/sound/radius.mp3') }}" type="audio/mpeg">
    </audio>
@endsection

@push('myscript')
    <script type="text/javascript">
        window.onload = function() {
            jam();
        }

        function jam() {
            var e = document.getElementById('jam'),
                d = new Date(),
                h, m, s;
            h = d.getHours();
            m = set(d.getMinutes());
            s = set(d.getSeconds());

            e.innerHTML = h + ':' + m + ':' + s;

            setTimeout('jam()', 1000);
        }

        function set(e) {
            e = e < 10 ? '0' + e : e;
            return e;
        }
    </script>
    <script>
        var notifikasi_in = document.getElementById('notifikasi_in');
        var notifikasi_out = document.getElementById('notifikasi_out');
        var radius_sound = document.getElementById('radius_sound');

        Webcam.set({
            width: 640,
            height: 480,
            dest_width: 640,
            dest_height: 480,
            image_format: 'jpeg',
            jpeg_quality: 80,
            force_flash: false,
            fps: 45,
            constraints: {
                facingMode: "user"
            }
        });

        Webcam.attach('.webcam-capture');

        Webcam.on('error', function(err) {
            Swal.fire({
                title: 'Akses Kamera Gagal!',
                text: 'Pastikan izin kamera di browser Anda telah diizinkan (Allow Camera).',
                icon: 'warning'
            });
        });

        var lokasi = document.getElementById('lokasi');
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(successCallback, errorCallback, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            });
        }

        function successCallback(position) {
            lokasi.value = position.coords.latitude + "," + position.coords.longitude;
            try {
                var map = L.map('map').setView([position.coords.latitude, position.coords.longitude], 18);
                var lokasi_kantor = "{{ $lok_kantor->lokasi_cabang ?? '' }}";
                if (lokasi_kantor && lokasi_kantor.indexOf(",") !== -1) {
                    var lok = lokasi_kantor.split(",");
                    var lat_kantor = lok[0];
                    var long_kantor = lok[1];
                    var radius = "{{ $lok_kantor->radius_cabang ?? 50 }}";
                    L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                        maxZoom: 20,
                        subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
                    }).addTo(map);
                    var marker = L.marker([position.coords.latitude, position.coords.longitude]).addTo(map);
                    var circle = L.circle([lat_kantor, long_kantor], {
                        color: 'red',
                        fillColor: '#f03',
                        fillOpacity: 0.5,
                        radius: radius
                    }).addTo(map);
                }
            } catch(e) {
                console.log(e);
            }
        }

        function errorCallback(err) {
            console.warn("Geolocation warning:", err);
        }

        $("#takeabsen").click(function(e) {
            var $btn = $(this);
            var originalHtml = $btn.html();
            var image = null;

            try {
                Webcam.snap(function(uri) {
                    image = uri;
                });
            } catch(err) {
                console.error("Webcam snap error:", err);
            }

            if (!image) {
                Swal.fire({
                    title: 'Kamera Belum Siap!',
                    text: 'Foto selfie belum tertangkap. Pastikan kamera aktif dan wajah terlihat jelas.',
                    icon: 'warning'
                });
                return false;
            }

            // Disable button dan tampilkan spinner agar tidak double submit
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses Presensi...');

            var lokasi = $("#lokasi").val();
            $.ajax({
                type: 'POST',
                url: '/presensi/store',
                data: {
                    _token: "{{ csrf_token() }}",
                    image: image,
                    lokasi: lokasi,
                    kode_jam_kerja: "{{ $kode_jam_kerja }}"
                },
                cache: false,
                timeout: 15000,
                success: function(respond) {
                    var status = respond.split("|");
                    if (status[0] == "success") {
                        if (status[2] == "in") {
                            try { notifikasi_in.play(); } catch(e){}
                        } else {
                            try { notifikasi_out.play(); } catch(e){}
                        }
                        Swal.fire({
                            title: 'Berhasil !',
                            text: status[1],
                            icon: 'success'
                        });
                        setTimeout(function() {
                            location.href = '/dashboard';
                        }, 2500);
                    } else {
                        $btn.prop('disabled', false).html(originalHtml);
                        if (status[2] == "radius") {
                            try { radius_sound.play(); } catch(e){}
                        }
                        Swal.fire({
                            title: 'Error !',
                            text: status[1] || 'Terjadi kesalahan saat memproses presensi.',
                            icon: 'error'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    $btn.prop('disabled', false).html(originalHtml);
                    Swal.fire({
                        title: 'Koneksi Terganggu!',
                        text: 'Gagal menghubungi server. Silakan periksa koneksi internet Anda dan coba lagi.',
                        icon: 'error'
                    });
                }
            });

        });
    </script>
@endpush
