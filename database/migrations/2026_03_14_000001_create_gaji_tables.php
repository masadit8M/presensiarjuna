<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('gaji_master')) {
            Schema::create('gaji_master', function (Blueprint $table) {
                $table->id();
                $table->string('nik', 30)->unique();
                $table->decimal('gaji_pokok', 12, 2)->default(0);
                $table->decimal('tunjangan_transportasi', 12, 2)->default(150000);
                $table->decimal('tunjangan_jabatan', 12, 2)->default(0);
                $table->decimal('tunjangan_konsumsi', 12, 2)->default(0);
                $table->decimal('tunjangan_kehadiran', 12, 2)->default(0);
                $table->decimal('tunjangan_beras', 12, 2)->default(0);
                $table->decimal('tunjangan_fungsional', 12, 2)->default(0);
                $table->decimal('tunjangan_wali_kelas', 12, 2)->default(0);
                $table->decimal('tunjangan_masa_kerja', 12, 2)->default(0);
                $table->decimal('tarif_honor_kegiatan', 12, 2)->default(0);
                $table->decimal('tarif_ekskul', 12, 2)->default(0);
                $table->decimal('tarif_lembur', 12, 2)->default(0);
                $table->decimal('bpjs_kesehatan', 12, 2)->default(0);
                $table->decimal('bpjs_ketenagakerjaan', 12, 2)->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('penggajian_periode')) {
            Schema::create('penggajian_periode', function (Blueprint $table) {
                $table->id();
                $table->string('kode_periode', 50)->unique();
                $table->string('nama_periode', 100);
                $table->integer('bulan');
                $table->integer('tahun');
                $table->date('tgl_mulai');
                $table->date('tgl_selesai');
                $table->integer('hk_standar')->default(26);
                $table->integer('total_siswa_tpa')->default(0);
                $table->enum('status', ['draft', 'final', 'terbayar'])->default('draft');
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('penggajian_detail')) {
            Schema::create('penggajian_detail', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('periode_id')->index();
                $table->string('nik', 30)->index();
                $table->string('kode_cabang', 20)->nullable();
                $table->string('kode_dept', 20)->nullable();
                $table->string('jabatan', 100)->nullable();
                $table->integer('hk_standar')->default(26);
                $table->integer('hadir')->default(0);
                $table->integer('izin')->default(0);
                $table->integer('sakit')->default(0);
                $table->integer('alpha')->default(0);
                $table->decimal('terlambat_jam', 6, 2)->default(0);
                $table->decimal('gaji_pokok', 12, 2)->default(0);
                $table->decimal('tunjangan_transportasi', 12, 2)->default(0);
                $table->decimal('tunjangan_jabatan', 12, 2)->default(0);
                $table->decimal('tunjangan_konsumsi', 12, 2)->default(0);
                $table->decimal('tunjangan_kehadiran', 12, 2)->default(0);
                $table->decimal('tunjangan_lainnya', 12, 2)->default(0);
                $table->decimal('honor_kegiatan', 12, 2)->default(0);
                $table->decimal('honor_ekskul', 12, 2)->default(0);
                $table->decimal('upah_lembur', 12, 2)->default(0);
                $table->decimal('insentif_pool_tpa', 12, 2)->default(0);
                $table->decimal('reward_disiplin', 12, 2)->default(0);
                $table->decimal('bonus_tambahan', 12, 2)->default(0);
                $table->decimal('potongan_absen', 12, 2)->default(0);
                $table->decimal('potongan_terlambat', 12, 2)->default(0);
                $table->decimal('potongan_kasbon', 12, 2)->default(0);
                $table->decimal('potongan_bpjs', 12, 2)->default(0);
                $table->decimal('potongan_lainnya', 12, 2)->default(0);
                $table->decimal('total_penghasilan', 12, 2)->default(0);
                $table->decimal('total_potongan', 12, 2)->default(0);
                $table->decimal('gaji_bersih', 12, 2)->default(0);
                $table->text('catatan')->nullable();
                $table->tinyInteger('status_kirim_wa')->default(0);
                $table->dateTime('waktu_kirim_wa')->nullable();
                $table->string('pdf_path', 255)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penggajian_detail');
        Schema::dropIfExists('penggajian_periode');
        Schema::dropIfExists('gaji_master');
    }
};
