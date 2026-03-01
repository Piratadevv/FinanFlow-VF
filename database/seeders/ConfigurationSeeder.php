<?php

namespace Database\Seeders;

use App\Models\Configuration;
use Illuminate\Database\Seeder;

class ConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        Configuration::create([
            'id' => 1,
            'autorisation_bancaire' => 200000.00,
        ]);
    }
}
