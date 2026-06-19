<?php

use App\Models\Setting;
use App\Models\Site;
use App\Support\CmsForgeBanner;
use App\Support\SiteVisibility;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $platformPanel = Filament::getPanel('platform');
    $adminPanel = Filament::getPanel('admin');

    return view('cms.platform', [
        'settings' => Setting::make([
            'site_name' => config('app.name'),
            'site_tagline' => 'Tenant-powered CMS platform',
            'meta_description' => 'Launch and manage tenant-owned websites with Laravel and Filament.',
        ]),
        'sites' => Site::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->get(),
        'cmsForgeBanner' => CmsForgeBanner::forLanding(
            $platformPanel?->getLoginUrl(),
            $adminPanel?->getLoginUrl(),
        ),
        'platformLoginUrl' => $platformPanel?->getLoginUrl(),
        'siteOwnerLoginUrl' => $adminPanel?->getLoginUrl(),
        'siteRegistrationUrl' => $adminPanel?->getRegistrationUrl(),
    ]);
})->name('platform.home');

Route::prefix('/sites/{site:slug}')->name('sites.')->group(function (): void {
    Route::get('/', function (Site $site) {
        abort_unless(SiteVisibility::canView($site, auth()->user()), 404, 'This site is not currently active.');

        $settings = Setting::forSite($site);
        $homepage = $site->pages()
            ->published()
            ->where('is_homepage', true)
            ->first();

        if ($homepage) {
            $homepage->load(['author', 'featuredImage']);

            return view('cms.page', [
                'site' => $site,
                'page' => $homepage,
                'settings' => $settings,
            ]);
        }

        return view('cms.home', [
            'site' => $site,
            'posts' => $site->posts()
                ->published()
                ->with(['author', 'categories', 'tags', 'featuredImage'])
                ->latest('published_at')
                ->take(6)
                ->get(),
            'settings' => $settings,
        ]);
    })->name('home');

    Route::get('/blog', function (Site $site) {
        abort_unless(SiteVisibility::canView($site, auth()->user()), 404, 'This site is not currently active.');

        $settings = Setting::forSite($site);

        return view('cms.blog', [
            'site' => $site,
            'posts' => $site->posts()
                ->published()
                ->with(['author', 'categories', 'tags', 'featuredImage'])
                ->latest('published_at')
                ->paginate(max(1, $settings->posts_per_page ?? 10)),
            'settings' => $settings,
        ]);
    })->name('blog.index');

    Route::get('/blog/{slug}', function (Site $site, string $slug) {
        abort_unless(SiteVisibility::canView($site, auth()->user()), 404, 'This site is not currently active.');

        $post = $site->posts()
            ->with(['author', 'categories', 'tags', 'featuredImage'])
            ->where('slug', $slug)
            ->published()
            ->first();

        abort_if(blank($post), 404, 'Blog post not found.');

        return view('cms.post', [
            'site' => $site,
            'post' => $post,
            'settings' => Setting::forSite($site),
        ]);
    })->name('blog.show');

    Route::middleware('signed')->prefix('preview')->name('preview.')->group(function (): void {
        Route::get('/blog/{slug}', function (Site $site, string $slug) {
            $post = $site->posts()
                ->with(['author', 'categories', 'tags', 'featuredImage'])
                ->where('slug', $slug)
                ->first();

            abort_if(blank($post), 404, 'Preview post not found.');
            abort_if($post->trashed(), 404, 'This preview is no longer available.');

            return view('cms.post', [
                'site' => $site,
                'post' => $post,
                'settings' => Setting::forSite($site),
            ]);
        })->name('blog.show');

        Route::get('/pages/{slug}', function (Site $site, string $slug) {
            $page = $site->pages()
                ->with(['author', 'featuredImage'])
                ->where('slug', $slug)
                ->first();

            abort_if(blank($page), 404, 'Preview page not found.');
            abort_if($page->trashed(), 404, 'This preview is no longer available.');

            return view('cms.page', [
                'site' => $site,
                'page' => $page,
                'settings' => Setting::forSite($site),
            ]);
        })->name('pages.show');
    });

    Route::get('/pages/{slug}', function (Site $site, string $slug) {
        abort_unless(SiteVisibility::canView($site, auth()->user()), 404, 'This site is not currently active.');

        $page = $site->pages()
            ->with(['author', 'featuredImage'])
            ->where('slug', $slug)
            ->published()
            ->first();

        abort_if(blank($page), 404, 'Page not found.');

        return view('cms.page', [
            'site' => $site,
            'page' => $page,
            'settings' => Setting::forSite($site),
        ]);
    })->name('pages.show');
});
