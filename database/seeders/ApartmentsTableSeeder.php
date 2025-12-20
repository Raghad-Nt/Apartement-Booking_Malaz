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
            // Damascus & Rif Dimashq
            ['province' => 'Damascus', 'city' => 'Damascus'],
            ['province' => 'Damascus', 'city' => 'Mazzeh'],
            ['province' => 'Damascus', 'city' => 'Baramkeh'],
            ['province' => 'Rif Dimashq', 'city' => 'Douma'],
            ['province' => 'Rif Dimashq', 'city' => 'Jaramana'],
            ['province' => 'Rif Dimashq', 'city' => 'Daraya'],

            // Aleppo
            ['province' => 'Aleppo', 'city' => 'Aleppo'],
            ['province' => 'Aleppo', 'city' => 'Azaz'],
            ['province' => 'Aleppo', 'city' => 'Manbij'],
            ['province' => 'Aleppo', 'city' => 'Al-Bab'],

            // Homs
            ['province' => 'Homs', 'city' => 'Homs'],
            ['province' => 'Homs', 'city' => 'Al-Qusayr'],
            ['province' => 'Homs', 'city' => 'Tadmor'],

            // Hama
            ['province' => 'Hama', 'city' => 'Hama'],
            ['province' => 'Hama', 'city' => 'Masyaf'],
            ['province' => 'Hama', 'city' => 'Salamiyah'],

            // Latakia (Coast)
            ['province' => 'Latakia', 'city' => 'Latakia'],
            ['province' => 'Latakia', 'city' => 'Jableh'],
            ['province' => 'Latakia', 'city' => 'Qardaha'],

            // Tartous (Coast)
            ['province' => 'Tartous', 'city' => 'Tartous'],
            ['province' => 'Tartous', 'city' => 'Baniyas'],
            ['province' => 'Tartous', 'city' => 'Safita'],

            // Idlib
            ['province' => 'Idlib', 'city' => 'Idlib'],
            ['province' => 'Idlib', 'city' => 'Jisr al-Shughur'],
            ['province' => 'Idlib', 'city' => 'Ariha'],

            // Deir ez-Zor
            ['province' => 'Deir ez-Zor', 'city' => 'Deir ez-Zor'],
            ['province' => 'Deir ez-Zor', 'city' => 'Al-Mayadin'],
            ['province' => 'Deir ez-Zor', 'city' => 'Al-Bukamal'],

            // Raqqa
            ['province' => 'Raqqa', 'city' => 'Raqqa'],
            ['province' => 'Raqqa', 'city' => 'Tell Abyad'],

            // Hasakah
            ['province' => 'Hasakah', 'city' => 'Hasakah'],
            ['province' => 'Hasakah', 'city' => 'Qamishli'],
            ['province' => 'Hasakah', 'city' => 'Ras al-Ayn'],

            // Daraa
            ['province' => 'Daraa', 'city' => 'Daraa'],
            ['province' => 'Daraa', 'city' => 'Izra'],
            ['province' => 'Daraa', 'city' => 'Nawa'],

            // As-Suwayda
            ['province' => 'As-Suwayda', 'city' => 'As-Suwayda'],
            ['province' => 'As-Suwayda', 'city' => 'Shahba'],
            ['province' => 'As-Suwayda', 'city' => 'Salkhad'],

            // Quneitra
            ['province' => 'Quneitra', 'city' => 'Quneitra'],
            ['province' => 'Quneitra', 'city' => 'Khan Arnabah']
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