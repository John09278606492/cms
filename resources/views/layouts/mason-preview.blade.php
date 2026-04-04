<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @masonStyles
    </head>
    <body class="min-h-screen bg-stone-100 px-6 py-8 text-stone-900">
        <main id="mason-preview-container" class="mx-auto max-w-5xl">
            @include('mason::iframe-preview-content', ['blocks' => $blocks])
        </main>
    </body>
</html>
