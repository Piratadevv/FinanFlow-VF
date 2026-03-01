<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ConfigurationSeeder::class,
            EscompteSeeder::class,
            RefinancementSeeder::class,
            LogSeeder::class,
        ]);
    }
}
