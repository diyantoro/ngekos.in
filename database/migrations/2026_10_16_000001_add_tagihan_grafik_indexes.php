<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Index grafik & dashboard pemilik (tahap 2 performa).
     * Query berat: Tagihan::whereNotIn('status',...)->where('jatuh_tempo'|'created_at'|'updated_at', ...)
     * yang sebelumnya full-scan saat data produksi membesar.
     */
    public function up(): void
    {
        Schema::table('tagihans', function (Blueprint $table) {
            $table->index(['status', 'jatuh_tempo'], 'tagihans_status_jatuh_tempo_index');
            $table->index(['status', 'created_at'], 'tagihans_status_created_at_index');
            $table->index(['status', 'updated_at'], 'tagihans_status_updated_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('tagihans', function (Blueprint $table) {
            $table->dropIndex('tagihans_status_jatuh_tempo_index');
            $table->dropIndex('tagihans_status_created_at_index');
            $table->dropIndex('tagihans_status_updated_at_index');
        });
    }
};
