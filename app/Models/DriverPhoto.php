<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'photo_url',
        'photo_type',
        'description',
        'is_primary',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
        'metadata' => 'array',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get the photo URL
     */
    public function getUrlAttribute(): ?string
    {
        if (!$this->photo_url) {
            return null;
        }

        // Try MinIO first, then public disk
        try {
            $minioConfig = config('filesystems.disks.minio');
            if ($minioConfig && \Storage::disk('minio')->exists($this->photo_url)) {
                // Build URL manually for path-style endpoint
                $baseUrl = rtrim($minioConfig['url'] ?? '', '/');
                $bucket = $minioConfig['bucket'] ?? '';
                $path = ltrim($this->photo_url, '/');
                $minioUrl = "{$baseUrl}/{$bucket}/{$path}";
                
                // Validate that URL was generated successfully
                if (filter_var($minioUrl, FILTER_VALIDATE_URL)) {
                    return $minioUrl;
                }
            }
        } catch (\Exception $e) {
            \Log::debug('Failed to get MinIO URL for photo', [
                'photo_id' => $this->id ?? 'unknown',
                'path' => $this->photo_url,
                'error' => $e->getMessage(),
            ]);
        }

        // Fallback to public disk
        try {
            if (\Storage::disk('public')->exists($this->photo_url)) {
                return \Storage::disk('public')->url($this->photo_url);
            }
        } catch (\Exception $e) {
            \Log::debug('Failed to get public disk URL for photo', [
                'photo_id' => $this->id ?? 'unknown',
                'path' => $this->photo_url,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Scope for primary photo
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope for photo type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('photo_type', $type);
    }
}

