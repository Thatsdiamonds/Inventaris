<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class OptimizeSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:optimize 
                            {--clear : Clear all caches before optimizing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize the system for better performance (config, routes, views caching)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Starting System Optimization...');
        $this->newLine();

        // Clear caches if requested
        if ($this->option('clear')) {
            $this->warn('Clearing all caches...');
            
            $this->task('Clearing config cache', fn() => Artisan::call('config:clear'));
            $this->task('Clearing route cache', fn() => Artisan::call('route:clear'));
            $this->task('Clearing view cache', fn() => Artisan::call('view:clear'));
            $this->task('Clearing application cache', fn() => Artisan::call('cache:clear'));
            $this->task('Clearing compiled files', fn() => Artisan::call('clear-compiled'));
            
            $this->newLine();
        }

        // Optimize
        $this->info('Building optimization caches...');
        
        $this->task('Caching config', fn() => Artisan::call('config:cache'));
        $this->task('Caching routes', fn() => Artisan::call('route:cache'));
        $this->task('Caching views', fn() => Artisan::call('view:cache'));
        $this->task('Caching events', fn() => Artisan::call('event:cache'));
        
        // Composer optimization
        $this->newLine();
        $this->info('Optimizing autoloader...');
        exec('composer dump-autoload --optimize --no-dev 2>&1', $output, $returnCode);
        
        if ($returnCode === 0) {
            $this->info('✓ Composer autoloader optimized');
        } else {
            $this->warn('⚠ Could not optimize composer autoloader (run manually)');
        }

        $this->newLine();
        $this->info('==================== Summary ====================');
        $this->table(
            ['Optimization', 'Status'],
            [
                ['Config Cache', '✓ Cached'],
                ['Route Cache', '✓ Cached'],
                ['View Cache', '✓ Cached'],
                ['Event Cache', '✓ Cached'],
                ['Autoloader', $returnCode === 0 ? '✓ Optimized' : '⚠ Manual'],
            ]
        );

        $this->newLine();
        $this->info('✅ System optimization complete!');
        $this->comment('Tip: Run `php artisan images:optimize` to also optimize existing images.');

        return Command::SUCCESS;
    }

    /**
     * Run a task with a progress indicator
     */
    private function task(string $title, callable $callback): void
    {
        $this->output->write("  {$title}... ");
        $callback();
        $this->output->writeln('<info>✓</info>');
    }
}
