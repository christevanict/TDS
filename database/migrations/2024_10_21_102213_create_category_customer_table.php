<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryCustomerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_customers', function (Blueprint $table) {
            // Replace the default id with category_code
            $table->string('category_code')->primary(); // Using category_code as the primary key
            $table->string('category_name');
            $table->string('company_code');
            $table->string('created_by')->nullable(); // Store the ID of the user who created the record
            $table->string('updated_by')->nullable(); // Store the ID of the user who last updated the record
            $table->timestamps(); // Create created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('category_customers');
    }
}
