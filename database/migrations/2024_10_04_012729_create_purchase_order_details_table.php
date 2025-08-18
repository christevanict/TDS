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
        Schema::create('purchase_order_detail', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_order_number');
            $table->string('purchase_requisition_number');
            $table->string('item_id');
            $table->decimal('qty',20,2);
            $table->string('unit');
            $table->decimal('price',20,2);
            $table->decimal('disc_percent',20,2);
            $table->decimal('disc_nominal',20,2);
            $table->decimal('nominal',20,2);
            $table->decimal('number_row',20,2);
            $table->decimal('base_qty',20,2);
            $table->string('base_unit');
            $table->decimal('qty_left',20,2);
            $table->decimal('base_qty_left',20,2);
            $table->string('company_code')->nullable();
            $table->string('department_code');
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::table('purchase_order_detail', function($table) {
            $table->foreign('company_code')->references('company_code')->on('company');
            $table->foreign('item_id')->references('item_code')->on('item');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_detail');
    }
};
