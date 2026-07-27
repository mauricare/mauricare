<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Http\Requests\CompleteVisitRequest;
use App\Http\Requests\ConfirmPaymentRequest;
use App\Models\CareBooking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CareBookingActionController extends Controller
{
    public function assign(Request $request, CareBooking $careBooking): JsonResponse
    {
        Gate::authorize('assign', $careBooking);

        $claimed = CareBooking::whereKey($careBooking->id)
            ->whereNull('care_giver_id')
            ->where('status', BookingStatus::Open->value)
            ->update([
                'care_giver_id' => $request->user()->id,
                'status' => BookingStatus::Assigned,
            ]);

        abort_unless($claimed === 1, 409, 'This booking has already been taken by another care giver.');

        return $this->bookingResponse($careBooking);
    }

    public function completeVisit(CompleteVisitRequest $request, CareBooking $careBooking): JsonResponse
    {
        Gate::authorize('completeVisit', $careBooking);

        $careBooking->update([
            'amount_due' => $request->validated('amount_due'),
            'status' => BookingStatus::AwaitingPayment,
        ]);

        return $this->bookingResponse($careBooking);
    }

    public function confirmPayment(ConfirmPaymentRequest $request, CareBooking $careBooking): JsonResponse
    {
        Gate::authorize('confirmPayment', $careBooking);

        $careBooking->update([
            ...$request->validated(),
            'status' => BookingStatus::Paid,
        ]);

        return $this->bookingResponse($careBooking);
    }

    public function close(Request $request, CareBooking $careBooking): JsonResponse
    {
        Gate::authorize('close', $careBooking);

        $careBooking->update([
            'status' => BookingStatus::Closed,
        ]);

        return $this->bookingResponse($careBooking);
    }

    private function bookingResponse(CareBooking $careBooking): JsonResponse
    {
        return response()->json([
            'data' => $careBooking->fresh()->load(['user', 'careGiver']),
        ]);
    }
}
