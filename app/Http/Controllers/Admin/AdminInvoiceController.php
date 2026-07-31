<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Mail\CareGiverInvoice;
use App\Models\CareBooking;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminInvoiceController extends Controller
{
    public function index(): JsonResponse
    {
        $invoices = Invoice::query()
            ->with('careGiver:id,name,email')
            ->withCount('bookings')
            ->latest('id')
            ->get();

        return response()->json([
            'data' => $invoices->map(fn (Invoice $invoice): array => $this->serializeInvoice($invoice)),
        ]);
    }

    public function careGivers(): JsonResponse
    {
        return response()->json([
            'data' => User::query()
                ->whereHas('roles', fn (Builder $query): Builder => $query->where('name', 'care_giver'))
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
        ]);
    }

    public function estimate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'care_giver_id' => ['required', 'integer', 'exists:users,id'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
        ]);

        $bookings = CareBooking::query()
            ->where('care_giver_id', $validated['care_giver_id'])
            ->where('status', BookingStatus::Closed->value)
            ->whereNull('invoice_id')
            ->whereBetween('scheduled_date', [$validated['period_start'], $validated['period_end']])
            ->get(['amount_due', 'amount_paid']);

        return response()->json([
            'data' => [
                'bookings_count' => $bookings->count(),
                'booking_total' => number_format(
                    $bookings->sum(fn (CareBooking $booking): float => (float) ($booking->amount_paid ?? $booking->amount_due ?? 0)),
                    2,
                    '.',
                    '',
                ),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'care_giver_id' => ['required', 'integer', 'exists:users,id'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'rate' => ['required', 'numeric', 'gt:0', 'max:100'],
        ]);

        $careGiver = User::query()->findOrFail($validated['care_giver_id']);

        if (! $careGiver->hasRole('care_giver') && ! $careGiver->careGiverProfile()->exists()) {
            throw ValidationException::withMessages([
                'care_giver_id' => 'The selected user is not a care giver.',
            ]);
        }

        $invoice = DB::transaction(function () use ($request, $validated, $careGiver): Invoice {
            $bookings = CareBooking::query()
                ->where('care_giver_id', $careGiver->id)
                ->where('status', BookingStatus::Closed->value)
                ->whereNull('invoice_id')
                ->whereBetween('scheduled_date', [$validated['period_start'], $validated['period_end']])
                ->orderBy('scheduled_date')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($bookings->isEmpty()) {
                throw ValidationException::withMessages([
                    'period_start' => 'No uninvoiced closed bookings were found for this care giver and period.',
                ]);
            }

            $bookingTotal = $bookings->sum(fn (CareBooking $booking): float => (float) ($booking->amount_paid ?? $booking->amount_due ?? 0));
            $amountDue = round($bookingTotal * ((float) $validated['rate'] / 100), 2);

            $invoice = Invoice::create([
                'invoice_number' => 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
                'care_giver_id' => $careGiver->id,
                'generated_by' => $request->user()->id,
                'period_start' => $validated['period_start'],
                'period_end' => $validated['period_end'],
                'rate' => $validated['rate'],
                'booking_total' => $bookingTotal,
                'amount_due' => $amountDue,
            ]);

            CareBooking::whereKey($bookings->modelKeys())->update(['invoice_id' => $invoice->id]);

            return $invoice;
        });

        return response()->json([
            'message' => 'Invoice generated successfully.',
            'data' => $this->serializeInvoice($invoice->load(['careGiver:id,name,email', 'bookings.user:id,name'])->loadCount('bookings')),
        ], 201);
    }

    public function show(Invoice $invoice): JsonResponse
    {
        return response()->json([
            'data' => $this->serializeInvoice(
                $invoice->load(['careGiver:id,name,email', 'bookings.user:id,name'])->loadCount('bookings')
            ),
        ]);
    }

    public function send(Request $request, Invoice $invoice): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
        ]);
        $invoice->load(['careGiver:id,name,email', 'bookings.user:id,name']);
        $pdf = Pdf::loadView('pdf.invoice', ['invoice' => $invoice])
            ->setPaper('a4');

        Mail::to($validated['email'])
            ->send(new CareGiverInvoice($invoice, $pdf->output()));

        $invoice->update([
            'sent_at' => now(),
            'sent_count' => $invoice->sent_count + 1,
        ]);

        return response()->json([
            'message' => "Invoice sent to {$validated['email']}.",
            'data' => [
                'sent_at' => $invoice->sent_at->toISOString(),
                'sent_count' => $invoice->sent_count,
            ],
        ]);
    }

    private function serializeInvoice(Invoice $invoice): array
    {
        return [
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'period_start' => $invoice->period_start->format('Y-m-d'),
            'period_end' => $invoice->period_end->format('Y-m-d'),
            'rate' => $invoice->rate,
            'booking_total' => $invoice->booking_total,
            'amount_due' => $invoice->amount_due,
            'sent_at' => $invoice->sent_at?->toISOString(),
            'sent_count' => $invoice->sent_count,
            'bookings_count' => $invoice->bookings_count ?? $invoice->bookings->count(),
            'created_at' => $invoice->created_at->toISOString(),
            'care_giver' => [
                'id' => $invoice->careGiver->id,
                'name' => $invoice->careGiver->name,
                'email' => $invoice->careGiver->email,
            ],
            'bookings' => $invoice->relationLoaded('bookings')
                ? $invoice->bookings->map(fn (CareBooking $booking): array => [
                    'id' => $booking->id,
                    'scheduled_date' => $booking->scheduled_date->format('Y-m-d'),
                    'care_type' => $booking->care_type,
                    'care_seeker' => $booking->user?->name,
                    'amount' => $booking->amount_paid ?? $booking->amount_due ?? '0.00',
                ])->values()
                : [],
        ];
    }
}
