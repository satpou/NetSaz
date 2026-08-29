<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Package::create([
            'name' => 'Internet 10 Mbps',
            'description' => 'Paket dasar untuk browse dan media sosial',
            'price' => 150000,
            'speed' => '10 Mbps',
            'is_taxable' => true,
        ]);
        \App\Models\Package::create([
            'name' => 'Internet 20 Mbps',
            'description' => 'Paket sedang untuk streaming dan gaming',
            'price' => 250000,
            'speed' => '20 Mbps',
            'is_taxable' => true,
        ]);
        \App\Models\Package::create([
            'name' => 'Internet 50 Mbps',
            'description' => 'Paket premium untuk streaming 4K dan gaming berat',
            'price' => 400000,
            'speed' => '50 Mbps',
            'is_taxable' => false,
        ]);
    }
}
