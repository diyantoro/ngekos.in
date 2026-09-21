<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('amount')->nullable()->after('requested_plan');
            $table->string('payment_method', 20)->after('status')->nullable();
            $table->string('bukti_path', 255)->nullable()->after('payment_method');
            $table->timestamp('paid_at')->nullable()->after('bukti_path');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_requests', function (Blueprint $table) {
            $table->dropColumn(['amount', 'payment_method', 'bukti_path', 'paid_at']);
        });
    }
};