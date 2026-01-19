<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InventarisSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users
        User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'role' => 'admin',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Editor User',
            'username' => 'editor',
            'role' => 'editor',
            'password' => Hash::make('password'),
        ]);

        // 2. Settings
        Setting::create([
            'nama_gereja' => 'Gereja Imanuel',
            'alamat' => 'Jl. Merdeka No. 10',
        ]);

        // 3. Categories
        $cat1 = Category::create(['name' => 'Elektronik', 'unique_code' => 'ELEC', 'description' => 'Barang elektronik']);
        $cat2 = Category::create(['name' => 'Furniture', 'unique_code' => 'FURNI', 'description' => 'Meja, kursi, dll']);
        $cat3 = Category::create(['name' => 'Alat Musik', 'unique_code' => 'MUSIC', 'description' => 'Gitar, Piano, Drum']);

        // 4. Locations
        $loc1 = Location::create(['name' => 'Gudang Utama', 'unique_code' => 'GUDANG', 'description' => 'Terletak di basement']);
        $loc2 = Location::create(['name' => 'Ruang Ibadah', 'unique_code' => 'IBADAH', 'description' => 'Lantai 1']);
        $loc3 = Location::create(['name' => 'Kantor', 'unique_code' => 'KANTOR', 'description' => 'Lantai 2']);

        // 5. Items
        $itemsData = [
            [
                'name' => 'Proyektor BenQ',
                'category_id' => $cat1->id,
                'location_id' => $loc1->id,
                'condition' => 'baik',
                'service_interval_days' => 90,
                'service_required' => true,
                'acquisition_date' => '2023-01-15',
                'last_service_date' => '2025-10-01',
                'is_active' => true,
            ],
            [
                'name' => 'Kursi Lipat',
                'category_id' => $cat2->id,
                'location_id' => $loc2->id,
                'condition' => 'baik',
                'service_interval_days' => 0,
                'service_required' => false,
                'acquisition_date' => '2022-05-20',
                'last_service_date' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Piano Yamaha',
                'category_id' => $cat3->id,
                'location_id' => $loc2->id,
                'condition' => 'baik',
                'service_interval_days' => 180,
                'service_required' => true,
                'acquisition_date' => '2021-12-10',
                'last_service_date' => '2025-11-15',
                'is_active' => true,
            ],
            [
                'name' => 'Laptop Dell',
                'category_id' => $cat1->id,
                'location_id' => $loc3->id,
                'condition' => 'baik',
                'service_interval_days' => 30,
                'service_required' => true,
                'acquisition_date' => '2024-06-05',
                'last_service_date' => '2026-01-01',
                'is_active' => true,
            ],
        ];

        foreach ($itemsData as $data) {
            $cat = Category::find($data['category_id']);
            $loc = Location::find($data['location_id']);
            $year = Carbon::parse($data['acquisition_date'])->format('Y');

            $count = Item::where('location_id', $data['location_id'])
                ->where('category_id', $data['category_id'])
                ->count() + 1;

            $serial = str_pad($count, 3, '0', STR_PAD_LEFT);
            $data['uqcode'] = sprintf('%s.%s.%s.%s', $loc->unique_code, $cat->unique_code, $serial, $year);

            $item = Item::create($data);

            if ($data['service_required'] && $data['last_service_date']) {
                Service::create([
                    'item_id' => $item->id,
                    'vendor' => 'Service Center Resmi',
                    'date_in' => $data['last_service_date'],
                    'date_out' => $data['last_service_date'],
                    'description' => 'Regular Maintenance',
                    'cost' => 500000,
                ]);
            }
        }
    }
}
