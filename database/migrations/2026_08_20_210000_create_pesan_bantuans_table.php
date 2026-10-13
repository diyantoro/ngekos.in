<?php

use App\Enums\BantuanStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesan_bantuans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama', 100);
            $table->string('email', 255);
            $table->string('subjek', 150)->nullable();
            $table->text('pesan');
            $table->enum('status', BantuanStatus::values())->default(BantuanStatus::Baru->value);
            $table->text('balasan')->nullable();
            $table->foreignId('dibalas_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dibalas_at')->nullable();
            $table->timestamp('dibaca_pada')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesan_bantuans');
    }
};
