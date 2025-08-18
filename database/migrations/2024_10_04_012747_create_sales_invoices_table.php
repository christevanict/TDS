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
        Schema::create('sales_invoice', function (Blueprint $table) {
            $table->id();
            $table->string('sales_invoice_number');
            $table->string('sales_order_number')->nullable();
            $table->dateTime('document_date');
            $table->dateTime('delivery_date');
            $table->dateTime('due_date');
            $table->string('customer_code');
            $table->string('tax');
            $table->string('manual_number');
            $table->boolean('include')->nullable();
            $table->decimal('subtotal',20,2);
            $table->decimal('disc_nominal',20,2)->nullable();
            $table->decimal('tax_revenue',20,2)->nullable();
            $table->decimal('add_tax',20,2)->nullable();
            $table->decimal('total',20,2);
            $table->string('company_code')->nullable();
            $table->string('status')->nullable();
            $table->string('department_code');
            $table->string('tax_revenue_tariff');
            $table->text('notes')->nullable();
            $table->string('reason');
            $table->string('acc_number_acc_receivable')->nullable()->comment('DB');
            $table->string('acc_number_acc_add_tax')->nullable()->comment('CR, reference to tax_master(account_number)');
            $table->string('acc_number_acc_income_tax')->nullable()->comment('DB, reference to tax_master(account_number)');
            $table->string('created_by');
            $table->string('updated_by');
            $table->boolean('reimburse_status')->nullable();
            $table->timestamps();
        });

        Schema::table('sales_invoice', function($table) {
            $table->foreign('company_code')->references('company_code')->on('company');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_invoice');
    }
};
