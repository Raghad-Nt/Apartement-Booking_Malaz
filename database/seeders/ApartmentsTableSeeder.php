<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Apartment;
use App\Models\ApartmentImage;

class ApartmentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $locations = [
            ['province' => 'Damascus', 'city' => 'Damascus'],
            ['province' => 'Aleppo', 'city' => 'Aleppo'],
            ['province' => 'Homs', 'city' => 'Homs'],
            ['province' => 'Latakia', 'city' => 'Latakia'],
            ['province' => 'Tartous', 'city' => 'Tartous']
        ];

        
        for ($i = 0; $i < 10; $i++) {
            
            $location = $locations[array_rand($locations)];
            
            
            $allFeatures = ['wifi', 'parking', 'ac', 'kitchen'];
            $selectedFeatures = array_slice($allFeatures, 0, rand(1, count($allFeatures)));
            
            
            $apartment = Apartment::create([
                'title' => 'Beautiful Apartment ' . ($i + 1),
                'description' => 'This is a beautiful apartment located in ' . $location['city'] . ', ' . $location['province'] . '. It has all modern amenities and is perfect for families.',
                'price' => rand(100, 500),
                'location' => $location['city'] . ' Center',
                'province' => $location['province'],
                'city' => $location['city'],
                'features' => $selectedFeatures,
                'owner_id' => User::where('role', 'renter')->inRandomOrder()->first()->id ?? User::first()->id,
                'status' => 'available'
            ]);

            
            for ($j = 0; $j < 3; $j++) {
                ApartmentImage::create([
                    'apartment_id' => $apartment->id,
                    'image_path' => 'apartment_images/sample_' . ($i + 1) . '_' . ($j + 1) . '.jpg'
                ]);
            }
        }
    }
}