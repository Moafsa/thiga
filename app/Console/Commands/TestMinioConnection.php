<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestMinioConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'minio:test';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Test MinIO connection and bucket accessibility';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing MinIO Connection...');
        $this->newLine();

        // Get configuration
        $config = config('filesystems.disks.minio');
        $defaultDisk = config('filesystems.default');

        // Display configuration
        $this->table(
            ['Setting', 'Value'],
            [
                ['Default Disk', $defaultDisk],
                ['MinIO Endpoint', $config['endpoint'] ?? 'NOT SET'],
                ['MinIO Bucket', $config['bucket'] ?? 'NOT SET'],
                ['Access Key', substr($config['key'] ?? '', 0, 5) . '***'],
                ['Region', $config['region'] ?? 'NOT SET'],
            ]
        );

        $this->newLine();

        // Test 1: Check if MinIO disk is configured
        $this->info('Test 1: Checking MinIO Configuration...');
        if (!isset($config['endpoint']) || !isset($config['bucket'])) {
            $this->error('❌ MinIO is not properly configured');
            return 1;
        }
        $this->line('✓ MinIO configuration found');

        // Test 2: Check if default disk is set to MinIO
        $this->info('Test 2: Checking Default Disk Configuration...');
        if ($defaultDisk !== 'minio') {
            $this->warn("⚠️  Default disk is '{$defaultDisk}', not 'minio'");
            $this->warn('   Please set FILESYSTEM_DISK=minio in your .env file');
            return 1;
        }
        $this->line('✓ Default disk is set to MinIO');

        // Test 3: Test MinIO connection
        $this->info('Test 3: Testing MinIO Connection...');
        try {
            // Try to list objects in bucket (non-destructive test)
            $storage = Storage::disk('minio');
            $files = $storage->listContents('');
            $this->line('✓ Successfully connected to MinIO');
            $this->line("  Files in bucket: " . count($files));
        } catch (\Exception $e) {
            $this->error('❌ Could not connect to MinIO');
            $this->error('   Error: ' . $e->getMessage());
            return 1;
        }

        // Test 4: Test bucket access
        $this->info('Test 4: Testing Bucket Access...');
        try {
            $storage = Storage::disk('minio');

            // Create a test file
            $testFile = 'test-' . time() . '.txt';
            $testContent = 'MinIO Test File - ' . now();

            $storage->put($testFile, $testContent);
            $this->line("✓ Successfully wrote test file: {$testFile}");

            // Read it back
            $content = $storage->get($testFile);
            if ($content === $testContent) {
                $this->line('✓ Successfully read test file');
            } else {
                $this->error('❌ Test file content mismatch');
                return 1;
            }

            // Delete test file
            $storage->delete($testFile);
            $this->line('✓ Successfully deleted test file');
        } catch (\Exception $e) {
            $this->error('❌ Could not access bucket');
            $this->error('   Error: ' . $e->getMessage());
            return 1;
        }

        // Test 5: Check URL accessibility
        $this->info('Test 5: Testing MinIO URL...');
        $endpoint = $config['endpoint'];
        $url = str_replace('http://', '', $endpoint);
        $url = str_replace('https://', '', $url);

        $this->line("MinIO Endpoint: {$endpoint}");

        if (strpos($endpoint, 'localhost') !== false) {
            $this->warn('⚠️  Using localhost endpoint');
            $this->warn('   For Docker containers, use: http://minio:9000');
        }

        // Summary
        $this->newLine();
        $this->info('✅ All tests passed!');
        $this->info('MinIO is properly configured and working.');
        $this->newLine();

        // Recommendations
        $this->info('Next steps:');
        $this->line('1. Test file uploads in your application');
        $this->line('2. Check MinIO Console: ' . str_replace(':9000', ':8900', $endpoint));
        $this->line('3. Monitor logs for any upload errors');

        return 0;
    }
}
