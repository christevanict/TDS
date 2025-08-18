<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_order', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_order_number');
            $table->string('purchase_requisition_number')->nullable();
            $table->string('sales_order_number')->nullable();
            $table->dateTime('document_date');
            $table->dateTime('delivery_date');
            $table->dateTime('due_date')->nullable();
            $table->string('supplier_code');
            $table->string('tax')->nullable();
            $table->string('tax_revenue_tariff')->nullable();
            $table->boolean('include');
            $table->decimal('subtotal',20,2);
            $table->float('disc_percent');
            $table->decimal('disc_nominal',20,2);
            $table->decimal('tax_revenue',20,2)->nullable();
            $table->decimal('add_tax',20,2)->nullable();
            $table->decimal('total',20,2);
            $table->text('notes')->nullable();
            $table->string('status');
            $table->string('company_code')->unsigned();
            $table->string('department_code');
            $table->string('created_by');
            $table->string('updated_by');
            $table->string('cancel_notes')->nullable();
            $table->timestamps();
        });

        Schema::table('purchase_order', function($table) {
            $table->foreign('company_code')->references('company_code')->on('company');
            $table->foreign('department_code')->references('department_code')->on('department');
            $table->foreign('supplier_code')->references('supplier_code')->on('supplier')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order');
    }
};
