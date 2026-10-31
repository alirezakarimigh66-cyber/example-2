<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\CategorySeeder;

it('installs the three requested categories and can seed them idempotently', function () {
    $this->seed(CategorySeeder::class);
    $this->seed(CategorySeeder::class);
    $this->assertDatabaseCount('categories', 3);
    foreach (['مکمل‌های بدنسازی', 'قرص‌ها و تبلت‌ها', 'ویتامین‌ها و مواد معدنی'] as $name) {
        $this->assertDatabaseHas('categories', ['name' => $name]);
    }
});

it('redirects guests from every category management endpoint', function (string $method, string $path) {
    $category = Category::factory()->create();
    $this->{$method}(str_replace('{id}', (string) $category->id, $path))->assertRedirect(route('home', ['auth' => 'login']));
    $this->assertModelExists($category);
})->with([['get', '/admin/categories'], ['post', '/admin/categories'], ['get', '/admin/categories/{id}/edit'], ['put', '/admin/categories/{id}'], ['delete', '/admin/categories/{id}']]);

it('returns 403 to regular users on every category management endpoint', function (string $method, string $path) {
    $category = Category::factory()->create();
    $this->actingAs(User::factory()->create())->{$method}(str_replace('{id}', (string) $category->id, $path))->assertForbidden();
    $this->assertModelExists($category);
})->with([['get', '/admin/categories'], ['post', '/admin/categories'], ['get', '/admin/categories/{id}/edit'], ['patch', '/admin/categories/{id}'], ['delete', '/admin/categories/{id}']]);

it('lets administrators create rename and delete an unused category', function () {
    $this->actingAs(User::factory()->admin()->create())->post(route('admin.categories.store'), ['name' => 'Sports nutrition'])->assertRedirect(route('admin.categories.index'));
    $category = Category::where('name', 'Sports nutrition')->sole();
    $this->get(route('admin.categories.index'))->assertOk()->assertSee('Sports nutrition');
    $this->get(route('admin.categories.edit', $category))->assertOk()->assertSee('Sports nutrition');
    $this->put(route('admin.categories.update', $category), ['name' => 'Nutrition'])->assertRedirect(route('admin.categories.index'));
    $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Nutrition']);
    $this->put(route('admin.categories.update', $category), ['name' => 'Nutrition'])->assertSessionHasNoErrors();
    $this->delete(route('admin.categories.destroy', $category))->assertRedirect(route('admin.categories.index'));
    $this->assertModelMissing($category);
});

it('rejects duplicate and invalid category names', function (array $data, string $message) {
    Category::factory()->create(['name' => 'Existing']);
    $this->actingAs(User::factory()->admin()->create())->post(route('admin.categories.store'), $data)->assertSessionHasErrors(['name' => $message]);
    $this->assertDatabaseCount('categories', 4);
})->with([
    [[], 'نام دسته‌بندی را وارد کنید.'],
    [['name' => 'Existing'], 'این دسته‌بندی قبلاً ثبت شده است.'],
    [['name' => str_repeat('a', 256)], 'نام دسته‌بندی نباید بیشتر از ۲۵۵ نویسه باشد.'],
    [['name' => ['invalid']], 'نام دسته‌بندی باید متن باشد.'],
]);

it('keeps products and blocks deletion of their category', function () {
    $product = Product::factory()->create();
    $this->actingAs(User::factory()->admin()->create())->from(route('admin.categories.index'))->delete(route('admin.categories.destroy', $product->category_id))
        ->assertRedirect(route('admin.categories.index'))->assertSessionHasErrors('category');
    $this->assertModelExists($product);
    $this->assertDatabaseHas('categories', ['id' => $product->category_id]);
    $this->get(route('admin.categories.index'))->assertOk()->assertSee('در حال استفاده');
});

it('allows category deletion after its products are reassigned', function () {
    $product = Product::factory()->create();
    $oldCategory = $product->category;
    $newCategory = Category::factory()->create();
    $this->actingAs(User::factory()->admin()->create())->put(route('admin.products.update', $product), [
        'name' => $product->name, 'category_id' => $newCategory->id, 'price' => $product->price, 'stock_quantity' => $product->stock_quantity, 'status' => $product->status->value,
    ])->assertRedirect();
    $this->delete(route('admin.categories.destroy', $oldCategory))->assertRedirect(route('admin.categories.index'));
    $this->assertModelMissing($oldCategory);
    $this->assertDatabaseHas('products', ['id' => $product->id, 'category_id' => $newCategory->id]);
});
