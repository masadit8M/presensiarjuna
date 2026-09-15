<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>A4</title>

    <!-- Normalize or reset CSS with your favorite library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/7.0.0/normalize.min.css">

    <!-- Load paper.css for happy printing -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/paper-css/0.4.1/paper.css">

    <!-- Set page size here: A5, A4 or A3 -->
    <!-- Set also "landscape" if you need -->
    <style>
        @page {
            size: A4
        }

        #title {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 18px;
            font-weight: bold;
        }

        .tabeldatakaryawan {
            margin-top: 40px;
        }

        .tabeldatakaryawan tr td {
            padding: 5px;
        }

        .tabelpresensi {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .tabelpresensi tr th {
            border: 1px solid #131212;
            padding: 8px;
            background-color: #dbdbdb;
            font-size: 10px
        }

        .tabelpresensi tr td {
            border: 1px solid #131212;
            padding: 5px;
            font-size: 12px;
        }

        .foto {
            width: 40px;
            height: 30px;

        }


        body.A4.landscape .sheet {
            width: 297mm !important;
            height: auto !important;
        }
    </style>
</head>

<!-- Set "A5", "A4" or "A3" for class name -->
<!-- Set also "landscape" if you need -->

<body class="A4 landscape">
    <?php
    function selisih($jam_masuk, $jam_keluar)
    {
        [$h, $m, $s] = explode(':', $jam_masuk);
        $dtAwal = mktime($h, $m, $s, '1', '1', '1');
        [$h, $m, $s] = explode(':', $jam_keluar);
        $dtAkhir = mktime($h, $m, $s, '1', '1', '1');
        $dtSelisih = $dtAkhir - $dtAwal;
        $totalmenit = $dtSelisih / 60;
        $jam = explode('.', $totalmenit / 60);
        $sisamenit = $totalmenit / 60 - $jam[0];
        $sisamenit2 = $sisamenit * 60;
        $jml_jam = $jam[0];
        return $jml_jam . ':' . round($sisamenit2);
    }
    ?>
    <!-- Each sheet element should have the class "sheet" -->
    <!-- "padding-**mm" is optional: you can set 10, 15, 20 or 25 -->
    <section class="sheet padding-10mm">

        <table style="width: 100%">
            <tr>
                <td style="width: 30px">
                    <img src="{{ asset('assets/img/logopresensi.png') }}" width="70" height="70" alt="">
                </td>
                <td>
                    <span id="title">
                        REKAP PRESENSI KARYAWAN<br>
                        PERIODE {{ strtoupper($namabulan[$bulan]) }} {{ $tahun }}<br>
                        PT. ADAM ADIFA<br>
                    </span>
                    <span><i>Jln. H. Dahlan No. 75, Kecamatan Sindangrasa, Kabupaten Ciamis</i></span>
                </td>
            </tr>
        </table>
        <table class="tabelpresensi">
            <tr>
                <th rowspan="2">Nik</th>
                <th rowspan="2">Nama Karyawan</th>
                <th colspan="{{ $jmlhari }}">Bulan {{ $namabulan[$bulan] }} {{ $tahun }}</th>
                <th rowspan="2">H</th>
                <th rowspan="2">I</th>
                <th rowspan="2">S</th>
                <th rowspan="2">C</th>
                <th rowspan="2">A</th>
                <th rowspan="2" style="background:#d4edda; color:#155724;">Pagi<br>≤06:30</th>
            </tr>
            <tr>
                @foreach ($rangetanggal as $d)
                    @if ($d != null)
                        <th>{{ date('d', strtotime($d)) }}</th>
                    @endif
                @endforeach

            </tr>
            @foreach ($rekap as $r)
                <tr>
                    <td>{{ $r->nik }}</td>
                    <td>{{ $r->nama_lengkap }}</td>

                    <?php
                    $jml_hadir  = 0;
                    $jml_izin   = 0;
                    $jml_sakit  = 0;
                    $jml_cuti   = 0;
                    $jml_alpa   = 0;
                    $jml_pagi_disiplin = 0;
                    $color = "";
                    for($i=1; $i<=$jmlhari; $i++){
                        $tgl = "tgl_".$i;
                        $tgl_presensi = $rangetanggal[$i-1];
                        $search_items = [
                            'nik' => $r->nik,
                            'tanggal_libur' => $tgl_presensi
                        ];
                        $ceklibur = cekkaryawanlibur($datalibur, $search_items);

                        $datapresensi = explode("|", $r->$tgl);
                        if ($r->$tgl != NULL) {
                            $status = $datapresensi[2];
                            $jamIn  = (!empty($datapresensi[0]) && $datapresensi[0] !== 'NA') ? $datapresensi[0] : '';
                        } else {
                            $status = "";
                            $jamIn  = "";
                        }

                        $cekhari = gethari(date('D', strtotime($tgl_presensi)));
                        if ($status == "h") {
                            $jml_hadir += 1;
                            $color = "white";
                        }

                        if ($status == "i") {
                            $jml_izin += 1;
                            $color = "#ffbb00";
                        }

                        if ($status == "s") {
                            $jml_sakit += 1;
                            $color = "#34a1eb";
                        }

                        if ($status == "c") {
                            $jml_cuti += 1;
                            $color = "#a600ff";
                        }


                        if (empty($status) && empty($ceklibur) && $cekhari != 'Minggu') {
                            $jml_alpa += 1;
                            $color = "red";
                        }

                        if (!empty($ceklibur)) {
                            $color = "green";
                        }


                        if ($cekhari == "Minggu") {
                            $color = "orange";
                        }

                        // Jam In color & disiplin pagi counter
                        $jamInDisplay = '';
                        $jamInColor   = '#333';
                        if (!empty($jamIn)) {
                            $jamShort = substr($jamIn, 0, 5); // HH:MM
                            $jamInDisplay = $jamShort;
                            if ($jamShort <= '06:30') {
                                $jamInColor = '#1a7a1a'; // hijau tua = disiplin pagi ✓
                                $jml_pagi_disiplin += 1;
                            } elseif ($jamShort <= '07:00') {
                                $jamInColor = '#b35c00'; // coklat/orange = tepat waktu
                            } else {
                                $jamInColor = '#cc0000'; // merah = terlambat
                            }
                        }
                    ?>
                    <td style="background-color: {{ $color }}; text-align:center; padding:2px 3px; vertical-align:top;">
                        <div style="font-weight:bold; font-size:11px;">{{ $status }}</div>
                        @if (!empty($jamInDisplay))
                            <div style="font-size:9px; color:{{ $jamInColor }}; font-weight:bold; line-height:1.2;">{{ $jamInDisplay }}</div>
                        @endif
                    </td>
                    <?php
                    }
                ?>
                    <td>{{ !empty($jml_hadir) ? $jml_hadir : '' }}</td>
                    <td>{{ !empty($jml_izin) ? $jml_izin : '' }}</td>
                    <td>{{ !empty($jml_sakit) ? $jml_sakit : '' }}</td>
                    <td>{{ !empty($jml_cuti) ? $jml_cuti : '' }}</td>
                    <td>{{ !empty($jml_alpa) ? $jml_alpa : '' }}</td>
                    <td style="text-align:center; background:#d4edda; color:#155724; font-weight:bold;">
                        {{ $jml_pagi_disiplin > 0 ? $jml_pagi_disiplin : '' }}
                    </td>
                </tr>
            @endforeach
        </table>
        <h4>Keterangan Libur :</h4>
        <ol>
            @foreach ($harilibur as $d)
                <li>{{ date('d-m-Y', strtotime($d->tanggal_libur)) }} - {{ $d->keterangan }}</li>
            @endforeach
        </ol>
        <table width="100%" style="margin-top:100px">
            <tr>
                <td></td>
                <td style="text-align: center">Tasikmalaya, {{ date('d-m-Y') }}</td>
            </tr>
            <tr>
                <td style="text-align: center; vertical-align:bottom" height="100px">
                    <u>Qiana Aqila</u><br>
                    <i><b>HRD Manager</b></i>
                </td>
                <td style="text-align: center; vertical-align:bottom">
                    <u>Daffa</u><br>
                    <i><b>Direktur</b></i>
                </td>
            </tr>
        </table>


    </section>

</body>

</html>
