<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\DriverPhoto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DriverPhotoService
{
    /**
     * Store photo for driver
     */
    public static function storePhoto(Driver $driver, $photo, string $type = 'profile', bool $isPrimary = false, ?string $description = null): DriverPhoto
    {
        $disk = self::getStorageDisk();
        $path = "drivers/{$driver->tenant_id}/{$driver->id}/photos";
        
        \Log::info('Storing driver photo', [
            'driver_id' => $driver->id,
            'disk' => $disk,
            'type' => $type,
            'is_primary' => $isPrimary,
        ]);
        
        // Handle base64
        if (is_string($photo) && strpos($photo, 'data:image') === 0) {
            // Use optimization for base64 images
            $extension = 'jpg';
            if (preg_match('/^data:image\/(\w+);base64,/', $photo, $matches)) {
                $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
            }
            $filename = 'photo_' . time() . '_' . uniqid() . '.' . $extension;
            $fullPath = "{$path}/{$filename}";
            
            // Optimize and resize image (max 1200x1200, quality 85%)
            $optimizedData = self::optimizeImage($photo, 1200, 1200, 85);
            
            try {
                \Log::debug('Uploading photo to disk', ['disk' => $disk, 'path' => $fullPath, 'size' => strlen($optimizedData)]);
                Storage::disk($disk)->put($fullPath, $optimizedData);
                \Log::info('Photo uploaded successfully', ['disk' => $disk, 'path' => $fullPath]);
            } catch (\Exception $e) {
                // Fallback to public if MinIO fails
                if ($disk === 'minio') {
                    \Log::warning('MinIO upload failed, using public disk fallback', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    try {
                        Storage::disk('public')->put($fullPath, $optimizedData);
                        $disk = 'public';
                        \Log::info('Photo uploaded to public disk after MinIO failure', ['path' => $fullPath]);
                    } catch (\Exception $fallbackException) {
                        \Log::error('Both MinIO and public disk upload failed', [
                            'minio_error' => $e->getMessage(),
                            'public_error' => $fallbackException->getMessage(),
                        ]);
                        throw $fallbackException;
                    }
                } else {
                    throw $e;
                }
            }
        }
        // Handle UploadedFile
        elseif ($photo instanceof UploadedFile) {
            $extension = $photo->getClientOriginalExtension();
            $filename = 'photo_' . time() . '_' . uniqid() . '.' . $extension;
            
            // Optimize and resize image (max 1200x1200, quality 85%)
            $optimizedData = self::optimizeImage($photo, 1200, 1200, 85);
            $fullPath = "{$path}/{$filename}";
            
            try {
                \Log::debug('Uploading uploaded file to disk', ['disk' => $disk, 'path' => $fullPath, 'size' => strlen($optimizedData)]);
                Storage::disk($disk)->put($fullPath, $optimizedData);
                \Log::info('Uploaded file saved successfully', ['disk' => $disk, 'path' => $fullPath]);
            } catch (\Exception $e) {
                // Fallback to public if MinIO fails
                if ($disk === 'minio') {
                    \Log::warning('MinIO upload failed, using public disk fallback', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    try {
                        Storage::disk('public')->put($fullPath, $optimizedData);
                        $disk = 'public';
                        \Log::info('Uploaded file saved to public disk after MinIO failure', ['path' => $fullPath]);
                    } catch (\Exception $fallbackException) {
                        \Log::error('Both MinIO and public disk upload failed', [
                            'minio_error' => $e->getMessage(),
                            'public_error' => $fallbackException->getMessage(),
                        ]);
                        throw $fallbackException;
                    }
                } else {
                    throw $e;
                }
            }
        } else {
            throw new \InvalidArgumentException('Invalid photo type');
        }

        // If this is primary, unset other primary photos
        if ($isPrimary) {
            DriverPhoto::where('driver_id', $driver->id)
                ->where('is_primary', true)
                ->update(['is_primary' => false]);
        }

        // Get image metadata (don't fail if metadata can't be retrieved)
        $metadata = [];
        try {
            $metadata = self::getImageMetadata($fullPath, $disk);
        } catch (\Exception $e) {
            \Log::warning('Failed to get image metadata, continuing without it', [
                'path' => $fullPath,
                'disk' => $disk,
                'error' => $e->getMessage(),
            ]);
        }

        // Calculate sort_order (handle null case when no photos exist)
        $maxSortOrder = DriverPhoto::where('driver_id', $driver->id)->max('sort_order');
        $sortOrder = ($maxSortOrder !== null) ? $maxSortOrder + 1 : 1;

        try {
            $driverPhoto = DriverPhoto::create([
                'driver_id' => $driver->id,
                'photo_url' => $fullPath,
                'photo_type' => $type,
                'description' => $description,
                'is_primary' => $isPrimary,
                'sort_order' => $sortOrder,
                'metadata' => $metadata,
            ]);
            
            \Log::info('DriverPhoto created successfully', [
                'photo_id' => $driverPhoto->id,
                'driver_id' => $driver->id,
                'path' => $fullPath,
                'disk' => $disk,
            ]);
            
            return $driverPhoto;
        } catch (\Exception $e) {
            \Log::error('Failed to create DriverPhoto record', [
                'driver_id' => $driver->id,
                'path' => $fullPath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete photo
     */
    public static function deletePhoto(DriverPhoto $photo): bool
    {
        // Try both MinIO and public disks
        foreach (['minio', 'public'] as $disk) {
            try {
                if (Storage::disk($disk)->exists($photo->photo_url)) {
                    Storage::disk($disk)->delete($photo->photo_url);
                    break;
                }
            } catch (\Exception $e) {
                \Log::warning("Failed to delete photo from {$disk}", [
                    'photo_id' => $photo->id,
                    'path' => $photo->photo_url,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $photo->delete();
    }

    /**
     * Set primary photo
     */
    public static function setPrimaryPhoto(Driver $driver, DriverPhoto $photo): void
    {
        // Unset other primary photos
        DriverPhoto::where('driver_id', $driver->id)
            ->where('id', '!=', $photo->id)
            ->where('is_primary', true)
            ->update(['is_primary' => false]);

        $photo->update(['is_primary' => true]);
    }

    /**
     * Get image metadata
     */
    protected static function getImageMetadata(string $path, string $disk): array
    {
        try {
            if ($disk === 'minio') {
                // For MinIO, try to get basic info but don't fail if it doesn't work
                try {
                    if (Storage::disk($disk)->exists($path)) {
                        $size = Storage::disk($disk)->size($path);
                        $mimeType = Storage::disk($disk)->mimeType($path);
                        return [
                            'size' => $size,
                            'mime_type' => $mimeType,
                        ];
                    }
                } catch (\Exception $e) {
                    // MinIO metadata methods might fail, return empty array
                    \Log::debug('Could not get MinIO metadata', [
                        'path' => $path,
                        'error' => $e->getMessage(),
                    ]);
                    return [];
                }
            } else {
                $fullPath = Storage::disk($disk)->path($path);
                if (file_exists($fullPath)) {
                    $imageInfo = @getimagesize($fullPath);
                    if ($imageInfo !== false) {
                        return [
                            'width' => $imageInfo[0] ?? null,
                            'height' => $imageInfo[1] ?? null,
                            'mime_type' => $imageInfo['mime'] ?? null,
                            'size' => filesize($fullPath),
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::debug('Failed to get image metadata', [
                'path' => $path,
                'disk' => $disk,
                'error' => $e->getMessage(),
            ]);
        }

        return [];
    }

    /**
     * Get storage disk for photos (MinIO if available, otherwise public)
     */
    public static function getStorageDisk(): string
    {
        // Always try MinIO first - let the upload handle the fallback
        $minioConfig = config('filesystems.disks.minio');
        if ($minioConfig && isset($minioConfig['bucket']) && isset($minioConfig['endpoint'])) {
            return 'minio';
        }
        
        return 'public';
    }

    /**
     * Optimize image: resize and compress
     */
    private static function optimizeImage($image, int $maxWidth = 1200, int $maxHeight = 1200, int $quality = 85): string
    {
        try {
            // Get image resource
            if (is_string($image) && strpos($image, 'data:image') === 0) {
                // Base64 image
                $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $image));
                $source = imagecreatefromstring($imageData);
            } elseif ($image instanceof UploadedFile) {
                // Uploaded file
                $mimeType = $image->getMimeType();
                $tempPath = $image->getRealPath();
                
                switch ($mimeType) {
                    case 'image/jpeg':
                    case 'image/jpg':
                        $source = imagecreatefromjpeg($tempPath);
                        break;
                    case 'image/png':
                        $source = imagecreatefrompng($tempPath);
                        break;
                    case 'image/gif':
                        $source = imagecreatefromgif($tempPath);
                        break;
                    case 'image/webp':
                        $source = imagecreatefromwebp($tempPath);
                        break;
                    default:
                        throw new \InvalidArgumentException('Unsupported image type: ' . $mimeType);
                }
            } else {
                throw new \InvalidArgumentException('Invalid image type');
            }

            if (!$source) {
                throw new \RuntimeException('Failed to create image resource');
            }

            // Get original dimensions
            $originalWidth = imagesx($source);
            $originalHeight = imagesy($source);

            // Calculate new dimensions maintaining aspect ratio
            $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
            $newWidth = (int)($originalWidth * $ratio);
            $newHeight = (int)($originalHeight * $ratio);

            // Create resized image
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            
            // Preserve transparency for PNG and GIF
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefill($resized, 0, 0, $transparent);
            
            // Resize
            imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

            // Output to string
            ob_start();
            imagejpeg($resized, null, $quality);
            $optimizedData = ob_get_clean();

            // Clean up
            imagedestroy($source);
            imagedestroy($resized);

            return $optimizedData;
        } catch (\Exception $e) {
            \Log::error('Image optimization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Fallback: return original or empty
            if (is_string($image) && strpos($image, 'data:image') === 0) {
                return base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $image));
            } elseif ($image instanceof UploadedFile) {
                return file_get_contents($image->getRealPath());
            }
            
            throw $e;
        }
    }
}

