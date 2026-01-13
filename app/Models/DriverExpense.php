<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DriverExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'route_id',
        'expense_type',
        'description',
        'amount',
        'expense_date',
        'payment_method',
        'receipt_url',
        'notes',
        'status',
        'rejection_reason',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'metadata' => 'array',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Get receipt URL (for backward compatibility)
     */
    public function getReceiptUrlAttribute($value): ?string
    {
        if (!$value) {
            return null;
        }

        // If already a full URL, return it
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        // Try MinIO first, then public disk (same logic as DriverPhoto)
        try {
            $minioConfig = config('filesystems.disks.minio');
            if ($minioConfig && isset($minioConfig['bucket']) && isset($minioConfig['url'])) {
                // Build URL manually for path-style endpoint (same as DriverPhoto)
                $baseUrl = rtrim($minioConfig['url'] ?? '', '/');
                $bucket = $minioConfig['bucket'] ?? '';
                $path = ltrim($value, '/');
                $minioUrl = "{$baseUrl}/{$bucket}/{$path}";
                
                \Log::debug('Building MinIO receipt URL', [
                    'expense_id' => $this->id ?? null,
                    'original_path' => $value,
                    'baseUrl' => $baseUrl,
                    'bucket' => $bucket,
                    'path' => $path,
                    'minioUrl' => $minioUrl,
                ]);
                
                // Validate that URL was generated successfully
                if (filter_var($minioUrl, FILTER_VALIDATE_URL)) {
                    // Try to verify file exists (optional, but helpful)
                    try {
                        $exists = Storage::disk('minio')->exists($value);
                        \Log::debug('MinIO file exists check', [
                            'expense_id' => $this->id ?? null,
                            'path' => $value,
                            'exists' => $exists,
                        ]);
                        
                        if ($exists) {
                            return $minioUrl;
                        } else {
                            // Even if exists() fails, return the URL anyway (file might exist but check failed)
                            \Log::debug('MinIO file not found but returning URL anyway', [
                                'expense_id' => $this->id ?? null,
                                'url' => $minioUrl,
                            ]);
                            return $minioUrl;
                        }
                    } catch (\Exception $e) {
                        // Even if exists() fails, return the URL anyway (file might exist but check failed)
                        \Log::debug('MinIO exists() check failed, returning URL anyway', [
                            'expense_id' => $this->id ?? null,
                            'url' => $minioUrl,
                            'error' => $e->getMessage(),
                        ]);
                        return $minioUrl;
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::debug('Failed to get MinIO URL for receipt', [
                'expense_id' => $this->id ?? null,
                'receipt_path' => $value,
                'error' => $e->getMessage(),
            ]);
        }

        // Fallback to public disk
        try {
            $publicUrl = Storage::disk('public')->url($value);
            \Log::debug('Using public disk URL for receipt', [
                'expense_id' => $this->id ?? null,
                'path' => $value,
                'url' => $publicUrl,
            ]);
            return $publicUrl;
        } catch (\Exception $e) {
            \Log::debug('Failed to get public disk URL for receipt', [
                'expense_id' => $this->id ?? null,
                'receipt_path' => $value,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Get all receipt image URLs (supports multiple images via metadata)
     * Returns array of image URLs
     */
    public function getReceiptImagesAttribute(): array
    {
        $images = [];
        $receiptUrl = $this->attributes['receipt_url'] ?? null;

        // Add single receipt_url if exists
        if ($receiptUrl) {
            // If it's already a full URL (http/https), add it directly
            if (filter_var($receiptUrl, FILTER_VALIDATE_URL)) {
                $images[] = $receiptUrl;
            } else {
                // It's a storage path, try to generate URL using same logic as DriverPhoto
                // Try MinIO first
                try {
                    $minioConfig = config('filesystems.disks.minio');
                    if ($minioConfig && isset($minioConfig['bucket']) && isset($minioConfig['url'])) {
                        // Build URL manually for path-style endpoint
                        $baseUrl = rtrim($minioConfig['url'] ?? '', '/');
                        $bucket = $minioConfig['bucket'] ?? '';
                        $path = ltrim($receiptUrl, '/');
                        $minioUrl = "{$baseUrl}/{$bucket}/{$path}";
                        
                        // Validate URL format
                        if (filter_var($minioUrl, FILTER_VALIDATE_URL)) {
                            // Try to verify file exists (optional check)
                            try {
                                if (Storage::disk('minio')->exists($receiptUrl)) {
                                    $images[] = $minioUrl;
                                } else {
                                    // Even if exists() fails, add URL anyway (file might exist but check failed)
                                    $images[] = $minioUrl;
                                }
                            } catch (\Exception $e) {
                                // exists() check failed, but add URL anyway
                                $images[] = $minioUrl;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // MinIO failed, try public disk
                    try {
                        if (Storage::disk('public')->exists($receiptUrl)) {
                            $images[] = Storage::disk('public')->url($receiptUrl);
                        } else {
                            // Generate URL anyway
                            $images[] = Storage::disk('public')->url($receiptUrl);
                        }
                    } catch (\Exception $e2) {
                        // Fallback to asset
                        if (strpos($receiptUrl, '/') !== false) {
                            $images[] = asset('storage/' . ltrim($receiptUrl, '/'));
                        }
                    }
                }
                
                // If no MinIO URL was added, try public disk
                if (empty($images)) {
                    try {
                        if (Storage::disk('public')->exists($receiptUrl)) {
                            $images[] = Storage::disk('public')->url($receiptUrl);
                        } else {
                            $images[] = Storage::disk('public')->url($receiptUrl);
                        }
                    } catch (\Exception $e) {
                        if (strpos($receiptUrl, '/') !== false) {
                            $images[] = asset('storage/' . ltrim($receiptUrl, '/'));
                        }
                    }
                }
            }
        }

        // Add multiple images from metadata if exists
        if ($this->metadata && is_array($this->metadata)) {
            $metadataImages = $this->metadata['receipt_urls'] ?? $this->metadata['receipt_images'] ?? [];
            if (is_array($metadataImages)) {
                foreach ($metadataImages as $imagePath) {
                    if ($imagePath && is_string($imagePath)) {
                        // If it's already a full URL, add it directly
                        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                            $images[] = $imagePath;
                        } else {
                            // It's a storage path, generate URL using same MinIO logic
                            try {
                                $minioConfig = config('filesystems.disks.minio');
                                if ($minioConfig && isset($minioConfig['bucket']) && isset($minioConfig['url'])) {
                                    $baseUrl = rtrim($minioConfig['url'] ?? '', '/');
                                    $bucket = $minioConfig['bucket'] ?? '';
                                    $path = ltrim($imagePath, '/');
                                    $minioUrl = "{$baseUrl}/{$bucket}/{$path}";
                                    
                                    if (filter_var($minioUrl, FILTER_VALIDATE_URL)) {
                                        $images[] = $minioUrl;
                                    }
                                }
                            } catch (\Exception $e) {
                                // Try public disk
                                try {
                                    $images[] = Storage::disk('public')->url($imagePath);
                                } catch (\Exception $e2) {
                                    if (strpos($imagePath, '/') !== false) {
                                        $images[] = asset('storage/' . ltrim($imagePath, '/'));
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // Remove duplicates and empty values, then return
        return array_values(array_unique(array_filter($images)));
    }

    /**
     * Get expense type label
     */
    public function getExpenseTypeLabelAttribute(): string
    {
        return match($this->expense_type) {
            'toll' => 'Pedágio',
            'fuel' => 'Combustível',
            'meal' => 'Refeição',
            'parking' => 'Estacionamento',
            'other' => 'Outro',
            default => $this->expense_type,
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pendente',
            'approved' => 'Aprovado',
            'rejected' => 'Rejeitado',
            default => $this->status,
        };
    }

    /**
     * Check if expense is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if expense is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Scope for approved expenses
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for pending expenses
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope by expense type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('expense_type', $type);
    }
}






