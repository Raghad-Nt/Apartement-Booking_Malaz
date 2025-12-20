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
        
        User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'Admin',
            'email' => 'admin@example.com',
            'mobile' => '0912345678',
            'role' => 'admin',
            'status' => 'active',
            'password' => Hash::make('admin password'),
        ]);

        
        
        
        $this->call(ApartmentsTableSeeder::class);
    }
}