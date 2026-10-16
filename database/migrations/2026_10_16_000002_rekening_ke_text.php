<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Kolom rekening dienkripsi di level model (cast 'encrypted'):
     * payload enkripsi (IV + MAC + base64) jauh melampaui VARCHAR(50),
     * jadi kolom diperlebar ke TEXT agar tidak terpotong.
     * SQLite (dipakai test) tidak menegakkan panjang kolom: lewati.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE propertis MODIFY COLUMN bank_nama TEXT NULL');
        DB::statement('ALTER TABLE propertis MODIFY COLUMN no_rekening TEXT NULL');
        DB::statement('ALTER TABLE propertis MODIFY COLUMN rekening_nama TEXT NULL');
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE propertis MODIFY COLUMN bank_nama VARCHAR(50) NULL');
        DB::statement('ALTER TABLE propertis MODIFY COLUMN no_rekening VARCHAR(50) NULL');
        DB::statement('ALTER TABLE propertis MODIFY COLUMN rekening_nama VARCHAR(100) NULL');
    }
};
