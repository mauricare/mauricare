<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        Mail::send('emails.contact', ['data' => $data], function ($message) use ($data) {
            $message
                ->to(config('mail.contact_to'))
                ->subject('New Mauricare contact enquiry');

            if (! empty($data['email'])) {
                $message->replyTo($data['email'], $data['name']);
            }
        });

        return back()->with('contact_success', 'Thank you. Your message has been sent.');
    }
}
