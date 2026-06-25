<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Mail\ContactSubmissionMail;
use App\Models\ContactMessage;
use App\Models\Page;
use App\Models\Setting;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Build the encrypted delivery-config field exactly as the block partial
     * does, so submissions in tests carry the same settings as the live form.
     *
     * @param  array<string, mixed>  $overrides
     */
    protected function encryptedConfig(array $overrides = []): string
    {
        return Crypt::encrypt(array_merge([
            'to' => null,
            'send' => true,
            'redirect' => null,
            'success' => null,
            'page_id' => null,
        ], $overrides));
    }

    protected function siteWithContactForm(): Site
    {
        $site = Site::query()->create([
            'name' => 'Contact Site',
            'slug' => 'contact-site',
            'is_active' => true,
        ]);

        Page::query()->create([
            'site_id' => $site->getKey(),
            'title' => 'Contact',
            'slug' => 'contact',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'is_homepage' => true,
            'show_in_menu' => false,
            'content' => [
                ['type' => 'contact_form', 'data' => [
                    'heading' => 'Get in touch',
                    'button_label' => 'Send message',
                    'show_subject' => true,
                ]],
            ],
        ]);

        return $site;
    }

    public function test_contact_form_block_renders_with_a_working_action(): void
    {
        $site = $this->siteWithContactForm();

        $this->get("/sites/{$site->slug}")
            ->assertOk()
            ->assertSee('Get in touch')
            ->assertSee('Send message')
            ->assertSee(route('sites.contact', $site));
    }

    public function test_visitors_can_submit_a_contact_message(): void
    {
        $site = $this->siteWithContactForm();

        $this->post(route('sites.contact', $site), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'subject' => 'Hello',
            'message' => 'I would love to learn more.',
        ])->assertRedirect();

        $this->assertDatabaseHas('contact_messages', [
            'site_id' => $site->getKey(),
            'email' => 'ada@example.com',
            'subject' => 'Hello',
        ]);
    }

    public function test_honeypot_silently_discards_bot_submissions(): void
    {
        $site = $this->siteWithContactForm();

        $this->post(route('sites.contact', $site), [
            'name' => 'Spam Bot',
            'email' => 'bot@example.com',
            'message' => 'buy now',
            'company' => 'filled-by-bot',
        ])->assertRedirect();

        $this->assertSame(0, ContactMessage::query()->count());
    }

    public function test_contact_submissions_require_valid_input(): void
    {
        $site = $this->siteWithContactForm();

        $this->from("/sites/{$site->slug}")
            ->post(route('sites.contact', $site), [
                'name' => '',
                'email' => 'not-an-email',
                'message' => '',
            ])
            ->assertRedirect("/sites/{$site->slug}")
            ->assertSessionHasErrors(['name', 'email', 'message']);

        $this->assertSame(0, ContactMessage::query()->count());
    }

    public function test_submissions_are_rejected_for_inactive_sites(): void
    {
        $site = Site::query()->create([
            'name' => 'Hidden Site',
            'slug' => 'hidden-site',
            'is_active' => false,
        ]);

        $this->post(route('sites.contact', $site), [
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'message' => 'Hello',
        ])->assertNotFound();

        $this->assertSame(0, ContactMessage::query()->count());
    }

    public function test_editor_preview_does_not_emit_a_form_that_would_block_the_page_editor(): void
    {
        // Rendered as a Filament block preview there is no public {site} route,
        // so the partial must NOT emit a nested <form> or `required` inputs.
        // Those would be hoisted into the editor's own form by the browser and
        // block its Save button (the original "can't save" bug).
        $html = view('page-builder.blocks.contact_form', [
            'heading' => 'Talk to us',
            'button_label' => 'Send',
            'show_subject' => true,
            'show_phone' => true,
            'send_email' => true,
            'to_email' => 'owner@example.com',
            'success_message' => 'Thanks',
        ])->render();

        $this->assertStringNotContainsString('<form', $html);
        $this->assertStringNotContainsString('required', $html);
        $this->assertStringNotContainsString('name="message"', $html);
        // It still shows the fields visually so the preview is meaningful.
        $this->assertStringContainsString('Message', $html);
    }

    public function test_public_page_renders_a_real_submittable_form(): void
    {
        $site = $this->siteWithContactForm();

        $this->get("/sites/{$site->slug}")
            ->assertOk()
            ->assertSee('<form', false)
            ->assertSee('name="message"', false)
            ->assertSee(route('sites.contact', $site), false);
    }

    public function test_a_notification_email_is_sent_to_the_configured_recipient(): void
    {
        Mail::fake();
        $site = $this->siteWithContactForm();

        $this->post(route('sites.contact', $site), [
            'config' => $this->encryptedConfig(['to' => 'owner@example.com']),
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'subject' => 'Hello',
            'message' => 'I would love to learn more.',
        ])->assertRedirect();

        Mail::assertSent(ContactSubmissionMail::class, function (ContactSubmissionMail $mail): bool {
            return $mail->hasTo('owner@example.com')
                && $mail->message->email === 'ada@example.com';
        });
    }

    public function test_notifications_fall_back_to_the_site_email(): void
    {
        Mail::fake();
        $site = $this->siteWithContactForm();

        Setting::query()->updateOrCreate(
            ['site_id' => $site->getKey()],
            ['site_email' => 'hello@site.test'],
        );

        $this->post(route('sites.contact', $site), [
            'config' => $this->encryptedConfig(),
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'message' => 'Hello',
        ])->assertRedirect();

        Mail::assertSent(ContactSubmissionMail::class, fn (ContactSubmissionMail $mail): bool => $mail->hasTo('hello@site.test'));
    }

    public function test_no_email_is_sent_when_notifications_are_disabled(): void
    {
        Mail::fake();
        $site = $this->siteWithContactForm();

        $this->post(route('sites.contact', $site), [
            'config' => $this->encryptedConfig(['send' => false, 'to' => 'owner@example.com']),
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'message' => 'Hello',
        ])->assertRedirect();

        Mail::assertNothingSent();
        $this->assertSame(1, ContactMessage::query()->count());
    }

    public function test_a_phone_number_is_stored_when_provided(): void
    {
        Mail::fake();
        $site = $this->siteWithContactForm();

        $this->post(route('sites.contact', $site), [
            'config' => $this->encryptedConfig(),
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'phone' => '+1 555 0100',
            'message' => 'Call me',
        ])->assertRedirect();

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'ada@example.com',
            'phone' => '+1 555 0100',
        ]);
    }

    public function test_visitors_are_redirected_to_a_configured_url(): void
    {
        Mail::fake();
        $site = $this->siteWithContactForm();

        $this->post(route('sites.contact', $site), [
            'config' => $this->encryptedConfig(['redirect' => 'https://example.com/thank-you']),
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'message' => 'Hello',
        ])->assertRedirect('https://example.com/thank-you');
    }

    public function test_a_tampered_config_cannot_redirect_submissions(): void
    {
        Mail::fake();
        $site = $this->siteWithContactForm();

        // A hand-crafted (unencrypted) config is ignored, so an attacker cannot
        // inject their own recipient or redirect.
        $this->post(route('sites.contact', $site), [
            'config' => json_encode(['to' => 'attacker@evil.test', 'send' => true]),
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'message' => 'Hello',
        ])->assertRedirect();

        Mail::assertNothingSent();
        $this->assertSame(1, ContactMessage::query()->count());
    }
}
