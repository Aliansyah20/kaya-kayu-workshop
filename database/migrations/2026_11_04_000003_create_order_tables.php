<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no', 30)->unique();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->enum('channel', ['whatsapp', 'walk_in', 'phone', 'other'])->default('whatsapp');
            $table->date('order_date');
            $table->date('due_date');
            $table->enum('status', ['draft', 'pending_approval', 'confirmed', 'queued', 'in_production', 'finishing', 'production_done', 'delivered', 'transferred', 'cancelled'])->default('draft');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('dp_percent', 5, 2)->default(30);
            $table->decimal('dp_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->enum('payment_status', ['unpaid', 'dp_paid', 'partially_paid', 'paid', 'overdue'])->default('unpaid');
            $table->date('payment_deadline')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('production_completed_at')->nullable();
            $table->enum('delivery_type', ['pickup', 'delivery'])->nullable();
            $table->string('recipient_name', 100)->nullable();
            $table->text('delivery_address')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->string('delivery_proof_path')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'payment_status', 'due_date']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_model_id')->nullable()->constrained('product_models')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('item_name', 150);
            $table->text('description')->nullable();
            $table->enum('pricing_type', ['per_m2', 'fixed'])->default('per_m2');
            $table->enum('measurement_type', ['front_area', 'top_area', 'surface_area', 'manual'])->default('front_area');
            $table->foreignId('wood_type_id')->nullable()->constrained('wood_types')->nullOnDelete();
            $table->foreignId('wood_grade_id')->nullable()->constrained('wood_grades')->nullOnDelete();
            $table->foreignId('complexity_level_id')->nullable()->constrained('complexity_levels')->nullOnDelete();
            $table->decimal('length_cm', 10, 2)->nullable();
            $table->decimal('width_cm', 10, 2)->nullable();
            $table->decimal('height_cm', 10, 2)->nullable();
            $table->decimal('area_m2', 12, 4)->default(0);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('complexity_factor', 8, 4)->default(1);
            $table->decimal('wood_type_factor', 8, 4)->default(1);
            $table->decimal('wood_grade_factor', 8, 4)->default(1);
            $table->decimal('calculated_price', 15, 2)->default(0);
            $table->decimal('additional_fee', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('final_price', 15, 2)->default(0);
            $table->text('price_override_reason')->nullable();
            $table->text('custom_notes')->nullable();
            $table->enum('status', ['pending', 'queued', 'in_progress', 'finishing', 'qc', 'done', 'transferred', 'cancelled'])->default('pending');
            $table->foreignId('assigned_employee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('target_date')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('qc_at')->nullable();
            $table->foreignId('qc_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'assigned_employee_id', 'target_date']);
        });

        Schema::create('order_item_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('materials')->restrictOnDelete();
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('planned_qty', 15, 4)->default(0);
            $table->decimal('actual_qty', 15, 4)->default(0);
            $table->decimal('waste_percent', 5, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['order_item_id', 'material_id', 'unit_id'], 'order_item_material_unique');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_no', 30)->unique();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->enum('payment_type', ['dp', 'installment', 'settlement', 'refund'])->default('installment');
            $table->decimal('amount', 15, 2);
            $table->enum('payment_method', ['cash', 'transfer', 'qris', 'other'])->default('transfer');
            $table->string('proof_path')->nullable();
            $table->timestamp('paid_at');
            $table->foreignId('received_by')->constrained('users')->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('order_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->cascadeOnDelete();
            $table->enum('approval_type', ['price', 'discount', 'cancel', 'production_done', 'transfer', 'stock_adjustment']);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->cascadeOnDelete();
            $table->string('status_from', 30)->nullable();
            $table->string('status_to', 30);
            $table->foreignId('changed_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('production_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('users')->restrictOnDelete();
            $table->string('status_from', 30)->nullable();
            $table->string('status_to', 30)->nullable();
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->text('notes')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('order_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('original_order_id')->constrained('orders')->restrictOnDelete();
            $table->foreignId('original_order_item_id')->constrained('order_items')->restrictOnDelete();
            $table->enum('transfer_type', ['next_order', 'ready_stock', 'other']);
            $table->foreignId('new_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('new_order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->boolean('dp_forfeited')->default(true);
            $table->foreignId('transferred_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('transferred_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_transfers');
        Schema::dropIfExists('production_logs');
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('order_approvals');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_item_materials');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
