<?php

use App\Services\FiturTambahanSchemaService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        FiturTambahanSchemaService::ensureTablesExist();
    }

    public function down()
    {
        Schema::dropIfExists('revisi_absensi');
        Schema::dropIfExists('pengajuan_masalah');
        Schema::dropIfExists('pinjaman_angsuran');
        Schema::dropIfExists('pinjaman_pegawai');
        Schema::dropIfExists('pengumuman_pegawai');
        Schema::dropIfExists('pengumuman');
    }
};
