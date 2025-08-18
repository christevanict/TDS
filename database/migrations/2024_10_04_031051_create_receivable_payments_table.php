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
        Schema::create('receivable_payment', function (Blueprint $table) {
            $table->id();
            $table->string('receivable_payment_number');
            $table->dateTime('receivable_payment_date');
            $table->string('customer_code');
            $table->decimal('total_debt',20,2);
            $table->string('acc_total_disc')->nullable();
            $table->string('acc_disc')->nullable();
            $table->string('company_code')->unsigned();
            $table->string('department_code');
            $table->string('created_by');
            $table->string('updated_by');
            $table->timestamps();
        });
        Schema::table('receivable_payment', function($table) {
            $table->foreign('company_code')->references('company_code')->on('company');
            $table->foreign('acc_total_disc')->references('account_number')->on('coa');
            $table->foreign('acc_disc')->references('account_number')->on('coa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receivable_payment');
    }
};
