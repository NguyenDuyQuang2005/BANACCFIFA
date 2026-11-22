@props([
    'title' => null,
    'metaDescription' => null,
    'robots' => 'index, follow',
])

@php
    $pageTitle = $title ? $title.' | '.config('site.name') : config('site.name').' — '.config('site.tagline');
    $description = $metaDescription ?? config('site.description');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $pageTitle }}</title>
        <meta name="description" content="{{ $description }}">
        <link rel="canonical" href="{{ url()->current() }}">
        <meta name="robots" content="{{ $robots }}">
        <meta property="og:title" content="{{ $pageTitle }}">
        <meta property="og:description" content="{{ $description }}">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:site_name" content="{{ config('site.name') }}">
        <meta name="twitter:card" content="summary_large_image">
        @if (config('site.seo.og_image'))
            <meta property="og:image" content="{{ config('site.seo.og_image') }}">
            <meta name="twitter:image" content="{{ config('site.seo.og_image') }}">
        @endif
        @if (config('site.seo.google_verification'))
            <meta name="google-site-verification" content="{{ config('site.seo.google_verification') }}">
        @endif
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-canvas text-ink antialiased">
        <a href="#noi-dung" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-accent focus:px-4 focus:py-2 focus:text-canvas">
            Bỏ qua điều hướng
        </a>

        <x-header :site="$site" :categories="$navCategories" :tools="$navTools" />

        <main id="noi-dung" class="pb-24 lg:pb-0">
            {{ $slot }}
        </main>

        <x-footer :site="$site" :tools="$navTools" />
        <x-mobile-dock />

        <x-buy-modal />

        @if ($sitePopup)
            <x-site-popup :popup="$sitePopup" />
        @endif
    </body>
</html>
