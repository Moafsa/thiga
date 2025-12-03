<?php

namespace App\Providers;

use Aws\S3\S3Client;
use Illuminate\Filesystem\AwsS3V3Adapter;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter as S3Adapter;
use League\Flysystem\AwsS3V3\PortableVisibilityConverter;
use League\Flysystem\AwsS3V3\VisibilityConverter;
use League\Flysystem\Filesystem;
use League\Flysystem\Visibility;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Ensure VisibilityConverter interface is loaded first
        if (! interface_exists(\League\Flysystem\AwsS3V3\VisibilityConverter::class)) {
            $interfaceFile = base_path('vendor/league/flysystem-aws-s3-v3/VisibilityConverter.php');
            if (file_exists($interfaceFile)) {
                require_once $interfaceFile;
            }
        }

        // Ensure PortableVisibilityConverter class is loaded before FilesystemManager tries to use it
        if (! class_exists(\League\Flysystem\AwsS3V3\PortableVisibilityConverter::class)) {
            $classFile = base_path('vendor/league/flysystem-aws-s3-v3/PortableVisibilityConverter.php');
            if (file_exists($classFile)) {
                require_once $classFile;
            }
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register observers
        \App\Models\Branch::observe(\App\Observers\BranchObserver::class);
        
        // Fix for Laravel 10 compatibility with league/flysystem-aws-s3-v3
        // Override the default S3 driver creation to use the correct PortableVisibilityConverter
        Storage::extend('s3', function ($app, $config) {
            $s3Config = $this->formatS3Config($config);

            $root = (string) ($s3Config['root'] ?? '');

            $visibility = new PortableVisibilityConverter(
                $config['visibility'] ?? Visibility::PUBLIC
            );

            $streamReads = $s3Config['stream_reads'] ?? false;

            $client = new S3Client($s3Config);

            $adapter = new S3Adapter($client, $s3Config['bucket'], $root, $visibility, null, $config['options'] ?? [], $streamReads);

            // Create Filesystem with proper configuration
            $filesystemConfig = array_filter([
                'directory_visibility' => $config['directory_visibility'] ?? null,
                'visibility' => $config['visibility'] ?? null,
                'retain_visibility' => $config['retain_visibility'] ?? true,
            ]);

            return new AwsS3V3Adapter(
                new Filesystem($adapter, $filesystemConfig),
                $adapter,
                $s3Config,
                $client
            );
        });
    }

    /**
     * Format the given S3 configuration with the default options.
     *
     * @param  array  $config
     * @return array
     */
    protected function formatS3Config(array $config)
    {
        $config += ['version' => 'latest'];

        if (! empty($config['key']) && ! empty($config['secret'])) {
            $config['credentials'] = Arr::only($config, ['key', 'secret']);
        }

        if (! empty($config['token'])) {
            $config['credentials']['token'] = $config['token'];
        }

        return Arr::except($config, ['token']);
    }
}























