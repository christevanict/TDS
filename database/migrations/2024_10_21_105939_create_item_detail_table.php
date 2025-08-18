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
        Schema::create('item_details', function (Blueprint $table) {
            $table->id();
            $table->string('item_code'); // Foreign key from Item Model
            $table->string('base_unit'); // Foreign key from Item Unit Model
            $table->decimal('conversion', 20, 2); // Conversion rate
            $table->string('unit_conversion'); // Foreign key from Item Unit Model for conversion unit
            $table->boolean('status')->nullable(); // Nullable barcode
            $table->string('barcode')->nullable(); // Nullable barcode
            $table->string('company_code'); // Foreign key from Company Model
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::dropIfExists('item_details');
    }
};
