<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_no', 30)->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->date('purchase_date');
            $table->string('invoice_no', 50)->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('materials')->restrictOnDelete();
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->string('opname_no', 30)->unique();
            $table->date('opname_date');
            $table->enum('status', ['draft', 'completed', 'approved'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_opname_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained('stock_opnames')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('materials')->restrictOnDelete();
            $table->decimal('system_qty', 15, 4)->default(0);
            $table->decimal('physical_qty', 15, 4)->default(0);
            $table->decimal('difference_qty', 15, 4)->default(0);
            $table->text('reason')->nullable();
            $table->timestamps();
            $table->unique(['stock_opname_id', 'material_id'], 'opname_material_unique');
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('materials')->restrictOnDelete();
            $table->enum('movement_type', ['purchase_in', 'production_out', 'return_in', 'adjustment_in', 'adjustment_out', 'opname_in', 'opname_out', 'damage_out', 'transfer_in', 'transfer_out']);
            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('purchase_id')->nullable()->constrained('purchases')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            $table->foreignId('order_item_material_id')->nullable()->constrained('order_item_materials')->nullOnDelete();
            $table->foreignId('stock_opname_id')->nullable()->constrained('stock_opnames')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->timestamp('movement_date');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['material_id', 'movement_type', 'movement_date'], 'stock_move_index');
            $table->index(['reference_type', 'reference_id']);
        });

        Schema::create('product_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->enum('movement_type', ['production_in', 'sale_out', 'transfer_in', 'transfer_out', 'adjustment_in', 'adjustment_out']);
            $table->decimal('quantity', 15, 4);
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['product_id', 'movement_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_stock_movements');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stock_opname_items');
        Schema::dropIfExists('stock_opnames');
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
    }
};
