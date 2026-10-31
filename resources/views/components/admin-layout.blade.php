@props(['title'])
<x-layout :title="$title">
    <div class="dashboard-shell">
        <aside class="dashboard-nav flex flex-col gap-6" aria-label="منوی مدیریت">
            <p class="muted">مدیریت دوپامین مارکت</p>
            <nav class="flex flex-wrap gap-4 sm:flex-col" aria-label="بخش‌های مدیریت">
                <a href="{{ route('admin.dashboard') }}">نمای کلی</a>
                <a href="{{ route('admin.products.index') }}" @if(request()->routeIs('admin.products.*')) aria-current="page" class="current" @endif>محصولات</a>
                <a href="{{ route('admin.categories.index') }}" @if(request()->routeIs('admin.categories.*')) aria-current="page" class="current" @endif>دسته‌بندی‌ها</a>
                <a href="{{ route('home') }}">صفحه اصلی</a>
            </nav>
        </aside>
        <main class="dashboard-sheet catalog-sheet flex flex-col gap-6">{{ $slot }}</main>
    </div>
    <dialog class="auth-modal" id="delete-confirmation" aria-labelledby="delete-title" aria-describedby="delete-description">
        <div class="modal-content flex flex-col gap-5">
            <h2 id="delete-title">تأیید حذف</h2>
            <p id="delete-description">«<span data-delete-name></span>» حذف شود؟ این کار قابل بازگشت نیست.</p>
            <div class="flex flex-wrap gap-3">
                <button type="button" class="button danger" data-delete-confirm>حذف قطعی</button>
                <button type="button" class="button secondary" data-delete-cancel autofocus>انصراف</button>
            </div>
        </div>
    </dialog>
</x-layout>
