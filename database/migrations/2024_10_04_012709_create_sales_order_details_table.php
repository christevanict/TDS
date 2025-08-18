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
        Schema::create('sales_order_detail', function (Blueprint $table) {
            $table->id();
            $table->string('sales_order_number');
            $table->string('item_id');
            $table->string('status')->nullable();
            $table->decimal('qty',20,2);
            $table->string('unit');
            $table->decimal('price',20,2);
            $table->decimal('disc_percent',20,2)->nullable();
            $table->decimal('disc_nominal',20,2)->nullable();
            $table->decimal('nominal',20,2);
            $table->decimal('number_row',20,2)->nullable();
            $table->decimal('base_qty',20,2);
            $table->string('base_unit');
            $table->decimal('qty_left',20,2)->nullable();
            $table->decimal('base_qty_left',20,2)->nullable();
            $table->string('company_code')->unsigned();
            $table->string('department_code');
            $table->string('created_by');
            $table->string('updated_by');
            $table->string('description')->nullable();
            $table->timestamps();
        });
        Schema::table('sales_order_detail', function($table) {
            $table->foreign('company_code')->references('company_code')->on('company');
            $table->foreign('item_id')->references('item_code')->on('item');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_order_detail');
    }
};
