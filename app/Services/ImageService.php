<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\UploadedFile;
use Exception;

class ImageService
{
    /**
     * Resize and optimize image
     *
     * @param string|UploadedFile $image Image file path or UploadedFile instance
     * @param int $maxWidth Maximum width
     * @param int $maxHeight Maximum height
     * @param int $quality JPEG quality (1-100)
     * @return string Base64 encoded image data
     * @throws Exception
     */
    public static function resizeImage($image, int $maxWidth = 800, int $maxHeight = 800, int $quality = 85): string
    {
        // Handle base64 string
        if (is_string($image) && strpos($image, 'data:image') === 0) {
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $image));
            $sourceImage = imagecreatefromstring($imageData);
        } 
        // Handle UploadedFile
        elseif ($image instanceof UploadedFile) {
            $sourceImage = self::createImageFromFile($image);
        }
        // Handle file path
        elseif (is_string($image) && file_exists($image)) {
            $sourceImage = self::createImageFromPath($image);
        } else {
            throw new Exception('Invalid image source');
        }

        if (!$sourceImage) {
            throw new Exception('Failed to create image resource');
        }

        $originalWidth = imagesx($sourceImage);
        $originalHeight = imagesy($sourceImage);

        // Calculate new dimensions maintaining aspect ratio
        $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
        $newWidth = (int) ($originalWidth * $ratio);
        $newHeight = (int) ($originalHeight * $ratio);

        // Create new image
        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG/GIF
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
        imagefill($newImage, 0, 0, $transparent);

        // Resize
        imagecopyresampled(
            $newImage,
            $sourceImage,
            0, 0, 0, 0,
            $newWidth, $newHeight,
            $originalWidth, $originalHeight
        );

        // Output to buffer
        ob_start();
        imagejpeg($newImage, null, $quality);
        $imageData = ob_get_contents();
        ob_end_clean();

        // Clean up
        imagedestroy($sourceImage);
        imagedestroy($newImage);

        return base64_encode($imageData);
    }

    /**
     * Create image resource from UploadedFile
     */
    private static function createImageFromFile(UploadedFile $file)
    {
        $mimeType = $file->getMimeType();
        $tempPath = $file->getRealPath();

        return self::createImageFromMime($mimeType, $tempPath);
    }

    /**
     * Create image resource from file path
     */
    private static function createImageFromPath(string $path)
    {
        $mimeType = mime_content_type($path);
        return self::createImageFromMime($mimeType, $path);
    }

    /**
     * Create image resource based on MIME type
     */
    private static function createImageFromMime(string $mimeType, string $path)
    {
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                return imagecreatefromjpeg($path);
            case 'image/png':
                return imagecreatefrompng($path);
            case 'image/gif':
                return imagecreatefromgif($path);
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    return imagecreatefromwebp($path);
                }
                break;
            default:
                throw new Exception("Unsupported image type: {$mimeType}");
        }

        return false;
    }

    /**
     * Store image with cache optimization and WebP conversion when possible
     *
     * @param string|UploadedFile $image
     * @param string $path Storage path
     * @param int $maxWidth
     * @param int $maxHeight
     * @param bool $convertToWebP Convert to WebP format if supported
     * @return string Stored file path
     */
    public static function storeOptimized($image, string $path, int $maxWidth = 800, int $maxHeight = 800, bool $convertToWebP = true): string
    {
        $disk = Storage::disk('public');
        
        // Check if WebP is supported
        $webPSupported = function_exists('imagewebp');
        
        // Handle base64
        if (is_string($image) && strpos($image, 'data:image') === 0) {
            $resizedData = base64_decode(self::resizeImage($image, $maxWidth, $maxHeight));
            
            // Convert to WebP if supported and requested
            if ($convertToWebP && $webPSupported) {
                $path = preg_replace('/\.(jpg|jpeg|png|gif)$/i', '.webp', $path);
                $resizedData = self::convertToWebP($resizedData);
            }
            
            return $disk->put($path, $resizedData) ? $path : '';
        }
        
        // Handle UploadedFile
        if ($image instanceof UploadedFile) {
            $resizedData = base64_decode(self::resizeImage($image, $maxWidth, $maxHeight));
            
            // Convert to WebP if supported and requested
            if ($convertToWebP && $webPSupported) {
                $path = preg_replace('/\.(jpg|jpeg|png|gif)$/i', '.webp', $path);
                $resizedData = self::convertToWebP($resizedData);
            }
            
            return $disk->put($path, $resizedData) ? $path : '';
        }

        return '';
    }

    /**
     * Convert image data to WebP format
     *
     * @param string $imageData Binary image data (JPEG/PNG)
     * @param int $quality WebP quality (0-100)
     * @return string WebP image data
     */
    private static function convertToWebP(string $imageData, int $quality = 85): string
    {
        if (!function_exists('imagewebp')) {
            return $imageData; // Return original if WebP not supported
        }

        // Create image from string
        $sourceImage = @imagecreatefromstring($imageData);
        if (!$sourceImage) {
            return $imageData; // Return original if conversion fails
        }

        // Preserve transparency
        imagealphablending($sourceImage, false);
        imagesavealpha($sourceImage, true);

        // Output to buffer
        ob_start();
        imagewebp($sourceImage, null, $quality);
        $webpData = ob_get_contents();
        ob_end_clean();

        imagedestroy($sourceImage);

        return $webpData ?: $imageData; // Return WebP or original if conversion fails
    }

    /**
     * Get cached photo URL or generate cache
     *
     * @param string|null $photoPath
     * @param int $size Size for avatar (width/height)
     * @return string|null
     */
    public static function getCachedPhotoUrl(?string $photoPath, int $size = 150): ?string
    {
        if (!$photoPath) {
            return null;
        }

        // Check if file exists
        $fullPath = storage_path('app/public/' . $photoPath);
        if (!file_exists($fullPath)) {
            return null;
        }

        // Check if already cached
        $cacheDir = 'cache/photos/' . dirname($photoPath);
        $cacheFileName = pathinfo($photoPath, PATHINFO_FILENAME) . "_{$size}." . pathinfo($photoPath, PATHINFO_EXTENSION);
        $cachePath = $cacheDir . '/' . $cacheFileName;
        $cacheFullPath = storage_path('app/public/' . $cachePath);
        
        // If cached file exists and is newer than original, use it
        if (file_exists($cacheFullPath) && filemtime($cacheFullPath) >= filemtime($fullPath)) {
            return Storage::url($cachePath);
        }

        // Generate cache
        $cacheKey = "photo_cache_{$photoPath}_{$size}";
        $cachedPath = Cache::remember($cacheKey, 86400, function() use ($photoPath, $size, $cachePath, $fullPath) {
            try {
                $resizedData = self::resizeImage($fullPath, $size, $size, 90);
                
                // Ensure cache directory exists
                $cacheDirPath = storage_path('app/public/' . dirname($cachePath));
                if (!file_exists($cacheDirPath)) {
                    mkdir($cacheDirPath, 0755, true);
                }
                
                Storage::disk('public')->put($cachePath, base64_decode($resizedData));
                return $cachePath;
            } catch (Exception $e) {
                \Log::error('Failed to cache photo', ['path' => $photoPath, 'error' => $e->getMessage()]);
                return $photoPath; // Return original if cache fails
            }
        });

        return $cachedPath ? Storage::url($cachedPath) : Storage::url($photoPath);
    }
}

