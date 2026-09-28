<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Hapus total fitur staff: nonaktifkan akun staff tersisa,
     * cabut role staff, hapus role, lalu drop users.pemilik_id.
     */
    public function up(): void
    {
        $staffRole = Role::where('name', 'staff')->first();

        if ($staffRole) {
            $staffIds = DB::table('model_has_roles')
                ->where('role_id', $staffRole->id)
                ->where('model_type', 'App\\Models\\User')
                ->pluck('model_id');

            if ($staffIds->isNotEmpty()) {
                DB::table('users')
                    ->whereIn('id', $staffIds)
                    ->whereNull('dinonaktifkan_pada')
                    ->update(['dinonaktifkan_pada' => now()]);

                DB::table('model_has_roles')
                    ->where('role_id', $staffRole->id)
                    ->where('model_type', 'App\\Models\\User')
                    ->delete();
            }

            $staffRole->delete();
        }

        if (Schema::hasColumn('users', 'pemilik_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['pemilik_id']);
                $table->dropColumn('pemilik_id');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'pemilik_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('pemilik_id')->nullable()->after('id')
                    ->constrained('users')->cascadeOnDelete();
            });
        }

        Role::firstOrCreate(['name' => 'staff']);
    }
};
