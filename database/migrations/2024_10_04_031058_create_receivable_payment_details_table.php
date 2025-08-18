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
        Schema::create('receivable_payment_detail', function (Blueprint $table) {
            $table->id();
            $table->string('receivable_payment_number');
            $table->dateTime('receivable_payment_date');
            $table->string('receivable_payment_detail_id');
            $table->string('customer_code');
            $table->string('document_number');
            $table->dateTime('document_date');
            $table->decimal('document_nominal',20,2);
            $table->decimal('document_payment',20,2);
            $table->decimal('discount',20,2);
            $table->decimal('nominal',20,2);
            $table->string('company_code')->unsigned();
            $table->string('department_code');
            $table->string('created_by');
            $table->string('updated_by');
            $table->timestamps();
        });
        Schema::table('receivable_payment_detail', function($table) {
            $table->foreign('company_code')->references('company_code')->on('company');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receivable_payment_detail');
    }
};
