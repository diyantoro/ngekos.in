<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesan_bantuans', function (Blueprint $table) {
            $table->timestamp('dibaca_pada')->nullable()->after('dibalas_at');
        });
    }

    public function down(): void
    {
        Schema::table('pesan_bantuans', function (Blueprint $table) {
            $table->dropColumn('dibaca_pada');
        });
    }
};