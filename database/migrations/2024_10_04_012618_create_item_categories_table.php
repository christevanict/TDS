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
        Schema::create('item_category', function (Blueprint $table) {
            $table->id();
            $table->string('item_category_code');
            $table->string('item_category_name');
            $table->string('company_code');
            $table->text('acc_number_purchase');
            $table->text('acc_number_purchase_return');
            $table->text('acc_number_purchase_discount');
            $table->text('acc_number_sales');
            $table->text('acc_number_sales_return');
            $table->text('acc_number_sales_discount');
            $table->text('acc_number_grpo');
            $table->text('acc_number_do');
            $table->text('acc_number_wip');
            $table->text('acc_number_wip_variance');
            $table->string('account_inventory');
            $table->string('acc_cogs');
            $table->string('created_by');
            $table->string('updated_by');
            $table->timestamps();
        });
        Schema::table('item_category', function($table) {
            $table->foreign('company_code')->references('company_code')->on('company');
            $table->foreign('acc_number_purchase')->references('account_number')->on('coa');
            $table->foreign('acc_number_purchase_return')->references('account_number')->on('coa');
            $table->foreign('acc_number_purchase_discount')->references('account_number')->on('coa');
            $table->foreign('acc_number_sales')->references('account_number')->on('coa');
            $table->foreign('acc_number_sales_return')->references('account_number')->on('coa');
            $table->foreign('acc_number_sales_discount')->references('account_number')->on('coa');
            $table->foreign('acc_number_grpo')->references('account_number')->on('coa');
            $table->foreign('acc_number_do')->references('account_number')->on('coa');
            $table->foreign('acc_number_wip')->references('account_number')->on('coa');
            $table->foreign('acc_number_wip_variance')->references('account_number')->on('coa');
            $table->foreign('account_inventory')->references('account_number')->on('coa');
            $table->foreign('acc_cogs')->references('account_number')->on('coa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_category');
    }
};
