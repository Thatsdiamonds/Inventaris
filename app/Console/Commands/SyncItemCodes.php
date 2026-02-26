<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemHistory;

class SyncItemCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'items:sync-codes {--dry-run : Only show what would be changed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize item unique codes with their current category/location codes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting item code synchronization...');
        
        $categories = Category::all()->keyBy('id');
        // If we had locations to sync too, we'd load them. For now focusing on Categories as requested.
        // $locations = Location::all()->keyBy('id'); // enhancing scope if needed
        
        $items = Item::with(['category'])->get();
        $updatedCount = 0;
        $dryRun = $this->option('dry-run');

        $bar = $this->output->createProgressBar(count($items));
        $bar->start();

        foreach ($items as $item) {
            if (!$item->category) {
                // Skip items with no valid category
                $bar->advance();
                continue;
            }

            $currentCode = $item->uqcode;
            $parts = explode('.', $currentCode);
            
            // Expected format: LOC.CAT... or LOC.CAT.NAME...
            if (count($parts) >= 4) {
                $categoryCode = $item->category->unique_code;
                
                // Check if Category part (index 1) matches
                if ($parts[1] !== $categoryCode) {
                    $oldUqcode = $currentCode;
                    $parts[1] = $categoryCode;
                    $newCode = implode('.', $parts);
                    
                    if ($dryRun) {
                        $this->line(" Would update Item ID {$item->id}: {$currentCode} -> {$newCode}");
                    } else {
                        // Check for collision
                        if (Item::where('uqcode', $newCode)->where('id', '!=', $item->id)->exists()) {
                            // Conflict detected. This implies we have duplicate items or logic error.
                            // Strategy: Try to preserve serial but ensure uniqueness. 
                            // If simple replacement causes conflict, maybe the target code is already taken.
                            // Let's try to append a unique suffix or increment serial.
                            // For bulk sync, safest is to log error and skip, or force new serial.
                            // Let's try to increment serial.
                            $this->error("Collision detected for {$newCode}. Skipping Item ID {$item->id}.");
                            continue;
                        }

                        $item->update(['uqcode' => $newCode]);
                        
                        // Log history
                        ItemHistory::create([
                            'item_id' => $item->id,
                            'old_uqcode' => $oldUqcode,
                            'new_uqcode' => $newCode,
                            'reason' => 'SyncItemCodes Command (Fix Inconsistency)',
                        ]);
                    }
                    $updatedCount++;
                }
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        
        if ($dryRun) {
            $this->info("Dry run complete. {$updatedCount} items would be updated.");
        } else {
            $this->info("Synchronization complete. {$updatedCount} items updated.");
        }
    }
}
