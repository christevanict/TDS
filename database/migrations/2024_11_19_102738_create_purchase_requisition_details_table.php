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
        Schema::create('purchase_requisition_detail', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_requisition_number');
            $table->string('item_id');
            $table->string('unit');
            $table->decimal('qty',20,2);
            $table->string('base_unit');
            $table->decimal('base_qty',20,2);
            $table->string('notes')->nullable();
            $table->string('department_code');
            $table->string('created_by');
            $table->string('updated_by');
            $table->string('status');
            $table->decimal('qty_left');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_requisition_details');
    }
};
