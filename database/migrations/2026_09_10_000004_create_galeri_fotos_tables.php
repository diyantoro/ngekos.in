<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properti_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('properti_id')->constrained('propertis')->cascadeOnDelete();
            $table->string('path');
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->boolean('is_cover')->default(false);
            $table->timestamps();

            $table->index(['properti_id', 'urutan']);
        });

        Schema::create('kamar_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kamar_id')->constrained('kamars')->cascadeOnDelete();
            $table->string('path');
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->boolean('is_cover')->default(false);
            $table->timestamps();

            $table->index(['kamar_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kamar_fotos');
        Schema::dropIfExists('properti_fotos');
    }
};
