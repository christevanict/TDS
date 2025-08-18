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
        Schema::create('supplier', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_code')->unique();
            $table->string('supplier_name');
            $table->text('address');
            $table->text('warehouse_address');
            $table->string('phone_number');
            $table->boolean('pkp');
            $table->boolean('include');
            $table->string('currency_code')->nullable();
            $table->text('account_payable');
            $table->text('account_dp');
            $table->string('account_payable_grpo');
            $table->string('account_add_tax');
            $table->string('company_code')->unsigned();
            $table->string('created_by');
            $table->string('updated_by');
            $table->timestamps();
        });
        Schema::table('supplier', function($table) {
            $table->foreign('company_code')->references('company_code')->on('company');
            $table->foreign('account_payable')->references('account_number')->on('coa');
            $table->foreign('account_dp')->references('account_number')->on('coa');
            $table->foreign('account_payable_grpo')->references('account_number')->on('coa');
            $table->foreign('account_add_tax')->references('account_number')->on('coa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier');
    }
};
