<?php

namespace App\Http\Controllers;

use App\Mail\ContactSubmissionMail;
use App\Models\ContactMessage;
use App\Models\Page;
use App\Models\Setting;
use App\Models\Site;
use App\Support\SiteVisibility;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactFormController extends Controller
{
    public function store(Request $request, Site $site): RedirectResponse
    {
        abort_unless(SiteVisibility::canView($site, $request->user()), 404);

        $config = $this->decodeConfig($request->input('config'));

        // Honeypot: a real visitor never fills the hidden "company" field. Mimic
        // a successful submission so bots learn nothing, but store/send nothing.
        if (filled($request->input('company'))) {
            return $this->finish($config);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'subject' => ['nullable', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:5000'],
        ], [], ['message' => 'message']);

        $message = ContactMessage::create([
            'site_id' => $site->getKey(),
            'page_id' => $this->resolvePageId($site, $config['page_id'] ?? null),
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'subject' => $data['subject'] ?? null,
            'message' => $data['message'],
        ]);

        $this->notify($site, $message, $config);

        return $this->finish($config);
    }

    /**
     * @return array<string, mixed>
     */
    protected function decodeConfig(?string $raw): array
    {
        if (blank($raw)) {
            return [];
        }

        try {
            $config = Crypt::decrypt($raw);
        } catch (DecryptException) {
            return [];
        }

        return is_array($config) ? $config : [];
    }

    /**
     * Only keep a page reference that genuinely belongs to this site.
     */
    protected function resolvePageId(Site $site, mixed $pageId): ?int
    {
        if (blank($pageId)) {
            return null;
        }

        return Page::query()
            ->whereBelongsTo($site, 'site')
            ->whereKey($pageId)
            ->value('id');
    }

    /**
     * @param  array<string, mixed>  $config
     */
    protected function notify(Site $site, ContactMessage $message, array $config): void
    {
        if (($config['send'] ?? true) === false) {
            return;
        }

        $recipients = $this->recipients($site, $config['to'] ?? null);

        if ($recipients === []) {
            return;
        }

        try {
            Mail::to($recipients)->send(new ContactSubmissionMail($site, $message));
        } catch (\Throwable $e) {
            // A delivery failure must never lose the visitor's message — it is
            // already stored and visible under Form submissions.
            Log::warning('Contact form email could not be sent: '.$e->getMessage(), [
                'site_id' => $site->getKey(),
                'contact_message_id' => $message->getKey(),
            ]);
        }
    }

    /**
     * Resolve the notification recipients: the block's configured addresses, or
     * the site's own email as a fallback. Always validated.
     *
     * @return array<int, string>
     */
    protected function recipients(Site $site, mixed $to): array
    {
        $addresses = collect(preg_split('/[,;]+/', (string) ($to ?? '')))
            ->map(fn (string $address): string => trim($address))
            ->filter()
            ->all();

        if ($addresses === []) {
            $siteEmail = Setting::forSite($site)->site_email;

            if (filled($siteEmail)) {
                $addresses = [$siteEmail];
            }
        }

        return array_values(array_filter(
            $addresses,
            fn (string $address): bool => (bool) filter_var($address, FILTER_VALIDATE_EMAIL),
        ));
    }

    /**
     * @param  array<string, mixed>  $config
     */
    protected function finish(array $config): RedirectResponse
    {
        if (filled($config['redirect'] ?? null)) {
            return redirect()->away($config['redirect']);
        }

        return back()->with(
            'contact_form_success',
            $config['success'] ?? 'Thanks! Your message has been sent.',
        );
    }
}
