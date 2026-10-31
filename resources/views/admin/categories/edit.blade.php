<x-admin-layout title="ویرایش دسته‌بندی">
    <h1 class="text-2xl">ویرایش دسته‌بندی</h1>
    <x-admin-errors />
    <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="flex w-full max-w-xl flex-col gap-5">@csrf @method('PUT')<div class="field"><label for="name">نام دسته‌بندی</label><input id="name" name="name" maxlength="255" required value="{{ old('name', $category->name) }}"></div><div class="flex flex-wrap gap-3"><button class="button primary" type="submit">ذخیره تغییرات</button><a class="button secondary" href="{{ route('admin.categories.index') }}">انصراف</a></div></form>
</x-admin-layout>
