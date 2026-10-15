<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\JsonResponse;

class TrackingController extends Controller
{
    public function show(string $tracking_number): JsonResponse
    {
        $shipment = Shipment::query()
            ->with([
                'originBranch',
                'destinationBranch',
                'checkpoints' => fn ($query) => $query
                    ->orderBy('event_timestamp'),
            ])
            ->where('tracking_number', strtoupper($tracking_number))
            ->first();

        if (! $shipment) {
            return response()->json([
                'meta' => [
                    'api_version' => 'v1',
                    'status' => 'error',
                ],
                'error' => [
                    'code' => 'ERR_CONSIGNMENT_NOT_FOUND',
                    'message' => 'Nomor resi tidak ditemukan. Periksa kembali nomor resi Anda.',
                ],
            ], 404);
        }

        return response()->json([
            'meta' => [
                'api_version' => 'v1',
                'status' => 'success',
            ],
            'data' => [
                'tracking_number' => $shipment->tracking_number,
                'service_type' => $shipment->service_type,
                'status_summary' => [
                    'code' => $shipment->current_status,
                    'display_text' => $shipment->current_status,
                    'updated_at' => optional(
                        $shipment->checkpoints->last()?->event_timestamp
                    )->toIso8601String(),
                ],
                'routing' => [
                    'origin' => $shipment->originBranch,
                    'destination' => $shipment->destinationBranch,
                    'estimated_delivery' => $shipment->estimated_arrival,
                ],
                'shipment_metrics' => [
                    'weight_kg' => $shipment->chargeable_weight,
                ],
                'timeline' => $shipment->checkpoints->map(fn ($checkpoint) => [
                    'status' => $checkpoint->status,
                    'title' => $checkpoint->status,
                    'location' => $checkpoint->location_label,
                    'description' => $checkpoint->description,
                    'occurred_at' => $checkpoint->event_timestamp?->toIso8601String(),
                ])->values(),
            ],
        ]);
    }
}
