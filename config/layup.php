<?php

return [
    'widgets' => [
        \App\Layup\Widgets\HeroWidget::class,
        \App\Layup\Widgets\RichTextWidget::class,
        \App\Layup\Widgets\ImageWidget::class,
        \App\Layup\Widgets\FeatureGridWidget::class,
        \App\Layup\Widgets\CallToActionWidget::class,
    ],

    'pages' => [
        'table' => 'layup_pages',
        'model' => \App\Models\Page::class,
        'default_slug' => null,
    ],

    'frontend' => [
        'enabled' => false,
        'prefix' => 'pages',
        'middleware' => ['web'],
        'domain' => null,
        'layout' => 'app',
        'view' => 'layup::frontend.page',
        'max_width' => 'container',
        'include_scripts' => true,
        'excluded_paths' => [],
    ],
];
