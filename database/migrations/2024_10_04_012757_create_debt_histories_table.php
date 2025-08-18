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
        Schema::create('debt_history', function (Blueprint $table) {
            $table->id();
            $table->string('document_number');
            $table->dateTime('document_date');
            $table->string('supplier_code');
            $table->string('payment_number');
            $table->string('payment_method')->nullable();
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

        Schema::table('debt_history', function($table) {
            $table->foreign('company_code')->references('company_code')->on('company');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debt_history');
    }
};
