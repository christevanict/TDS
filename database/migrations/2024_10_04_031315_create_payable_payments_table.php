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
        Schema::create('payable_payment', function (Blueprint $table) {
            $table->id();
            $table->string('payable_payment_number');
            $table->dateTime('payable_payment_date');
            $table->string('supplier_code');
            $table->decimal('total_debt',20,2);
            $table->string('acc_total')->nullable();
            $table->string('acc_disc')->nullable();
            $table->string('company_code')->unsigned();
            $table->string('department_code');
            $table->string('created_by');
            $table->string('updated_by');
            $table->timestamps();
        });

        Schema::table('payable_payment', function($table) {
            $table->foreign('company_code')->references('company_code')->on('company');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payable_payment');
    }
};
