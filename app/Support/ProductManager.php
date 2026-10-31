<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class ProductManager
{
    /** @param array<string, mixed> $attributes */
    public function save(Product $product, array $attributes, ?UploadedFile $image, bool $removeImage = false): Product
    {
        $newPath = null;
        if ($image !== null) {
            $newPath = $image->store('products', 'local');
            if ($newPath === false) {
                throw new RuntimeException('Unable to store product image.');
            }
        }

        $oldPath = null;
        try {
            $saved = DB::transaction(function () use ($product, $attributes, $newPath, $removeImage, &$oldPath): Product {
                $record = $product->exists ? Product::query()->lockForUpdate()->findOrFail($product->id) : $product;
                $oldPath = $record->image_path;
                $record->fill($attributes);
                if ($newPath !== null || $removeImage) {
                    $record->image_path = $newPath;
                }
                $record->save();

                return $record;
            });
        } catch (Throwable $exception) {
            $this->deleteImage($newPath);
            throw $exception;
        }

        if ($oldPath !== $saved->image_path) {
            $this->deleteImage($oldPath);
        }

        return $saved;
    }

    public function delete(Product $product): void
    {
        $oldPath = DB::transaction(function () use ($product): ?string {
            $record = Product::query()->lockForUpdate()->findOrFail($product->id);
            $path = $record->image_path;
            $record->delete();

            return $path;
        });

        $this->deleteImage($oldPath);
    }

    private function deleteImage(?string $path): void
    {
        if ($path === null) {
            return;
        }

        try {
            if (! Storage::disk('local')->delete($path)) {
                Log::warning('Unable to delete product image.', ['path' => $path]);
            }
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
