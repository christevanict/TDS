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
        Schema::create('customer', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code')->unique();
            $table->string('customer_name');
            $table->string('address')->nullable();
            $table->string('warehouse_address')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('city')->nullable();
            $table->string('zone')->nullable();
            $table->string('sales')->nullable();
            $table->string('npwp')->nullable();
            $table->string('nik')->nullable();
            $table->string('email')->nullable();
            $table->boolean('pkp');
            $table->boolean('include');
            $table->boolean('bonded_zone')->nullable();
            $table->string('currency_code')->nullable();
            $table->string('group_customer')->nullable();
            $table->string('category_customer')->nullable();
            $table->text('account_receivable');
            $table->text('account_dp');
            $table->text('account_add_tax');
            $table->text('account_add_tax_bonded_zone');
            $table->string('company_code')->unsigned();
            $table->string('created_by');
            $table->string('updated_by');
            $table->timestamps();
        });

        Schema::table('customer', function($table) {
            $table->foreign('company_code')->references('company_code')->on('company');
            $table->foreign('account_receivable')->references('account_number')->on('coa');
            $table->foreign('account_dp')->references('account_number')->on('coa');
            $table->foreign('account_add_tax')->references('account_number')->on('coa');
            $table->foreign('account_add_tax_bonded_zone')->references('account_number')->on('coa');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer');
    }
};
