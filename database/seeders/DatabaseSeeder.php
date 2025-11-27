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
        $this->call([
            DirectionSeeder::class,
            ServiceSeeder::class,
            UserSeeder::class,
            RolePermissionSeeder::class,
            CourrierSeeder::class,
        ]);
    }
}
