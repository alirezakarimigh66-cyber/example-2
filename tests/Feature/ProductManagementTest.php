<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Support\ProductManager;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function productImage(string $name = 'product.png', string $fixture = 'product.png'): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, file_get_contents(__DIR__.'/../Fixtures/'.$fixture));
}

it('redirects guests from every product management endpoint', function (string $method, string $path) {
    $product = Product::factory()->create();
    $this->{$method}(str_replace('{id}', (string) $product->id, $path))->assertRedirect(route('home', ['auth' => 'login']));
    $this->assertModelExists($product);
})->with([
    ['get', '/admin/products'], ['get', '/admin/products/create'], ['post', '/admin/products'],
    ['get', '/admin/products/{id}'], ['get', '/admin/products/{id}/edit'], ['put', '/admin/products/{id}'],
    ['delete', '/admin/products/{id}'], ['get', '/admin/products/{id}/image'],
]);

it('returns 403 to regular users on every product management endpoint', function (string $method, string $path) {
    $product = Product::factory()->create();
    $this->actingAs(User::factory()->create())->{$method}(str_replace('{id}', (string) $product->id, $path))->assertForbidden();
    $this->assertModelExists($product);
})->with([
    ['get', '/admin/products'], ['get', '/admin/products/create'], ['post', '/admin/products'],
    ['get', '/admin/products/{id}'], ['get', '/admin/products/{id}/edit'], ['patch', '/admin/products/{id}'],
    ['delete', '/admin/products/{id}'], ['get', '/admin/products/{id}/image'],
]);

it('creates a product with a private generated image and ignores injected paths', function () {
    Storage::fake('local');
    $category = Category::factory()->create();
    $this->actingAs(User::factory()->admin()->create())->post(route('admin.products.store'), [
        'name' => 'Whey Protein', 'category_id' => $category->id, 'description' => 'Daily supplement',
        'price' => '123456.78', 'stock_quantity' => 8, 'status' => 'active',
        'image' => productImage('untrusted.php.png'), 'image_path' => 'outside/private.txt',
    ])->assertRedirect(route('admin.products.show', Product::sole()))->assertSessionHas('status');
    $product = Product::sole();
    $this->assertDatabaseHas('products', ['id' => $product->id, 'category_id' => $category->id, 'name' => 'Whey Protein', 'price' => '123456.78', 'stock_quantity' => 8, 'status' => 'active']);
    expect($product->image_path)->toStartWith('products/')->not->toContain('untrusted');
    Storage::disk('local')->assertExists($product->image_path);
    $this->get(route('admin.products.image', $product))->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff')->assertHeader('Content-Type', 'image/png');
});

it('renders create edit and detail views and escapes stored content', function () {
    $product = Product::factory()->create(['name' => '<script>alert(1)</script>', 'description' => '<img src=x onerror=alert(1)>']);
    $this->actingAs(User::factory()->admin()->create());
    $this->get(route('admin.products.create'))->assertOk()->assertSee('enctype="multipart/form-data"', false)->assertSee('افزودن محصول');
    $this->get(route('admin.products.edit', $product))->assertOk()->assertSee($product->name);
    $this->get(route('admin.products.show', $product))->assertOk()->assertSee($product->description)->assertDontSee($product->description, false);
    $this->get(route('admin.products.index'))->assertOk()->assertSee($product->name)->assertDontSee($product->name, false);
});

it('requires core product fields', function () {
    $this->actingAs(User::factory()->admin()->create())->post(route('admin.products.store'), [])
        ->assertSessionHasErrors(['name' => 'تکمیل نام محصول الزامی است.', 'category_id', 'price', 'stock_quantity', 'status']);
    $this->assertDatabaseCount('products', 0);
});

it('rejects invalid product values without writing a record', function (array $changes, string $field, string $message) {
    $category = Category::factory()->create();
    $data = ['name' => 'Protein', 'category_id' => $category->id, 'description' => '', 'price' => '1.25', 'stock_quantity' => 0, 'status' => 'inactive'];
    $this->actingAs(User::factory()->admin()->create())->post(route('admin.products.store'), array_replace($data, $changes))
        ->assertSessionHasErrors([$field => $message]);
    $this->assertDatabaseCount('products', 0);
})->with([
    'long name' => [['name' => str_repeat('a', 256)], 'name', 'نام محصول نباید بیشتر از ۲۵۵ نویسه باشد.'],
    'missing category' => [['category_id' => 999999], 'category_id', 'دسته‌بندی انتخاب‌شده معتبر نیست.'],
    'negative price' => [['price' => '-1'], 'price', 'قیمت نمی‌تواند منفی باشد.'],
    'fraction precision' => [['price' => '1.234'], 'price', 'قیمت حداکثر دو رقم اعشار دارد.'],
    'price range' => [['price' => '10000000000'], 'price', 'قیمت از محدوده مجاز بیشتر است.'],
    'text price' => [['price' => 'free'], 'price', 'قیمت باید عدد باشد.'],
    'negative stock' => [['stock_quantity' => -1], 'stock_quantity', 'موجودی نمی‌تواند منفی باشد.'],
    'fractional stock' => [['stock_quantity' => 1.5], 'stock_quantity', 'موجودی باید عدد صحیح باشد.'],
    'stock range' => [['stock_quantity' => 2147483648], 'stock_quantity', 'موجودی از محدوده مجاز بیشتر است.'],
    'invalid status' => [['status' => 'published'], 'status', 'وضعیت انتخاب‌شده معتبر نیست.'],
    'long description' => [['description' => str_repeat('a', 20001)], 'description', 'توضیحات نباید بیشتر از ۲۰۰۰۰ نویسه باشد.'],
    'invalid removal flag' => [['remove_image' => 'yes'], 'remove_image', 'گزینه حذف تصویر معتبر نیست.'],
]);

it('rejects unsafe oversized or excessively wide images', function (string $kind) {
    Storage::fake('local');
    $category = Category::factory()->create();
    $image = match ($kind) {
        'svg' => UploadedFile::fake()->createWithContent('unsafe.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
        'text' => UploadedFile::fake()->createWithContent('fake.jpg', '<?php echo "bad";'),
        'size' => productImage()->size(4097),
        'dimensions' => productImage('wide.png', 'product-wide.png'),
        'gif' => productImage('animated.gif', 'product.gif'),
    };
    $this->actingAs(User::factory()->admin()->create())->post(route('admin.products.store'), [
        'name' => 'Protein', 'category_id' => $category->id, 'price' => 0, 'stock_quantity' => 0, 'status' => 'inactive', 'image' => $image,
    ])->assertSessionHasErrors('image');
    $this->assertDatabaseCount('products', 0);
    expect(Storage::disk('local')->allFiles('products'))->toBeEmpty();
})->with(['svg', 'text', 'size', 'dimensions', 'gif']);

it('updates all product details and retains the image when no replacement is supplied', function () {
    Storage::fake('local');
    Storage::disk('local')->put('products/original.jpg', 'original');
    $product = Product::factory()->active()->create(['image_path' => 'products/original.jpg']);
    $category = Category::factory()->create();
    $this->actingAs(User::factory()->admin()->create())->put(route('admin.products.update', $product), [
        'name' => 'Updated', 'category_id' => $category->id, 'description' => 'Changed', 'price' => '0.00', 'stock_quantity' => 0, 'status' => 'inactive', 'image_path' => 'injected',
    ])->assertRedirect(route('admin.products.show', $product));
    $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Updated', 'category_id' => $category->id, 'description' => 'Changed', 'price' => 0, 'stock_quantity' => 0, 'status' => 'inactive', 'image_path' => 'products/original.jpg']);
    Storage::disk('local')->assertExists('products/original.jpg');
});

it('replaces and explicitly removes images without leaving old files', function () {
    Storage::fake('local');
    Storage::disk('local')->put('products/original.jpg', 'original');
    $product = Product::factory()->create(['image_path' => 'products/original.jpg']);
    $data = ['name' => 'Protein', 'category_id' => $product->category_id, 'price' => '10.50', 'stock_quantity' => 3, 'status' => 'active'];
    $this->actingAs(User::factory()->admin()->create())->put(route('admin.products.update', $product), $data + ['image' => productImage('replacement.png'), 'remove_image' => 1])->assertRedirect();
    $newPath = $product->fresh()->image_path;
    Storage::disk('local')->assertMissing('products/original.jpg');
    Storage::disk('local')->assertExists($newPath);
    $this->put(route('admin.products.update', $product), $data + ['remove_image' => 1])->assertRedirect();
    $this->assertDatabaseHas('products', ['id' => $product->id, 'image_path' => null]);
    Storage::disk('local')->assertMissing($newPath);
});

it('retains saved data and the old image when an update is invalid', function () {
    Storage::fake('local');
    Storage::disk('local')->put('products/original.jpg', 'original');
    $product = Product::factory()->create(['name' => 'Unchanged', 'image_path' => 'products/original.jpg']);
    $this->actingAs(User::factory()->admin()->create())->put(route('admin.products.update', $product), ['price' => -1, 'image' => productImage('replacement.png')])->assertSessionHasErrors();
    $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Unchanged', 'image_path' => 'products/original.jpg']);
    expect(Storage::disk('local')->allFiles('products'))->toBe(['products/original.jpg']);
});

it('removes the new upload and preserves the old image if database persistence fails', function () {
    Storage::fake('local');
    Storage::disk('local')->put('products/original.jpg', 'original');
    $product = Product::factory()->create(['image_path' => 'products/original.jpg']);
    expect(fn () => app(ProductManager::class)->save($product, ['category_id' => 999999], productImage('replacement.png')))->toThrow(QueryException::class);
    $this->assertDatabaseHas('products', ['id' => $product->id, 'category_id' => $product->category_id, 'image_path' => 'products/original.jpg']);
    expect(Storage::disk('local')->allFiles('products'))->toBe(['products/original.jpg']);
});

it('deletes the product and its private image', function () {
    Storage::fake('local');
    Storage::disk('local')->put('products/original.jpg', 'original');
    $product = Product::factory()->create(['image_path' => 'products/original.jpg']);
    $this->actingAs(User::factory()->admin()->create())->delete(route('admin.products.destroy', $product))->assertRedirect(route('admin.products.index'));
    $this->assertModelMissing($product);
    Storage::disk('local')->assertMissing('products/original.jpg');
});

it('returns 404 for missing products and missing image files', function () {
    Storage::fake('local');
    $product = Product::factory()->create();
    $this->actingAs(User::factory()->admin()->create())->get(route('admin.products.image', $product))->assertNotFound();
    $product->update(['image_path' => 'products/missing.jpg']);
    $this->get(route('admin.products.image', $product))->assertNotFound();
    $this->get('/admin/products/999999')->assertNotFound();
    $this->delete('/admin/products/999999')->assertNotFound();
});

it('combines search and category filtering and preserves filters across pages', function () {
    $category = Category::factory()->create();
    $products = Product::factory()->for($category)->count(16)->create(['name' => 'Protein match']);
    Product::factory()->create(['name' => 'Protein wrong category']);
    Product::factory()->for($category)->create(['name' => 'Unrelated']);
    $this->actingAs(User::factory()->admin()->create())->get(route('admin.products.index', ['search' => 'Protein', 'category_id' => $category->id]))
        ->assertOk()->assertViewHas('products', fn ($page) => $page->total() === 16 && $page->count() === 15 && $page->first()->id === $products->last()->id)
        ->assertSee('search=Protein', false)->assertSee('category_id='.$category->id, false)
        ->assertDontSee('Protein wrong category')->assertDontSee('Unrelated');
    $this->get(route('admin.products.index', ['search' => 'Protein', 'category_id' => $category->id, 'page' => 2]))
        ->assertOk()->assertViewHas('products', fn ($page) => $page->count() === 1 && $page->first()->id === $products->first()->id);
});

it('treats zero as a search term and keeps SQL payloads bound', function () {
    Product::factory()->create(['name' => 'Vitamin 0']);
    Product::factory()->create(['name' => 'Other']);
    $this->actingAs(User::factory()->admin()->create())->get(route('admin.products.index', ['search' => '0']))->assertOk()->assertSee('Vitamin 0')->assertDontSee('Other');
    $this->get(route('admin.products.index', ['search' => "' OR 1=1 --"]))->assertOk()->assertViewHas('products', fn ($page) => $page->total() === 0);
});

it('returns 422 for malformed inventory filters', function (array $query, string $field) {
    $this->actingAs(User::factory()->admin()->create())->getJson(route('admin.products.index', $query))->assertUnprocessable()->assertJsonValidationErrors($field);
})->with([
    [['search' => ['bad']], 'search'], [['category_id' => 999999], 'category_id'], [['page' => 0], 'page'],
]);
