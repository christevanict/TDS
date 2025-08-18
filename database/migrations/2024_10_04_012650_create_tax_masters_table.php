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
        Schema::create('tax_master', function (Blueprint $table) {
            $table->id();
            $table->string('tax_code')->unique();
            $table->string('tax_name');
            $table->float('tariff')->comment('in percentage');
            $table->string('tax_base');
            $table->string('account_number')->comment('reference account_number_account_income_tax(DB) in sales_invoice');
            $table->string('company_code')->unsigned();
            $table->string('created_by');
            $table->string('updated_by');
            $table->timestamps();
        });
        Schema::table('tax_master', function($table) {
            $table->foreign('company_code')->references('company_code')->on('company');
            $table->foreign('account_number')->references('account_number')->on('coa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_master');
    }
};
