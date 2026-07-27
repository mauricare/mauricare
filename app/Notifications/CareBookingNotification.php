<?php

namespace App\Notifications;

use App\Models\CareBooking;
use Illuminate\Notifications\Notification;

class CareBookingNotification extends Notification
{
    public function __construct(
        private readonly CareBooking $booking,
        private readonly string $message,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => $this->message,
            'care_booking_id' => $this->booking->id,
            'status' => $this->booking->status->value,
        ];
    }
}
