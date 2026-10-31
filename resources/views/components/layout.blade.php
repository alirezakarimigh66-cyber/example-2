@props(['title' => 'دوپامین مارکت'])
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} | دوپامین مارکت</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="site-header flex flex-wrap items-center justify-between gap-4 px-5 py-5 sm:px-10">
        <a class="brand" href="{{ route('home') }}">دوپامین<span>مارکت</span></a>
        <nav aria-label="ناوبری اصلی" class="flex items-center gap-4">
            @auth
                <a href="{{ route(auth()->user()->role->dashboardRoute()) }}">حساب من</a>
                <form method="POST" action="{{ route('logout') }}">@csrf <button class="text-button" type="submit">خروج</button></form>
            @else
                <x-auth-trigger mode="login">ورود</x-auth-trigger>
                <x-auth-trigger mode="register" class="button secondary">ساخت حساب</x-auth-trigger>
            @endauth
        </nav>
    </header>
    <div id="auth-notice" role="status" aria-live="polite" class="page-notice">{{ session('status') }}</div>
    {{ $slot }}
    @guest
        <x-auth-modal />
    @endguest
</body>
</html>
