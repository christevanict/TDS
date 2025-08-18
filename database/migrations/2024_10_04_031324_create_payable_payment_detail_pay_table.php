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
        Schema::create('payable_payment_detail_pay', function (Blueprint $table) {
            $table->id();
            $table->string('payable_payment_number');
            $table->dateTime('payable_payment_date');
            $table->string('payable_payment_detail_id');
            $table->string('payment_method');
            $table->integer('payment_nominal');
            $table->string('bg_check_number')->nullable();
            $table->string('acc_debt_bg');
            $table->string('company_code')->unsigned();
            $table->string('department_code');
            $table->string('created_by');
            $table->string('updated_by');
            $table->timestamps();
        });

        Schema::table('payable_payment_detail_pay', function($table) {
            $table->foreign('company_code')->references('company_code')->on('company');
            $table->foreign('acc_debt_bg')->references('account_number')->on('coa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payable_payment_detail_pay');
    }
};
