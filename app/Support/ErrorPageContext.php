<?php

namespace App\Support;

use App\Models\Site;
use Filament\Facades\Filament;
use Illuminate\Http\Request;

class ErrorPageContext
{
    public function resolve404(Request $request, ?string $message = null): array
    {
        return $this->resolve(
            request: $request,
            message: $message,
            status: 404,
            defaults: [
                'eyebrow' => 'Not found',
                'title' => 'Page not found',
                'description' => 'We could not find the page you were looking for.',
                'primaryLabel' => 'Go home',
                'primaryUrl' => route('platform.home'),
                'secondaryLabel' => 'Browse active sites',
                'secondaryUrl' => route('platform.home'),
                'details' => $message,
                'highlights' => [
                    [
                        'title' => 'Check the URL',
                        'copy' => 'A small typo in the path can lead to this page.',
                    ],
                    [
                        'title' => 'Go back',
                        'copy' => 'Return to the last page and try a different link.',
                    ],
                    [
                        'title' => 'Start over',
                        'copy' => 'Use the platform home to find the right site or article.',
                    ],
                ],
            ],
        );
    }

    public function resolve403(Request $request, ?string $message = null): array
    {
        return $this->resolve(
            request: $request,
            message: $message,
            status: 403,
            defaults: [
                'eyebrow' => 'Forbidden',
                'title' => 'Access denied',
                'description' => 'You do not have permission to view this page.',
                'primaryLabel' => 'Go home',
                'primaryUrl' => route('platform.home'),
                'secondaryLabel' => 'Log in',
                'secondaryUrl' => Filament::getLoginUrl() ?? route('platform.home'),
                'details' => $message,
                'highlights' => [
                    [
                        'title' => 'Not your page',
                        'copy' => 'The account you are using cannot open this route.',
                    ],
                    [
                        'title' => 'Use the right role',
                        'copy' => 'Try again with a role that has the required permission.',
                    ],
                    [
                        'title' => 'Return safely',
                        'copy' => 'You can always go back to the platform homepage.',
                    ],
                ],
            ],
        );
    }

    /**
     * @param  array{
     *     eyebrow: string,
     *     title: string,
     *     description: string,
     *     primaryLabel: string,
     *     primaryUrl: string,
     *     secondaryLabel: string,
     *     secondaryUrl: string,
     *     details: ?string,
     *     highlights: array<int, array{title: string, copy: string}>
     * }  $defaults
     * @return array{
     *     eyebrow: string,
     *     title: string,
     *     description: string,
     *     primaryLabel: string,
     *     primaryUrl: string,
     *     secondaryLabel: string,
     *     secondaryUrl: string,
     *     details: ?string,
     *     highlights: array<int, array{title: string, copy: string}>
     * }
     */
    private function resolve(Request $request, ?string $message, int $status, array $defaults): array
    {
        $path = trim($request->path(), '/');
        $segments = array_values(array_filter(explode('/', $path)));
        $siteSlug = $segments[1] ?? null;
        $section = $segments[2] ?? null;
        $recordSlug = $segments[3] ?? null;
        $site = filled($siteSlug) ? Site::query()->where('slug', $siteSlug)->first() : null;

        if ($status === 403) {
            if (str_starts_with($path, 'admin')) {
                return array_replace($defaults, [
                    'eyebrow' => 'Admin forbidden',
                    'title' => 'Admin access denied',
                    'description' => $message ?? 'You cannot access this admin route.',
                    'primaryLabel' => 'Go to platform',
                    'primaryUrl' => route('platform.home'),
                    'secondaryLabel' => 'Log in again',
                    'secondaryUrl' => Filament::getLoginUrl() ?? route('platform.home'),
                    'details' => $message,
                    'highlights' => [
                        [
                            'title' => 'Admin only',
                            'copy' => 'This route belongs to a restricted dashboard area.',
                        ],
                        [
                            'title' => 'Check your role',
                            'copy' => 'A missing permission usually causes this response.',
                        ],
                        [
                            'title' => 'Try the homepage',
                            'copy' => 'Use the public platform while you sort out access.',
                        ],
                    ],
                ]);
            }

            if (($segments[0] ?? null) === 'sites') {
                return array_replace($defaults, [
                    'eyebrow' => 'Site forbidden',
                    'title' => 'Site access denied',
                    'description' => $message ?? 'You cannot access that tenant or its content.',
                    'primaryLabel' => 'Browse active sites',
                    'primaryUrl' => route('platform.home'),
                    'secondaryLabel' => 'Go home',
                    'secondaryUrl' => route('platform.home'),
                    'details' => $message,
                    'highlights' => [
                        [
                            'title' => 'Tenant restricted',
                            'copy' => 'You may not be a member of this site.',
                        ],
                        [
                            'title' => 'Permission missing',
                            'copy' => 'The action or section may require a different role.',
                        ],
                        [
                            'title' => 'Back to safety',
                            'copy' => 'Return to the public site list and choose another tenant.',
                        ],
                    ],
                ]);
            }

            if (filled($message)) {
                $defaults['description'] = $message;
                $defaults['details'] = $message;
            }

            return $defaults;
        }

        if (($segments[0] ?? null) === 'sites') {
            if (! $site) {
                return array_replace($defaults, [
                    'eyebrow' => 'Tenant not found',
                    'title' => 'Site not found',
                    'description' => $message ?? 'The site in this address does not exist.',
                    'primaryLabel' => 'Browse active sites',
                    'primaryUrl' => route('platform.home'),
                    'secondaryLabel' => 'Go home',
                    'secondaryUrl' => route('platform.home'),
                    'details' => $message,
                    'highlights' => [
                        [
                            'title' => 'Verify the slug',
                            'copy' => 'Check whether the tenant URL was typed correctly.',
                        ],
                        [
                            'title' => 'Browse the catalog',
                            'copy' => 'Start from the platform home and open an active site.',
                        ],
                        [
                            'title' => 'Ask the owner',
                            'copy' => 'The site may have been removed or renamed.',
                        ],
                    ],
                ]);
            }

            if (! $site->is_active) {
                return array_replace($defaults, [
                    'eyebrow' => 'Tenant unavailable',
                    'title' => 'Site unavailable',
                    'description' => $message ?? 'That site exists, but it is currently inactive.',
                    'primaryLabel' => 'Go to platform',
                    'primaryUrl' => route('platform.home'),
                    'secondaryLabel' => 'Open site home',
                    'secondaryUrl' => route('sites.home', $site),
                    'details' => $message,
                    'highlights' => [
                        [
                            'title' => 'Inactive site',
                            'copy' => 'The tenant is present, but it is not published right now.',
                        ],
                        [
                            'title' => 'Return to the platform',
                            'copy' => 'Find another active tenant or create a new one.',
                        ],
                        [
                            'title' => 'Try again later',
                            'copy' => 'The owner may be preparing the site for launch.',
                        ],
                    ],
                ]);
            }

            if ($section === 'blog') {
                $post = filled($recordSlug)
                    ? $site->posts()->withoutGlobalScopes()->where('slug', $recordSlug)->first()
                    : null;

                if ($post && ! $post->isPublished()) {
                    return array_replace($defaults, [
                        'eyebrow' => 'Blog unavailable',
                        'title' => 'Blog post not published',
                        'description' => 'The article exists, but it is not public right now.',
                        'primaryLabel' => 'Open the blog',
                        'primaryUrl' => route('sites.blog.index', $site),
                        'secondaryLabel' => 'Open site home',
                        'secondaryUrl' => route('sites.home', $site),
                        'details' => $message,
                        'highlights' => [
                            [
                                'title' => 'Article moved',
                                'copy' => 'The post may have a new slug or a different publish state.',
                            ],
                            [
                                'title' => 'Look in the blog',
                                'copy' => 'Browse the latest posts from the site instead.',
                            ],
                            [
                                'title' => 'Return home',
                                'copy' => 'You can always start again from the tenant homepage.',
                            ],
                        ],
                    ]);
                }

                return array_replace($defaults, [
                    'eyebrow' => 'Blog post not found',
                    'title' => 'Blog post not found',
                    'description' => $message ?? 'We could not find that article on this site.',
                    'primaryLabel' => 'Open the blog',
                    'primaryUrl' => route('sites.blog.index', $site),
                    'secondaryLabel' => 'Open site home',
                    'secondaryUrl' => route('sites.home', $site),
                    'details' => $message,
                    'highlights' => [
                        [
                            'title' => 'Article moved',
                            'copy' => 'The post may have a new slug or a different publish state.',
                        ],
                        [
                            'title' => 'Look in the blog',
                            'copy' => 'Browse the latest posts from the site instead.',
                        ],
                        [
                            'title' => 'Return home',
                            'copy' => 'You can always start again from the tenant homepage.',
                        ],
                    ],
                ]);
            }

            if ($section === 'pages') {
                $page = filled($recordSlug)
                    ? $site->pages()->withoutGlobalScopes()->where('slug', $recordSlug)->first()
                    : null;

                if ($page && ! $page->isPublished()) {
                    return array_replace($defaults, [
                        'eyebrow' => 'Page unavailable',
                        'title' => 'Page not published',
                        'description' => 'The page exists, but it is not public right now.',
                        'primaryLabel' => 'Open site home',
                        'primaryUrl' => route('sites.home', $site),
                        'secondaryLabel' => 'Open the blog',
                        'secondaryUrl' => route('sites.blog.index', $site),
                        'details' => $message,
                        'highlights' => [
                            [
                                'title' => 'Page moved',
                                'copy' => 'The page may have been renamed or hidden from the menu.',
                            ],
                            [
                                'title' => 'Open the site',
                                'copy' => 'Try the homepage and navigate back to the page.',
                            ],
                            [
                                'title' => 'Check drafts',
                                'copy' => 'The owner may still be editing the content.',
                            ],
                        ],
                    ]);
                }

                return array_replace($defaults, [
                    'eyebrow' => 'Page not found',
                    'title' => 'Page not found',
                    'description' => $message ?? 'We could not find that page on this site.',
                    'primaryLabel' => 'Open site home',
                    'primaryUrl' => route('sites.home', $site),
                    'secondaryLabel' => 'Open the blog',
                    'secondaryUrl' => route('sites.blog.index', $site),
                    'details' => $message,
                    'highlights' => [
                        [
                            'title' => 'Page moved',
                            'copy' => 'The page may have been renamed or hidden from the menu.',
                        ],
                        [
                            'title' => 'Open the site',
                            'copy' => 'Try the homepage and navigate back to the page.',
                        ],
                        [
                            'title' => 'Check drafts',
                            'copy' => 'The owner may still be editing the content.',
                        ],
                    ],
                ]);
            }

            return array_replace($defaults, [
                'eyebrow' => 'Site route not found',
                'title' => 'Section not found',
                'description' => $message ?? 'That part of the site is not available.',
                'primaryLabel' => 'Open site home',
                'primaryUrl' => route('sites.home', $site),
                'secondaryLabel' => 'Open the blog',
                'secondaryUrl' => route('sites.blog.index', $site),
                'details' => $message,
                'highlights' => [
                    [
                        'title' => 'Unsupported path',
                        'copy' => 'The site does not expose that section publicly.',
                    ],
                    [
                        'title' => 'Try the homepage',
                        'copy' => 'The homepage will route you to the available content.',
                    ],
                    [
                        'title' => 'View the blog',
                        'copy' => 'Recent posts are often the quickest next step.',
                    ],
                ],
            ]);
        }

        if (str_starts_with($path, 'admin')) {
            return array_replace($defaults, [
                'eyebrow' => 'Admin route not found',
                'title' => 'Dashboard route not found',
                'description' => $message ?? 'The admin page you requested does not exist.',
                'primaryLabel' => 'Go to platform',
                'primaryUrl' => route('platform.home'),
                'secondaryLabel' => 'Go to login',
                'secondaryUrl' => Filament::getLoginUrl() ?? route('platform.home'),
                'details' => $message,
                'highlights' => [
                    [
                        'title' => 'Wrong admin path',
                        'copy' => 'The dashboard route may have changed or been removed.',
                    ],
                    [
                        'title' => 'Sign in again',
                        'copy' => 'If you were logged out, start at the login screen.',
                    ],
                    [
                        'title' => 'Back to the platform',
                        'copy' => 'Return to the public site index and choose another path.',
                    ],
                ],
            ]);
        }

        if (filled($message)) {
            $defaults['description'] = $message;
            $defaults['details'] = $message;
        }

        return $defaults;
    }
}
