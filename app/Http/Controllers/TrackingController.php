<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Services\ShipmentTimelineService;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    protected ShipmentTimelineService $timelineService;

    public function __construct(ShipmentTimelineService $timelineService)
    {
        $this->timelineService = $timelineService;
    }

    /**
     * Show public tracking page
     */
    public function show(string $trackingNumber)
    {
        $shipment = Shipment::where('tracking_number', $trackingNumber)
            ->orWhere('tracking_code', $trackingNumber)
            ->with(['senderClient', 'receiverClient', 'timeline'])
            ->first();

        if (!$shipment) {
            abort(404, 'Shipment not found');
        }

        $timeline = $this->timelineService->getPublicTimeline($shipment);

        return view('tracking.show', compact('shipment', 'timeline'));
    }
}











