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
        Schema::create('payment_method', function (Blueprint $table) {
            $table->id();
            $table->string('payment_method_code');
            $table->string('payment_name');
            $table->string('cost_payment');
            $table->string('account_number');
            $table->string('acc_number_cost');
            $table->string('company_code')->unsigned();
            $table->string('created_by');
            $table->string('updated_by');
            $table->timestamps();
        });
        Schema::table('payment_method', function($table) {
            $table->foreign('company_code')->references('company_code')->on('company');
            $table->foreign('account_number')->references('account_number')->on('coa');
            $table->foreign('acc_number_cost')->references('account_number')->on('coa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_method');
    }
};
