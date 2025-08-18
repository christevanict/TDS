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
        Schema::create('sales_order', function (Blueprint $table) {
            $table->id();
            $table->string('sales_order_number')->unique();
            $table->string('manual_number')->nullable();
            $table->dateTime('document_date');
            $table->string('status')->nullable();
            $table->dateTime('eta_date')->nullable();
            $table->dateTime('delivery_date');
            $table->dateTime('due_date');
            $table->string('customer_code')->nullable();
            $table->string('tax')->nullable();
            $table->boolean('include')->nullable();
            $table->decimal('subtotal',20,2);
            $table->decimal('disc_nominal',20,2)->nullable();
            $table->decimal('tax_revenue',20,2)->nullalbe();
            $table->decimal('add_tax',20,2)->nullalbe();
            $table->decimal('total',20,2);
            $table->string('company_code')->unsigned();
            $table->string('department_code');
            $table->text('notes')->nullable();
            $table->text('cancel_notes')->nullable();
            $table->string('created_by');
            $table->string('updated_by');
            $table->timestamps();
        });
        Schema::table('sales_order', function($table) {
            $table->foreign('company_code')->references('company_code')->on('company');
            $table->foreign('department_code')->references('department_code')->on('department');
            $table->foreign('customer_code')->references('customer_code')->on('customer')->onUpdate('cascade');
            $table->foreign('tax')->references('tax_code')->on('tax_master');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_order');
    }
};
