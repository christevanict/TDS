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
        Schema::create('purchase_invoice', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_invoice_number');
            $table->dateTime('document_date');
            $table->dateTime('due_date');
            $table->dateTime('delivery_date');
            $table->string('supplier_code');
            $table->string('purchase_order_number')->nullable();
            $table->string('manual_number')->nullable();
            $table->string('tax');
            $table->string('tax_revenue_tariff');
            $table->boolean('include');
            $table->decimal('subtotal',20,2);
            $table->decimal('disc_percent',20,2)->nullable();
            $table->decimal('disc_nominal',20,2);
            $table->decimal('tax_revenue',20,2);
            $table->decimal('add_tax',20,2);
            $table->decimal('total',20,2);
            $table->text('notes')->nullable();
            $table->string('account_number')->comment('CR')->nullable();
            $table->string('company_code')->nullable();
            $table->string('department_code');
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::table('purchase_invoice', function($table) {
            $table->foreign('company_code')->references('company_code')->on('company');
            $table->foreign('supplier_code')->references('supplier_code')->on('supplier')->onUpdate('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_invoice');
    }
};
