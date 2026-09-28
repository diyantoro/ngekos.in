<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Staf (penjaga kos/admin pembantu) ditautkan ke akun pemilik yang mengundangnya.
     * Scope data staf selalu mengikuti pemilik_id ini.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // foreignId sudah membuat index sendiri.
            $table->foreignId('pemilik_id')->nullable()->after('id')
                ->constrained('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['pemilik_id']);
            $table->dropColumn('pemilik_id');
        });
    }
};
