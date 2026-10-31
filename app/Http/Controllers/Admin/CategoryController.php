<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', ['categories' => Category::query()->withCount('products')->orderBy('name')->paginate(15)]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        Category::create($request->validated());

        return to_route('admin.categories.index')->with('status', 'دسته‌بندی ایجاد شد.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return to_route('admin.categories.index')->with('status', 'دسته‌بندی به‌روزرسانی شد.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->withErrors(['category' => 'ابتدا محصولات این دسته‌بندی را به دسته‌بندی دیگری منتقل کنید.']);
        }

        try {
            $category->deleteOrFail();
        } catch (QueryException $exception) {
            if (! in_array((string) $exception->getCode(), ['23000', '23503'], true)) {
                throw $exception;
            }

            return back()->withErrors(['category' => 'این دسته‌بندی در حال استفاده است و حذف نمی‌شود.']);
        }

        return to_route('admin.categories.index')->with('status', 'دسته‌بندی حذف شد.');
    }
}
