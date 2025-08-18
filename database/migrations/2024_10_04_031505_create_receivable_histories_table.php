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
        Schema::create('receivable_history', function (Blueprint $table) {
            $table->id();
            $table->string('document_number')->comment('reference sales_invoice_detail(sales_invoice_number)');
            $table->dateTime('document_date')->comment('reference sales_invoice_detail(document_date)');
            $table->string('customer_code');
            $table->string('payment_number');
            $table->string('payment_method');
            $table->dateTime('payment_date');
            $table->decimal('total_debt',20,2);
            $table->string('payment');
            $table->decimal('debt_balance',20,2);
            $table->string('company_code')->unsigned();
            $table->string('department_code');
            $table->string('created_by');
            $table->string('updated_by');
            $table->timestamps();
        });

        Schema::table('receivable_history', function($table) {
            $table->foreign('company_code')->references('company_code')->on('company');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receivable_history');
    }
};
