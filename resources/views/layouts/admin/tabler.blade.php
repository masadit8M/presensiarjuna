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
</body>
</html>
