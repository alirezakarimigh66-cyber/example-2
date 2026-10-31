<?php

namespace App\Models;

use App\ProductStatus;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string|null $description
 * @property string $price
 * @property int $stock_quantity
 * @property string|null $image_path
 * @property ProductStatus $status
 * @property-read Category $category
 */
#[Fillable(['category_id', 'name', 'description', 'price', 'stock_quantity', 'image_path', 'status'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'stock_quantity' => 'integer', 'status' => ProductStatus::class];
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
