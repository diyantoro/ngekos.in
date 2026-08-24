<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Urutan penting: role & permission dulu, baru user, lalu data demo.
        $this->call([
            RolesAndPermissionsSeeder::class,
            UserSeeder::class,
            DomainDataSeeder::class,
        ]);
    }
}
