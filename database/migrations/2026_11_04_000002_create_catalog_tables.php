<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_models', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->text('description')->nullable();
            $table->enum('pricing_type', ['per_m2', 'fixed'])->default('per_m2');
            $table->enum('default_measurement_type', ['front_area', 'top_area', 'surface_area', 'manual'])->default('front_area');
            $table->decimal('default_price_per_m2', 15, 2)->default(1000000);
            $table->decimal('default_fixed_price', 15, 2)->nullable();
            $table->foreignId('default_complexity_level_id')->nullable()->constrained('complexity_levels')->nullOnDelete();
            $table->foreignId('default_wood_type_id')->nullable()->constrained('wood_types')->nullOnDelete();
            $table->foreignId('default_wood_grade_id')->nullable()->constrained('wood_grades')->nullOnDelete();
            $table->decimal('default_length_cm', 10, 2)->nullable();
            $table->decimal('default_width_cm', 10, 2)->nullable();
            $table->decimal('default_height_cm', 10, 2)->nullable();
            $table->unsignedInteger('estimated_days')->default(0);
            $table->string('photo_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->enum('material_type', ['wood', 'finishing', 'hardware', 'consumable', 'other'])->default('wood');
            $table->foreignId('wood_type_id')->nullable()->constrained('wood_types')->nullOnDelete();
            $table->foreignId('wood_grade_id')->nullable()->constrained('wood_grades')->nullOnDelete();
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('thickness_mm', 10, 2)->nullable();
            $table->decimal('width_mm', 10, 2)->nullable();
            $table->decimal('length_mm', 10, 2)->nullable();
            $table->decimal('stock_qty', 15, 4)->default(0);
            $table->decimal('min_stock_qty', 15, 4)->default(0);
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->string('location', 100)->nullable();
            $table->string('photo_path')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['material_type', 'stock_qty']);
        });

        Schema::create('bom_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_model_id')->constrained('product_models')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('materials')->restrictOnDelete();
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('quantity', 15, 4);
            $table->decimal('waste_percent', 5, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['product_model_id', 'material_id', 'unit_id'], 'bom_unique');
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->foreignId('product_model_id')->nullable()->constrained('product_models')->nullOnDelete();
            $table->text('description')->nullable();
            $table->decimal('length_cm', 10, 2)->nullable();
            $table->decimal('width_cm', 10, 2)->nullable();
            $table->decimal('height_cm', 10, 2)->nullable();
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('sale_price', 15, 2)->default(0);
            $table->decimal('stock_qty', 15, 4)->default(0);
            $table->decimal('min_stock_qty', 15, 4)->default(0);
            $table->string('photo_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('bom_items');
        Schema::dropIfExists('materials');
        Schema::dropIfExists('product_models');
    }
};
