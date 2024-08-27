<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Client Type User
        User::factory(3)->create([
            'creditScore' => rand(0,50)
        ]);

        // Seller Type User

        User::factory(3)->create([
            'type' => 'seller'
        ]);

    }
}
