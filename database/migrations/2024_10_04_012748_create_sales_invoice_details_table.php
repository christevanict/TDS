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
        Schema::create('sales_invoice_detail', function (Blueprint $table) {
            $table->id();
            $table->string('sales_invoice_number');
            $table->string('item_id');
            $table->string('delivery_order_number')->nullable();
            $table->decimal('qty',20,2);
            $table->string('unit');
            $table->float('price');
            $table->float('disc_percent');
            $table->decimal('disc_nominal',20,2);
            $table->decimal('nominal',20,2);
            $table->decimal('base_qty',20,2);
            $table->string('base_unit');
            $table->decimal('qty_left',20,2);
            $table->decimal('disc_header',20,2);
            $table->decimal('base_qty_left',20,2);
            $table->string('company_code')->nullable();
            $table->string('status')->nullable();
            $table->string('description')->nullable();
            $table->string('department_code');
            $table->string('acc_number_cogs')->nullable()->comment('DB, reference journal(account_number)');
            $table->string('acc_number_sales')->nullable()->comment('CR, reference tax_master(account_number)');
            $table->string('acc_number_inventory')->nullable()->comment('CR, reference tax_master(account_number)');
            $table->string('created_by');
            $table->string('updated_by');
            $table->string('warehouse_code');
            $table->timestamps();
        });

        Schema::table('sales_invoice_detail', function($table) {
            $table->foreign('company_code')->references('company_code')->on('company');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_invoice_detail');
    }
};
