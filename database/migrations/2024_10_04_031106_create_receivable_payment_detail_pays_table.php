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
        Schema::create('receivable_payment_detail_pay', function (Blueprint $table) {
            $table->id();
            $table->string('receivable_payment_number');
            $table->dateTime('receivable_payment_date');
            $table->string('receivable_payment_detail_id');
            $table->string('payment_method')->unsigned();
            $table->decimal('payment_nominal',20,2);
            $table->string('bg_check_number');
            $table->string('company_code')->unsigned();
            $table->string('department_code');
            $table->string('created_by');
            $table->string('updated_by');
            $table->timestamps();
        });
        Schema::table('receivable_payment_detail_pay', function($table) {
            $table->foreign('company_code')->references('company_code')->on('company');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receivable_payment_detail_pay');
    }
};
