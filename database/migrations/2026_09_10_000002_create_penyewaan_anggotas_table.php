<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penyewaan_anggotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penyewaan_id')->constrained('penyewaans')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('porsi_persen')->default(50);
            $table->string('status')->default('aktif');
            $table->string('ktp_path')->nullable();
            $table->date('tanggal_keluar')->nullable();
            $table->timestamps();

            $table->unique(['penyewaan_id', 'user_id']);
            $table->index(['penyewaan_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penyewaan_anggotas');
    }
};
