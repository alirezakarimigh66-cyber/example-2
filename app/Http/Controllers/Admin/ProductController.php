<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductIndexRequest;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\ProductStatus;
use App\Support\ProductManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(ProductIndexRequest $request): View
    {
        $filters = $request->validated();
        $products = Product::query()->with('category:id,name')
            ->when(isset($filters['search']) && $filters['search'] !== '', fn ($query) => $query->whereLike('name', '%'.$filters['search'].'%'))
            ->when($filters['category_id'] ?? null, fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->orderByDesc('id')->paginate(15)->withQueryString();

        return view('admin.products.index', ['products' => $products, 'categories' => Category::query()->orderBy('name')->get(), 'filters' => $filters]);
    }

    public function create(): View
    {
        return view('admin.products.form', ['product' => new Product, 'categories' => Category::query()->orderBy('name')->get(), 'statuses' => ProductStatus::cases()]);
    }

    public function store(ProductRequest $request, ProductManager $manager): RedirectResponse
    {
        $product = $manager->save(new Product, $request->safe()->except(['image', 'remove_image']), $request->file('image'));

        return to_route('admin.products.show', $product)->with('status', 'محصول با موفقیت ایجاد شد.');
    }

    public function show(Product $product): View
    {
        return view('admin.products.show', ['product' => $product->load('category')]);
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', ['product' => $product, 'categories' => Category::query()->orderBy('name')->get(), 'statuses' => ProductStatus::cases()]);
    }

    public function update(ProductRequest $request, Product $product, ProductManager $manager): RedirectResponse
    {
        $manager->save($product, $request->safe()->except(['image', 'remove_image']), $request->file('image'), $request->boolean('remove_image'));

        return to_route('admin.products.show', $product)->with('status', 'تغییرات محصول ذخیره شد.');
    }

    public function destroy(Product $product, ProductManager $manager): RedirectResponse
    {
        $manager->delete($product);

        return to_route('admin.products.index')->with('status', 'محصول حذف شد.');
    }
}
