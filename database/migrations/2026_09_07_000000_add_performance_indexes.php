<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_pesans', function (Blueprint $table) {
            $table->index(['anak_kos_id', 'dibaca_pada']);
            $table->index(['pengirim_id', 'dibaca_pada']);
        });

        Schema::table('propertis', function (Blueprint $table) {
            $table->index(['status', 'kota']);
        });
    }

    public function down(): void
    {
        Schema::table('chat_pesans', function (Blueprint $table) {
            $table->dropIndex(['anak_kos_id', 'dibaca_pada']);
            $table->dropIndex(['pengirim_id', 'dibaca_pada']);
        });

        Schema::table('propertis', function (Blueprint $table) {
            $table->dropIndex(['status', 'kota']);
        });
    }
};