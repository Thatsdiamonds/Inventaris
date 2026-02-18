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
use Illuminate\Support\Facades\Schema;

class InventarisSeeder extends Seeder
{
    public function run(): void
    {
        // Reset all tables to ensure clean state and IDs restart from 1
        Schema::disableForeignKeyConstraints();
        User::truncate();
        Setting::truncate();
        Category::truncate();
        Location::truncate();
        Item::truncate();
        Service::truncate();
        Schema::enableForeignKeyConstraints();

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
            'nama_gereja' => 'Gereja Katolik Santo Jusup Paroki Pati',
            'alamat' => 'Jl. Kamandowo 3, Pati Kidul, Kec. Pati, Kabupaten Pati, Jawa Tengah, 59114',
        ]);

        // 3. Categories
        $cat1 = Category::create(['name' => 'Elektronik', 'unique_code' => 'ELK', 'description' => 'Barang elektronik']);
        $cat2 = Category::create(['name' => 'Meubeler', 'unique_code' => 'MEB', 'description' => 'Meja, kursi, dll']);
        $cat3 = Category::create(['name' => 'Alat Musik', 'unique_code' => 'AMSK', 'description' => 'Gitar, Piano, Drum']);
        $cat4 = Category::create(['name' => 'Kendaraan', 'unique_code' => 'KEND', 'description' => 'Kendaraan.']);

        // 4. Locations
        $locations = [
            // Ground Floor / Area Utama
            ['name' => 'Pos Satpam', 'unique_code' => 'POS', 'description' => 'Pos keamanan'],
            ['name' => 'Ruang Sekretariat', 'unique_code' => 'SEKR', 'description' => 'Ruang administrasi'],
            ['name' => 'Ruang Bendahara', 'unique_code' => 'BEND', 'description' => 'Ruang keuangan'],
            ['name' => 'Ruang Gereja', 'unique_code' => 'GRJ', 'description' => 'Ruang ibadah utama'],
            ['name' => 'Ruang Aula', 'unique_code' => 'AULA', 'description' => 'Ruang AULA'],
            ['name' => 'Ruang Komsos Atas', 'unique_code' => 'KOMA', 'description' => 'Ruang komunikasi sosial lantai atas'],
            ['name' => 'Ruang Komsos Bawah', 'unique_code' => 'KOMB', 'description' => 'Ruang komunikasi sosial lantai bawah'],
            ['name' => 'Ruang Adorasi', 'unique_code' => 'ADO', 'description' => 'Ruang Adorasi'],
            ['name' => 'Ruang Serbaguna / Legio Maria', 'unique_code' => 'LM', 'description' => 'Ruang serbaguna / Legio Maria'],
            ['name' => 'Ruang OMK', 'unique_code' => 'OMK', 'description' => 'Ruang Orang Muda Katolik'],
            ['name' => 'Garasi', 'unique_code' => 'GRS', 'description' => 'Garasi'],
            ['name' => 'Ruang DPPH', 'unique_code' => 'DPPH', 'description' => 'Ruang Dewan Paroki dan Pengurus Harian'],
            ['name' => 'Ruang Tamu Romo', 'unique_code' => 'RTA', 'description' => 'Ruang tamu untuk Romo'],
            ['name' => 'Ruang Tamu Bawah', 'unique_code' => 'RTB', 'description' => 'Ruang tamu lantai bawah'],
            ['name' => 'Ruang Anna', 'unique_code' => 'ANNA', 'description' => 'Ruang serbaguna'],
            ['name' => 'Kamar Tidur Romo 1', 'unique_code' => 'RTR1', 'description' => 'Kamar Tidur Romo 1'],
            ['name' => 'Kamar Tidur Romo 2', 'unique_code' => 'RTR2', 'description' => 'Kamar Tidur Romo 2'],
            ['name' => 'Kamar Tidur Romo 3', 'unique_code' => 'RTR3', 'description' => 'Kamar Tidur Romo 3'],
            ['name' => 'Ruang Doa', 'unique_code' => 'DOA', 'description' => 'Ruang untuk berdoa pribadi'],
            ['name' => 'Balkon', 'unique_code' => 'BALK', 'description' => 'Area balkon'],
            ['name' => 'Ruang Kesehatan', 'unique_code' => 'KES', 'description' => 'Ruang Kesehatan'],
            ['name' => 'Ruang Pengakuan Dosa 1', 'unique_code' => 'RPD1', 'description' => 'Ruang pengakuan dosa 1'],
            ['name' => 'Ruang Pengakuan Dosa 2', 'unique_code' => 'RPD2', 'description' => 'Ruang pengakuan dosa 2'],
            ['name' => 'Ruang Pengakuan Dosa 3', 'unique_code' => 'RPD3', 'description' => 'Ruang pengakuan dosa 3'],
            ['name' => 'Lorong Gereja', 'unique_code' => 'LORG', 'description' => 'Koridor area gereja'],
            ['name' => 'Gua Maria', 'unique_code' => 'GMR', 'description' => 'Area Gua Maria'],
            ['name' => 'Toko Paroki', 'unique_code' => 'TOKO', 'description' => 'Toko Paroki'],
            ['name' => 'Lorong Depan Pasturan', 'unique_code' => 'LDP', 'description' => 'Koridor depan pasturan'],
            ['name' => 'Ruang Setrika', 'unique_code' => 'SETR', 'description' => 'Area untuk setrika'],
            ['name' => 'Kamar Tamu 1', 'unique_code' => 'KT1', 'description' => 'Kamar tamu 1'],
            ['name' => 'Kamar Tamu 2', 'unique_code' => 'KT2', 'description' => 'Kamar tamu 2'],
            ['name' => 'Ruang Cuci', 'unique_code' => 'CUCI', 'description' => 'Ruang cuci'],
            ['name' => 'Gudang Paramenta', 'unique_code' => 'GP', 'description' => 'Gudang Paramenta'],
            ['name' => 'Kamar Tidur Romo Tamu 1', 'unique_code' => 'KTR1', 'description' => 'Kamar Tidur Romo Tamu 1'],
            ['name' => 'Kamar Tidur Romo Tamu 2', 'unique_code' => 'KTR2', 'description' => 'Kamar Tidur Romo Tamu 2'],
            ['name' => 'Gudang Atas', 'unique_code' => 'GA', 'description' => 'Gudang Atas'],
            ['name' => 'Gudang Bawah', 'unique_code' => 'GB', 'description' => 'Gudang Bawah'],
            ['name' => 'Ruang Ganti Misdinar', 'unique_code' => 'RGM', 'description' => 'Ruang ganti misdinar'],
            ['name' => 'Ruang Ganti Romo', 'unique_code' => 'RGR', 'description' => 'Ruang ganti Romo'],
            ['name' => 'Teras Ruang Ganti Petugas Liturgi', 'unique_code' => 'TRG', 'description' => 'Teras ruang ganti petugas liturgi'],
        ];

        foreach ($locations as $locData) {
            Location::create($locData);
        }

        // Retrieve created locations for use in items
        $loc1 = Location::where('unique_code', 'GRJ')->first(); // Ruang Gereja
        $loc2 = Location::where('unique_code', 'AULA')->first();   // Ruang Aula
        $loc3 = Location::where('unique_code', 'SEKR')->first(); // Ruang Sekretariat

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

            $nameCode = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $data['name']));

            $prefix = sprintf('%s.%s.%s.', $loc->unique_code, $cat->unique_code, $nameCode);
            $count = Item::where('uqcode', 'LIKE', $prefix.'%')->count() + 1;

            $serial = str_pad($count, 3, '0', STR_PAD_LEFT);
            $data['uqcode'] = sprintf('%s%s.%s', $prefix, $serial, $year);

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
