<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductImageController extends Controller
{
    public function __invoke(Product $product): StreamedResponse
    {
        abort_if($product->image_path === null || ! Storage::disk('local')->exists($product->image_path), 404);

        return Storage::disk('local')->response($product->image_path, null, [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
            'Content-Security-Policy' => "default-src 'none'; sandbox",
        ], 'inline');
    }
}
