<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('propertis', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('status');
            $table->string('kota')->nullable()->after('alamat');
        });

        Schema::table('kamars', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('propertis', function (Blueprint $table) {
            $table->dropColumn(['foto', 'kota']);
        });

        Schema::table('kamars', function (Blueprint $table) {
            $table->dropColumn('foto');
        });
    }
};
