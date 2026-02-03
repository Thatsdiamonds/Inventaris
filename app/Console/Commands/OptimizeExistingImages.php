<?php

namespace App\Console\Commands;

use App\Services\ImageOptimizationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class OptimizeExistingImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:optimize 
                            {--directory=items : Directory to optimize (items, settings, or all)}
                            {--quality=80 : Image quality (1-100)}
                            {--dry-run : Show what would be optimized without actually doing it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize existing images by converting to WebP and compressing';

    /**
     * Execute the console command.
     */
    public function handle(ImageOptimizationService $imageService): int
    {
        $directory = $this->option('directory');
        $quality = (int) $this->option('quality');
        $dryRun = $this->option('dry-run');

        $directories = $directory === 'all' 
            ? ['items', 'settings'] 
            : [$directory];

        $this->info('Starting image optimization...');
        $this->newLine();

        $totalOriginalSize = 0;
        $totalOptimizedSize = 0;
        $optimizedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        foreach ($directories as $dir) {
            $this->info("Processing directory: {$dir}");
            
            $files = Storage::disk('public')->files($dir);
            
            if (empty($files)) {
                $this->warn("  No files found in {$dir}");
                continue;
            }

            $progressBar = $this->output->createProgressBar(count($files));
            $progressBar->start();

            foreach ($files as $file) {
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                
                // Skip non-image files and already optimized WebP files
                if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp'])) {
                    if ($extension === 'webp') {
                        $skippedCount++;
                    }
                    $progressBar->advance();
                    continue;
                }

                try {
                    $originalSize = Storage::disk('public')->size($file);
                    $totalOriginalSize += $originalSize;

                    if ($dryRun) {
                        $this->line("  Would optimize: {$file} (" . $this->formatBytes($originalSize) . ")");
                        $optimizedCount++;
                        $progressBar->advance();
                        continue;
                    }

                    $newPath = $imageService->optimizeExisting($file, [
                        'quality' => $quality,
                        'webp' => true
                    ]);

                    if ($newPath) {
                        $newSize = Storage::disk('public')->size($newPath);
                        $totalOptimizedSize += $newSize;
                        $optimizedCount++;
                        
                        $savings = round((1 - ($newSize / $originalSize)) * 100, 1);
                        $this->line("  Optimized: {$file} -> {$newPath} (saved {$savings}%)");
                    } else {
                        $errorCount++;
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    $this->error("  Error processing {$file}: " . $e->getMessage());
                }

                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine(2);
        }

        // Summary
        $this->newLine();
        $this->info('==================== Summary ====================');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Images Optimized', $optimizedCount],
                ['Images Skipped (already WebP)', $skippedCount],
                ['Errors', $errorCount],
                ['Original Total Size', $this->formatBytes($totalOriginalSize)],
                ['Optimized Total Size', $this->formatBytes($totalOptimizedSize)],
                ['Space Saved', $this->formatBytes($totalOriginalSize - $totalOptimizedSize)],
                ['Reduction', $totalOriginalSize > 0 
                    ? round((1 - ($totalOptimizedSize / $totalOriginalSize)) * 100, 1) . '%' 
                    : '0%'],
            ]
        );

        if ($dryRun) {
            $this->warn('This was a dry run. No files were actually modified.');
            $this->info('Run without --dry-run to optimize images.');
        }

        return Command::SUCCESS;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $index = 0;
        
        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }
        
        return round($bytes, 2) . ' ' . $units[$index];
    }
}
