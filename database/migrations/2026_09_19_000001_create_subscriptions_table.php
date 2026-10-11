<?php

use App\Enums\LanggananPlan;
use App\Enums\LanggananStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('plan', LanggananPlan::values())->default(LanggananPlan::Free->value);
            $table->enum('status', LanggananStatus::values())->default(LanggananStatus::Active->value);
            $table->boolean('is_trial')->default(false);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index('user_id');
            $table->index('status');
            $table->index('expires_at');
            $table->index('is_trial');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
