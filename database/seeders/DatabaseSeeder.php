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
        
        // Check if admin user already exists before creating
        if (!User::where('email', 'mohamadkhallouff@gmail.com')->exists()) {
            User::factory()->create([
                'first_name' => 'Mohamad',
                'last_name' => 'Khallouff',
                'email' => 'mohamadkhallouff@gmail.com',
                'mobile' => '0987654321',
                'date_of_birth' => '2002-02-17',
                'role' => 'admin',
                'status' => 'active',
                'password' => Hash::make('mohamad123'),
            ]);
        }

        
        
        
        $this->call(ApartmentsTableSeeder::class);
    }
}