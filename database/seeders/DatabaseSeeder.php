<?php

namespace Database\Seeders;

use App\Models\ReportLayout;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run()
    {
        // Default Layout Laporan
        ReportLayout::firstOrCreate(
            ['report_type' => 'inventory'],
            [
                'columns' => [
                    'code',
                    'name',
                    'category.name',
                    'location.name',
                    'condition',
                    'last_service_date',
                ]
            ]
        );

        // Seed Roles dan Users
        $this->call([
            RoleAndUserSeeder::class,
        ]);
    }
}
