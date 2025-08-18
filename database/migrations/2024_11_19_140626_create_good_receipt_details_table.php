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
        Schema::create('good_receipt_detail', function (Blueprint $table) {
            $table->id();
            $table->string('good_receipt_number');
            $table->string('item_id');
            $table->decimal('qty', 20, 2);
            $table->string('unit');
            $table->integer('number_row')->nullable();
            $table->decimal('base_qty');
            $table->string('base_unit');
            $table->string('department_code');
            $table->decimal('nominal',20,2)->nullable();
            $table->text('purchase_order_number')->nullable();
            $table->text('description')->nullable();
            $table->string('created_by');
            $table->string('updated_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('good_receipt_details');
    }
};
