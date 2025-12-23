<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class CleanOldCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clean-old 
                            {--days=7 : Number of days to keep cache files}
                            {--force : Force cleanup without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean old cached files and cache entries';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $force = $this->option('force');
        
        if (!$force && !$this->confirm("This will delete cache files older than {$days} days. Continue?")) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $this->info('Starting cache cleanup...');
        
        $deletedFiles = 0;
        $deletedCacheEntries = 0;
        $freedSpace = 0;

        // Clean photo cache files
        $this->info('Cleaning photo cache...');
        $photoCachePath = storage_path('app/public/cache/photos');
        if (File::exists($photoCachePath)) {
            $files = File::allFiles($photoCachePath);
            $cutoffTime = now()->subDays($days)->getTimestamp();
            
            foreach ($files as $file) {
                if ($file->getMTime() < $cutoffTime) {
                    $size = $file->getSize();
                    if (File::delete($file->getPathname())) {
                        $deletedFiles++;
                        $freedSpace += $size;
                    }
                }
            }
        }

        // Clean location history cache entries
        $this->info('Cleaning location history cache...');
        $cachePattern = 'driver_location_history_*';
        // Note: Laravel cache doesn't support pattern matching directly,
        // so we'll clean based on TTL expiration (handled automatically)
        // But we can clear specific patterns if using Redis
        if (config('cache.default') === 'redis') {
            $redis = Cache::getStore()->getRedis();
            $keys = $redis->keys($cachePattern);
            foreach ($keys as $key) {
                $redis->del($key);
                $deletedCacheEntries++;
            }
        }

        // Clean photo cache entries
        $this->info('Cleaning photo cache entries...');
        $photoCachePattern = 'photo_cache_*';
        if (config('cache.default') === 'redis') {
            $redis = Cache::getStore()->getRedis();
            $keys = $redis->keys($photoCachePattern);
            foreach ($keys as $key) {
                $redis->del($key);
                $deletedCacheEntries++;
            }
        }

        // Format freed space
        $freedSpaceFormatted = $this->formatBytes($freedSpace);

        $this->info("Cache cleanup completed!");
        $this->info("Deleted files: {$deletedFiles}");
        $this->info("Deleted cache entries: {$deletedCacheEntries}");
        $this->info("Freed space: {$freedSpaceFormatted}");

        return 0;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}


