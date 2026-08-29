<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Customer::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@email.com',
            'phone' => '081234567890',
            'address' => 'Jl. Merdeka No. 1, Jakarta Selatan',
            'package_id' => 1,
            'status' => 'active',
            'join_date' => '2025-01-15',
        ]);
        \App\Models\Customer::create([
            'name' => 'Sari Dewi',
            'email' => 'sari@email.com',
            'phone' => '081234567891',
            'address' => 'Jl. Pahlawan No. 2, Bandung',
            'package_id' => 2,
            'status' => 'active',
            'join_date' => '2025-02-20',
        ]);
        \App\Models\Customer::create([
            'name' => 'Andi Prasetyo',
            'email' => 'andi@email.com',
            'phone' => '081234567892',
            'address' => 'Jl. Sudirman No. 3, Surabaya',
            'package_id' => 3,
            'status' => 'isolated',
            'join_date' => '2024-11-10',
        ]);
        \App\Models\Customer::create([
            'name' => 'Rina Kartika',
            'email' => 'rina@email.com',
            'phone' => '081234567893',
            'address' => 'Jl. Gatot Subroto No. 4, Medan',
            'package_id' => 1,
            'status' => 'active',
            'join_date' => '2025-03-05',
        ]);
        \App\Models\Customer::create([
            'name' => 'Dewi Lestari',
            'email' => 'dewi@email.com',
            'phone' => '081234567894',
            'address' => 'Jl. Ahmad Yani No. 5, Yogyakarta',
            'package_id' => 2,
            'status' => 'active',
            'join_date' => '2025-04-01',
        ]);
    }
}
