<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->string('nama_agen');
            $table->string('nama_pemilik');
            $table->string('tipe_agen')->default('Kios Eceran');
            $table->string('nomor_whatsapp')->nullable();
            $table->text('alamat_lengkap')->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->foreignId('cabang_id')->constrained('cabangs')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
