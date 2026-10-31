<x-admin-layout title="محصولات">
    <div class="flex w-full flex-wrap items-center justify-between gap-4">
        <div><h1 class="text-2xl">محصولات</h1><p class="muted mt-2">{{ number_format($products->total()) }} محصول در فهرست فعلی</p></div>
        <a class="button primary" href="{{ route('admin.products.create') }}">افزودن محصول</a>
    </div>
    <x-admin-errors />
    <form method="GET" action="{{ route('admin.products.index') }}" class="catalog-filters grid w-full gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto]">
        <div class="field"><label for="search">جستجوی نام محصول</label><input id="search" name="search" type="search" maxlength="255" value="{{ $filters['search'] ?? '' }}"></div>
        <div class="field"><label for="filter-category">دسته‌بندی</label><select id="filter-category" name="category_id"><option value="">همه دسته‌بندی‌ها</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(($filters['category_id'] ?? '') == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
        <div class="flex flex-wrap items-end gap-3"><button type="submit" class="button secondary">اعمال فیلتر</button><a class="button" href="{{ route('admin.products.index') }}">پاک کردن</a></div>
    </form>
    <div class="catalog-table-wrap w-full" role="region" aria-label="فهرست محصولات" tabindex="0">
        <table class="catalog-table product-table">
            <caption class="sr-only">محصولات، قیمت به تومان و موجودی انبار</caption>
            <thead><tr><th scope="col">محصول</th><th scope="col">دسته‌بندی</th><th scope="col">قیمت (تومان)</th><th scope="col">موجودی</th><th scope="col">وضعیت</th><th scope="col">عملیات</th></tr></thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td><div class="flex items-center gap-3">@if($product->image_path)<img class="product-thumb" src="{{ route('admin.products.image', $product) }}" alt="" width="48" height="48" loading="lazy">@else<span class="product-thumb product-placeholder" aria-label="بدون تصویر">—</span>@endif<a class="product-name" href="{{ route('admin.products.show', $product) }}">{{ $product->name }}</a></div></td>
                        <td class="muted">{{ $product->category->name }}</td>
                        <td class="tabular-nums whitespace-nowrap">{{ number_format((float) $product->price, 2) }}</td>
                        <td class="tabular-nums">{{ number_format($product->stock_quantity) }}@if($product->stock_quantity === 0)<span class="muted text-xs block">ناموجود</span>@endif</td>
                        <td><span class="product-status" data-status="{{ $product->status->value }}">{{ $product->status->label() }}</span></td>
                        <td><div class="flex items-center gap-4"><a href="{{ route('admin.products.edit', $product) }}">ویرایش</a><form method="POST" action="{{ route('admin.products.destroy', $product) }}" data-delete-form data-object-name="{{ $product->name }}">@csrf @method('DELETE')<button type="submit" class="danger-text">حذف</button></form></div></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="catalog-empty"><p>{{ isset($filters['search']) || !empty($filters['category_id']) ? 'محصولی با این فیلترها پیدا نشد.' : 'هنوز محصولی ثبت نشده است.' }}</p><a class="mt-3 inline-block" href="{{ route('admin.products.create') }}">افزودن محصول</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="w-full flex flex-col gap-4"><p class="muted text-sm tabular-nums">نمایش {{ $products->firstItem() ?? 0 }} تا {{ $products->lastItem() ?? 0 }} از {{ $products->total() }}</p>{{ $products->links() }}</div>
</x-admin-layout>
