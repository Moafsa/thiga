<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryProof extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'shipment_id',
        'driver_id',
        'proof_type',
        'description',
        'latitude',
        'longitude',
        'address',
        'city',
        'state',
        'photos',
        'documents',
        'recipient_name',
        'recipient_document',
        'recipient_signature',
        'delivery_time',
        'status',
        'rejection_reason',
        'metadata',
        'device_info',
        'app_version',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'photos' => 'array',
        'documents' => 'array',
        'delivery_time' => 'datetime',
        'metadata' => 'array',
    ];
    
    /**
     * Ensure photos is always an array when accessed
     */
    protected function getPhotosAttribute($value)
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : [];
        }
        
        return is_array($value) ? $value : [];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('proof_type', $type);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pendente',
            'approved' => 'Aprovado',
            'rejected' => 'Rejeitado',
            default => 'Desconhecido'
        };
    }

    public function getFormattedLocationAttribute(): string
    {
        if ($this->address) {
            return "{$this->address}, {$this->city}/{$this->state}";
        }

        return "Lat: {$this->latitude}, Lng: {$this->longitude}";
    }

    public function getPhotosCountAttribute(): int
    {
        return count($this->photos ?? []);
    }

    public function getDocumentsCountAttribute(): int
    {
        return count($this->documents ?? []);
    }

    /**
     * Get photo URLs (convert paths to full URLs using MinIO or public disk)
     * Uses same logic as DriverExpense for consistency
     */
    public function getPhotoUrlsAttribute(): array
    {
        // Get raw photos from attributes to avoid infinite loop
        $rawPhotos = $this->attributes['photos'] ?? null;
        
        // Try to decode JSON if it's a string
        if (is_string($rawPhotos)) {
            $decoded = json_decode($rawPhotos, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $rawPhotos = $decoded;
            }
        }
        
        // Use casted photos if available
        if (!$rawPhotos || !is_array($rawPhotos)) {
            $rawPhotos = $this->photos ?? [];
        }
        
        if (empty($rawPhotos) || !is_array($rawPhotos)) {
            \Log::debug('Delivery proof has no photos', [
                'proof_id' => $this->id ?? null,
                'raw_photos' => $rawPhotos,
                'attributes_photos' => $this->attributes['photos'] ?? null,
                'casted_photos' => $this->photos ?? null,
            ]);
            return [];
        }

        $urls = [];
        foreach ($rawPhotos as $photoPath) {
            if (!$photoPath || !is_string($photoPath)) {
                continue;
            }

            // If it's already a URL, return as is
            if (filter_var($photoPath, FILTER_VALIDATE_URL)) {
                $urls[] = $photoPath;
                continue;
            }

            // Generate MinIO URL
            $urlGenerated = false;
            try {
                $minioConfig = config('filesystems.disks.minio');
                if ($minioConfig && isset($minioConfig['bucket']) && ($minioConfig['endpoint'] ?? $minioConfig['url'] ?? null)) {
                    // Use endpoint if available, otherwise use url
                    $endpoint = rtrim($minioConfig['endpoint'] ?? $minioConfig['url'] ?? '', '/');
                    $bucket = $minioConfig['bucket'] ?? '';
                    $path = ltrim($photoPath, '/');
                    
                    // Build URL for path-style endpoint (use_path_style_endpoint = true)
                    // Format: https://endpoint/bucket/path
                    $minioUrl = "{$endpoint}/{$bucket}/{$path}";
                    
                    \Log::debug('Building MinIO delivery proof URL', [
                        'proof_id' => $this->id ?? null,
                        'original_path' => $photoPath,
                        'endpoint' => $endpoint,
                        'bucket' => $bucket,
                        'path' => $path,
                        'minioUrl' => $minioUrl,
                    ]);
                    
                    // Validate URL format
                    if (filter_var($minioUrl, FILTER_VALIDATE_URL)) {
                        // Verify file exists in MinIO (optional check, log only)
                        try {
                            $exists = \Storage::disk('minio')->exists($photoPath);
                            \Log::debug('MinIO file exists check', [
                                'proof_id' => $this->id ?? null,
                                'path' => $photoPath,
                                'exists' => $exists,
                            ]);
                        } catch (\Exception $e) {
                            \Log::warning('MinIO exists() check failed, continuing anyway', [
                                'proof_id' => $this->id ?? null,
                                'path' => $photoPath,
                                'error' => $e->getMessage(),
                            ]);
                        }
                        
                        // Add URL if it's valid (file might exist but check failed)
                        $urls[] = $minioUrl;
                        $urlGenerated = true;
                        continue; // Move to next photo
                    } else {
                        \Log::warning('Invalid MinIO URL format', [
                            'proof_id' => $this->id ?? null,
                            'url' => $minioUrl,
                            'path' => $photoPath,
                        ]);
                    }
                } else {
                    \Log::warning('MinIO configuration missing or incomplete', [
                        'proof_id' => $this->id ?? null,
                        'has_config' => !empty($minioConfig),
                        'has_bucket' => isset($minioConfig) && isset($minioConfig['bucket']),
                        'has_endpoint' => isset($minioConfig) && isset($minioConfig['endpoint']),
                        'has_url' => isset($minioConfig) && isset($minioConfig['url']),
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to generate MinIO URL for delivery proof photo', [
                    'proof_id' => $this->id ?? null,
                    'photo_path' => $photoPath,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            // Fallback: Try to use Storage facade URL method if manual URL generation failed
            if (!$urlGenerated) {
                try {
                    $storageUrl = \Storage::disk('minio')->url($photoPath);
                    if ($storageUrl && $storageUrl !== $photoPath && filter_var($storageUrl, FILTER_VALIDATE_URL)) {
                        $urls[] = $storageUrl;
                        \Log::debug('Using Storage facade URL for delivery proof photo', [
                            'proof_id' => $this->id ?? null,
                            'path' => $photoPath,
                            'url' => $storageUrl,
                        ]);
                        continue;
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to get Storage URL for delivery proof photo', [
                        'proof_id' => $this->id ?? null,
                        'photo_path' => $photoPath,
                        'error' => $e->getMessage(),
                    ]);
                }
                
                // Last resort: return path as-is (shouldn't happen if MinIO is properly configured)
                \Log::warning('Could not generate URL for delivery proof photo, returning path as-is', [
                    'proof_id' => $this->id ?? null,
                    'photo_path' => $photoPath,
                ]);
                $urls[] = $photoPath;
            }
        }
        
        \Log::debug('Delivery proof photo URLs generated', [
            'proof_id' => $this->id ?? null,
            'raw_photos' => $rawPhotos,
            'generated_urls' => $urls,
        ]);

        return array_filter($urls); // Remove any null/empty values
    }
}
