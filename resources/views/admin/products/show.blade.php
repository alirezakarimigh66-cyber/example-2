<x-admin-layout :title="$product->name">
    <div class="flex w-full flex-wrap items-center justify-between gap-4"><div class="min-w-0"><a href="{{ route('admin.products.index') }}" class="muted text-sm">بازگشت به محصولات</a><h1 class="text-2xl mt-4 break-words">{{ $product->name }}</h1></div><a class="button primary" href="{{ route('admin.products.edit', $product) }}">ویرایش محصول</a></div>
    <div class="grid w-full gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(220px,1fr)]">
        <div class="min-w-0 flex flex-col gap-6">
            <dl class="account-details grid gap-6 sm:grid-cols-2">
                <div><dt class="muted text-sm">دسته‌بندی</dt><dd class="mt-2">{{ $product->category->name }}</dd></div>
                <div><dt class="muted text-sm">وضعیت</dt><dd class="mt-2">{{ $product->status->label() }}</dd></div>
                <div><dt class="muted text-sm">قیمت (تومان)</dt><dd class="mt-2 text-xl tabular-nums">{{ number_format((float) $product->price, 2) }}</dd></div>
                <div><dt class="muted text-sm">موجودی انبار</dt><dd class="mt-2 text-xl tabular-nums">{{ number_format($product->stock_quantity) }}</dd></div>
            </dl>
            <div><h2 class="text-lg">توضیحات</h2><p class="mt-3 whitespace-pre-wrap break-words leading-8">{{ $product->description ?: 'توضیحاتی ثبت نشده است.' }}</p></div>
            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" data-delete-form data-object-name="{{ $product->name }}">@csrf @method('DELETE')<button type="submit" class="button danger">حذف محصول</button></form>
        </div>
        @if($product->image_path)<img src="{{ route('admin.products.image', $product) }}" class="product-preview" alt="{{ $product->name }}" width="320" height="320">@else<div class="product-preview product-placeholder">بدون تصویر</div>@endif
    </div>
</x-admin-layout>
