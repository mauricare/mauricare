<?php

namespace App\Http\Controllers;

use App\Mail\ContactEnquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        return $this->send(
            $request,
            config('mail.public_contact_to'),
            'New Mauricare contact enquiry',
        );
    }

    public function support(Request $request): RedirectResponse
    {
        return $this->send(
            $request,
            config('mail.support_contact_to'),
            'New Mauricare support request',
        );
    }

    private function send(Request $request, string $recipient, string $subject): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        Mail::to($recipient)->send(new ContactEnquiry($data, $subject));

        return back()->with('contact_success', 'Thank you. Your message has been sent.');
    }
}
