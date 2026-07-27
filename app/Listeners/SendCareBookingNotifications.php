<?php

namespace App\Listeners;

use App\Enums\BookingStatus;
use App\Models\CareBooking;
use App\Notifications\CareBookingNotification;
use Illuminate\Support\Carbon;

class SendCareBookingNotifications
{
    public function __invoke(CareBooking $booking): void
    {
        if ($booking->wasChanged('status')) {
            $this->notifyStatusChange($booking);

            return;
        }

        if ($booking->care_giver_id && $booking->wasChanged(['scheduled_date', 'start_time', 'address', 'contact_phone', 'description'])) {
            $booking->careGiver->notify(new CareBookingNotification(
                $booking,
                "{$booking->user->name} updated the booking scheduled for {$this->visitMoment($booking)}.",
            ));
        }
    }

    private function notifyStatusChange(CareBooking $booking): void
    {
        $moment = $this->visitMoment($booking);

        match ($booking->status) {
            BookingStatus::Assigned => $booking->user->notify(new CareBookingNotification(
                $booking,
                "{$booking->careGiver->name} has accepted your booking for {$moment}.",
            )),
            BookingStatus::AwaitingPayment => $booking->user->notify(new CareBookingNotification(
                $booking,
                "{$booking->careGiver->name} completed the visit of {$moment}. Amount to pay: Rs {$booking->amount_due}.",
            )),
            BookingStatus::Paid => $booking->careGiver?->notify(new CareBookingNotification(
                $booking,
                "{$booking->user->name} recorded a payment of Rs {$booking->amount_paid} for the booking of {$moment}.",
            )),
            BookingStatus::Closed => $booking->user->notify(new CareBookingNotification(
                $booking,
                "{$booking->careGiver->name} confirmed receiving your payment. The booking of {$moment} is now closed.",
            )),
            BookingStatus::Cancelled => $booking->careGiver?->notify(new CareBookingNotification(
                $booking,
                "{$booking->user->name} cancelled the booking of {$moment}.",
            )),
            default => null,
        };
    }

    private function visitMoment(CareBooking $booking): string
    {
        $date = $booking->scheduled_date->format('d M Y');
        $time = Carbon::createFromFormat('H:i', substr($booking->start_time, 0, 5))->format('g:i A');

        return "{$date} at {$time}";
    }
}
