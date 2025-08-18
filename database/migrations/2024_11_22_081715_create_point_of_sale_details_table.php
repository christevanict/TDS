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
        Schema::create('point_of_sale_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('point_of_sale_id');
            $table->string('item_id');
            $table->string('item_name', 100);
            $table->decimal('price', 10, 2);
            $table->integer('quantity');
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });

        
    }

    public function down(): void
    {
        Schema::dropIfExists('point_of_sale_details');
    }
};
