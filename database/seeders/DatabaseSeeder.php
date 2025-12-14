<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'mobile' => '0912345678',
            'role' => 'admin',
            'status' => 'active',
            'password' => Hash::make('secret_admin_password'),
        ]);

        // Create 5 sample renters
        User::factory(5)->create([
            'role' => 'renter',
            'status' => 'active'
        ]);

        // Call apartments seeder
        $this->call(ApartmentsTableSeeder::class);
    }
}