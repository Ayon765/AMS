<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->boolean('is_admin')->default(false);
            $t->json('permissions')->nullable();
            $t->timestamps();
        });

        Schema::create('settings', function (Blueprint $t) {
            $t->id();
            $t->string('key')->unique();
            $t->text('value')->nullable();
            $t->timestamps();
        });

        Schema::create('branches', function (Blueprint $t) {
            $t->id();
            $t->string('code')->nullable()->unique();
            $t->string('name');
            $t->string('phone')->nullable();
            $t->string('address')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        foreach (['designations', 'departments', 'categories', 'brands', 'conditions'] as $table) {
            Schema::create($table, function (Blueprint $t) {
                $t->id();
                $t->string('name')->unique();
                $t->timestamps();
            });
        }

        Schema::create('uoms', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->string('short_name')->nullable();
            $t->timestamps();
        });

        Schema::create('warranties', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->unsignedInteger('months')->default(0);
            $t->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('contact_person')->nullable();
            $t->string('phone')->nullable();
            $t->string('email')->nullable();
            $t->string('address')->nullable();
            $t->timestamps();
        });

        Schema::create('employees', function (Blueprint $t) {
            $t->id();
            $t->string('code')->nullable()->unique();
            $t->string('name');
            $t->string('phone')->nullable();
            $t->string('email')->nullable();
            $t->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('designation_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $t->date('joining_date')->nullable();
            $t->string('address')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->string('code')->nullable()->unique();
            $t->string('name');
            $t->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('uom_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('condition_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('warranty_id')->nullable()->constrained()->nullOnDelete();
            $t->decimal('purchase_price', 12, 2)->default(0);
            $t->decimal('sale_price', 12, 2)->default(0);
            $t->unsignedInteger('min_stock')->default(0);
            $t->text('description')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('stocks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $t->decimal('qty', 12, 2)->default(0);
            $t->timestamps();
            $t->unique(['product_id', 'branch_id']);
        });

        Schema::create('stock_documents', function (Blueprint $t) {
            $t->id();
            $t->string('type')->index(); // purchase, delivery, adjustment, damage, transfer, proposal, invoice
            $t->string('ref_no')->nullable()->unique();
            $t->date('doc_date');
            $t->foreignId('branch_id')->constrained();
            $t->foreignId('to_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $t->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $t->string('customer_name')->nullable();
            $t->string('customer_phone')->nullable();
            $t->string('status')->default('pending');
            $t->decimal('subtotal', 14, 2)->default(0);
            $t->decimal('discount', 14, 2)->default(0);
            $t->decimal('total', 14, 2)->default(0);
            $t->decimal('paid', 14, 2)->default(0);
            $t->text('note')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->unsignedBigInteger('approved_by')->nullable();
            $t->timestamp('approved_at')->nullable();
            $t->timestamps();
        });

        Schema::create('stock_document_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('stock_document_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained();
            $t->decimal('qty', 12, 2);
            $t->decimal('price', 12, 2)->default(0);
            $t->string('direction')->nullable(); // adjustment: in / out
        });

        Schema::create('cash_entries', function (Blueprint $t) {
            $t->id();
            $t->string('type'); // in / out
            $t->date('entry_date');
            $t->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $t->decimal('amount', 14, 2);
            $t->string('head');
            $t->text('note')->nullable();
            $t->unsignedBigInteger('stock_document_id')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['cash_entries', 'stock_document_items', 'stock_documents', 'stocks', 'products', 'employees',
                  'suppliers', 'warranties', 'uoms', 'conditions', 'brands', 'categories', 'departments',
                  'designations', 'branches', 'settings', 'roles'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
