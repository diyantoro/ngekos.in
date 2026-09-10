<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pindahkan foto tunggal lama (propertis.foto / kamars.foto)
     * menjadi baris cover pertama di tabel galeri baru.
     */
    public function up(): void
    {
        if (! Schema::hasTable('properti_fotos') || ! Schema::hasTable('kamar_fotos')) {
            return;
        }

        if (Schema::hasColumn('propertis', 'foto')) {
            DB::table('propertis')->whereNotNull('foto')->orderBy('id')->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    if (! $row->foto) {
                        continue;
                    }

                    $sudah = DB::table('properti_fotos')->where('properti_id', $row->id)->exists();

                    if (! $sudah) {
                        DB::table('properti_fotos')->insert([
                            'properti_id' => $row->id,
                            'path' => $row->foto,
                            'urutan' => 0,
                            'is_cover' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            });
        }

        if (Schema::hasColumn('kamars', 'foto')) {
            DB::table('kamars')->whereNotNull('foto')->orderBy('id')->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    if (! $row->foto) {
                        continue;
                    }

                    $sudah = DB::table('kamar_fotos')->where('kamar_id', $row->id)->exists();

                    if (! $sudah) {
                        DB::table('kamar_fotos')->insert([
                            'kamar_id' => $row->id,
                            'path' => $row->foto,
                            'urutan' => 0,
                            'is_cover' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        // Backfill tidak di-rollback (data galeri dibiarkan) agar aman.
    }
};
