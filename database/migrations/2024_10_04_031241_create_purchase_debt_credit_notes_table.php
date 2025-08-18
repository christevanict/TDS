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
        Schema::create('purchase_debt_credit_note', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_credit_note_number');
            $table->dateTime('purchase_credit_note_date');
            $table->string('supplier_code')->nullable();
            $table->string('invoice_number');
            $table->decimal('total',20,2);
            $table->string('account_payable');
            $table->string('company_code')->unsigned();
            $table->string('department_code');
            $table->string('created_by');
            $table->string('updated_by');
            $table->string('status');
            $table->timestamps();
        });

        Schema::table('purchase_debt_credit_note', function($table) {
            $table->foreign('company_code')->references('company_code')->on('company');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_debt_credit_note');
    }
};
