<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('propertis', function (Blueprint $table) {
            $table->string('tipe_hunian', 10)->nullable()->after('status');
            $table->index('tipe_hunian');
        });
    }

    public function down(): void
    {
        Schema::table('propertis', function (Blueprint $table) {
            $table->dropIndex(['tipe_hunian']);
            $table->dropColumn('tipe_hunian');
        });
    }
};
