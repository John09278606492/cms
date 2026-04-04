<?php

return [
    'generator' => [
        'namespace' => 'App\\Mason',
        'views_path' => 'mason',
    ],
    'preview' => [
        'layout' => 'layouts.mason-preview',
    ],
    'entry' => [
        'layout' => 'layouts.mason-preview',
    ],
    'routes' => [
        'middleware' => ['web', 'auth'],
    ],
];
