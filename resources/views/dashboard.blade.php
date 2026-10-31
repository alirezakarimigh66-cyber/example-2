<x-layout :title="$user->role === \App\UserRole::Admin ? 'مدیریت' : 'حساب من'">
    <div class="dashboard-shell">
        <aside class="dashboard-nav flex flex-col gap-6" aria-label="منوی حساب">
            <p class="muted">{{ $user->role === \App\UserRole::Admin ? 'فضای مدیریت' : 'حساب شخصی' }}</p>
            <nav class="flex flex-wrap gap-4 sm:flex-col" aria-label="داشبورد">
                <a href="{{ route($user->role->dashboardRoute()) }}" aria-current="page" class="current">نمای کلی</a>
                <a href="{{ route('home') }}">صفحه اصلی</a>
                @if($user->role === \App\UserRole::Admin)
                    <a href="{{ route('admin.products.index') }}">محصولات</a>
                    <a href="{{ route('admin.categories.index') }}">دسته‌بندی‌ها</a>
                    <a href="#account-summary">خلاصه کاربران</a>
                @endif
            </nav>
        </aside>
        <main class="dashboard-sheet flex flex-col gap-8">
            <div>
                <h1 class="text-2xl">{{ $user->role === \App\UserRole::Admin ? 'داشبورد مدیریت' : 'حساب من' }}</h1>
                <p class="muted mt-3">{{ $user->name }}، به دوپامین مارکت خوش آمدید.</p>
            </div>
            <section aria-labelledby="profile-title">
                <h2 id="profile-title" class="text-lg mb-4">اطلاعات حساب</h2>
                <dl class="account-details grid gap-5 sm:grid-cols-2">
                    <div><dt class="muted text-sm">نام</dt><dd class="mt-2">{{ $user->name }}</dd></div>
                    <div><dt class="muted text-sm">شماره موبایل</dt><dd class="mt-2" dir="ltr">{{ $user->phone ?? '—' }}</dd></div>
                    <div><dt class="muted text-sm">نوع دسترسی</dt><dd class="mt-2">{{ $user->role === \App\UserRole::Admin ? 'مدیر' : 'کاربر' }}</dd></div>
                    <div><dt class="muted text-sm">تاریخ عضویت</dt><dd class="mt-2">{{ $user->created_at?->format('Y/m/d') }}</dd></div>
                </dl>
            </section>
            @if($user->role === \App\UserRole::Admin)
                <section id="account-summary" aria-labelledby="summary-title">
                    <h2 id="summary-title" class="text-lg">خلاصه کاربران</h2>
                    <p class="mt-4 text-3xl tabular-nums">{{ number_format($userCount) }}</p>
                    <p class="muted mt-2">حساب ثبت‌شده در دوپامین مارکت</p>
                </section>
            @endif
            <form method="POST" action="{{ route('logout') }}">@csrf <button type="submit" class="button secondary">خروج از حساب</button></form>
        </main>
    </div>
</x-layout>
