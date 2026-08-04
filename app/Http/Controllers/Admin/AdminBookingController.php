<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\CareBooking;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminBookingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::enum(BookingStatus::class)],
            'search' => ['nullable', 'string', 'max:255'],
            'sort_by' => ['nullable', 'in:booking,care_seeker,care_giver,schedule,type,amount,status'],
            'sort_direction' => ['nullable', 'in:asc,desc'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ]);
        $search = trim($validated['search'] ?? '');
        $sortBy = $validated['sort_by'] ?? 'booking';
        $sortDirection = $validated['sort_direction'] ?? 'desc';

        $filteredQuery = CareBooking::query()
            ->when($search !== '', fn (Builder $query): Builder => $this->applySearch($query, $search));

        $bookings = (clone $filteredQuery)
            ->with(['user.media', 'careGiver.media'])
            ->when(
                isset($validated['status']),
                fn (Builder $query): Builder => $query->where('status', $validated['status'])
            )
            ->when($sortBy === 'booking', fn (Builder $query): Builder => $query->orderBy('id', $sortDirection))
            ->when($sortBy === 'care_seeker', fn (Builder $query): Builder => $query->orderBy(
                User::select('name')->whereColumn('users.id', 'care_bookings.user_id'),
                $sortDirection
            ))
            ->when($sortBy === 'care_giver', fn (Builder $query): Builder => $query->orderBy(
                User::select('name')->whereColumn('users.id', 'care_bookings.care_giver_id'),
                $sortDirection
            ))
            ->when($sortBy === 'schedule', fn (Builder $query): Builder => $query
                ->orderBy('scheduled_date', $sortDirection)
                ->orderBy('start_time', $sortDirection))
            ->when($sortBy === 'type', fn (Builder $query): Builder => $query->orderBy('care_type', $sortDirection))
            ->when($sortBy === 'amount', fn (Builder $query): Builder => $query->orderBy('amount_due', $sortDirection))
            ->when($sortBy === 'status', fn (Builder $query): Builder => $query->orderBy('status', $sortDirection))
            ->orderBy('id', $sortDirection)
            ->paginate($validated['per_page'] ?? 10);

        $counts = (clone $filteredQuery)
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return response()->json([
            'data' => collect($bookings->items())
                ->map(fn (CareBooking $booking): array => $this->serializeBooking($booking))
                ->values(),
            'meta' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'per_page' => $bookings->perPage(),
                'total' => $bookings->total(),
            ],
            'status_counts' => collect(BookingStatus::cases())
                ->mapWithKeys(fn (BookingStatus $status): array => [
                    $status->value => (int) ($counts[$status->value] ?? 0),
                ]),
        ]);
    }

    private function applySearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $query) use ($search): void {
            if (ctype_digit($search)) {
                $query->orWhereKey((int) $search);
            }

            $query->orWhere('care_type', 'like', "%{$search}%")
                ->orWhereHas('user', fn (Builder $userQuery): Builder => $userQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"))
                ->orWhereHas('careGiver', fn (Builder $userQuery): Builder => $userQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
        });
    }

    public function cancel(CareBooking $careBooking): JsonResponse
    {
        abort_if($careBooking->status->isTerminal(), 422, 'Closed or cancelled bookings cannot be cancelled.');

        $careBooking->update(['status' => BookingStatus::Cancelled]);

        return response()->json([
            'message' => 'Booking cancelled successfully.',
            'data' => $this->serializeBooking($careBooking->load(['user.media', 'careGiver.media'])),
        ]);
    }

    private function serializeBooking(CareBooking $booking): array
    {
        return [
            'id' => $booking->id,
            'scheduled_date' => $booking->scheduled_date?->format('Y-m-d'),
            'start_time' => $booking->start_time,
            'duration_hours' => $booking->duration_hours,
            'care_type' => $booking->care_type,
            'preferred_carer_type' => $booking->preferred_carer_type,
            'status' => $booking->status->value,
            'amount_due' => $booking->amount_due,
            'amount_paid' => $booking->amount_paid,
            'address' => $booking->address,
            'created_at' => $booking->created_at?->toISOString(),
            'care_seeker' => [
                'id' => $booking->user->id,
                'name' => $booking->user->name,
                'email' => $booking->user->email,
                'avatar_url' => $booking->user->avatar_url,
            ],
            'care_giver' => $booking->careGiver ? [
                'id' => $booking->careGiver->id,
                'name' => $booking->careGiver->name,
                'email' => $booking->careGiver->email,
                'avatar_url' => $booking->careGiver->avatar_url,
            ] : null,
        ];
    }
}
