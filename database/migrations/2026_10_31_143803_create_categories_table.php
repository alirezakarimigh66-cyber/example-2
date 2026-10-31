<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        foreach (['مکمل‌های بدنسازی', 'قرص‌ها و تبلت‌ها', 'ویتامین‌ها و مواد معدنی'] as $name) {
            DB::table('categories')->insert(['name' => $name, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
