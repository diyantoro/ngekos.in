<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('propertis', function (Blueprint $table) {
            $table->decimal('harga', 12, 2)->nullable()->after('denda_per_hari');
            $table->string('jenis_harga', 10)->default('bulanan')->after('harga');
        });

        Schema::table('kamars', function (Blueprint $table) {
            $table->string('jenis_harga', 10)->default('bulanan')->after('harga_sewa_bulanan');
        });
    }

    public function down(): void
    {
        Schema::table('propertis', function (Blueprint $table) {
            $table->dropColumn(['harga', 'jenis_harga']);
        });

        Schema::table('kamars', function (Blueprint $table) {
            $table->dropColumn('jenis_harga');
        });
    }
};
