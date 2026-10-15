<!doctype html>
<!--
* Tabler - Premium and Open Source dashboard template with responsive and high quality UI.
* @version 1.0.0-beta17
* @link https://tabler.io
* Copyright 2018-2023 The Tabler Authors
* Copyright 2018-2023 codecalm.net Paweł Kuna
* Licensed under MIT (https://github.com/tabler/tabler/blob/master/LICENSE)
-->
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Dashboard - Tabler - Premium and Open Source dashboard template with responsive and high quality UI.</title>
    <!-- CSS files -->
    <link href="{{ asset('tabler/dist/css/tabler.min.css?1674944402') }}" rel="stylesheet" />
    <link href="{{ asset('tabler/dist/css/tabler-flags.min.css?1674944402') }}" rel="stylesheet" />
    <link href="{{ asset('tabler/dist/css/tabler-payments.min.css?1674944402') }}" rel="stylesheet" />
    <link href="{{ asset('tabler/dist/css/tabler-vendors.min.css?1674944402') }}" rel="stylesheet" />
    <link href="{{ asset('tabler/dist/css/demo.min.css?1674944402') }}" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/css/datepicker.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" integrity="sha256-kLaT2GOSpHechhsozzB+flnD+zUyjE2LlfWPgU04xyI=" crossorigin="" />
    <style>
        @import url('https://rsms.me/inter/inter.css');

        :root {
            --tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
        }

        body {
            font-feature-settings: "cv03", "cv04", "cv11";
        }

        .table-responsive table {
            white-space: nowrap !important;
        }

        /* Floating top scrollbar */
        .floating-scrollbar-wrapper {
            overflow-x: auto;
            overflow-y: hidden;
            position: sticky;
            bottom: 0;
            z-index: 100;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }
        .floating-scrollbar-inner {
            height: 12px;
        }

        .preview-foto {
            cursor: pointer !important;
            transition: transform 0.15s ease-in-out, box-shadow 0.15s ease-in-out !important;
        }
        .preview-foto:hover {
            transform: scale(1.18) !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3) !important;
            z-index: 5 !important;
            position: relative;
        }

    </style>
</head>
<body>
    <script src="{{ asset('tabler/dist/js/demo-theme.min.js?1674944402') }}"></script>
    <div class="page">
        <!-- Sidebar -->
        @include('layouts.admin.sidebar')
        <!-- Navbar -->
        @include('layouts.admin.header')
        <div class="page-wrapper">
            @yield('content')
            @include('layouts.admin.footer')
        </div>
    </div>

    <!-- Libs JS -->
    <script src="{{ asset('tabler/dist/libs/apexcharts/dist/apexcharts.min.js?1674944402') }}" defer></script>
    <script src="{{ asset('tabler/dist/libs/jsvectormap/dist/js/jsvectormap.min.js?1674944402') }}" defer></script>
    <script src="{{ asset('tabler/dist/libs/jsvectormap/dist/maps/world.js?1674944402') }}" defer></script>
    <script src="{{ asset('tabler/dist/libs/jsvectormap/dist/maps/world-merc.js?1674944402') }}" defer></script>

    <!-- Tabler Core -->
    <script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.6.0/Chart.min.js"></script>
    <script src="{{ asset('tabler/dist/js/tabler.min.js?1674944402') }}" defer></script>
    <script src="{{ asset('tabler/dist/js/demo.min.js?1674944402') }}" defer></script>
    <script src="{{ asset('assets/js/lib/sweetalert.js') }}"></script>
    <script src="{{ asset('assets/js/lib/jquery.mask.min.js') }}"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/js/bootstrap-datepicker.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js" integrity="sha256-WBkoXOwTeyKclOHuWtc+i2uENFpDZ9YPdf5Hf+D7ewM=" crossorigin=""></script>
    @stack('myscript')

    {{-- Floating Horizontal Scrollbar for wide tables --}}
    <script>
    $(document).ready(function() {
        function initFloatingScrollbar() {
            // Find all wide tables (tables wider than their container)
            $('table').each(function() {
                var $table = $(this);
                var $parent = $table.parent();

                // Skip if already processed
                if ($parent.hasClass('floating-scrollbar-processed')) return;
                // Only process if table is wider than viewport
                if ($table.width() <= $(window).width() - 200) return;

                $parent.addClass('floating-scrollbar-processed');

                // Create floating scrollbar element
                var $scrollWrapper = $('<div class="floating-scrollbar-wrapper"></div>');
                var $scrollInner = $('<div class="floating-scrollbar-inner"></div>');
                $scrollWrapper.append($scrollInner);
                $parent.after($scrollWrapper);

                // Set width of inner div to match table width
                function updateScrollWidth() {
                    $scrollInner.width($table.width());
                    var rect = $parent[0].getBoundingClientRect();
                    $scrollWrapper.css({
                        'width': $parent.outerWidth() + 'px',
                        'margin-left': 0
                    });
                }
                updateScrollWidth();

                // Sync: floating scrollbar → table container
                $scrollWrapper.on('scroll', function() {
                    $parent.scrollLeft($scrollWrapper.scrollLeft());
                });

                // Sync: table container scroll → floating scrollbar  
                $parent.on('scroll', function() {
                    $scrollWrapper.scrollLeft($parent.scrollLeft());
                });

                // Show/hide based on table visibility in viewport
                $(window).on('scroll resize', function() {
                    var rect = $parent[0].getBoundingClientRect();
                    var tableBottom = rect.bottom;
                    var tableTop = rect.top;
                    var windowHeight = $(window).height();

                    // Show floating scrollbar if table extends beyond viewport
                    if (tableTop < windowHeight && tableBottom > windowHeight) {
                        $scrollWrapper.show();
                    } else {
                        $scrollWrapper.hide();
                    }
                    updateScrollWidth();
                });

                // Trigger initial check
                $(window).trigger('scroll');
            });
        }

        // Init on page load
        initFloatingScrollbar();

        // Re-init after AJAX loads (for dynamic tables like monitoring)
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length > 0) {
                    initFloatingScrollbar();
                }
            });
        });
        observer.observe(document.body, { childList: true, subtree: true });
    });
    </script>

    <!-- Modal Preview Foto Selfie / Profil -->
    <div class="modal modal-blur fade" id="modal-previewfoto" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 520px;">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header py-2" style="background-color: #206bc4; color: #fff;">
                    <div>
                        <h5 class="modal-title text-white mb-0" id="previewfoto-nama">Preview Foto Selfie</h5>
                        <small class="text-white-50" id="previewfoto-sub">Detail Presensi Karyawan</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-2 text-center" style="background-color: #0f172a; min-height: 320px; display: flex; align-items: center; justify-content: center;">
                    <img id="previewfoto-img" src="" alt="Foto Selfie Presensi" class="img-fluid rounded" style="max-height: 65vh; max-width: 100%; object-fit: contain; box-shadow: 0 4px 16px rgba(0,0,0,0.5);">
                </div>
                <div class="modal-footer py-2 d-flex justify-content-between align-items-center bg-light">
                    <div class="d-flex align-items-center gap-1 flex-wrap">
                        <span class="badge bg-primary text-white" id="previewfoto-badge-tipe">Presensi</span>
                        <span class="badge bg-secondary text-white" id="previewfoto-badge-jam">Jam</span>
                        <span class="badge bg-azure text-white" id="previewfoto-badge-cabang">Cabang</span>
                    </div>
                    <div class="d-flex gap-2">
                        <a id="previewfoto-link" href="#" target="_blank" class="btn btn-outline-primary btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-zoom-in" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                                <path d="M7 10l6 0" />
                                <path d="M10 7l0 6" />
                                <path d="M21 21l-6 -6" />
                            </svg>
                            Resolusi Penuh
                        </a>
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" data-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        $(document).on('click', '.preview-foto', function(e) {
            e.preventDefault();
            var fotoUrl = $(this).attr('data-foto') || $(this).attr('src');
            var nama = $(this).attr('data-nama') || 'Foto Karyawan';
            var nik = $(this).attr('data-nik') || '';
            var tipe = $(this).attr('data-tipe') || 'Foto Selfie';
            var jam = $(this).attr('data-jam') || '';
            var tgl = $(this).attr('data-tgl') || '';
            var cabang = $(this).attr('data-cabang') || '';
            var dept = $(this).attr('data-dept') || '';
            var status = $(this).attr('data-status') || '';

            $('#previewfoto-nama').text(nama + (nik ? ' (' + nik + ')' : ''));

            var subDetails = [];
            if (tipe) subDetails.push(tipe);
            if (tgl) subDetails.push(tgl);
            if (jam) subDetails.push('Pukul ' + jam);
            if (status) subDetails.push(status);
            $('#previewfoto-sub').text(subDetails.join(' • '));

            $('#previewfoto-badge-tipe').text(tipe || 'Foto');
            if (jam) {
                $('#previewfoto-badge-jam').text(jam).show();
            } else {
                $('#previewfoto-badge-jam').hide();
            }
            if (cabang) {
                $('#previewfoto-badge-cabang').text(cabang).show();
            } else {
                $('#previewfoto-badge-cabang').hide();
            }

            $('#previewfoto-img').attr('src', fotoUrl);
            $('#previewfoto-link').attr('href', fotoUrl);

            // Buka modal secara aman untuk Bootstrap 5 & 3
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                try {
                    var modalEl = document.getElementById('modal-previewfoto');
                    var myModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    myModal.show();
                } catch (err) {
                    $('#modal-previewfoto').modal('show');
                }
            } else if ($.fn.modal) {
                $('#modal-previewfoto').modal('show');
            }
        });

        $(document).on('click', '#modal-previewfoto [data-bs-dismiss="modal"], #modal-previewfoto [data-dismiss="modal"]', function() {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                try {
                    var modalEl = document.getElementById('modal-previewfoto');
                    var inst = bootstrap.Modal.getInstance(modalEl);
                    if (inst) inst.hide();
                } catch (e) {}
            }
            $('#modal-previewfoto').modal('hide');
        });
    });
    </script>
</body>
</html>
