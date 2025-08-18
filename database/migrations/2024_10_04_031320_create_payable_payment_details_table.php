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
        Schema::create('payable_payment_detail', function (Blueprint $table) {
            $table->id();
            $table->string('payable_payment_number')->unsigned();
            $table->string('payable_payment_detail_id');
            $table->dateTime('payable_payment_date');
            $table->string('supplier_code');
            $table->string('document_number');
            $table->dateTime('document_date');
            $table->decimal('document_nominal',20,2);
            $table->decimal('document_payment',20,2);
            $table->decimal('nominal_payment',20,2);
            $table->decimal('discount',20,2);
            $table->decimal('balance',20,2);
            $table->decimal('acc_debt',20,2)->nullable();
            $table->string('company_code')->unsigned();
            $table->string('department_code');
            $table->string('created_by');
            $table->string('updated_by');
            $table->timestamps();
        });

        Schema::table('payable_payment_detail', function($table) {
            $table->foreign('company_code')->references('company_code')->on('company');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payable_payment_detail');
    }
};
