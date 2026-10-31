<x-admin-layout :title="$product->exists ? 'ویرایش محصول' : 'افزودن محصول'">
    <div><a class="muted text-sm" href="{{ route('admin.products.index') }}">بازگشت به محصولات</a><h1 class="text-2xl mt-4">{{ $product->exists ? 'ویرایش محصول' : 'افزودن محصول' }}</h1></div>
    <x-admin-errors />
    @if($categories->isEmpty())<p role="status">ابتدا <a href="{{ route('admin.categories.index') }}">یک دسته‌بندی ایجاد کنید</a>.</p>@endif
    <form method="POST" enctype="multipart/form-data" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" class="product-form grid w-full gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(220px,1fr)]">
        @csrf
        @if($product->exists) @method('PUT') @endif
        <div class="flex min-w-0 flex-col gap-5">
            <div class="field"><label for="name">نام محصول <span class="muted">(الزامی)</span></label><input id="name" name="name" required maxlength="255" value="{{ old('name', $product->name) }}" @error('name') aria-invalid="true" @enderror></div>
            <div class="field"><label for="category_id">دسته‌بندی <span class="muted">(الزامی)</span></label><select id="category_id" name="category_id" required @error('category_id') aria-invalid="true" @enderror><option value="">انتخاب دسته‌بندی</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
            <div class="field"><label for="description">توضیحات</label><textarea id="description" name="description" rows="7" maxlength="20000" @error('description') aria-invalid="true" @enderror>{{ old('description', $product->description) }}</textarea></div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div class="field"><label for="price">قیمت (تومان) <span class="muted">(الزامی)</span></label><input id="price" name="price" type="number" min="0" max="9999999999.99" step="0.01" required dir="ltr" value="{{ old('price', $product->price) }}" @error('price') aria-invalid="true" @enderror></div>
                <div class="field"><label for="stock_quantity">موجودی انبار <span class="muted">(الزامی)</span></label><input id="stock_quantity" name="stock_quantity" type="number" min="0" max="2147483647" step="1" required dir="ltr" value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}" @error('stock_quantity') aria-invalid="true" @enderror></div>
            </div>
            <div class="field"><label for="status">وضعیت محصول</label><select id="status" name="status" required>@foreach($statuses as $status)<option value="{{ $status->value }}" @selected(old('status', $product->status?->value ?? 'inactive') === $status->value)>{{ $status->label() }}</option>@endforeach</select></div>
        </div>
        <div class="flex min-w-0 flex-col gap-5">
            <div class="field"><label for="image">تصویر محصول</label>@if($product->image_path)<img src="{{ route('admin.products.image', $product) }}" class="product-preview" alt="{{ $product->name }}" width="320" height="320">@else<div class="product-preview product-placeholder">بدون تصویر</div>@endif<input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" aria-describedby="image-hint" @error('image') aria-invalid="true" @enderror><p id="image-hint" class="field-hint">JPG، PNG یا WebP؛ حداکثر ۴ مگابایت و ۶۰۰۰ × ۶۰۰۰ پیکسل. انتخاب تصویر اختیاری است.</p></div>
            @if($product->image_path)<label class="flex items-center gap-3"><input type="checkbox" name="remove_image" value="1" @checked(old('remove_image'))>حذف تصویر فعلی</label><p class="field-hint">با انتخاب تصویر جدید، تصویر فعلی جایگزین می‌شود.</p>@endif
        </div>
        <div class="flex flex-wrap gap-3 lg:col-span-2"><button type="submit" class="button primary">{{ $product->exists ? 'ذخیره تغییرات' : 'ایجاد محصول' }}</button><a class="button secondary" href="{{ route('admin.products.index') }}">انصراف</a></div>
    </form>
</x-admin-layout>
