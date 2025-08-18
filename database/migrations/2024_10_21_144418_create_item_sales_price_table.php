<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_sales_price', function (Blueprint $table) {
            $table->id();
            $table->string('barcode')->nullable(); // Nullable barcode
            $table->string('item_code'); // Foreign key from Item Model
            $table->decimal('sales_price', 20, 2); // Sales price with precision
            $table->string('unit'); // Foreign key from Unit Model
            $table->string('category_customer')->nullable(); // Customer category
            $table->string('company_code'); // Foreign key from Company Model
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps(); // Created at and Updated at

            // Optional: Add indexes or foreign key constraints if necessary
            // $table->foreign('item_code')->references('code')->on('items');
            // $table->foreign('company_code')->references('code')->on('companies');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item_sales_price');
    }
};
