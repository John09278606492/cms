<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Site;
use App\Models\Tag;
use App\Models\User;
use Datlechin\FilamentMenuBuilder\Models\MenuItem;
use Datlechin\FilamentMenuBuilder\Models\MenuLocation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;

/**
 * Builds a complete, creative demo site — "Lumina, Dark-Sky Retreats" — to
 * exercise the CMS end to end: tenant settings, page-builder pages (hero,
 * feature grid, rich text, image, CTA), auto-synced navigation, a footer menu,
 * and a categorised/tagged blog. Idempotent: re-running rebuilds the demo site
 * without touching any other tenant.
 */
class ExampleSiteSeeder extends Seeder
{
    private const SLUG = 'lumina';

    public function run(): void
    {
        $owner = User::query()->updateOrCreate(
            ['email' => 'vera@lumina.test'],
            ['name' => 'Vera Lumen', 'password' => 'password'],
        );

        if ($role = Role::query()->where('name', 'site_owner')->first()) {
            $owner->assignRole($role);
        }

        $site = Site::query()->updateOrCreate(
            ['slug' => self::SLUG],
            [
                'name' => 'Lumina',
                'owner_id' => $owner->getKey(),
                'description' => 'Dark-sky retreats and guided stargazing, 9,000 feet above the nearest streetlight.',
                'is_active' => true,
            ],
        );

        $this->resetDemoContent($site);
        $this->configureSettings($site);

        $pages = $this->createPages($site, $owner);
        $this->createBlog($site, $owner);
        $this->createFooterMenu($site);

        $this->command?->info("Lumina demo site ready at /sites/{$site->slug}");
    }

    private function resetDemoContent(Site $site): void
    {
        MenuItem::query()
            ->whereIn('menu_id', $site->menus()->pluck('id'))
            ->delete();
        $site->menus()->delete();
        $site->posts()->withTrashed()->forceDelete();
        $site->pages()->withTrashed()->forceDelete();
        $site->categories()->delete();
        $site->tags()->delete();
    }

    private function configureSettings(Site $site): void
    {
        Setting::query()->updateOrCreate(
            ['site_id' => $site->getKey()],
            [
                'site_name' => 'Lumina',
                'site_tagline' => 'Dark-Sky Retreats',
                'site_description' => 'A high-altitude basecamp for meteor showers, aurora nights, and slow mornings under impossibly clear skies.',
                'site_email' => 'hello@lumina.test',
                'site_phone' => '+1 (555) 0199-STAR',
                'site_address' => 'Ridgeline Camp, Cascade Dark-Sky Reserve',
                'posts_per_page' => 6,
                'meta_title' => 'Lumina — Dark-Sky Retreats',
                'meta_description' => 'Guided stargazing retreats, meteor camps, and aurora voyages in a certified dark-sky reserve.',
                'social_links' => [
                    ['platform' => 'Instagram', 'url' => 'https://instagram.com/lumina'],
                    ['platform' => 'YouTube', 'url' => 'https://youtube.com/@lumina'],
                ],
            ],
        );
    }

    /**
     * @return array<string, Page>
     */
    private function createPages(Site $site, User $owner): array
    {
        $home = $this->page($site, $owner, [
            'title' => 'Home',
            'slug' => 'home',
            'is_homepage' => true,
            'show_in_menu' => false,
            'excerpt' => 'Trade light pollution for the Milky Way.',
            'content' => ['rows' => [
                $this->widgetRow('hero', [
                    'eyebrow' => 'Certified Dark-Sky Reserve',
                    'heading' => 'The night sky, the way it was meant to be seen.',
                    'copy' => 'Lumina is a small basecamp at 9,000 feet, far past the last streetlight. Come for the meteor showers, stay for the silence — guided by astronomers who know every constellation by heart.',
                    'primary_label' => 'Explore experiences',
                    'primary_url' => "/sites/{$site->slug}/pages/experiences",
                    'secondary_label' => 'Read the field notes',
                    'secondary_url' => "/sites/{$site->slug}/blog",
                    'alignment' => 'center',
                    'surface' => 'contrast',
                ]),
                $this->widgetRow('feature-grid', [
                    'eyebrow' => 'What awaits',
                    'heading' => 'Three ways to meet the dark',
                    'intro' => 'Every stay includes a guided sky session, warm gear, and a thermos of something good.',
                    'columns' => '3',
                    'items' => [
                        ['emoji' => '🌠', 'title' => 'Meteor Camps', 'description' => 'Heated reclining pods aimed straight up during peak shower nights.'],
                        ['emoji' => '🔭', 'title' => 'Telescope Suites', 'description' => 'Private 14-inch Dobsonians and an astronomer on call until dawn.'],
                        ['emoji' => '🌌', 'title' => 'Aurora Voyages', 'description' => 'Winter expeditions chasing the lights to the edge of the reserve.'],
                    ],
                ]),
                $this->widgetRow('image', [
                    'src' => 'https://picsum.photos/seed/lumina-sky/1280/720',
                    'alt' => 'The Milky Way arcing over a dark mountain ridge',
                    'caption' => 'A 20-second exposure from Ridgeline Camp — no filter, no city glow.',
                    'width' => 'wide',
                ]),
                $this->widgetRow('rich-text', [
                    'heading' => 'Why darkness matters',
                    'width' => 'content',
                    'content' => '<p>Two-thirds of people will never see the Milky Way from home. Lumina exists to give it back — a place where your eyes adjust, your phone goes dark, and 2,500 stars come out one by one.</p><p>We are a Bronze-tier International Dark-Sky community. Every light on the property points down, glows amber, and switches off by 10pm.</p>',
                ]),
                $this->widgetRow('call-to-action', [
                    'eyebrow' => 'Limited to 12 guests a night',
                    'heading' => 'Reserve a night under the dark.',
                    'copy' => 'New-moon weekends book out months ahead. Join the waitlist and we will hold your spot for the next clear sky.',
                    'button_label' => 'Plan your visit',
                    'button_url' => "/sites/{$site->slug}/pages/visit",
                    'theme' => 'amber',
                ]),
            ]],
        ]);

        $experiences = $this->page($site, $owner, [
            'title' => 'Experiences',
            'slug' => 'experiences',
            'show_in_menu' => true,
            'sort_order' => 1,
            'excerpt' => 'Guided nights for first-timers and seasoned sky-watchers alike.',
            'content' => ['rows' => [
                $this->widgetRow('hero', [
                    'eyebrow' => 'Experiences',
                    'heading' => 'Pick your kind of dark.',
                    'copy' => 'From a casual meteor night to a multi-day aurora expedition, every experience is small-group and astronomer-led.',
                    'alignment' => 'start',
                    'surface' => 'soft',
                ]),
                $this->widgetRow('feature-grid', [
                    'heading' => 'The full menu',
                    'columns' => '2',
                    'items' => [
                        ['emoji' => '🌠', 'title' => 'Meteor Camp · 1 night', 'description' => 'Peak-shower nights in heated pods with a guided tour of what is overhead.'],
                        ['emoji' => '🔭', 'title' => 'Deep-Sky Suite · 2 nights', 'description' => 'Your own telescope, astrophotography coaching, and late-night galaxy hunting.'],
                        ['emoji' => '🌌', 'title' => 'Aurora Voyage · 4 nights', 'description' => 'A winter chase to the northern edge of the reserve when the forecast lights up.'],
                        ['emoji' => '🌅', 'title' => 'Slow Morning · add-on', 'description' => 'Sunrise yoga on the ridge and pour-over coffee before you head home.'],
                    ],
                ]),
                $this->widgetRow('call-to-action', [
                    'heading' => 'Not sure which to pick?',
                    'copy' => 'Tell us your dates and we will match you to the clearest skies and the right guide.',
                    'button_label' => 'Ask an astronomer',
                    'button_url' => 'mailto:hello@lumina.test',
                    'theme' => 'stone',
                ]),
            ]],
        ]);

        $about = $this->page($site, $owner, [
            'title' => 'About',
            'slug' => 'about',
            'show_in_menu' => true,
            'sort_order' => 2,
            'excerpt' => 'A tiny team obsessed with giving the night sky back.',
            'content' => ['rows' => [
                $this->widgetRow('rich-text', [
                    'heading' => 'Our story',
                    'content' => '<p>Lumina started with a broken-down telescope and a frustration: you had to drive four hours from the city to see anything at all. So we built a place worth driving to.</p><p>Today we are three astronomers, one chef, and a very good dog named Comet.</p>',
                ]),
                $this->widgetRow('feature-grid', [
                    'eyebrow' => 'What we believe',
                    'heading' => 'Field rules',
                    'columns' => '3',
                    'items' => [
                        ['emoji' => '🕯️', 'title' => 'Lights down', 'description' => 'Amber light only, all of it pointed at the ground.'],
                        ['emoji' => '🤫', 'title' => 'Quiet nights', 'description' => 'No engines, no speakers — just wind and the occasional gasp.'],
                        ['emoji' => '♻️', 'title' => 'Leave it darker', 'description' => 'We give more to the reserve than we take from it.'],
                    ],
                ]),
                $this->widgetRow('call-to-action', [
                    'heading' => 'Come see for yourself.',
                    'copy' => 'Words do not do a dark sky justice. Book a night and let your eyes do the talking.',
                    'button_label' => 'Plan your visit',
                    'button_url' => "/sites/{$site->slug}/pages/visit",
                    'theme' => 'amber',
                ]),
            ]],
        ]);

        $visit = $this->page($site, $owner, [
            'title' => 'Visit',
            'slug' => 'visit',
            'show_in_menu' => true,
            'sort_order' => 3,
            'excerpt' => 'How to find us, what to pack, and when the skies are best.',
            'content' => ['rows' => [
                $this->widgetRow('hero', [
                    'eyebrow' => 'Visit',
                    'heading' => 'Getting to the dark.',
                    'copy' => 'Ridgeline Camp sits inside the Cascade Dark-Sky Reserve — about two hours from the nearest airport and a world away from the nearest billboard.',
                    'alignment' => 'start',
                    'surface' => 'minimal',
                ]),
                $this->widgetRow('rich-text', [
                    'heading' => 'Before you come',
                    'content' => '<ul><li><strong>Best skies:</strong> new-moon weekends, September through April.</li><li><strong>Pack:</strong> layers, a red flashlight, and your worst sense of bedtime.</li><li><strong>Getting here:</strong> we send a shuttle from the valley trailhead at dusk.</li></ul>',
                ]),
                $this->widgetRow('call-to-action', [
                    'eyebrow' => 'Questions?',
                    'heading' => 'Reach the basecamp.',
                    'copy' => 'Email hello@lumina.test or call the camp line — a real human answers until midnight.',
                    'button_label' => 'Email us',
                    'button_url' => 'mailto:hello@lumina.test',
                    'theme' => 'stone',
                ]),
            ]],
        ]);

        return compact('home', 'experiences', 'about', 'visit');
    }

    private function createBlog(Site $site, User $owner): void
    {
        $events = Category::query()->create(['site_id' => $site->getKey(), 'name' => 'Celestial Events', 'slug' => 'celestial-events']);
        $guides = Category::query()->create(['site_id' => $site->getKey(), 'name' => 'Field Guides', 'slug' => 'field-guides']);
        $life = Category::query()->create(['site_id' => $site->getKey(), 'name' => 'Retreat Life', 'slug' => 'retreat-life']);

        $tags = collect(['aurora', 'meteor-showers', 'astrophotography', 'dark-sky', 'beginners'])
            ->mapWithKeys(fn (string $slug): array => [$slug => Tag::query()->create([
                'site_id' => $site->getKey(),
                'name' => ucwords(str_replace('-', ' ', $slug)),
                'slug' => $slug,
            ])]);

        $posts = [
            [
                'title' => 'Chasing the Geminids: a field log from Ridgeline Camp',
                'excerpt' => 'One pod, two thermoses, and 94 meteors before midnight. Here is how the best shower of the year unfolded.',
                'category' => $events,
                'tags' => ['meteor-showers', 'astrophotography'],
                'days_ago' => 4,
                'body' => '<p>We counted our first meteor at 9:42pm — a slow, green-tinged streak that drew an actual gasp from pod three. By midnight we were past ninety.</p><p>The Geminids are the most reliable shower of the year, and from a true dark site they are simply unreal. No telescope required: just lie back, let your eyes adjust for twenty minutes, and wait.</p>',
            ],
            [
                'title' => 'How to read the night sky without a telescope',
                'excerpt' => 'Five anchor points that turn a wall of stars into a map you can actually navigate.',
                'category' => $guides,
                'tags' => ['dark-sky', 'beginners'],
                'days_ago' => 11,
                'body' => '<p>Start with the brightest thing you can find and work outward. Once you can hop from the Big Dipper to Polaris to Cassiopeia, the rest of the sky stops being intimidating.</p><p>Bring a red flashlight, leave the phone in your pocket, and give your eyes a full twenty minutes. Patience is the only gear that matters.</p>',
            ],
            [
                'title' => 'Why we built a retreat 9,000 feet above the light',
                'excerpt' => 'A short manifesto about silence, altitude, and giving the Milky Way back to people who have never seen it.',
                'category' => $life,
                'tags' => ['dark-sky'],
                'days_ago' => 23,
                'body' => '<p>Two-thirds of humanity lives under skies too bright to see our own galaxy. That statistic is why Lumina exists.</p><p>Altitude buys us thinner, drier, steadier air. Distance buys us darkness. Together they buy you a view that rearranges how you think about your place in things.</p>',
            ],
            [
                'title' => 'Aurora season is coming — what to pack',
                'excerpt' => 'The forecast is stirring. Here is the exact kit our guides carry on a winter aurora voyage.',
                'category' => $events,
                'tags' => ['aurora', 'astrophotography'],
                'days_ago' => 2,
                'body' => '<p>Solar cycle 25 is near its peak, and our long-range models are lighting up. If you have ever wanted to see the aurora, this is the winter.</p><p>Pack in layers, bring hand warmers for your camera batteries, and set your expectations to <em>maybe</em> — the lights keep their own schedule, and the chase is half the magic.</p>',
            ],
        ];

        foreach ($posts as $data) {
            $post = Post::query()->create([
                'site_id' => $site->getKey(),
                'user_id' => $owner->getKey(),
                'title' => $data['title'],
                'slug' => str($data['title'])->slug(),
                'excerpt' => $data['excerpt'],
                'status' => ContentStatus::Published,
                'published_at' => Carbon::now()->subDays($data['days_ago']),
                'is_featured' => $data['days_ago'] < 5,
                'content' => ['rows' => [
                    $this->widgetRow('rich-text', [
                        'content' => $data['body'],
                        'width' => 'content',
                    ]),
                ]],
            ]);

            $post->categories()->attach($data['category']->getKey());
            $post->tags()->attach(
                collect($data['tags'])->map(fn (string $slug) => $tags[$slug]->getKey())->all(),
            );
        }
    }

    private function createFooterMenu(Site $site): void
    {
        $footer = Menu::query()->create([
            'site_id' => $site->getKey(),
            'name' => 'Footer',
            'is_visible' => true,
        ]);

        MenuLocation::query()->firstOrCreate([
            'menu_id' => $footer->getKey(),
            'location' => 'footer',
        ]);

        $links = [
            ['title' => 'Field Notes', 'url' => "/sites/{$site->slug}/blog"],
            ['title' => 'Visit Us', 'url' => "/sites/{$site->slug}/pages/visit"],
            ['title' => 'Email the Camp', 'url' => 'mailto:hello@lumina.test'],
            ['title' => 'Instagram', 'url' => 'https://instagram.com/lumina'],
        ];

        foreach ($links as $order => $link) {
            MenuItem::query()->create([
                'menu_id' => $footer->getKey(),
                'title' => $link['title'],
                'url' => $link['url'],
                'order' => $order + 1,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function page(Site $site, User $owner, array $attributes): Page
    {
        return Page::query()->create(array_merge([
            'site_id' => $site->getKey(),
            'user_id' => $owner->getKey(),
            'status' => ContentStatus::Published,
            'published_at' => Carbon::now()->subDay(),
            'is_homepage' => false,
            'show_in_menu' => false,
            'sort_order' => 0,
        ], $attributes));
    }

    /**
     * Wrap a single page-builder widget in a full-width row/column.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function widgetRow(string $type, array $data): array
    {
        return [
            'columns' => [
                [
                    'span' => 12,
                    'widgets' => [
                        ['type' => $type, 'data' => $data],
                    ],
                ],
            ],
        ];
    }
}
