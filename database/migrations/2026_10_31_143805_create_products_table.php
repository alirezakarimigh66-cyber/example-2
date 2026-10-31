<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->string('image_path')->nullable();
            $table->string('status')->default('inactive');
            $table->timestamps();
            $table->index(['category_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
