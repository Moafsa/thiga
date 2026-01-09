<?php

namespace App\Observers;

use App\Models\Branch;
use App\Services\GoogleMapsService;
use Illuminate\Support\Facades\Log;

class BranchObserver
{
    /**
     * Handle the Branch "created" event.
     */
    public function created(Branch $branch): void
    {
        $this->geocodeBranch($branch);
    }

    /**
     * Handle the Branch "updated" event.
     */
    public function updated(Branch $branch): void
    {
        // Geocode if address changed and coordinates are missing
        if ($branch->wasChanged(['address', 'address_number', 'city', 'state', 'neighborhood']) 
            && (!$branch->latitude || !$branch->longitude)) {
            $this->geocodeBranch($branch);
        }
    }

    /**
     * Geocode branch address
     */
    protected function geocodeBranch(Branch $branch): void
    {
        // Skip if already has coordinates
        if ($branch->latitude && $branch->longitude) {
            return;
        }

        // Build full address
        $fullAddress = trim(implode(', ', array_filter([
            $branch->address,
            $branch->address_number,
            $branch->neighborhood,
            $branch->city,
            $branch->state,
            $branch->postal_code,
        ])));

        if (empty($fullAddress)) {
            return;
        }

        try {
            $googleMapsService = app(GoogleMapsService::class);
            $geocoded = $googleMapsService->geocode($fullAddress);

            if ($geocoded) {
                $branch->update([
                    'latitude' => $geocoded['latitude'],
                    'longitude' => $geocoded['longitude'],
                ]);

                Log::info('Branch address geocoded automatically', [
                    'branch_id' => $branch->id,
                    'address' => $fullAddress,
                    'coordinates' => [
                        'lat' => $geocoded['latitude'],
                        'lng' => $geocoded['longitude'],
                    ],
                ]);
            } else {
                Log::warning('Failed to geocode branch address', [
                    'branch_id' => $branch->id,
                    'address' => $fullAddress,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error geocoding branch address', [
                'branch_id' => $branch->id,
                'address' => $fullAddress,
                'error' => $e->getMessage(),
            ]);
        }
    }
}































