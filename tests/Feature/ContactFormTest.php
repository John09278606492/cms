<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\ContactMessage;
use App\Models\Page;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

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
}
