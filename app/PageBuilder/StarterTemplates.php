<?php

namespace App\PageBuilder;

/**
 * Pre-built, ready-to-insert page sections that ship with the CMS (Elementor's
 * "template library"). Each entry is a list of blocks in the flat
 * ['type' => ..., 'data' => ...] format the page builder stores. Owners insert
 * them from the page editor and then edit the placeholder copy.
 */
class StarterTemplates
{
    /**
     * @return array<string, array{label: string, description: string, blocks: array<int, array{type: string, data: array<string, mixed>}>}>
     */
    public static function all(): array
    {
        return [
            'landing' => [
                'label' => 'Landing page',
                'description' => 'Hero, feature grid and a closing call to action.',
                'blocks' => [
                    self::block('hero', [
                        'eyebrow' => 'Welcome',
                        'heading' => 'A bold headline that sells the idea',
                        'copy' => 'Explain the value in a sentence or two. Keep it focused on the visitor and the outcome they want.',
                        'primary_label' => 'Get started',
                        'primary_url' => '#',
                        'secondary_label' => 'Learn more',
                        'secondary_url' => '#',
                        'surface' => 'contrast',
                        'align' => 'center',
                    ]),
                    self::block('feature_grid', [
                        'eyebrow' => 'Why us',
                        'heading' => 'Everything you need',
                        'columns' => '3',
                        'items' => [
                            ['emoji' => '⚡', 'title' => 'Fast', 'description' => 'Describe a key benefit here.'],
                            ['emoji' => '🔒', 'title' => 'Secure', 'description' => 'Describe a key benefit here.'],
                            ['emoji' => '💜', 'title' => 'Loved', 'description' => 'Describe a key benefit here.'],
                        ],
                    ]),
                    self::block('call_to_action', [
                        'heading' => 'Ready to begin?',
                        'copy' => 'Add a final nudge for visitors to take the next step.',
                        'button_label' => 'Get started',
                        'button_url' => '#',
                        'theme' => 'amber',
                    ]),
                ],
            ],
            'about' => [
                'label' => 'About page',
                'description' => 'Intro heading, image + text, stats and a testimonial.',
                'blocks' => [
                    self::block('heading', ['text' => 'About us', 'level' => 'h1', 'align' => 'center']),
                    self::block('media_text', [
                        'image_side' => 'left',
                        'heading' => 'Our story',
                        'body' => '<p>Share how you started and what you care about. A short, human paragraph works best.</p>',
                        'button_label' => 'Meet the team',
                        'button_url' => '#',
                    ]),
                    self::block('stats', [
                        'heading' => 'By the numbers',
                        'columns' => '3',
                        'items' => [
                            ['value' => '10+', 'label' => 'Years'],
                            ['value' => '5k', 'label' => 'Customers'],
                            ['value' => '99%', 'label' => 'Satisfaction'],
                        ],
                    ]),
                    self::block('testimonial', [
                        'quote' => 'A short, glowing quote from a happy customer goes here.',
                        'author' => 'Jordan Rivers',
                        'role' => 'Founder, Acme',
                    ]),
                ],
            ],
            'services' => [
                'label' => 'Services / pricing',
                'description' => 'Section heading, feature grid and a pricing table.',
                'blocks' => [
                    self::block('heading', ['text' => 'What we offer', 'level' => 'h2', 'align' => 'center']),
                    self::block('feature_grid', [
                        'heading' => 'Services',
                        'columns' => '3',
                        'items' => [
                            ['emoji' => '🎯', 'title' => 'Strategy', 'description' => 'Describe this service.'],
                            ['emoji' => '🎨', 'title' => 'Design', 'description' => 'Describe this service.'],
                            ['emoji' => '🚀', 'title' => 'Delivery', 'description' => 'Describe this service.'],
                        ],
                    ]),
                    self::block('pricing_table', [
                        'heading' => 'Simple pricing',
                        'columns' => '3',
                        'plans' => [
                            ['name' => 'Starter', 'price' => '$0', 'period' => '/mo', 'features' => "1 project\nCommunity support", 'button_label' => 'Choose', 'button_url' => '#'],
                            ['name' => 'Pro', 'price' => '$29', 'period' => '/mo', 'featured' => true, 'features' => "Unlimited projects\nPriority support", 'button_label' => 'Choose', 'button_url' => '#'],
                            ['name' => 'Team', 'price' => '$99', 'period' => '/mo', 'features' => "Everything in Pro\nSSO", 'button_label' => 'Choose', 'button_url' => '#'],
                        ],
                    ]),
                ],
            ],
            'contact' => [
                'label' => 'Contact page',
                'description' => 'Heading, contact form and a map.',
                'blocks' => [
                    self::block('heading', ['text' => 'Get in touch', 'level' => 'h1', 'align' => 'center']),
                    self::block('contact_form', [
                        'heading' => 'Send us a message',
                        'intro' => 'We usually reply within one business day.',
                        'button_label' => 'Send message',
                        'show_subject' => true,
                        'send_email' => true,
                    ]),
                    self::block('map', ['query' => 'Times Square, New York', 'height' => 'md', 'zoom' => '13']),
                ],
            ],
            'event' => [
                'label' => 'Event page',
                'description' => 'Hero, countdown, key numbers and a registration form.',
                'blocks' => [
                    self::block('hero', [
                        'eyebrow' => 'Save the date',
                        'heading' => 'Our biggest event of the year',
                        'copy' => 'Add the date, venue and a one-line pitch that makes people want to attend.',
                        'primary_label' => 'Register now',
                        'primary_url' => '#register',
                        'surface' => 'contrast',
                        'align' => 'center',
                    ]),
                    self::block('countdown', [
                        'heading' => 'Starts in',
                        'until' => now()->addDays(30)->setTime(9, 0)->toDateTimeString(),
                        'expired_text' => "It's happening today!",
                    ]),
                    self::block('counter', [
                        'heading' => 'Why attend',
                        'columns' => '3',
                        'items' => [
                            ['value' => 40, 'suffix' => '+', 'label' => 'Speakers'],
                            ['value' => 12, 'label' => 'Workshops'],
                            ['value' => 2000, 'suffix' => '+', 'label' => 'Attendees'],
                        ],
                    ]),
                    self::block('contact_form', [
                        'heading' => 'Register your interest',
                        'button_label' => 'Count me in',
                        'show_subject' => false,
                        'send_email' => true,
                    ]),
                ],
            ],
            'portfolio' => [
                'label' => 'Portfolio / work',
                'description' => 'Intro, image carousel, services and testimonial.',
                'blocks' => [
                    self::block('heading', ['text' => 'Selected work', 'level' => 'h1', 'align' => 'center']),
                    self::block('carousel', ['images' => [], 'autoplay' => true, 'interval' => '5000', 'ratio' => 'wide']),
                    self::block('feature_grid', [
                        'eyebrow' => 'What I do',
                        'heading' => 'Capabilities',
                        'columns' => '3',
                        'items' => [
                            ['emoji' => '🎨', 'title' => 'Branding', 'description' => 'Describe this capability.'],
                            ['emoji' => '🖥️', 'title' => 'Web', 'description' => 'Describe this capability.'],
                            ['emoji' => '📷', 'title' => 'Photography', 'description' => 'Describe this capability.'],
                        ],
                    ]),
                    self::block('testimonial', [
                        'quote' => 'A short, glowing quote from a happy client.',
                        'author' => 'A. Client',
                        'role' => 'Director, Studio',
                    ]),
                ],
            ],
        ];
    }

    /**
     * @return array<string, string> key => label, for a Select.
     */
    public static function options(): array
    {
        return collect(self::all())->map(fn (array $t): string => $t['label'])->all();
    }

    /**
     * @return array<int, array{type: string, data: array<string, mixed>}>
     */
    public static function blocks(string $key): array
    {
        return self::all()[$key]['blocks'] ?? [];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{type: string, data: array<string, mixed>}
     */
    protected static function block(string $type, array $data): array
    {
        return ['type' => $type, 'data' => $data];
    }
}
