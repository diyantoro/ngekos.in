<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_pesans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('properti_id')->constrained('propertis')->cascadeOnDelete();
            $table->foreignId('anak_kos_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pengirim_id')->constrained('users')->cascadeOnDelete();
            $table->text('isi');
            $table->timestamp('dibaca_pada')->nullable();
            $table->timestamps();
            $table->index(['properti_id', 'anak_kos_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_pesans');
    }
};
