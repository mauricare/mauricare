<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\CareBooking;
use App\Models\Invoice;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminStatisticsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'month' => ['nullable', 'integer', 'between:1,12'],
        ]);
        $year = (int) $validated['year'];
        $month = isset($validated['month']) ? (int) $validated['month'] : null;
        $start = $month
            ? CarbonImmutable::create($year, $month, 1)->startOfDay()
            : CarbonImmutable::create($year, 1, 1)->startOfDay();
        $end = $month ? $start->endOfMonth() : $start->endOfYear();

        $bookingQuery = CareBooking::query()->whereBetween('created_at', [$start, $end]);
        $bookingsCreated = (clone $bookingQuery)->count();
        $bookingsCancelled = (clone $bookingQuery)->where('status', BookingStatus::Cancelled->value)->count();
        $bookingsClosed = (clone $bookingQuery)->where('status', BookingStatus::Closed->value)->count();
        $resolvedBookings = $bookingsCancelled + $bookingsClosed;

        $invoiceQuery = Invoice::query()->whereBetween('created_at', [$start, $end]);
        $invoicesGenerated = (clone $invoiceQuery)->count();
        $unpaidInvoices = (clone $invoiceQuery)->whereNull('paid_at');
        $paidInvoices = Invoice::query()->whereBetween('paid_at', [$start, $end]);
        $paidInvoiceCount = (clone $paidInvoices)->count();
        $paidInvoiceTotal = (float) (clone $paidInvoices)->sum('amount_due');

        return response()->json([
            'period' => [
                'year' => $year,
                'month' => $month,
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
            ],
            'data' => [
                'paid_invoice_total' => number_format($paidInvoiceTotal, 2, '.', ''),
                'paid_invoice_count' => $paidInvoiceCount,
                'average_paid_invoice' => number_format($paidInvoiceCount ? $paidInvoiceTotal / $paidInvoiceCount : 0, 2, '.', ''),
                'invoices_generated' => $invoicesGenerated,
                'unpaid_invoice_count' => (clone $unpaidInvoices)->count(),
                'unpaid_invoice_total' => number_format((float) $unpaidInvoices->sum('amount_due'), 2, '.', ''),
                'bookings_created' => $bookingsCreated,
                'bookings_cancelled' => $bookingsCancelled,
                'bookings_closed' => $bookingsClosed,
                'booking_closure_rate' => $resolvedBookings
                    ? round(($bookingsClosed / $resolvedBookings) * 100, 1)
                    : 0,
                'care_seekers_joined' => $this->joinedUsers('care_seeker', $start, $end),
                'care_givers_joined' => $this->joinedUsers('care_giver', $start, $end),
            ],
            'monthly' => $this->monthlyBreakdown($year),
        ]);
    }

    private function joinedUsers(string $role, CarbonImmutable $start, CarbonImmutable $end): int
    {
        return User::query()
            ->whereHas('roles', fn (Builder $query): Builder => $query->where('name', $role))
            ->whereBetween('created_at', [$start, $end])
            ->count();
    }

    private function monthlyBreakdown(int $year): array
    {
        $yearStart = CarbonImmutable::create($year, 1, 1)->startOfDay();
        $yearEnd = $yearStart->endOfYear();
        $bookings = CareBooking::query()->whereBetween('created_at', [$yearStart, $yearEnd])->get(['created_at']);
        $paidInvoices = Invoice::query()->whereBetween('paid_at', [$yearStart, $yearEnd])->get(['paid_at', 'amount_due']);
        $careSeekers = User::query()->whereHas('roles', fn (Builder $query): Builder => $query->where('name', 'care_seeker'))
            ->whereBetween('created_at', [$yearStart, $yearEnd])->get(['created_at']);
        $careGivers = User::query()->whereHas('roles', fn (Builder $query): Builder => $query->where('name', 'care_giver'))
            ->whereBetween('created_at', [$yearStart, $yearEnd])->get(['created_at']);

        return collect(range(1, 12))->map(fn (int $month): array => [
            'month' => $month,
            'bookings_created' => $bookings->filter(fn (CareBooking $booking): bool => $booking->created_at->month === $month)->count(),
            'paid_invoice_total' => number_format((float) $paidInvoices
                ->filter(fn (Invoice $invoice): bool => $invoice->paid_at->month === $month)
                ->sum('amount_due'), 2, '.', ''),
            'care_seekers_joined' => $careSeekers->filter(fn (User $user): bool => $user->created_at->month === $month)->count(),
            'care_givers_joined' => $careGivers->filter(fn (User $user): bool => $user->created_at->month === $month)->count(),
        ])->all();
    }
}
