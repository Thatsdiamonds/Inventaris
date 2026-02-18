<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MassItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure Categories exist
        if (Category::count() < 5) {
            $categories = [
                ['name' => 'Elektronik Test', 'unique_code' => 'ELKT'],
                ['name' => 'Furniture Test', 'unique_code' => 'FURT'],
                ['name' => 'Musical Instrument Test', 'unique_code' => 'MUSIC'],
                ['name' => 'Office Supplies Test', 'unique_code' => 'OFFC'],
                ['name' => 'Decorations Test', 'unique_code' => 'DECO'],
            ];
            foreach ($categories as $cat) {
                Category::firstOrCreate(['unique_code' => $cat['unique_code']], $cat);
            }
        }
        $categories = Category::all();

        // 2. Ensure Locations exist
        if (Location::count() < 10) {
            for ($i = 1; $i <= 10; $i++) {
                Location::firstOrCreate(
                    ['unique_code' => 'TEST' . str_pad($i, 2, '0', STR_PAD_LEFT)],
                    [
                        'name' => 'Test Room ' . $i, 
                        'description' => 'Dummy location for testing'
                    ]
                );
            }
        }
        $locations = Location::all();

        // 3. Define Item Names per Category (for realistic testing)
        // Map generic categories if no specific names found, use randomness
        $commonItems = [
            'Laptop High-End', 'Monitor 24inch', 'Mouse Wireless', 
            'Meja Kantor', 'Kursi Direktur', 'Lemari Arsip',
            'Gitar Akustik', 'Drum Set', 'Microphone Condenser',
            'Sofa Tamu', 'Lampu Hias', 'AC Split 1PK',
            'Proyektor Epson', 'Layar Proyektor', 'Whiteboard',
            'Kipas Angin', 'Dispenser Galon', 'Printer Laser',
            'Scanner Dokumen', 'Brankas Kecil'
        ];

        $totalItemsGenerated = 0;
        $batchSize = 500;
        $data = [];

        foreach ($locations as $location) {
            foreach ($categories as $category) {
                // Pick random 2-5 item types for this location-category pair
                $selectedItems = collect($commonItems)->random(rand(2, 5));

                foreach ($selectedItems as $itemName) {
                    $count = rand(5, 20);
                    $nameCode = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $itemName));
                    
                    // Prefix is Loc.Cat.Name.
                    $prefix = sprintf('%s.%s.%s.', $location->unique_code, $category->unique_code, $nameCode);
                    
                    // Count existing items with this prefix to determine start serial
                    // Note: This only counts COMMITTED items. Since we batch insert, we must ensure unique serials within this batch too.
                    $dbCount = Item::where('uqcode', 'LIKE', $prefix . '%')->count();

                    for ($i = 1; $i <= $count; $i++) {
                        $currentSerial = $dbCount + $i;
                        
                        // Create instance to get random attributes
                        $factoryItem = Item::factory()->make([
                            'category_id' => $category->id,
                            'location_id' => $location->id,
                        ]);

                        $year = $factoryItem->acquisition_date->format('Y');
                        $serialStr = str_pad($currentSerial, 3, '0', STR_PAD_LEFT);
                        
                        // Construct UQCode: LOC.CAT.NAME.SERIAL.YEAR
                        $uqcode = sprintf('%s%s.%s', $prefix, $serialStr, $year);

                        $itemArray = $factoryItem->toArray();
                        $itemArray['name'] = $itemName . ' #' . $currentSerial;
                        $itemArray['uqcode'] = $uqcode;
                        $itemArray['created_at'] = now();
                        $itemArray['updated_at'] = now();

                        $data[] = $itemArray;
                    }
                }

                // Batch Insert Check
                if (count($data) >= 500) {
                    Item::insert($data);
                    $totalItemsGenerated += count($data);
                    $data = [];
                    $this->command->info("Inserted batch... Total: $totalItemsGenerated");
                }
            }
        }


        // Insert remaining
        if (!empty($data)) {
            Item::insert($data);
            $totalItemsGenerated += count($data);
        }

        $this->command->info("Completed! Total $totalItemsGenerated items generated.");
    }
}
