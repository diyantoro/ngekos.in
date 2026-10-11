<?php

use App\Enums\LanggananPlan;
use App\Enums\MetodeBayarLangganan;
use App\Enums\PengajuanStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('requested_plan', LanggananPlan::values());
            $table->unsignedBigInteger('amount')->nullable();
            $table->enum('status', PengajuanStatus::values())->default(PengajuanStatus::Pending->value);
            $table->enum('payment_method', MetodeBayarLangganan::values())->nullable();
            $table->string('bukti_path', 255)->nullable();
            $table->timestamp('paid_at')->nullable();
            // Teks panjang -> TEXT, bukan varchar(255).
            $table->text('keterangan')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
            $table->index('user_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_requests');
    }
};
