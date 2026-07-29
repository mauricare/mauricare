<?php

namespace App\Mail;

use App\Models\Message;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminMessageReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $sender,
        public Message $receivedMessage,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New Mauricare message from {$this->sender->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-message-received',
            with: [
                'sender' => $this->sender,
                'receivedMessage' => $this->receivedMessage,
                'messagesUrl' => route('dashboard', ['section' => 'messages']),
            ],
        );
    }
}
