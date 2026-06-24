<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Site;
use App\Support\SiteVisibility;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactFormController extends Controller
{
    public function store(Request $request, Site $site): RedirectResponse
    {
        abort_unless(SiteVisibility::canView($site, $request->user()), 404);

        // Honeypot: a real visitor never fills the hidden "company" field.
        if (filled($request->input('company'))) {
            return back()->with('contact_form_success', 'Thanks! Your message has been sent.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'subject' => ['nullable', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:5000'],
        ], [], ['message' => 'message']);

        ContactMessage::create([
            'site_id' => $site->getKey(),
            'name' => $data['name'],
            'email' => $data['email'],
            'subject' => $data['subject'] ?? null,
            'message' => $data['message'],
        ]);

        return back()->with('contact_form_success', 'Thanks! Your message has been sent.');
    }
}
