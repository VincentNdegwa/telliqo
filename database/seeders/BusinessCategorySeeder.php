<?php

namespace Database\Seeders;

use App\Models\BusinessCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BusinessCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Restaurant & Food',
                'icon' => 'pi pi-building',
                'description' => 'Restaurants, cafes, bakeries, and food service businesses',
            ],
            [
                'name' => 'Retail & Shopping',
                'icon' => 'pi pi-shopping-cart',
                'description' => 'Retail stores, boutiques, and shopping centers',
            ],
            [
                'name' => 'Healthcare & Medical',
                'icon' => 'pi pi-heart',
                'description' => 'Hospitals, clinics, dental offices, and medical practices',
            ],
            [
                'name' => 'Beauty & Spa',
                'icon' => 'pi pi-star',
                'description' => 'Salons, spas, barbershops, and beauty services',
            ],
            [
                'name' => 'Automotive',
                'icon' => 'pi pi-car',
                'description' => 'Auto repair, car dealerships, and automotive services',
            ],
            [
                'name' => 'Professional Services',
                'icon' => 'pi pi-briefcase',
                'description' => 'Consulting, legal, accounting, and business services',
            ],
            [
                'name' => 'Entertainment & Recreation',
                'icon' => 'pi pi-play',
                'description' => 'Theaters, gyms, entertainment venues, and recreational facilities',
            ],
            [
                'name' => 'Education & Training',
                'icon' => 'pi pi-book',
                'description' => 'Schools, tutoring centers, and training institutions',
            ],
            [
                'name' => 'Home Services',
                'icon' => 'pi pi-home',
                'description' => 'Cleaning, landscaping, repair, and home improvement services',
            ],
            [
                'name' => 'Technology & IT',
                'icon' => 'pi pi-desktop',
                'description' => 'IT services, software development, and tech support',
            ],
            [
                'name' => 'Hospitality & Hotels',
                'icon' => 'pi pi-building',
                'description' => 'Hotels, resorts, and accommodation services',
            ],
            [
                'name' => 'Real Estate',
                'icon' => 'pi pi-map-marker',
                'description' => 'Real estate agencies, property management, and realtors',
            ],
        ];

        foreach ($categories as $category) {
            BusinessCategory::create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'icon' => $category['icon'],
                'description' => $category['description'],
                'is_active' => true,
            ]);
        }
    }
}
