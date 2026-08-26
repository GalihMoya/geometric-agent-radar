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
        Schema::create('cabangs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_cabang');
            $table->string('kode_cabang')->unique(); // tulungagung, blitar, trenggalek
            $table->text('alamat')->nullable();
            $table->string('telepon')->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('warna')->default('#004B87');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cabangs');
    }
};
