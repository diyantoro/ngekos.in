<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Merapatkan panjang kolom varchar agar tidak boros:
     * kota 50, no_hp 15, nama user/kos 100, nama kamar 50, dsb.
     * Kolom path file, TEXT konten, dan ENUM tidak diubah.
     *
     * Hanya jalan di MySQL (ALTER ... MODIFY). Di SQLite (pengujian)
     * panjang varchar tidak diberlakukan sehingga migrasi dilewati.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Pangkas data lama yang kepanjangan dulu agar MODIFY tidak gagal
        // pada MySQL strict mode.
        DB::table('users')->whereRaw('CHAR_LENGTH(nama) > 100')->update(['nama' => DB::raw('LEFT(nama, 100)')]);
        DB::table('users')->whereRaw('CHAR_LENGTH(no_hp) > 15')->update(['no_hp' => DB::raw('LEFT(no_hp, 15)')]);
        DB::table('propertis')->whereRaw('CHAR_LENGTH(nama) > 100')->update(['nama' => DB::raw('LEFT(nama, 100)')]);
        DB::table('propertis')->whereRaw('CHAR_LENGTH(kota) > 50')->update(['kota' => DB::raw('LEFT(kota, 50)')]);
        DB::table('propertis')->whereRaw('CHAR_LENGTH(alamat) > 500')->update(['alamat' => DB::raw('LEFT(alamat, 500)')]);
        DB::table('kamars')->whereRaw('CHAR_LENGTH(nama) > 50')->update(['nama' => DB::raw('LEFT(nama, 50)')]);
        DB::table('tagihans')->whereRaw('CHAR_LENGTH(periode) > 100')->update(['periode' => DB::raw('LEFT(periode, 100)')]);
        DB::table('pembayarans')->whereRaw('CHAR_LENGTH(nomor_kwitansi) > 50')->update(['nomor_kwitansi' => DB::raw('LEFT(nomor_kwitansi, 50)')]);
        DB::table('pesan_bantuans')->whereRaw('CHAR_LENGTH(nama) > 100')->update(['nama' => DB::raw('LEFT(nama, 100)')]);
        DB::table('pesan_bantuans')->whereRaw('CHAR_LENGTH(subjek) > 150')->update(['subjek' => DB::raw('LEFT(subjek, 150)')]);
        DB::table('pengaturans')->whereRaw('CHAR_LENGTH(kunci) > 100')->update(['kunci' => DB::raw('LEFT(kunci, 100)')]);

        $cs = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';

        DB::statement("ALTER TABLE `users` MODIFY `nama` VARCHAR(100) {$cs} NOT NULL");
        DB::statement("ALTER TABLE `users` MODIFY `no_hp` VARCHAR(15) {$cs} NULL");
        DB::statement("ALTER TABLE `propertis` MODIFY `nama` VARCHAR(100) {$cs} NOT NULL");
        DB::statement("ALTER TABLE `propertis` MODIFY `kota` VARCHAR(50) {$cs} NULL");
        DB::statement("ALTER TABLE `propertis` MODIFY `alamat` VARCHAR(500) {$cs} NULL");
        DB::statement("ALTER TABLE `kamars` MODIFY `nama` VARCHAR(50) {$cs} NOT NULL");
        DB::statement("ALTER TABLE `tagihans` MODIFY `periode` VARCHAR(100) {$cs} NOT NULL");
        DB::statement("ALTER TABLE `pembayarans` MODIFY `nomor_kwitansi` VARCHAR(50) {$cs} NULL");
        DB::statement("ALTER TABLE `pesan_bantuans` MODIFY `nama` VARCHAR(100) {$cs} NOT NULL");
        DB::statement("ALTER TABLE `pesan_bantuans` MODIFY `subjek` VARCHAR(150) {$cs} NULL");
        DB::statement("ALTER TABLE `pengaturans` MODIFY `kunci` VARCHAR(100) {$cs} NOT NULL");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Kembalikan ke ukuran semula. Index yang ada dipertahankan MySQL.
        $cs = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';

        DB::statement("ALTER TABLE `users` MODIFY `nama` VARCHAR(255) {$cs} NOT NULL");
        DB::statement("ALTER TABLE `users` MODIFY `no_hp` VARCHAR(255) {$cs} NULL");
        DB::statement("ALTER TABLE `propertis` MODIFY `nama` VARCHAR(255) {$cs} NOT NULL");
        DB::statement("ALTER TABLE `propertis` MODIFY `kota` VARCHAR(255) {$cs} NULL");
        DB::statement("ALTER TABLE `propertis` MODIFY `alamat` TEXT {$cs} NULL");
        DB::statement("ALTER TABLE `kamars` MODIFY `nama` VARCHAR(255) {$cs} NOT NULL");
        DB::statement("ALTER TABLE `tagihans` MODIFY `periode` VARCHAR(255) {$cs} NOT NULL");
        DB::statement("ALTER TABLE `pembayarans` MODIFY `nomor_kwitansi` VARCHAR(255) {$cs} NULL");
        DB::statement("ALTER TABLE `pesan_bantuans` MODIFY `nama` VARCHAR(255) {$cs} NOT NULL");
        DB::statement("ALTER TABLE `pesan_bantuans` MODIFY `subjek` VARCHAR(255) {$cs} NULL");
        DB::statement("ALTER TABLE `pengaturans` MODIFY `kunci` VARCHAR(255) {$cs} NOT NULL");
        // MODIFY mempertahankan index (termasuk unik kunci pengaturans).
    }
};
