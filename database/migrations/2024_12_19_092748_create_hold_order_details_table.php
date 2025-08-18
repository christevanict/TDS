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
        Schema::create('hold_orders_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hold_order_id');
            $table->string('item');
            $table->decimal('price', 10, 2);
            $table->integer('quantity');
            $table->decimal('total', 15, 2);
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('hold_order_id')->references('id')->on('hold_orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hold_orders_detail');
    }
};
