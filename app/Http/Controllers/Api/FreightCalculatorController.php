<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FreightCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FreightCalculatorController extends Controller
{
    public function __invoke(
        Request $request,
        FreightCalculator $calculator
    ): JsonResponse {
        $validated = $request->validate([
            'origin_branch_id' => ['required', 'integer', 'exists:branches,id'],
            'destination_branch_id' => ['required', 'integer', 'exists:branches,id', 'different:origin_branch_id'],
            'service_type' => ['required', 'string', 'in:REGULAR_SEA,EXPRESS_AIR,OVERLAND'],
            'actual_weight_kg' => ['required', 'numeric', 'gt:0', 'max:100000'],
            'length_cm' => ['required', 'numeric', 'gt:0', 'max:100000'],
            'width_cm' => ['required', 'numeric', 'gt:0', 'max:100000'],
            'height_cm' => ['required', 'numeric', 'gt:0', 'max:100000'],
        ]);

        $quote = $calculator->calculate(
            originBranchId: (int) $validated['origin_branch_id'],
            destinationBranchId: (int) $validated['destination_branch_id'],
            serviceType: $validated['service_type'],
            actualWeightKg: (float) $validated['actual_weight_kg'],
            lengthCm: (float) $validated['length_cm'],
            widthCm: (float) $validated['width_cm'],
            heightCm: (float) $validated['height_cm'],
        );

        return response()->json([
            'meta' => [
                'api_version' => 'v1',
                'status' => 'success',
            ],
            'data' => $quote,
        ]);
    }
}
