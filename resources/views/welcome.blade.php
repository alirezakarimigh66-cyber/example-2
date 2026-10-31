<x-layout title="خانه">
    <main class="public-main mx-auto flex max-w-5xl flex-col items-start gap-8 px-6 py-14 sm:px-10 sm:py-24">
        <p class="muted">دوپامین مارکت / حساب کاربری</p>
        <h1 class="text-4xl leading-normal sm:text-5xl">جای شما در دوپامین</h1>
        <p class="max-w-lg text-lg leading-loose muted">برای دسترسی به حساب شخصی خود وارد شوید. اولین بار است اینجایید؟ با شماره موبایلتان حساب بسازید.</p>
        <div class="flex flex-wrap gap-4">
            @auth
                <a class="button primary" href="{{ route(auth()->user()->role->dashboardRoute()) }}">رفتن به حساب من</a>
            @else
                <x-auth-trigger mode="login" class="button primary">ورود به حساب</x-auth-trigger>
                <x-auth-trigger mode="register" class="button secondary">ثبت‌نام در دوپامین</x-auth-trigger>
            @endauth
        </div>
        <p class="muted text-sm">ورود فقط با شماره موبایل و رمز عبور انجام می‌شود.</p>
    </main>
</x-layout>
